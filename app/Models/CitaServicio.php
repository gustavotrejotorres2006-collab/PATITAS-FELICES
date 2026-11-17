<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitaServicio extends Model
{
    protected $table = 'cita_servicios';

    protected $fillable = ['cita_id', 'servicio_id', 'precio', 'duracion_minutos'];

    protected $casts = [
        'precio' => 'decimal:2',
        'duracion_minutos' => 'integer',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
