<?php

use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('registra un producto y detecta stock bajo', function (): void {
    $service = new InventarioService;

    $producto = $service->createProduct([
        'codigo' => 'INV-001',
        'nombre' => 'Vacuna',
        'unidad_base' => 'pieza',
        'stock_minimo' => 10,
        'precio_venta' => 120,
        'estado' => 'activo',
    ]);

    $service->createLote([
        'producto_id' => $producto->id,
        'numero_lote' => 'LOT-001',
        'cantidad_inicial' => 8,
        'cantidad_disponible' => 8,
        'estado' => 'disponible',
    ]);

    expect($service->findProductById($producto->id)->codigo)->toBe('INV-001')
        ->and($service->lowStockProducts()->pluck('id')->all())->toContain($producto->id);
});

it('registra un movimiento de inventario', function (): void {
    $service = new InventarioService;

    $producto = Producto::query()->create([
        'codigo' => 'INV-002',
        'nombre' => 'Jeringa',
        'unidad_base' => 'pieza',
        'stock_minimo' => 1,
        'precio_venta' => 20,
        'estado' => 'activo',
    ]);

    $movement = $service->registerMovement([
        'producto_id' => $producto->id,
        'tipo' => 'entrada',
        'cantidad' => 10,
        'motivo' => 'Compra inicial',
    ]);

    expect($movement->producto_id)->toBe($producto->id)
        ->and($movement->tipo)->toBe('entrada')
        ->and((float) $movement->cantidad)->toBe(10.0);
});
