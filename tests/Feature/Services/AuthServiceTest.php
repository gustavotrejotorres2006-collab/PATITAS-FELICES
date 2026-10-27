<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('autentica a un usuario con credenciales válidas', function (): void {
    $user = User::factory()->create([
        'email' => 'vet@patitasfelices.test',
        'password' => Hash::make('secret123'),
    ]);

    $service = new AuthService;
    $authenticatedUser = $service->login([
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    expect($authenticatedUser->id)->toBe($user->id)
        ->and(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($user->id);
});

it('rechaza credenciales incorrectas', function (): void {
    User::factory()->create([
        'email' => 'vet@patitasfelices.test',
        'password' => Hash::make('secret123'),
    ]);

    $service = new AuthService;

    expect(fn () => $service->login([
        'email' => 'vet@patitasfelices.test',
        'password' => 'incorrecta',
    ]))->toThrow(ValidationException::class);
});

it('hashea la contraseña al registrar un usuario', function (): void {
    $service = new AuthService;

    $service->register([
        'name' => 'Diana Rosales',
        'email' => 'nuevo@patitasfelices.test',
        'password' => 'midPassword123',
        'telefono' => '5551234567',
    ]);

    $user = User::query()->where('email', 'nuevo@patitasfelices.test')->firstOrFail();

    expect(Hash::check('midPassword123', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('midPassword123');
});
