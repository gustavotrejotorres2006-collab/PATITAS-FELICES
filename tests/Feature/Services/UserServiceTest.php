<?php

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea un usuario y protege su contraseña', function (): void {
    $service = new UserService;

    $user = $service->create([
        'name' => 'Ana Gómez',
        'email' => 'ana@patitasfelices.test',
        'password' => 'secret456',
        'estado' => 'activo',
    ]);

    expect($user->email)->toBe('ana@patitasfelices.test')
        ->and(Hash::check('secret456', $user->password))->toBeTrue();
});

it('consulta usuarios activos por id y email', function (): void {
    User::factory()->create([
        'email' => 'usuario@patitasfelices.test',
        'estado' => 'activo',
    ]);

    $service = new UserService;
    $foundById = $service->findById(1);
    $foundByEmail = $service->findByEmail('usuario@patitasfelices.test');

    expect($foundById)->not->toBeNull()
        ->and($foundByEmail)->not->toBeNull()
        ->and($service->listActive())->toHaveCount(1);
});
