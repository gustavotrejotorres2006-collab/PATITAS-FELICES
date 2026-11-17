<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Propietario extends Model
{
    use SoftDeletes;

    protected $table = 'propietarios';

    protected $fillable = ['user_id', 'codigo', 'nombre', 'apellido', 'telefono', 'email', 'direccion', 'estado'];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mascotas(): BelongsToMany
    {
        return $this->belongsToMany(Mascota::class, 'mascota_propietario', 'propietario_id', 'mascota_id')
            ->withPivot(['es_principal', 'fecha_inicio', 'fecha_fin', 'estado'])
            ->withTimestamps();
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'propietario_id');
    }
}
