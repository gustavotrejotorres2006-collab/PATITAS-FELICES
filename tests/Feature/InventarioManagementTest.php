<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\CatalogoVacuna;
use App\Models\Compra;
use App\Models\LoteInventario;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_and_official_reports_are_restricted_to_administrators(): void
    {
        $role = Role::query()->create(['nombre' => 'Recepcionista']);
        $receptionist = User::factory()->create([
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);

        $this->get(route('portal.inventario.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($receptionist)
            ->get(route('portal.inventario.index'))
            ->assertForbidden();

        $this->get(route('portal.inventario.reportes'))
            ->assertForbidden();

        $this->post(route('portal.inventario.productos.store'), [
            'codigo' => 'NO-AUTORIZADO',
            'nombre' => 'Producto no autorizado',
            'unidad_base' => 'pieza',
            'costo_referencia' => 1,
            'precio_venta' => 2,
            'stock_minimo' => 0,
            'stock_optimo' => 10,
            'venta_publico' => '1',
            'uso_interno' => '0',
            'maneja_lote' => '0',
            'maneja_caducidad' => '0',
        ])->assertForbidden();
        $this->assertDatabaseMissing('productos', ['codigo' => 'NO-AUTORIZADO']);

        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('portal.inventario.index'))
            ->assertOk()
            ->assertSee('Control de inventario');
    }

    public function test_administrator_can_receive_a_purchase_into_inventory_lots(): void
    {
        $administrator = $this->createAdministrator();
        $product = $this->createProduct([
            'maneja_lote' => true,
            'maneja_caducidad' => true,
        ]);

        $response = $this->actingAs($administrator)
            ->post(route('portal.inventario.compras.store'), [
                'fecha' => now()->toDateString(),
                'detalles' => [
                    [
                        'producto_id' => $product->id,
                        'cantidad' => '12',
                        'costo_unitario' => '25.50',
                        'numero_lote' => 'VAC-2026-01',
                        'fecha_caducidad' => now()->addYear()->toDateString(),
                    ],
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $purchase = Compra::query()->firstOrFail();
        $lot = LoteInventario::query()->firstOrFail();

        $response->assertRedirect(route('portal.inventario.index'));
        $this->assertDatabaseHas('compras', [
            'id' => $purchase->id,
            'estado' => 'recibida',
            'creado_por' => $administrator->id,
            'total' => 306,
        ]);
        $this->assertDatabaseHas('compra_detalles', [
            'compra_id' => $purchase->id,
            'producto_id' => $product->id,
            'cantidad_base' => 12,
            'subtotal' => 306,
        ]);
        $this->assertDatabaseHas('lotes_inventario', [
            'id' => $lot->id,
            'numero_lote' => 'VAC-2026-01',
            'cantidad_inicial' => 12,
            'cantidad_disponible' => 12,
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $product->id,
            'lote_id' => $lot->id,
            'tipo' => 'entrada',
            'cantidad' => 12,
        ]);
    }

    public function test_purchase_is_rolled_back_when_a_required_lot_number_is_missing(): void
    {
        $administrator = $this->createAdministrator();
        $product = $this->createProduct([
            'maneja_lote' => true,
        ]);

        $response = $this->actingAs($administrator)
            ->from(route('portal.inventario.index'))
            ->post(route('portal.inventario.compras.store'), [
                'fecha' => now()->toDateString(),
                'detalles' => [
                    [
                        'producto_id' => $product->id,
                        'cantidad' => 5,
                        'costo_unitario' => 10,
                    ],
                ],
            ]);

        $response->assertRedirect(route('portal.inventario.index'))
            ->assertSessionHasErrors('detalles.0.numero_lote');
        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('compra_detalles', 0);
        $this->assertDatabaseCount('lotes_inventario', 0);
        $this->assertDatabaseCount('movimientos_inventario', 0);
    }

    public function test_consumption_uses_nearest_expiration_first_and_never_allows_negative_stock(): void
    {
        $administrator = $this->createAdministrator();
        $product = $this->createProduct();
        $laterLot = $product->lotes()->create([
            'numero_lote' => 'LATE',
            'fecha_caducidad' => now()->addMonths(8)->toDateString(),
            'cantidad_inicial' => 4,
            'cantidad_disponible' => 4,
            'estado' => 'disponible',
        ]);
        $earlierLot = $product->lotes()->create([
            'numero_lote' => 'EARLY',
            'fecha_caducidad' => now()->addMonths(2)->toDateString(),
            'cantidad_inicial' => 2,
            'cantidad_disponible' => 2,
            'estado' => 'disponible',
        ]);

        $response = $this->actingAs($administrator)
            ->post(route('portal.inventario.consumos.store'), [
                'producto_id' => $product->id,
                'cantidad' => '2.500',
                'motivo' => 'Uso clínico',
            ]);

        $response->assertRedirect(route('portal.inventario.index'));
        $this->assertDatabaseHas('lotes_inventario', [
            'id' => $earlierLot->id,
            'cantidad_disponible' => 0,
            'estado' => 'agotado',
        ]);
        $this->assertDatabaseHas('lotes_inventario', [
            'id' => $laterLot->id,
            'cantidad_disponible' => 3.5,
            'estado' => 'disponible',
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id' => $earlierLot->id,
            'tipo' => 'salida',
            'cantidad' => 2,
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id' => $laterLot->id,
            'tipo' => 'salida',
            'cantidad' => 0.5,
        ]);

        $this->from(route('portal.inventario.index'))
            ->post(route('portal.inventario.consumos.store'), [
                'producto_id' => $product->id,
                'cantidad' => '4',
                'motivo' => 'Consumo mayor a existencias',
            ])
            ->assertRedirect(route('portal.inventario.index'))
            ->assertSessionHasErrors('cantidad');

        $this->assertDatabaseCount('movimientos_inventario', 2);
        $this->assertDatabaseHas('lotes_inventario', [
            'id' => $laterLot->id,
            'cantidad_disponible' => 3.5,
        ]);
    }

    public function test_dashboard_alerts_when_vaccine_stock_is_at_or_below_fifteen_percent_of_optimal_capacity(): void
    {
        $administrator = $this->createAdministrator();
        $criticalProduct = $this->createProduct([
            'nombre' => 'Vacuna crítica',
            'stock_optimo' => 20,
        ]);
        $normalProduct = $this->createProduct([
            'codigo' => 'INV-002',
            'nombre' => 'Vacuna suficiente',
            'stock_optimo' => 20,
        ]);
        $otherProduct = $this->createProduct([
            'codigo' => 'INV-003',
            'nombre' => 'Material general',
            'stock_optimo' => 20,
        ]);
        CatalogoVacuna::query()->create([
            'nombre' => 'Vacuna crítica',
            'producto_id' => $criticalProduct->id,
        ]);
        CatalogoVacuna::query()->create([
            'nombre' => 'Vacuna suficiente',
            'producto_id' => $normalProduct->id,
        ]);
        $criticalProduct->lotes()->create([
            'cantidad_inicial' => 3,
            'cantidad_disponible' => 3,
            'estado' => 'disponible',
        ]);
        $criticalProduct->lotes()->create([
            'fecha_caducidad' => now()->subDay()->toDateString(),
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 100,
            'estado' => 'disponible',
        ]);
        $normalProduct->lotes()->create([
            'cantidad_inicial' => 4,
            'cantidad_disponible' => 4,
            'estado' => 'disponible',
        ]);
        $otherProduct->lotes()->create([
            'cantidad_inicial' => 0,
            'cantidad_disponible' => 0,
            'estado' => 'disponible',
        ]);

        $inventoryResponse = $this->actingAs($administrator)
            ->get(route('portal.inventario.index'));

        $inventoryResponse
            ->assertOk()
            ->assertSee('Vacuna crítica')
            ->assertSee('15%')
            ->assertViewHas('productosCriticos', fn ($products) => $products->pluck('id')->all() === [$criticalProduct->id]);

        $this->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Alerta crítica de inventario')
            ->assertSee('Vacuna crítica')
            ->assertDontSee('Vacuna suficiente');
    }

    public function test_administrator_can_link_a_vaccine_catalog_entry_to_a_product(): void
    {
        $administrator = $this->createAdministrator();
        $vaccine = CatalogoVacuna::query()->create(['nombre' => 'Rabia']);

        $response = $this->actingAs($administrator)
            ->post(route('portal.inventario.productos.store'), [
                'codigo' => 'VAC-001',
                'nombre' => 'Vacuna antirrábica',
                'unidad_base' => 'dosis',
                'costo_referencia' => 60,
                'precio_venta' => 100,
                'stock_minimo' => 2,
                'stock_optimo' => 20,
                'venta_publico' => '1',
                'uso_interno' => '0',
                'maneja_lote' => '1',
                'maneja_caducidad' => '1',
                'vacuna_id' => $vaccine->id,
            ]);

        $response->assertRedirect(route('portal.inventario.index'))
            ->assertSessionHasNoErrors();
        $product = Producto::query()->where('codigo', 'VAC-001')->firstOrFail();

        $this->assertDatabaseHas('catalogo_vacunas', [
            'id' => $vaccine->id,
            'producto_id' => $product->id,
        ]);
    }

    public function test_opening_the_official_inventory_report_is_audited(): void
    {
        $administrator = $this->createAdministrator();
        $product = $this->createProduct();

        $this->actingAs($administrator)
            ->get(route('portal.inventario.reportes'))
            ->assertOk()
            ->assertSee($product->nombre);

        $audit = Auditoria::query()->firstOrFail();

        $this->assertSame($administrator->id, $audit->user_id);
        $this->assertSame('reporte_inventario_consultado', $audit->accion);
        $this->assertSame('reporte_inventario', $audit->entidad);
    }

    public function test_vaccine_product_link_requires_an_optimal_stock_capacity(): void
    {
        $administrator = $this->createAdministrator();
        $vaccine = CatalogoVacuna::query()->create(['nombre' => 'Rabia']);

        $this->actingAs($administrator)
            ->from(route('portal.inventario.index'))
            ->post(route('portal.inventario.productos.store'), [
                'codigo' => 'VAC-001',
                'nombre' => 'Vacuna antirrábica',
                'unidad_base' => 'dosis',
                'precio_venta' => 100,
                'stock_minimo' => 2,
                'vacuna_id' => $vaccine->id,
            ])
            ->assertRedirect(route('portal.inventario.index'))
            ->assertSessionHasErrors('stock_optimo');

        $this->assertDatabaseMissing('productos', ['codigo' => 'VAC-001']);
    }

    private function createAdministrator(): User
    {
        $role = Role::query()->create(['nombre' => 'Administrador']);

        return User::factory()->create([
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Producto
    {
        return Producto::query()->create(array_merge([
            'codigo' => 'INV-001',
            'nombre' => 'Producto de inventario',
            'unidad_base' => 'pieza',
            'costo_referencia' => 10,
            'precio_venta' => 20,
            'stock_minimo' => 1,
            'stock_optimo' => null,
            'estado' => 'activo',
        ], $attributes));
    }
}
