<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntecedenteClinico extends Model
{
    protected $table = 'antecedentes_clinicos';

    protected $fillable = ['mascota_id', 'atencion_id', 'tipo', 'descripcion', 'fecha', 'registrado_por'];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'atencion_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
