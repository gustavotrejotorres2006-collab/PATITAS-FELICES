<?php

namespace App\Services;

use App\Models\Profesional;
use Illuminate\Database\Eloquent\Collection;

class ProfesionalService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Profesional
    {
        return Profesional::query()->create($data);
    }

    public function findById(int $id): ?Profesional
    {
        return Profesional::query()->find($id);
    }

    /**
     * @return Collection<int, Profesional>
     */
    public function listActive(): Collection
    {
        return Profesional::query()->where('estado', 'activo')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Profesional $profesional, array $data): Profesional
    {
        $profesional->update($data);

        return $profesional->fresh();
    }
}
