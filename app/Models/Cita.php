<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = ['folio', 'mascota_id', 'propietario_id', 'profesional_id', 'inicio', 'fin', 'estado', 'urgente', 'motivo', 'observaciones', 'creado_por', 'confirmado_por', 'confirmado_at'];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'urgente' => 'boolean',
        'confirmado_at' => 'datetime',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'propietario_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'cita_servicios', 'cita_id', 'servicio_id')
            ->withPivot(['precio', 'duracion_minutos'])
            ->withTimestamps();
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'cita_profesionales', 'cita_id', 'profesional_id')
            ->withPivot(['responsable'])
            ->withTimestamps();
    }

    public function atenciones(): HasMany
    {
        return $this->hasMany(Atencion::class, 'cita_id');
    }
}
