<?php

namespace App\Services;

use App\Models\Notificacion;
use Illuminate\Database\Eloquent\Collection;

class NotificacionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Notificacion
    {
        return Notificacion::query()->create($data);
    }

    /**
     * @return Collection<int, Notificacion>
     */
    public function listPending(): Collection
    {
        return Notificacion::query()->where('estado', 'pendiente')->get();
    }
}
