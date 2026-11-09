<?php

namespace Database\Seeders;

use App\Models\Atencion;
use App\Models\AtencionDiagnostico;
use App\Models\CatalogoVacuna;
use App\Models\CategoriaProducto;
use App\Models\CategoriaServicio;
use App\Models\Cirugia;
use App\Models\Cita;
use App\Models\Diagnostico;
use App\Models\EspacioClinico;
use App\Models\Especialidad;
use App\Models\Hospitalizacion;
use App\Models\LoteInventario;
use App\Models\Mascota;
use App\Models\MonitoreoHospitalario;
use App\Models\MovimientoInventario;
use App\Models\NotaClinica;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Role;
use App\Models\Servicio;
use App\Models\SignosVitales;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use LogicException;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'PatitasDemo2026!';

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new LogicException('Los datos de demostración no se pueden cargar en producción.');
        }

        $administratorRole = Role::query()->updateOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Administración de la clínica', 'activo' => true],
        );
        $receptionistRole = Role::query()->updateOrCreate(
            ['nombre' => 'Recepcionista'],
            ['descripcion' => 'Recepción y agenda de citas', 'activo' => true],
        );
        $veterinarianRole = Role::query()->updateOrCreate(
            ['nombre' => 'Veterinario'],
            ['descripcion' => 'Atención clínica y hospitalaria', 'activo' => true],
        );

        $administrator = $this->user(
            'Administración Demo',
            'admin@patitas.test',
            $administratorRole,
            '555-0100',
        );
        $receptionist = $this->user(
            'Recepción Demo',
            'recepcion@patitas.test',
            $receptionistRole,
            '555-0101',
        );
        $veterinarianUser = $this->user(
            'Dra. Valeria Ríos',
            'veterinaria@patitas.test',
            $veterinarianRole,
            '555-0102',
        );
        $anesthesiologistUser = $this->user(
            'Dr. Mateo Cruz',
            'anestesia@patitas.test',
            $veterinarianRole,
            '555-0103',
        );

        $veterinarian = Profesional::query()->updateOrCreate(
            ['cedula_profesional' => 'DEMO-VET-001'],
            [
                'user_id' => $veterinarianUser->id,
                'nombre' => 'Valeria',
                'apellido' => 'Ríos',
                'telefono' => '555-0102',
                'estado' => 'activo',
            ],
        );
        $anesthesiologist = Profesional::query()->updateOrCreate(
            ['cedula_profesional' => 'DEMO-VET-002'],
            [
                'user_id' => $anesthesiologistUser->id,
                'nombre' => 'Mateo',
                'apellido' => 'Cruz',
                'telefono' => '555-0103',
                'estado' => 'activo',
            ],
        );

        $generalMedicine = Especialidad::query()->updateOrCreate(
            ['nombre' => 'Medicina general'],
            ['descripcion' => 'Consulta y medicina preventiva', 'activo' => true],
        );
        $surgerySpecialty = Especialidad::query()->updateOrCreate(
            ['nombre' => 'Cirugía'],
            ['descripcion' => 'Procedimientos quirúrgicos', 'activo' => true],
        );
        $anesthesiaSpecialty = Especialidad::query()->updateOrCreate(
            ['nombre' => 'Anestesiología'],
            ['descripcion' => 'Anestesia y monitoreo perioperatorio', 'activo' => true],
        );
        $veterinarian->especialidades()->syncWithoutDetaching([
            $generalMedicine->id,
            $surgerySpecialty->id,
        ]);
        $anesthesiologist->especialidades()->syncWithoutDetaching([
            $anesthesiaSpecialty->id,
        ]);

        [$lunaOwner, $luna] = $this->ownerAndPet(
            'DEMO-OWN-001',
            'Ana',
            'Pérez',
            'DEMO-PET-001',
            'Luna',
            'perro',
            'Labrador',
        );
        [$miloOwner, $milo] = $this->ownerAndPet(
            'DEMO-OWN-002',
            'Carlos',
            'Mendoza',
            'DEMO-PET-002',
            'Milo',
            'gato',
            'Doméstico de pelo corto',
            'macho',
        );
        [$nubeOwner, $nube] = $this->ownerAndPet(
            'DEMO-OWN-003',
            'Lucía',
            'Torres',
            'DEMO-PET-003',
            'Nube',
            'perro',
            'Mestiza',
        );
        [$rockyOwner, $rocky] = $this->ownerAndPet(
            'DEMO-OWN-004',
            'Diego',
            'López',
            'DEMO-PET-004',
            'Rocky',
            'perro',
            'Schnauzer',
            'macho',
        );

        $consultationCategory = CategoriaServicio::query()->updateOrCreate(
            ['nombre' => 'Consultas'],
            ['activo' => true],
        );
        $vaccineCategory = CategoriaServicio::query()->updateOrCreate(
            ['nombre' => 'Vacunación'],
            ['activo' => true],
        );
        $consultation = Servicio::query()->updateOrCreate(
            ['codigo' => 'DEMO-CONSULTA'],
            [
                'categoria_id' => $consultationCategory->id,
                'nombre' => 'Consulta general',
                'descripcion' => 'Revisión clínica general',
                'duracion_minutos' => 30,
                'precio' => 450,
                'requiere_atencion' => true,
                'activo' => true,
            ],
        );
        $vaccinationService = Servicio::query()->updateOrCreate(
            ['codigo' => 'DEMO-VACUNA'],
            [
                'categoria_id' => $vaccineCategory->id,
                'nombre' => 'Aplicación de vacuna',
                'descripcion' => 'Aplicación y registro de vacuna',
                'duracion_minutos' => 30,
                'precio' => 350,
                'requiere_atencion' => true,
                'activo' => true,
            ],
        );

        $historicalAppointment = $this->appointment(
            'DEMO-CIT-001',
            $luna,
            $lunaOwner,
            $veterinarian,
            $receptionist,
            $consultation,
            now()->subDays(10)->setTime(10, 0),
            'completada',
        );
        $preoperativeAppointment = $this->appointment(
            'DEMO-CIT-002',
            $milo,
            $miloOwner,
            $veterinarian,
            $receptionist,
            $consultation,
            now()->subDays(3)->setTime(11, 0),
            'completada',
        );
        $this->appointment(
            'DEMO-CIT-003',
            $luna,
            $lunaOwner,
            $veterinarian,
            $receptionist,
            $consultation,
            now()->addDays(2)->setTime(9, 0),
            'pendiente',
        );
        $this->appointment(
            'DEMO-CIT-004',
            $luna,
            $lunaOwner,
            $veterinarian,
            $receptionist,
            $vaccinationService,
            now()->addDays(3)->setTime(14, 0),
            'confirmada',
        );

        $generalAttention = $this->attention(
            'DEMO-ATN-001',
            $historicalAppointment,
            $luna,
            $lunaOwner,
            $veterinarian,
            $veterinarianUser,
            now()->subDays(10)->setTime(10, 0),
            false,
            'DEMO-DIAG-001',
            'Paciente clínicamente estable',
            'Exploración general sin hallazgos de alarma. Se aplicó la vacuna indicada.',
        );
        $surgeryAttention = $this->attention(
            'DEMO-ATN-002',
            $preoperativeAppointment,
            $milo,
            $miloOwner,
            $veterinarian,
            $veterinarianUser,
            now()->subDays(3)->setTime(11, 0),
            false,
            'DEMO-DIAG-002',
            'Evaluación preoperatoria',
            'Paciente apto para procedimiento programado. Consentimiento revisado con el propietario.',
        );
        $nubeAttention = $this->attention(
            'DEMO-ATN-003',
            null,
            $nube,
            $nubeOwner,
            $veterinarian,
            $veterinarianUser,
            now()->subHours(8),
            true,
            'DEMO-DIAG-003',
            'Observación clínica',
            'Paciente en observación; continuar hidratación y registrar evolución.',
        );
        $rockyAttention = $this->attention(
            'DEMO-ATN-004',
            null,
            $rocky,
            $rockyOwner,
            $veterinarian,
            $veterinarianUser,
            now()->subHours(12),
            true,
            'DEMO-DIAG-004',
            'Recuperación postoperatoria',
            'Recuperación estable; mantener analgesia y vigilancia periódica.',
        );

        $medicationCategory = CategoriaProducto::query()->updateOrCreate(
            ['nombre' => 'Medicamentos'],
            ['activo' => true],
        );
        $surgicalCategory = CategoriaProducto::query()->updateOrCreate(
            ['nombre' => 'Material quirúrgico'],
            ['activo' => true],
        );
        $vaccineProduct = $this->product(
            'DEMO-VAC-RABIA',
            $medicationCategory,
            'Vacuna antirrábica',
            'dosis',
            180,
            350,
            2,
            20,
            true,
            true,
            true,
            true,
        );
        $sutureProduct = $this->product(
            'DEMO-SUTURA',
            $surgicalCategory,
            'Sutura absorbible 2-0',
            'pieza',
            90,
            180,
            2,
            15,
            true,
            false,
            true,
            true,
        );
        $analgesicProduct = $this->product(
            'DEMO-ANALGESICO',
            $medicationCategory,
            'Analgésico veterinario',
            'tableta',
            12,
            25,
            5,
            40,
            true,
            true,
            true,
            true,
        );

        $vaccineLot = $this->inventoryLot(
            $vaccineProduct,
            'DEMO-RAB-2026-A',
            10,
            9,
            180,
            now()->addMonths(8),
            $administrator,
        );
        $this->inventoryLot(
            $vaccineProduct,
            'DEMO-RAB-2027-B',
            10,
            10,
            185,
            now()->addMonths(18),
            $administrator,
        );
        $this->inventoryLot(
            $sutureProduct,
            'DEMO-SUT-2026-A',
            8,
            8,
            90,
            now()->addYear(),
            $administrator,
        );
        $this->inventoryLot(
            $sutureProduct,
            'DEMO-SUT-2027-B',
            8,
            8,
            95,
            now()->addYears(2),
            $administrator,
        );
        $this->inventoryLot(
            $analgesicProduct,
            'DEMO-ANL-2026-A',
            30,
            30,
            12,
            now()->addMonths(10),
            $administrator,
        );

        $vaccine = CatalogoVacuna::query()->updateOrCreate(
            ['nombre' => 'Rabia (demostración)', 'especie' => 'perro'],
            [
                'descripcion' => 'Vacuna de prueba vinculada al inventario.',
                'activo' => true,
                'producto_id' => $vaccineProduct->id,
            ],
        );
        $vaccination = Vacunacion::query()->updateOrCreate(
            [
                'mascota_id' => $luna->id,
                'vacuna_id' => $vaccine->id,
                'atencion_id' => $generalAttention->id,
            ],
            [
                'fecha_aplicacion' => now()->subDays(10)->toDateString(),
                'proxima_fecha' => now()->addMonths(2)->toDateString(),
                'lote' => $vaccineLot->numero_lote,
                'dosis' => 1,
                'unidad_dosis' => 'dosis',
                'aplicada_por' => $veterinarianUser->id,
                'observaciones' => 'Aplicación registrada en la atención de demostración.',
            ],
        );
        MovimientoInventario::query()->updateOrCreate(
            [
                'producto_id' => $vaccineProduct->id,
                'lote_id' => $vaccineLot->id,
                'tipo' => 'salida',
                'referencia_tipo' => 'Vacunacion',
                'referencia_id' => $vaccination->id,
            ],
            [
                'cantidad' => 1,
                'costo_unitario' => 180,
                'motivo' => 'Aplicación de vacuna de demostración',
                'usuario_id' => $veterinarianUser->id,
            ],
        );

        $operatingRoom = $this->space('Quirófano Demo 1', 'quirofano', 'disponible', $administrator);
        $this->space('Quirófano Demo 2', 'quirofano', 'disponible', $administrator);
        $nubeBed = $this->space('Hospital Demo 1', 'hospitalizacion', 'ocupado', $administrator);
        $rockyBed = $this->space('Hospital Demo 2', 'hospitalizacion', 'ocupado', $administrator);
        $this->space('Hospital Demo 3', 'hospitalizacion', 'disponible', $administrator);

        Cirugia::query()->updateOrCreate(
            ['folio' => 'DEMO-CIR-001'],
            [
                'atencion_id' => $surgeryAttention->id,
                'espacio_id' => $operatingRoom->id,
                'cirujano_id' => $veterinarian->id,
                'anestesiologo_id' => $anesthesiologist->id,
                'propietario_id' => $miloOwner->id,
                'programada_at' => now()->addDays(5)->setTime(9, 0),
                'duracion_minutos' => 90,
                'estado' => 'programada',
                'consentimiento_firmado_at' => now()->subDays(2),
                'consentimiento_verificado_por' => $administrator->id,
                'observaciones' => 'Cirugía de demostración; incluye registro de consentimiento.',
            ],
        );

        $nubeHospitalization = $this->hospitalization(
            'DEMO-HOSP-001',
            $nubeAttention,
            $nubeBed,
            $veterinarianUser,
            now()->subHours(8),
        );
        $rockyHospitalization = $this->hospitalization(
            'DEMO-HOSP-002',
            $rockyAttention,
            $rockyBed,
            $veterinarianUser,
            now()->subHours(12),
        );
        $this->monitoring(
            $nubeHospitalization,
            $veterinarianUser,
            now()->subHours(2),
            'Continuar observación y ofrecer agua.',
            'Paciente tranquilo, hidratado y con evolución favorable.',
        );
        $this->monitoring(
            $rockyHospitalization,
            $veterinarianUser,
            now()->subHours(8),
            'Mantener analgesia según indicación.',
            'Paciente estable, responde al tratamiento y continúa en recuperación.',
        );
    }

    private function user(string $name, string $email, Role $role, string $phone): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => self::DEMO_PASSWORD,
                'role_id' => $role->id,
                'estado' => 'activo',
                'telefono' => $phone,
            ],
        );
    }

    /**
     * @return array{Propietario, Mascota}
     */
    private function ownerAndPet(
        string $ownerCode,
        string $ownerName,
        string $ownerSurname,
        string $petCode,
        string $petName,
        string $species,
        string $breed,
        string $sex = 'hembra',
    ): array {
        $owner = Propietario::query()->updateOrCreate(
            ['codigo' => $ownerCode],
            [
                'nombre' => $ownerName,
                'apellido' => $ownerSurname,
                'telefono' => '555-'.substr($ownerCode, -3),
                'email' => strtolower($ownerCode).'@patitas.test',
                'direccion' => 'Domicilio de demostración',
                'estado' => 'activo',
            ],
        );
        $pet = Mascota::query()->updateOrCreate(
            ['codigo' => $petCode],
            [
                'nombre' => $petName,
                'especie' => $species,
                'raza' => $breed,
                'sexo' => $sex,
                'fecha_nacimiento' => now()->subYears(3)->toDateString(),
                'color' => 'Tricolor',
                'esterilizado' => true,
                'observaciones' => 'Registro ficticio para pruebas locales.',
                'estado' => 'activo',
            ],
        );
        $pet->propietarios()->syncWithoutDetaching([
            $owner->id => [
                'es_principal' => true,
                'fecha_inicio' => now()->subYears(2)->toDateString(),
                'estado' => 'activo',
            ],
        ]);

        return [$owner, $pet];
    }

    private function appointment(
        string $folio,
        Mascota $pet,
        Propietario $owner,
        Profesional $professional,
        User $creator,
        Servicio $service,
        Carbon $start,
        string $status,
    ): Cita {
        $appointment = Cita::query()->updateOrCreate(
            ['folio' => $folio],
            [
                'mascota_id' => $pet->id,
                'propietario_id' => $owner->id,
                'profesional_id' => $professional->id,
                'inicio' => $start,
                'fin' => $start->copy()->addMinutes($service->duracion_minutos),
                'estado' => $status,
                'urgente' => false,
                'motivo' => $service->nombre,
                'observaciones' => 'Cita de demostración.',
                'creado_por' => $creator->id,
                'confirmado_por' => $status === 'confirmada' ? $creator->id : null,
                'confirmado_at' => $status === 'confirmada' ? now() : null,
            ],
        );
        $appointment->servicios()->syncWithoutDetaching([
            $service->id => [
                'precio' => $service->precio,
                'duracion_minutos' => $service->duracion_minutos,
            ],
        ]);

        return $appointment;
    }

    private function attention(
        string $folio,
        ?Cita $appointment,
        Mascota $pet,
        Propietario $owner,
        Profesional $professional,
        User $author,
        Carbon $date,
        bool $hospitalizationRecommended,
        string $diagnosisCode,
        string $diagnosisName,
        string $note,
    ): Atencion {
        $attention = Atencion::query()->updateOrCreate(
            ['folio' => $folio],
            [
                'cita_id' => $appointment?->id,
                'mascota_id' => $pet->id,
                'propietario_id' => $owner->id,
                'profesional_id' => $professional->id,
                'creado_por' => $author->id,
                'fecha_atencion' => $date,
                'estado' => 'abierta',
                'motivo' => $appointment?->motivo ?? 'Atención hospitalaria de demostración',
                'hospitalizacion_recomendada' => $hospitalizationRecommended,
            ],
        );
        $diagnosis = Diagnostico::query()->updateOrCreate(
            ['codigo' => $diagnosisCode],
            ['nombre' => $diagnosisName, 'descripcion' => $diagnosisName, 'activo' => true],
        );
        AtencionDiagnostico::query()->updateOrCreate(
            [
                'atencion_id' => $attention->id,
                'diagnostico_id' => $diagnosis->id,
            ],
            [
                'diagnostico_personalizado' => $diagnosisName,
                'observaciones' => 'Diagnóstico ficticio de demostración.',
            ],
        );
        NotaClinica::query()->updateOrCreate(
            ['atencion_id' => $attention->id, 'version' => 1],
            [
                'profesional_id' => $professional->id,
                'autor_id' => $author->id,
                'contenido' => $note,
            ],
        );
        SignosVitales::query()->updateOrCreate(
            ['atencion_id' => $attention->id],
            [
                'peso_kg' => 12.5,
                'temperatura_c' => 38.4,
                'frecuencia_cardiaca' => 92,
                'frecuencia_respiratoria' => 24,
                'presion_arterial' => '120/80',
                'condicion_corporal' => 5,
                'observaciones' => 'Signos de referencia para pruebas.',
            ],
        );

        return $attention;
    }

    private function product(
        string $code,
        CategoriaProducto $category,
        string $name,
        string $unit,
        float $cost,
        float $price,
        float $minimumStock,
        float $optimalStock,
        bool $forSale,
        bool $internalUse,
        bool $tracksLot,
        bool $tracksExpiration,
    ): Producto {
        return Producto::query()->updateOrCreate(
            ['codigo' => $code],
            [
                'categoria_id' => $category->id,
                'nombre' => $name,
                'descripcion' => 'Producto ficticio para pruebas locales.',
                'unidad_base' => $unit,
                'costo_referencia' => $cost,
                'precio_venta' => $price,
                'stock_minimo' => $minimumStock,
                'stock_optimo' => $optimalStock,
                'venta_publico' => $forSale,
                'uso_interno' => $internalUse,
                'maneja_lote' => $tracksLot,
                'maneja_caducidad' => $tracksExpiration,
                'estado' => 'activo',
            ],
        );
    }

    private function inventoryLot(
        Producto $product,
        string $batchNumber,
        float $initialQuantity,
        float $availableQuantity,
        float $unitCost,
        Carbon $expiration,
        User $creator,
    ): LoteInventario {
        $lot = LoteInventario::query()->updateOrCreate(
            ['producto_id' => $product->id, 'numero_lote' => $batchNumber],
            [
                'fecha_caducidad' => $expiration->toDateString(),
                'cantidad_inicial' => $initialQuantity,
                'cantidad_disponible' => $availableQuantity,
                'costo_unitario' => $unitCost,
                'estado' => $availableQuantity > 0 ? 'disponible' : 'agotado',
            ],
        );
        MovimientoInventario::query()->updateOrCreate(
            [
                'producto_id' => $product->id,
                'lote_id' => $lot->id,
                'tipo' => 'entrada',
                'referencia_tipo' => self::class,
                'referencia_id' => $lot->id,
            ],
            [
                'cantidad' => $initialQuantity,
                'costo_unitario' => $unitCost,
                'motivo' => 'Existencia inicial de demostración',
                'usuario_id' => $creator->id,
            ],
        );

        return $lot;
    }

    private function space(
        string $name,
        string $type,
        string $status,
        User $creator,
    ): EspacioClinico {
        return EspacioClinico::query()->updateOrCreate(
            ['nombre' => $name],
            [
                'tipo' => $type,
                'estado' => $status,
                'activo' => true,
                'desinfectado_at' => now()->subDay(),
                'desinfectado_por' => $creator->id,
                'creado_por' => $creator->id,
            ],
        );
    }

    private function hospitalization(
        string $folio,
        Atencion $attention,
        EspacioClinico $space,
        User $creator,
        Carbon $admittedAt,
    ): Hospitalizacion {
        return Hospitalizacion::query()->updateOrCreate(
            ['folio' => $folio],
            [
                'atencion_id' => $attention->id,
                'espacio_id' => $space->id,
                'ingresada_at' => $admittedAt,
                'ingresada_por' => $creator->id,
                'estado' => 'internada',
                'alta_at' => null,
                'dada_por' => null,
            ],
        );
    }

    private function monitoring(
        Hospitalizacion $hospitalization,
        User $veterinarian,
        Carbon $recordedAt,
        string $treatment,
        string $progressNote,
    ): MonitoreoHospitalario {
        return MonitoreoHospitalario::query()->updateOrCreate(
            [
                'hospitalizacion_id' => $hospitalization->id,
                'registrada_por' => $veterinarian->id,
                'nota_progreso' => $progressNote,
            ],
            [
                'registrada_at' => $recordedAt,
                'temperatura_c' => 38.3,
                'frecuencia_cardiaca' => 88,
                'frecuencia_respiratoria' => 22,
                'tratamiento' => $treatment,
            ],
        );
    }
}
