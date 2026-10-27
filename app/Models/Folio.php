<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folio extends Model
{
    protected $table = 'folios';

    protected $fillable = ['modulo', 'prefijo', 'ultimo_numero', 'longitud'];

    protected $casts = [
        'ultimo_numero' => 'integer',
        'longitud' => 'integer',
    ];
}
