<?php

namespace App\Services;

use App\Models\Caja;
use Illuminate\Database\Eloquent\Collection;

class CajaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function open(array $data): Caja
    {
        return Caja::query()->create($data);
    }

    public function findById(int $id): ?Caja
    {
        return Caja::query()->find($id);
    }

    /**
     * @return Collection<int, Caja>
     */
    public function listOpen(): Collection
    {
        return Caja::query()->where('estado', 'abierta')->get();
    }
}
