<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EspacioClinico extends Model
{
    protected $table = 'espacios_clinicos';

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
        'activo',
        'desinfectado_at',
        'desinfectado_por',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'desinfectado_at' => 'datetime',
        ];
    }

    public function cirugias(): HasMany
    {
        return $this->hasMany(Cirugia::class, 'espacio_id');
    }

    public function hospitalizaciones(): HasMany
    {
        return $this->hasMany(Hospitalizacion::class, 'espacio_id');
    }
}
