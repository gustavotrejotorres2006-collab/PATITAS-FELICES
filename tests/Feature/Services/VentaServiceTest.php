<?php

use App\Models\Caja;
use App\Models\Propietario;
use App\Models\Servicio;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea una venta y agrega un detalle', function (): void {
    $service = new VentaService;
    $usuario = \App\Models\User::factory()->create();
    $propietario = Propietario::query()->create([
        'codigo' => 'P-VTA',
        'nombre' => 'Laura',
        'estado' => 'activo',
    ]);
    $caja = Caja::query()->create([
        'usuario_id' => $usuario->id,
        'abierta_at' => now(),
        'monto_inicial' => 500,
        'estado' => 'abierta',
    ]);
    $servicio = Servicio::query()->create([
        'codigo' => 'SRV-001',
        'nombre' => 'Consulta general',
        'duracion_minutos' => 30,
        'precio' => 350,
        'activo' => true,
    ]);

    $venta = $service->create([
        'folio' => 'VTA-001',
        'propietario_id' => $propietario->id,
        'caja_id' => $caja->id,
        'fecha' => now(),
        'subtotal' => 350,
        'descuento' => 0,
        'total' => 350,
        'estado' => 'pagada',
    ]);

    $detalle = $service->addDetail($venta, [
        'tipo' => 'servicio',
        'servicio_id' => $servicio->id,
        'descripcion' => 'Consulta general',
        'cantidad' => 1,
        'precio_unitario' => 350,
        'descuento' => 0,
        'total' => 350,
    ]);

    expect($service->findById($venta->id)->folio)->toBe('VTA-001')
        ->and($detalle->venta_id)->toBe($venta->id)
        ->and($venta->fresh()->detalles()->count())->toBe(1);
});

it('lista ventas del día actual', function (): void {
    $service = new VentaService;
    Venta::query()->create([
        'folio' => 'VTA-002',
        'fecha' => now(),
        'subtotal' => 100,
        'total' => 100,
        'estado' => 'pagada',
    ]);

    expect($service->listToday()->count())->toBe(1);
});
