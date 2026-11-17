<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignosVitales extends Model
{
    protected $table = 'signos_vitales';

    protected $fillable = ['atencion_id', 'peso_kg', 'temperatura_c', 'frecuencia_cardiaca', 'frecuencia_respiratoria', 'presion_arterial', 'condicion_corporal', 'observaciones'];

    protected $casts = [
        'peso_kg' => 'decimal:2',
        'temperatura_c' => 'decimal:2',
        'condicion_corporal' => 'decimal:1',
    ];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'atencion_id');
    }
}
