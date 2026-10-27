<?php

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Services\CitaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea y consulta citas por mascota, propietario y profesional', function (): void {
    $service = new CitaService;

    $propietario = Propietario::query()->create([
        'codigo' => 'P-CITA',
        'nombre' => 'Nora',
        'estado' => 'activo',
    ]);
    $mascota = Mascota::query()->create([
        'codigo' => 'M-CITA',
        'nombre' => 'Milo',
        'especie' => 'perro',
        'estado' => 'activo',
    ]);
    $profesional = Profesional::query()->create([
        'nombre' => 'Silvia',
        'apellido' => 'Ruiz',
        'cedula_profesional' => 'CITA-001',
        'estado' => 'activo',
    ]);

    $cita = $service->create([
        'folio' => 'CITA-001',
        'mascota_id' => $mascota->id,
        'propietario_id' => $propietario->id,
        'profesional_id' => $profesional->id,
        'inicio' => '2026-11-15 09:00:00',
        'fin' => '2026-11-15 09:30:00',
        'estado' => 'pendiente',
        'motivo' => 'Chequeo anual',
    ]);

    expect($service->findById($cita->id)->folio)->toBe('CITA-001')
        ->and($service->listForMascota($mascota))->toHaveCount(1)
        ->and($service->listForOwner($propietario))->toHaveCount(1)
        ->and($service->listForProfesional($profesional))->toHaveCount(1);
});

it('actualiza el estado de una cita', function (): void {
    $service = new CitaService;
    $cita = Cita::query()->create([
        'folio' => 'CITA-002',
        'mascota_id' => Mascota::query()->create(['codigo' => 'M-CITA-2', 'nombre' => 'Beto', 'especie' => 'gato', 'estado' => 'activo'])->id,
        'propietario_id' => Propietario::query()->create(['codigo' => 'P-CITA-2', 'nombre' => 'Carlos', 'estado' => 'activo'])->id,
        'profesional_id' => Profesional::query()->create(['nombre' => 'Oscar', 'cedula_profesional' => 'CITA-002', 'estado' => 'activo'])->id,
        'inicio' => '2026-11-16 10:00:00',
        'fin' => '2026-11-16 10:30:00',
        'estado' => 'pendiente',
    ]);

    $updated = $service->update($cita, ['estado' => 'confirmada']);

    expect($updated->estado)->toBe('confirmada')
        ->and($service->findById($cita->id)->estado)->toBe('confirmada');
});
