<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoreoHospitalario extends Model
{
    protected $table = 'monitoreos_hospitalarios';

    protected $fillable = [
        'hospitalizacion_id',
        'registrada_por',
        'registrada_at',
        'temperatura_c',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'tratamiento',
        'nota_progreso',
    ];

    protected function casts(): array
    {
        return [
            'registrada_at' => 'datetime',
            'temperatura_c' => 'decimal:2',
            'frecuencia_cardiaca' => 'integer',
            'frecuencia_respiratoria' => 'integer',
        ];
    }

    public function hospitalizacion(): BelongsTo
    {
        return $this->belongsTo(Hospitalizacion::class, 'hospitalizacion_id');
    }

    public function registradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }
}
