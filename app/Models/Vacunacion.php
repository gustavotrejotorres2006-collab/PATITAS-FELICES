<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacunacion extends Model
{
    protected $table = 'vacunaciones';

    protected $fillable = ['mascota_id', 'vacuna_id', 'atencion_id', 'fecha_aplicacion', 'proxima_fecha', 'lote', 'dosis', 'unidad_dosis', 'aplicada_por', 'observaciones'];

    protected $casts = [
        'fecha_aplicacion' => 'date',
        'proxima_fecha' => 'date',
        'dosis' => 'decimal:2',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function vacuna(): BelongsTo
    {
        return $this->belongsTo(CatalogoVacuna::class, 'vacuna_id');
    }
}
