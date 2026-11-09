<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CitaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Cita
    {
        return Cita::query()->create($data);
    }

    /**
     * @param  Collection<int, Servicio>  $servicios
     */
    public function schedule(
        Mascota $mascota,
        Propietario $propietario,
        Profesional $profesional,
        Collection $servicios,
        CarbonImmutable $inicio,
        bool $urgente,
        ?string $motivo,
        int $userId,
    ): Cita {
        if ($servicios->isEmpty()) {
            throw ValidationException::withMessages([
                'servicios' => 'Selecciona al menos un servicio.',
            ]);
        }

        $duracion = (int) $servicios->sum('duracion_minutos');

        if ($duracion < 1) {
            throw ValidationException::withMessages([
                'servicios' => 'Los servicios seleccionados no tienen una duración válida.',
            ]);
        }

        $fin = $inicio->addMinutes($duracion);
        $esCirugia = $this->includesSurgery($servicios);

        return DB::transaction(function () use (
            $mascota,
            $propietario,
            $profesional,
            $servicios,
            $inicio,
            $fin,
            $esCirugia,
            $urgente,
            $motivo,
            $userId,
        ): Cita {
            Profesional::query()
                ->whereKey($profesional->id)
                ->lockForUpdate()
                ->firstOrFail();

            $citasCercanas = Cita::query()
                ->where('profesional_id', $profesional->id)
                ->where('estado', '!=', 'cancelada')
                ->where('inicio', '<', $fin->addMinutes(90)->toDateTimeString())
                ->where('fin', '>', $inicio->subMinutes(90)->toDateTimeString())
                ->with('servicios.categoria')
                ->get();

            foreach ($citasCercanas as $citaExistente) {
                $separacion = $esCirugia || $this->includesSurgery($citaExistente->servicios)
                    ? 90
                    : 30;

                if (
                    $citaExistente->inicio->lt($fin->addMinutes($separacion))
                    && $citaExistente->fin->gt($inicio->subMinutes($separacion))
                ) {
                    throw ValidationException::withMessages([
                        'inicio' => 'El profesional necesita más tiempo de separación respecto a otra cita.',
                    ]);
                }
            }

            $recargoFueraDeHorario = $inicio->format('H:i') < '08:00'
                || $fin->format('H:i') > '20:00'
                || ! $inicio->isSameDay($fin);

            $cita = Cita::query()->create([
                'folio' => 'CIT-'.Str::upper((string) Str::ulid()),
                'mascota_id' => $mascota->id,
                'propietario_id' => $propietario->id,
                'profesional_id' => $profesional->id,
                'inicio' => $inicio,
                'fin' => $fin,
                'estado' => 'pendiente',
                'urgente' => $urgente,
                'motivo' => $motivo,
                'creado_por' => $userId,
            ]);

            $cita->servicios()->attach($servicios->mapWithKeys(
                fn (Servicio $servicio): array => [
                    $servicio->id => [
                        'precio' => $recargoFueraDeHorario
                            ? round((float) $servicio->precio * 1.3, 2)
                            : $servicio->precio,
                        'duracion_minutos' => $servicio->duracion_minutos,
                    ],
                ],
            )->all());

            return $cita->load(['mascota', 'propietario', 'profesional', 'servicios.categoria']);
        }, attempts: 3);
    }

    public function findById(int $id): ?Cita
    {
        return Cita::query()->find($id);
    }

    /**
     * @return Collection<int, Cita>
     */
    public function listForMascota(Mascota $mascota): Collection
    {
        return $mascota->citas()->orderBy('inicio')->get();
    }

    /**
     * @return Collection<int, Cita>
     */
    public function listForProfesional(Profesional $profesional): Collection
    {
        return $profesional->citas()->orderBy('inicio')->get();
    }

    /**
     * @return Collection<int, Cita>
     */
    public function listForOwner(Propietario $propietario): Collection
    {
        return $propietario->citas()->orderBy('inicio')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Cita $cita, array $data): Cita
    {
        $cita->update($data);

        return $cita->fresh();
    }

    public function cancel(Cita $cita): Cita
    {
        if (! in_array($cita->estado, ['pendiente', 'confirmada'], true)) {
            throw ValidationException::withMessages([
                'cita' => 'Solo se pueden cancelar citas pendientes o confirmadas.',
            ]);
        }

        $cita->update(['estado' => 'cancelada']);

        return $cita->refresh();
    }

    /**
     * @param  Collection<int, Servicio>  $servicios
     */
    private function includesSurgery(Collection $servicios): bool
    {
        return $servicios->contains(function (Servicio $servicio): bool {
            $categoria = Str::ascii(Str::lower($servicio->categoria?->nombre ?? ''));

            return Str::contains($categoria, 'cirug');
        });
    }
}
