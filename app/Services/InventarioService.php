<?php

namespace App\Services;

use App\Models\LoteInventario;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

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
            ->withSum('lotes as stock_disponible', 'cantidad_disponible')
            ->havingRaw('COALESCE(stock_disponible, 0) < stock_minimo')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createLote(array $data): LoteInventario
    {
        return LoteInventario::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function registerMovement(array $data): MovimientoInventario
    {
        return MovimientoInventario::query()->create($data);
    }
}
