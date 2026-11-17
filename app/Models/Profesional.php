<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesional extends Model
{
    protected $table = 'profesionales';

    protected $fillable = ['user_id', 'nombre', 'apellido', 'cedula_profesional', 'telefono', 'estado'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidad::class, 'profesional_especialidad', 'profesional_id', 'especialidad_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioProfesional::class, 'profesional_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'profesional_id');
    }
}
