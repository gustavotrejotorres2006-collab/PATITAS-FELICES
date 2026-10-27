<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mascota extends Model
{
    use SoftDeletes;

    protected $table = 'mascotas';

    protected $fillable = [
        'codigo',
        'nombre',
        'especie',
        'raza',
        'sexo',
        'fecha_nacimiento',
        'color',
        'microchip',
        'esterilizado',
        'foto_path',
        'observaciones',
        'estado',
        'validado_por',
        'validado_at',
    ];

    protected $casts = [
        'esterilizado' => 'boolean',
        'fecha_nacimiento' => 'date',
        'validado_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    public function propietarios(): BelongsToMany
    {
        return $this->belongsToMany(Propietario::class, 'mascota_propietario', 'mascota_id', 'propietario_id')
            ->withPivot(['es_principal', 'fecha_inicio', 'fecha_fin', 'estado'])
            ->withTimestamps();
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(PermisoMascota::class, 'mascota_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'mascota_id');
    }
}
