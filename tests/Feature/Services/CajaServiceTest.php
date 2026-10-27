<?php

use App\Models\Caja;
use App\Models\User;
use App\Services\CajaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('abre y lista una caja activa', function (): void {
    $user = User::factory()->create();
    $service = new CajaService;

    $caja = $service->open([
        'usuario_id' => $user->id,
        'abierta_at' => now(),
        'monto_inicial' => 500,
        'estado' => 'abierta',
    ]);

    expect($service->findById($caja->id)->estado)->toBe('abierta')
        ->and($service->listOpen())->toHaveCount(1);
});

it('recupera cajas por id', function (): void {
    $user = User::factory()->create();
    $caja = Caja::query()->create([
        'usuario_id' => $user->id,
        'abierta_at' => now(),
        'monto_inicial' => 250,
        'estado' => 'abierta',
    ]);

    expect(Caja::query()->find($caja->id)->usuario_id)->toBe($user->id);
});
