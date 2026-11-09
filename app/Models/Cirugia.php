<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cirugia extends Model
{
    protected $table = 'cirugias';

    protected $fillable = [
        'folio',
        'atencion_id',
        'espacio_id',
        'cirujano_id',
        'anestesiologo_id',
        'propietario_id',
        'programada_at',
        'duracion_minutos',
        'estado',
        'consentimiento_firmado_at',
        'consentimiento_verificado_por',
        'observaciones',
        'completada_at',
        'completada_por',
    ];

    protected function casts(): array
    {
        return [
            'programada_at' => 'datetime',
            'duracion_minutos' => 'integer',
            'consentimiento_firmado_at' => 'datetime',
            'completada_at' => 'datetime',
        ];
    }

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'atencion_id');
    }

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(EspacioClinico::class, 'espacio_id');
    }

    public function cirujano(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'cirujano_id');
    }

    public function anestesiologo(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'anestesiologo_id');
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'propietario_id');
    }

    public function insumos(): HasMany
    {
        return $this->hasMany(CirugiaInsumo::class, 'cirugia_id');
    }
}
