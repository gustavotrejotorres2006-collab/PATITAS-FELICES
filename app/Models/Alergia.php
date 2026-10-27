<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alergia extends Model
{
    protected $table = 'alergias';

    protected $fillable = ['mascota_id', 'sustancia', 'reaccion', 'severidad', 'observaciones', 'activa'];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }
}
