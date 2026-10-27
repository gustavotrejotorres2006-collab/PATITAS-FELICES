<?php

namespace App\Services;

use App\Models\Compra;
use Illuminate\Database\Eloquent\Collection;

class CompraService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Compra
    {
        return Compra::query()->create($data);
    }

    public function findById(int $id): ?Compra
    {
        return Compra::query()->find($id);
    }

    /**
     * @return Collection<int, Compra>
     */
    public function listPending(): Collection
    {
        return Compra::query()->where('estado', 'pendiente')->get();
    }
}
