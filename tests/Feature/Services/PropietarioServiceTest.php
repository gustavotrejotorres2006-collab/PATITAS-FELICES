<?php

use App\Models\Propietario;
use App\Services\PropietarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea y actualiza un propietario', function (): void {
    $service = new PropietarioService;

    $propietario = $service->create([
        'codigo' => 'P-1001',
        'nombre' => 'María',
        'apellido' => 'López',
        'telefono' => '5551001001',
        'email' => 'maria@patitasfelices.test',
        'direccion' => 'Calle 10 # 5',
        'estado' => 'activo',
    ]);

    $updated = $service->update($propietario, [
        'telefono' => '5552223333',
        'direccion' => 'Calle 12 # 7',
    ]);

    expect($propietario->id)->toBe($updated->id)
        ->and($updated->telefono)->toBe('5552223333')
        ->and($service->findById($updated->id)->direccion)->toBe('Calle 12 # 7')
        ->and($service->listActive())->toHaveCount(1);
});
