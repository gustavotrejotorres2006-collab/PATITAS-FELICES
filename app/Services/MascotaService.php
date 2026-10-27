<?php

namespace App\Services;

use App\Models\Mascota;
use App\Models\Propietario;
use Illuminate\Database\Eloquent\Collection;

class MascotaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Mascota
    {
        return Mascota::query()->create($data);
    }

    public function findById(int $id): ?Mascota
    {
        return Mascota::query()->find($id);
    }

    /**
     * @return Collection<int, Mascota>
     */
    public function listByOwner(Propietario $propietario): Collection
    {
        return $propietario->mascotas()->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Mascota $mascota, array $data): Mascota
    {
        $mascota->update($data);

        return $mascota->fresh();
    }

    public function assignOwner(Mascota $mascota, Propietario $propietario, bool $principal = false): void
    {
        $mascota->propietarios()->syncWithoutDetaching([
            $propietario->id => [
                'es_principal' => $principal,
                'estado' => 'activo',
            ],
        ]);
    }
}
