<?php

use App\Models\Atencion;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\User;
use App\Services\ClinicaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea una atención y registra notas y diagnósticos', function (): void {
    $service = new ClinicaService;
    $user = User::factory()->create();
    $propietario = Propietario::query()->create([
        'codigo' => 'P-CLIN',
        'nombre' => 'Inés',
        'estado' => 'activo',
    ]);
    $mascota = Mascota::query()->create([
        'codigo' => 'M-CLIN',
        'nombre' => 'Nina',
        'especie' => 'gato',
        'estado' => 'activo',
    ]);
    $profesional = Profesional::query()->create([
        'nombre' => 'María',
        'apellido' => 'Luna',
        'cedula_profesional' => 'CLIN-001',
        'estado' => 'activo',
    ]);
    $cita = Cita::query()->create([
        'folio' => 'CLIN-CITA-1',
        'mascota_id' => $mascota->id,
        'propietario_id' => $propietario->id,
        'profesional_id' => $profesional->id,
        'inicio' => '2026-12-01 09:00:00',
        'fin' => '2026-12-01 09:30:00',
        'estado' => 'pendiente',
    ]);

    $atencion = $service->createAtencion([
        'folio' => 'ATN-001',
        'cita_id' => $cita->id,
        'mascota_id' => $mascota->id,
        'propietario_id' => $propietario->id,
        'profesional_id' => $profesional->id,
        'creado_por' => $user->id,
        'fecha_atencion' => now(),
        'estado' => 'abierta',
        'motivo' => 'Pérdida de apetito',
    ]);

    $nota = $service->addNota([
        'atencion_id' => $atencion->id,
        'profesional_id' => $profesional->id,
        'autor_id' => $user->id,
        'contenido' => 'Se recomienda observación y control.',
        'version' => 1,
    ]);

    $diagnostico = $service->createDiagnostico([
        'codigo' => 'D-CLIN-1',
        'nombre' => 'Infección leve',
        'descripcion' => 'Se observa leve infección',
        'activo' => true,
    ]);

    expect($atencion->folio)->toBe('ATN-001')
        ->and($nota->contenido)->toBe('Se recomienda observación y control.')
        ->and($diagnostico->nombre)->toBe('Infección leve')
        ->and($service->listAtencionesOpen()->first()->id)->toBe($atencion->id);
});
