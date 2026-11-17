<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    protected $table = 'diagnosticos';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
