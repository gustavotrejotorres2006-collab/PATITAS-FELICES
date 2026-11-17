<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Desparasitacion extends Model
{
    protected $table = 'desparasitaciones';

    protected $fillable = ['mascota_id', 'atencion_id', 'producto', 'fecha', 'proxima_fecha', 'dosis', 'unidad_dosis', 'lote', 'aplicada_por', 'observaciones'];

    protected $casts = [
        'fecha' => 'date',
        'proxima_fecha' => 'date',
        'dosis' => 'decimal:2',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }
}
