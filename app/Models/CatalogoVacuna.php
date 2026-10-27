<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoVacuna extends Model
{
    protected $table = 'catalogo_vacunas';

    protected $fillable = ['nombre', 'especie', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
