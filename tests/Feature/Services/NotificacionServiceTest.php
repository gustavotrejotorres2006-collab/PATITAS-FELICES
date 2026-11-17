<?php

use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea y lista notificaciones pendientes', function (): void {
    $user = User::factory()->create();
    $service = new NotificacionService;

    $notification = $service->create([
        'user_id' => $user->id,
        'tipo' => 'recordatorio',
        'titulo' => 'Cita próxima',
        'mensaje' => 'Tiene una cita mañana a las 10:00.',
        'canal' => 'interno',
        'estado' => 'pendiente',
        'datos' => ['cita_id' => 1],
    ]);

    expect($notification->titulo)->toBe('Cita próxima')
        ->and($service->listPending())->toHaveCount(1);
});
