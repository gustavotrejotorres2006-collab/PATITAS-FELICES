<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Profesional;
use App\Models\Propietario;
use Illuminate\Database\Eloquent\Collection;

class CitaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Cita
    {
        return Cita::query()->create($data);
    }

    public function findById(int $id): ?Cita
    {
        return Cita::query()->find($id);
    }

    /**
     * @return Collection<int, Cita>
     */
    public function listForMascota(Mascota $mascota): Collection
    {
        return $mascota->citas()->orderBy('inicio')->get();
    }

    /**
     * @return Collection<int, Cita>
     */
    public function listForProfesional(Profesional $profesional): Collection
    {
        return $profesional->citas()->orderBy('inicio')->get();
    }

    /**
     * @return Collection<int, Cita>
     */
    public function listForOwner(Propietario $propietario): Collection
    {
        return $propietario->citas()->orderBy('inicio')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Cita $cita, array $data): Cita
    {
        $cita->update($data);

        return $cita->fresh();
    }
}
