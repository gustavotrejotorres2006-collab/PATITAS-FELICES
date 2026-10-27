<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Database\Eloquent\Collection;

class VentaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Venta
    {
        return Venta::query()->create($data);
    }

    public function findById(int $id): ?Venta
    {
        return Venta::query()->find($id);
    }

    /**
     * @return Collection<int, Venta>
     */
    public function listToday(): Collection
    {
        return Venta::query()->whereDate('fecha', now()->toDateString())->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addDetail(Venta $venta, array $data): VentaDetalle
    {
        $data['venta_id'] = $venta->id;

        return VentaDetalle::query()->create($data);
    }
}
