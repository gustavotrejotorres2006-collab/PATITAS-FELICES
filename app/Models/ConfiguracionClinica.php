<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionClinica extends Model
{
    protected $table = 'configuracion_clinica';

    protected $fillable = ['clave', 'valor'];
}
