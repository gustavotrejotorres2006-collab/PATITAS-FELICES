<?php

namespace App\Services;

use App\Models\Mascota;
use App\Models\Propietario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($mascota, $propietario, $principal): void {
            if ($principal) {
                $mascota->propietarios()
                    ->newPivotQuery()
                    ->where('mascota_id', $mascota->getKey())
                    ->where('estado', 'activo')
                    ->where('es_principal', true)
                    ->where('propietario_id', '!=', $propietario->getKey())
                    ->update(['es_principal' => false]);
            }

            $mascota->propietarios()->syncWithoutDetaching([
                $propietario->getKey() => [
                    'es_principal' => $principal,
                    'estado' => 'activo',
                ],
            ]);
        });
    }
}
