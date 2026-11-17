<?php

namespace App\Services;

use App\Models\Atencion;
use App\Models\Cirugia;
use App\Models\CirugiaInsumo;
use App\Models\Cita;
use App\Models\EspacioClinico;
use App\Models\Hospitalizacion;
use App\Models\MonitoreoHospitalario;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HospitalizacionService
{
    public function __construct(private InventarioService $inventarioService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSpace(array $data, User $user): EspacioClinico
    {
        return EspacioClinico::query()->create([
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'],
            'estado' => 'disponible',
            'activo' => true,
            'creado_por' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleSurgery(array $data, User $user): Cirugia
    {
        return DB::transaction(function () use ($data, $user): Cirugia {
            $attention = Atencion::query()
                ->whereKey($data['atencion_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($attention->notas()->doesntExist()) {
                throw ValidationException::withMessages([
                    'atencion_id' => 'Registra una nota clínica antes de programar la cirugía.',
                ]);
            }

            if (! $attention->propietario_id) {
                throw ValidationException::withMessages([
                    'atencion_id' => 'La atención debe tener un propietario asociado para registrar su consentimiento.',
                ]);
            }

            if (
                isset($data['propietario_id'])
                && (int) $data['propietario_id'] !== (int) $attention->propietario_id
            ) {
                throw ValidationException::withMessages([
                    'propietario_id' => 'El consentimiento debe corresponder al propietario de la atención.',
                ]);
            }

            $space = EspacioClinico::query()
                ->whereKey($data['espacio_id'])
                ->where('tipo', 'quirofano')
                ->where('estado', 'disponible')
                ->where('activo', true)
                ->lockForUpdate()
                ->firstOrFail();
            $professionalIds = [(int) $data['cirujano_id'], (int) $data['anestesiologo_id']];

            if ($professionalIds[0] === $professionalIds[1]) {
                throw ValidationException::withMessages([
                    'anestesiologo_id' => 'El cirujano y el anestesiólogo deben ser profesionales distintos.',
                ]);
            }

            $professionals = Profesional::query()
                ->whereKey($professionalIds)
                ->where('estado', 'activo')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($professionals->count() !== 2) {
                throw ValidationException::withMessages([
                    'cirujano_id' => 'Selecciona profesionales activos para el procedimiento.',
                ]);
            }

            $start = CarbonImmutable::parse($data['programada_at']);
            $duration = (int) $data['duracion_minutos'];
            $end = $start->addMinutes($duration);
            $windowStart = $start->subMinutes(810);
            $windowEnd = $end->addMinutes(90);

            $surgeries = Cirugia::query()
                ->where('estado', 'programada')
                ->whereBetween('programada_at', [$windowStart, $windowEnd])
                ->where(function (Builder $query) use ($space, $professionalIds): void {
                    $query->where('espacio_id', $space->id)
                        ->orWhereIn('cirujano_id', $professionalIds)
                        ->orWhereIn('anestesiologo_id', $professionalIds);
                })
                ->get();

            foreach ($surgeries as $existingSurgery) {
                $existingStart = CarbonImmutable::instance($existingSurgery->programada_at);
                $existingEnd = $existingStart->addMinutes($existingSurgery->duracion_minutos);

                if ($existingStart->lt($windowEnd) && $existingEnd->gt($windowStart)) {
                    throw ValidationException::withMessages([
                        'programada_at' => 'El quirófano, el cirujano o el anestesiólogo ya tienen un procedimiento en ese horario.',
                    ]);
                }
            }

            $appointmentConflict = Cita::query()
                ->where('estado', '!=', 'cancelada')
                ->where('inicio', '<', $windowEnd)
                ->where('fin', '>', $windowStart)
                ->where(function (Builder $query) use ($professionalIds): void {
                    $query->whereIn('profesional_id', $professionalIds)
                        ->orWhereHas('profesionales', fn (Builder $participants) => $participants
                            ->whereIn('profesionales.id', $professionalIds));
                })
                ->exists();

            if ($appointmentConflict) {
                throw ValidationException::withMessages([
                    'programada_at' => 'El cirujano o el anestesiólogo tienen una cita que se cruza con el horario de cirugía.',
                ]);
            }

            return Cirugia::query()->create([
                'folio' => 'CIR-'.Str::upper((string) Str::ulid()),
                'atencion_id' => $attention->id,
                'espacio_id' => $space->id,
                'cirujano_id' => $professionalIds[0],
                'anestesiologo_id' => $professionalIds[1],
                'propietario_id' => $attention->propietario_id,
                'programada_at' => $start,
                'duracion_minutos' => $duration,
                'estado' => 'programada',
                'consentimiento_firmado_at' => now(),
                'consentimiento_verificado_por' => $user->id,
                'observaciones' => $data['observaciones'] ?? null,
            ]);
        }, attempts: 3);
    }

    /**
     * @param  array<int, array<string, mixed>>  $supplies
     */
    public function completeSurgery(Cirugia $surgery, array $supplies, User $user): Cirugia
    {
        return DB::transaction(function () use ($surgery, $supplies, $user): Cirugia {
            $surgery = Cirugia::query()->whereKey($surgery->id)->lockForUpdate()->firstOrFail();

            if ($surgery->estado !== 'programada') {
                throw ValidationException::withMessages([
                    'cirugia' => 'Solo se pueden completar cirugías programadas.',
                ]);
            }

            if (now()->lt($surgery->programada_at)) {
                throw ValidationException::withMessages([
                    'cirugia' => 'La cirugía no puede marcarse como terminada antes de la hora programada.',
                ]);
            }

            $space = EspacioClinico::query()
                ->whereKey($surgery->espacio_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($space->estado !== 'disponible') {
                throw ValidationException::withMessages([
                    'espacio' => 'El quirófano debe estar disponible y desinfectado para cerrar la cirugía.',
                ]);
            }

            foreach ($supplies as $index => $supply) {
                $product = Producto::query()
                    ->whereKey($supply['producto_id'])
                    ->where('estado', 'activo')
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        "insumos.{$index}.producto_id" => 'Selecciona un producto activo.',
                    ]);
                }

                $movements = $this->inventarioService->consume(
                    $product,
                    (float) $supply['cantidad'],
                    $user,
                    'Insumo utilizado en la cirugía '.$surgery->folio,
                    Cirugia::class,
                    $surgery->id,
                );

                foreach ($movements as $movement) {
                    CirugiaInsumo::query()->create([
                        'cirugia_id' => $surgery->id,
                        'producto_id' => $movement->producto_id,
                        'lote_id' => $movement->lote_id,
                        'movimiento_id' => $movement->id,
                        'numero_lote' => $movement->lote?->numero_lote,
                        'cantidad' => $movement->cantidad,
                        'costo_unitario' => $movement->costo_unitario,
                    ]);
                }
            }

            $surgery->update([
                'estado' => 'completada',
                'completada_at' => now(),
                'completada_por' => $user->id,
            ]);
            $space->update(['estado' => 'limpieza']);

            return $surgery->fresh(['espacio', 'insumos']);
        }, attempts: 3);
    }

    public function admit(Atencion $attention, EspacioClinico $space, User $user): Hospitalizacion
    {
        return DB::transaction(function () use ($attention, $space, $user): Hospitalizacion {
            $attention = Atencion::query()->whereKey($attention->id)->lockForUpdate()->firstOrFail();

            if (! $attention->hospitalizacion_recomendada) {
                throw ValidationException::withMessages([
                    'atencion_id' => 'La atención debe recomendar hospitalización antes del ingreso.',
                ]);
            }

            if ($attention->notas()->doesntExist()) {
                throw ValidationException::withMessages([
                    'atencion_id' => 'Registra una nota clínica antes del ingreso hospitalario.',
                ]);
            }

            if ($attention->hospitalizaciones()->where('estado', 'internada')->exists()) {
                throw ValidationException::withMessages([
                    'atencion_id' => 'La atención ya tiene una hospitalización activa.',
                ]);
            }

            $lockedSpace = EspacioClinico::query()
                ->whereKey($space->id)
                ->where('tipo', 'hospitalizacion')
                ->where('estado', 'disponible')
                ->where('activo', true)
                ->lockForUpdate()
                ->firstOrFail();

            $hospitalization = Hospitalizacion::query()->create([
                'folio' => 'HOS-'.Str::upper((string) Str::ulid()),
                'atencion_id' => $attention->id,
                'espacio_id' => $lockedSpace->id,
                'ingresada_at' => now(),
                'ingresada_por' => $user->id,
                'estado' => 'internada',
            ]);

            $lockedSpace->update(['estado' => 'ocupado']);

            return $hospitalization;
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordMonitoring(
        Hospitalizacion $hospitalization,
        array $data,
        User $user,
    ): MonitoreoHospitalario {
        return DB::transaction(function () use ($hospitalization, $data, $user): MonitoreoHospitalario {
            $hospitalization = Hospitalizacion::query()
                ->whereKey($hospitalization->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($hospitalization->estado !== 'internada') {
                throw ValidationException::withMessages([
                    'hospitalizacion' => 'Solo se puede monitorear una hospitalización activa.',
                ]);
            }

            $lastMonitoring = $hospitalization->monitoreos()
                ->orderByDesc('registrada_at')
                ->orderByDesc('id')
                ->first();
            $nextMonitoringAt = ($lastMonitoring?->registrada_at ?? $hospitalization->ingresada_at)
                ->copy()
                ->addHours(4);

            if (now()->lt($nextMonitoringAt)) {
                throw ValidationException::withMessages([
                    'monitoreo' => 'El siguiente monitoreo estará disponible a partir de '.$nextMonitoringAt->format('d/m/Y H:i').'.',
                ]);
            }

            $monitoring = $hospitalization->monitoreos()->create([
                'registrada_por' => $user->id,
                'registrada_at' => now(),
                'temperatura_c' => $data['temperatura_c'] ?? null,
                'frecuencia_cardiaca' => $data['frecuencia_cardiaca'] ?? null,
                'frecuencia_respiratoria' => $data['frecuencia_respiratoria'] ?? null,
                'tratamiento' => $data['tratamiento'],
                'nota_progreso' => $data['nota_progreso'],
            ]);

            $attention = $hospitalization->atencion;
            $previousNote = $attention->notas()->orderByDesc('version')->firstOrFail();
            $attention->notas()->create([
                'profesional_id' => $attention->profesional_id,
                'autor_id' => $user->id,
                'contenido' => $data['nota_progreso'],
                'version' => $previousNote->version + 1,
                'nota_anterior_id' => $previousNote->id,
            ]);

            return $monitoring;
        }, attempts: 3);
    }

    public function discharge(Hospitalizacion $hospitalization, User $user): Hospitalizacion
    {
        return DB::transaction(function () use ($hospitalization, $user): Hospitalizacion {
            $hospitalization = Hospitalizacion::query()
                ->whereKey($hospitalization->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($hospitalization->estado !== 'internada') {
                throw ValidationException::withMessages([
                    'hospitalizacion' => 'La hospitalización ya fue dada de alta.',
                ]);
            }

            if (! $hospitalization->monitoreos()->whereNotNull('nota_progreso')->where('nota_progreso', '!=', '')->exists()) {
                throw ValidationException::withMessages([
                    'nota_progreso' => 'Registra al menos una nota de progreso antes de dar el alta.',
                ]);
            }

            $space = EspacioClinico::query()
                ->whereKey($hospitalization->espacio_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($space->estado !== 'ocupado') {
                throw ValidationException::withMessages([
                    'espacio' => 'La cama debe permanecer ocupada hasta registrar el alta.',
                ]);
            }

            $hospitalization->update([
                'estado' => 'alta',
                'alta_at' => now(),
                'dada_por' => $user->id,
            ]);
            $space->update(['estado' => 'limpieza']);

            return $hospitalization->fresh();
        }, attempts: 3);
    }

    public function disinfect(EspacioClinico $space, User $user): EspacioClinico
    {
        return DB::transaction(function () use ($space, $user): EspacioClinico {
            $space = EspacioClinico::query()->whereKey($space->id)->lockForUpdate()->firstOrFail();

            if ($space->estado !== 'limpieza') {
                throw ValidationException::withMessages([
                    'espacio' => 'Solo se puede desinfectar un espacio que esté pendiente de limpieza.',
                ]);
            }

            $space->update([
                'estado' => 'disponible',
                'desinfectado_at' => now(),
                'desinfectado_por' => $user->id,
            ]);

            return $space->refresh();
        }, attempts: 3);
    }
}
