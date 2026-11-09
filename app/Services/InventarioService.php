<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\LoteInventario;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createProduct(array $data): Producto
    {
        return Producto::query()->create($data);
    }

    public function findProductById(int $id): ?Producto
    {
        return Producto::query()->find($id);
    }

    /**
     * @return Collection<int, Producto>
     */
    public function lowStockProducts(): Collection
    {
        return Producto::query()
            ->whereRaw(
                '(SELECT COALESCE(SUM(lotes_inventario.cantidad_disponible), 0) FROM lotes_inventario WHERE lotes_inventario.producto_id = productos.id AND lotes_inventario.estado = ? AND (lotes_inventario.fecha_caducidad IS NULL OR lotes_inventario.fecha_caducidad >= ?)) < productos.stock_minimo',
                ['disponible', today()->toDateString()],
            )
            ->get();
    }

    /**
     * @return Collection<int, Producto>
     */
    public function criticalVaccineProducts(): Collection
    {
        return Producto::query()
            ->where('estado', 'activo')
            ->whereNotNull('stock_optimo')
            ->whereHas('vacuna')
            ->whereRaw(
                '(SELECT COALESCE(SUM(lotes_inventario.cantidad_disponible), 0) FROM lotes_inventario WHERE lotes_inventario.producto_id = productos.id AND lotes_inventario.estado = ? AND (lotes_inventario.fecha_caducidad IS NULL OR lotes_inventario.fecha_caducidad >= ?)) <= productos.stock_optimo * 0.15',
                ['disponible', today()->toDateString()],
            )
            ->with('vacuna')
            ->withSum([
                'lotes as stock_actual' => fn (Builder $query) => $query
                    ->where('estado', 'disponible')
                    ->where(fn (Builder $query) => $query
                        ->whereNull('fecha_caducidad')
                        ->orWhereDate('fecha_caducidad', '>=', today())),
            ], 'cantidad_disponible')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $details
     */
    public function receivePurchase(array $data, array $details, User $user): Compra
    {
        return DB::transaction(function () use ($data, $details, $user): Compra {
            $purchase = Compra::query()->create([
                'folio' => 'COMP-'.Str::upper((string) Str::ulid()),
                'proveedor_id' => $data['proveedor_id'] ?? null,
                'folio_proveedor' => $data['folio_proveedor'] ?? null,
                'fecha' => $data['fecha'],
                'estado' => 'recibida',
                'subtotal' => 0,
                'total' => 0,
                'observaciones' => $data['observaciones'] ?? null,
                'creado_por' => $user->id,
            ]);
            $total = 0;

            foreach ($details as $index => $detail) {
                $product = Producto::query()
                    ->whereKey($detail['producto_id'])
                    ->where('estado', 'activo')
                    ->lockForUpdate()
                    ->firstOrFail();
                $quantity = round((float) $detail['cantidad'], 3);
                $unitCost = round((float) $detail['costo_unitario'], 4);

                if ($quantity <= 0 || $unitCost < 0) {
                    throw ValidationException::withMessages([
                        "detalles.{$index}.cantidad" => 'La cantidad y el costo deben ser válidos.',
                    ]);
                }

                if ($product->maneja_lote && empty($detail['numero_lote'])) {
                    throw ValidationException::withMessages([
                        "detalles.{$index}.numero_lote" => 'El número de lote es obligatorio para este producto.',
                    ]);
                }

                if ($product->maneja_caducidad && empty($detail['fecha_caducidad'])) {
                    throw ValidationException::withMessages([
                        "detalles.{$index}.fecha_caducidad" => 'La fecha de caducidad es obligatoria para este producto.',
                    ]);
                }

                $lineTotal = round($quantity * $unitCost, 2);
                $purchaseDetail = CompraDetalle::query()->create([
                    'compra_id' => $purchase->id,
                    'producto_id' => $product->id,
                    'cantidad' => $quantity,
                    'cantidad_base' => $quantity,
                    'costo_unitario' => $unitCost,
                    'subtotal' => $lineTotal,
                ]);
                $lot = LoteInventario::query()->create([
                    'producto_id' => $product->id,
                    'compra_detalle_id' => $purchaseDetail->id,
                    'numero_lote' => $detail['numero_lote'] ?? null,
                    'fecha_caducidad' => $detail['fecha_caducidad'] ?? null,
                    'cantidad_inicial' => $quantity,
                    'cantidad_disponible' => $quantity,
                    'costo_unitario' => $unitCost,
                    'estado' => 'disponible',
                ]);
                MovimientoInventario::query()->create([
                    'producto_id' => $product->id,
                    'lote_id' => $lot->id,
                    'tipo' => 'entrada',
                    'cantidad' => $quantity,
                    'costo_unitario' => $unitCost,
                    'referencia_tipo' => Compra::class,
                    'referencia_id' => $purchase->id,
                    'motivo' => 'Recepción de compra '.$purchase->folio,
                    'usuario_id' => $user->id,
                ]);
                $total += $lineTotal;
            }

            $purchase->update([
                'subtotal' => $total,
                'total' => $total,
            ]);

            return $purchase->load('detalles.producto', 'proveedor');
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createLote(array $data): LoteInventario
    {
        $initialQuantity = round((float) ($data['cantidad_inicial'] ?? 0), 3);
        $availableQuantity = round((float) ($data['cantidad_disponible'] ?? $initialQuantity), 3);

        if (
            (int) round($initialQuantity * 1000) <= 0
            || (int) round($availableQuantity * 1000) !== (int) round($initialQuantity * 1000)
        ) {
            throw ValidationException::withMessages([
                'cantidad_disponible' => 'Un lote nuevo debe ingresar con toda su cantidad inicial disponible.',
            ]);
        }

        return DB::transaction(function () use ($data, $initialQuantity): LoteInventario {
            Producto::query()
                ->whereKey($data['producto_id'])
                ->where('estado', 'activo')
                ->lockForUpdate()
                ->firstOrFail();

            $lot = LoteInventario::query()->create([
                'producto_id' => $data['producto_id'],
                'compra_detalle_id' => $data['compra_detalle_id'] ?? null,
                'numero_lote' => $data['numero_lote'] ?? null,
                'fecha_caducidad' => $data['fecha_caducidad'] ?? null,
                'cantidad_inicial' => $initialQuantity,
                'cantidad_disponible' => $initialQuantity,
                'costo_unitario' => $data['costo_unitario'] ?? 0,
                'estado' => 'disponible',
            ]);

            MovimientoInventario::query()->create([
                'producto_id' => $lot->producto_id,
                'lote_id' => $lot->id,
                'tipo' => 'entrada',
                'cantidad' => $initialQuantity,
                'costo_unitario' => $lot->costo_unitario,
                'referencia_tipo' => $data['referencia_tipo'] ?? null,
                'referencia_id' => $data['referencia_id'] ?? null,
                'motivo' => $data['motivo'] ?? 'Registro inicial de existencias',
                'usuario_id' => $data['usuario_id'] ?? null,
            ]);

            return $lot;
        });
    }

    /**
     * @return Collection<int, MovimientoInventario>
     */
    public function consume(
        Producto $producto,
        float $cantidad,
        ?User $user,
        string $motivo,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null,
    ): Collection {
        return DB::transaction(function () use ($producto, $cantidad, $user, $motivo, $referenciaTipo, $referenciaId): Collection {
            $lockedProduct = Producto::query()
                ->whereKey($producto->id)
                ->where('estado', 'activo')
                ->lockForUpdate()
                ->firstOrFail();
            $requestedUnits = (int) round($cantidad * 1000);

            if ($requestedUnits <= 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad debe ser mayor que cero.',
                ]);
            }

            $lots = LoteInventario::query()
                ->where('producto_id', $lockedProduct->id)
                ->where('estado', 'disponible')
                ->where('cantidad_disponible', '>', 0)
                ->where(fn (Builder $query) => $query
                    ->whereNull('fecha_caducidad')
                    ->orWhereDate('fecha_caducidad', '>=', today()))
                ->orderByRaw('CASE WHEN fecha_caducidad IS NULL THEN 1 ELSE 0 END')
                ->orderBy('fecha_caducidad')
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $availableUnits = $lots->sum(
                fn (LoteInventario $lot): int => (int) round((float) $lot->cantidad_disponible * 1000),
            );

            if ($availableUnits < $requestedUnits) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No hay existencias suficientes disponibles para completar la salida.',
                ]);
            }

            $remainingUnits = $requestedUnits;
            $movements = new Collection;

            foreach ($lots as $lot) {
                if ($remainingUnits === 0) {
                    break;
                }

                $lotUnits = (int) round((float) $lot->cantidad_disponible * 1000);
                $consumedUnits = min($lotUnits, $remainingUnits);
                $remainingUnits -= $consumedUnits;
                $newQuantity = ($lotUnits - $consumedUnits) / 1000;
                $lot->update([
                    'cantidad_disponible' => $newQuantity,
                    'estado' => $lotUnits === $consumedUnits ? 'agotado' : 'disponible',
                ]);
                $movements->push(MovimientoInventario::query()->create([
                    'producto_id' => $lockedProduct->id,
                    'lote_id' => $lot->id,
                    'tipo' => 'salida',
                    'cantidad' => $consumedUnits / 1000,
                    'costo_unitario' => $lot->costo_unitario,
                    'referencia_tipo' => $referenciaTipo,
                    'referencia_id' => $referenciaId,
                    'motivo' => $motivo,
                    'usuario_id' => $user?->id,
                ]));
            }

            return $movements;
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function registerMovement(array $data): MovimientoInventario
    {
        if (($data['tipo'] ?? null) !== 'entrada') {
            throw ValidationException::withMessages([
                'tipo' => 'Las salidas deben procesarse mediante consumo FIFO.',
            ]);
        }

        $quantity = round((float) ($data['cantidad'] ?? 0), 3);

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad de entrada debe ser mayor que cero.',
            ]);
        }

        $lot = $this->createLote([
            'producto_id' => $data['producto_id'],
            'numero_lote' => $data['numero_lote'] ?? null,
            'fecha_caducidad' => $data['fecha_caducidad'] ?? null,
            'cantidad_inicial' => $quantity,
            'cantidad_disponible' => $quantity,
            'costo_unitario' => $data['costo_unitario'] ?? 0,
            'estado' => 'disponible',
            'referencia_tipo' => $data['referencia_tipo'] ?? null,
            'referencia_id' => $data['referencia_id'] ?? null,
            'motivo' => $data['motivo'] ?? null,
            'usuario_id' => $data['usuario_id'] ?? null,
        ]);

        return $lot->movimientos()->firstOrFail();
    }
}
