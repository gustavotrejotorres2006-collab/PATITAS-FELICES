<?php

namespace App\Services;

use App\Models\Atencion;
use App\Models\AtencionDiagnostico;
use App\Models\CatalogoVacuna;
use App\Models\Cita;
use App\Models\Diagnostico;
use App\Models\NotaClinica;
use App\Models\Profesional;
use App\Models\SignosVitales;
use App\Models\User;
use App\Models\Vacunacion;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClinicaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createAtencion(array $data): Atencion
    {
        return Atencion::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addNota(array $data): NotaClinica
    {
        return NotaClinica::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDiagnostico(array $data): Diagnostico
    {
        return Diagnostico::query()->create($data);
    }

    /**
     * @return Collection<int, Atencion>
     */
    public function listAtencionesOpen(): Collection
    {
        return Atencion::query()->where('estado', 'abierta')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  Collection<int, CatalogoVacuna>  $vacunas
     */
    public function recordAppointment(
        Cita $cita,
        Profesional $profesional,
        User $user,
        array $data,
        Collection $vacunas,
        InventarioService $inventarioService,
    ): Atencion {
        return DB::transaction(function () use ($cita, $profesional, $user, $data, $vacunas, $inventarioService): Atencion {
            $cita = Cita::query()->whereKey($cita->id)->lockForUpdate()->firstOrFail();

            if (
                $cita->profesional_id !== $profesional->id
                || ! in_array($cita->estado, ['pendiente', 'confirmada'], true)
                || $cita->atenciones()->exists()
            ) {
                throw ValidationException::withMessages([
                    'cita' => 'Esta cita ya fue atendida o no está asignada a tu perfil.',
                ]);
            }

            if ($vacunas->count() !== count($data['vacunas_aplicadas'] ?? [])) {
                throw ValidationException::withMessages([
                    'vacunas_aplicadas' => 'Una o más vacunas seleccionadas ya no están disponibles.',
                ]);
            }

            $atencion = Atencion::query()->create([
                'folio' => 'ATN-'.Str::upper((string) Str::ulid()),
                'cita_id' => $cita->id,
                'mascota_id' => $cita->mascota_id,
                'propietario_id' => $cita->propietario_id,
                'profesional_id' => $profesional->id,
                'creado_por' => $user->id,
                'fecha_atencion' => CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['fecha_atencion']),
                'estado' => 'abierta',
                'motivo' => $data['motivo'] ?? $cita->motivo,
                'hospitalizacion_recomendada' => (bool) ($data['hospitalizacion_recomendada'] ?? false),
            ]);

            AtencionDiagnostico::query()->create([
                'atencion_id' => $atencion->id,
                'diagnostico_personalizado' => $data['diagnostico'],
            ]);

            NotaClinica::query()->create([
                'atencion_id' => $atencion->id,
                'profesional_id' => $profesional->id,
                'autor_id' => $user->id,
                'contenido' => $data['nota'],
                'version' => 1,
            ]);

            $vitales = Arr::only($data, [
                'peso_kg',
                'temperatura_c',
                'frecuencia_cardiaca',
                'frecuencia_respiratoria',
                'presion_arterial',
                'condicion_corporal',
                'observaciones_vitales',
            ]);
            $vitales = array_filter($vitales, fn (mixed $value): bool => $value !== null);

            if ($vitales !== []) {
                $vitales['atencion_id'] = $atencion->id;
                $vitales['observaciones'] = $vitales['observaciones_vitales'] ?? null;
                unset($vitales['observaciones_vitales']);
                SignosVitales::query()->create($vitales);
            }

            foreach ($vacunas as $vacuna) {
                if (! $vacuna->producto || $vacuna->producto->estado !== 'activo') {
                    throw ValidationException::withMessages([
                        'vacunas_aplicadas' => "La vacuna {$vacuna->nombre} no tiene un producto activo vinculado al inventario.",
                    ]);
                }

                $movimientos = $inventarioService->consume(
                    $vacuna->producto,
                    1,
                    $user,
                    'Vacuna aplicada durante la atención '.$atencion->folio,
                    Atencion::class,
                    $atencion->id,
                );

                Vacunacion::query()->create([
                    'mascota_id' => $cita->mascota_id,
                    'vacuna_id' => $vacuna->id,
                    'atencion_id' => $atencion->id,
                    'fecha_aplicacion' => CarbonImmutable::today(),
                    'lote' => $movimientos->first()?->lote?->numero_lote,
                    'aplicada_por' => $user->id,
                ]);
            }

            $cita->update(['estado' => 'completada']);

            return $atencion;
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function appendNote(Atencion $atencion, Profesional $profesional, User $user, array $data): NotaClinica
    {
        return DB::transaction(function () use ($atencion, $profesional, $user, $data): NotaClinica {
            $atencion = Atencion::query()->whereKey($atencion->id)->lockForUpdate()->firstOrFail();
            $notaAnterior = $atencion->notas()->orderByDesc('version')->firstOrFail();

            $atencion->update([
                'hospitalizacion_recomendada' => (bool) ($data['hospitalizacion_recomendada'] ?? false),
            ]);

            return $atencion->notas()->create([
                'profesional_id' => $profesional->id,
                'autor_id' => $user->id,
                'contenido' => $data['contenido'],
                'version' => $notaAnterior->version + 1,
                'nota_anterior_id' => $notaAnterior->id,
            ]);
        }, attempts: 3);
    }
}
