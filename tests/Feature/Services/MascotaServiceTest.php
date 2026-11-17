<?php

use App\Models\Mascota;
use App\Models\Propietario;
use App\Services\MascotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('registra una mascota y asocia propietarios', function (): void {
    $service = new MascotaService;

    $mascota = $service->create([
        'codigo' => 'M-001',
        'nombre' => 'Firulais',
        'especie' => 'perro',
        'sexo' => 'macho',
        'estado' => 'activo',
    ]);

    $ownerOne = Propietario::query()->create([
        'codigo' => 'P-01',
        'nombre' => 'Luisa',
        'apellido' => 'Pérez',
        'estado' => 'activo',
    ]);
    $ownerTwo = Propietario::query()->create([
        'codigo' => 'P-02',
        'nombre' => 'Pedro',
        'apellido' => 'Pérez',
        'estado' => 'activo',
    ]);

    $service->assignOwner($mascota, $ownerOne, true);
    $service->assignOwner($mascota, $ownerTwo, true);

    $principal = $mascota->propietarios()->wherePivot('es_principal', true)->first();

    expect($principal?->id)->toBe($ownerTwo->id)
        ->and($mascota->propietarios()->count())->toBe(2)
        ->and($service->findById($mascota->id)->nombre)->toBe('Firulais');
});

it('lista mascotas por propietario', function (): void {
    $service = new MascotaService;
    $propietario = Propietario::query()->create([
        'codigo' => 'P-10',
        'nombre' => 'Camila',
        'estado' => 'activo',
    ]);

    $first = $service->create([
        'codigo' => 'M-010',
        'nombre' => 'Luna',
        'especie' => 'gato',
        'estado' => 'activo',
    ]);
    $second = $service->create([
        'codigo' => 'M-011',
        'nombre' => 'Coco',
        'especie' => 'perro',
        'estado' => 'activo',
    ]);

    $service->assignOwner($first, $propietario, true);
    $service->assignOwner($second, $propietario, false);

    expect($service->listByOwner($propietario))->toHaveCount(2);
});
