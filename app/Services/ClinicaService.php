<?php

namespace App\Services;

use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\NotaClinica;
use Illuminate\Database\Eloquent\Collection;

class ClinicaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createAtencion(array $data): Atencion
    {
        return Atencion::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addNota(array $data): NotaClinica
    {
        return NotaClinica::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDiagnostico(array $data): Diagnostico
    {
        return Diagnostico::query()->create($data);
    }

    /**
     * @return Collection<int, Atencion>
     */
    public function listAtencionesOpen(): Collection
    {
        return Atencion::query()->where('estado', 'abierta')->get();
    }
}
