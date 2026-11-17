<?php

use App\Models\Profesional;
use App\Services\ProfesionalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea y actualiza un profesional', function (): void {
    $service = new ProfesionalService;

    $profesional = $service->create([
        'nombre' => 'Ana',
        'apellido' => 'Vera',
        'cedula_profesional' => 'CVE-123',
        'telefono' => '555999888',
        'estado' => 'activo',
    ]);

    $updated = $service->update($profesional, [
        'telefono' => '555777666',
        'estado' => 'inactivo',
    ]);

    expect($updated->telefono)->toBe('555777666')
        ->and($updated->estado)->toBe('inactivo')
        ->and($service->listActive())->toHaveCount(0)
        ->and($service->findById($profesional->id)->telefono)->toBe('555777666');
});
