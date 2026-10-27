<?php

use App\Models\Compra;
use App\Services\CompraService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea una compra y la lista como pendiente', function (): void {
    $service = new CompraService;

    $compra = $service->create([
        'folio' => 'CMP-001',
        'fecha' => '2026-11-10',
        'estado' => 'pendiente',
        'subtotal' => 1500,
        'total' => 1500,
        'observaciones' => 'Compra de insumos',
    ]);

    expect($service->findById($compra->id)->folio)->toBe('CMP-001')
        ->and($service->listPending())->toHaveCount(1);
});

it('guarda una compra con total correcto', function (): void {
    $compra = Compra::query()->create([
        'folio' => 'CMP-002',
        'fecha' => '2026-11-11',
        'estado' => 'procesada',
        'subtotal' => 2300,
        'total' => 2300,
    ]);

    expect($compra->total)->toBe('2300.00');
});
