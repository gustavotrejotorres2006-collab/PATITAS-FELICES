<?php

namespace App\Services;

use App\Models\Propietario;
use Illuminate\Database\Eloquent\Collection;

class PropietarioService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Propietario
    {
        return Propietario::query()->create($data);
    }

    public function findById(int $id): ?Propietario
    {
        return Propietario::query()->find($id);
    }

    /**
     * @return Collection<int, Propietario>
     */
    public function listActive(): Collection
    {
        return Propietario::query()->where('estado', 'activo')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Propietario $propietario, array $data): Propietario
    {
        $propietario->update($data);

        return $propietario->fresh();
    }
}
