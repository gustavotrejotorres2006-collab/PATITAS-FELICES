<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atencion extends Model
{
    protected $table = 'atenciones';

    protected $fillable = ['folio', 'cita_id', 'mascota_id', 'propietario_id', 'profesional_id', 'creado_por', 'fecha_atencion', 'estado', 'motivo', 'hospitalizacion_recomendada'];

    protected $casts = [
        'fecha_atencion' => 'datetime',
        'hospitalizacion_recomendada' => 'boolean',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'propietario_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function signosVitales(): HasMany
    {
        return $this->hasMany(SignosVitales::class, 'atencion_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(NotaClinica::class, 'atencion_id');
    }

    public function diagnosticos(): HasMany
    {
        return $this->hasMany(AtencionDiagnostico::class, 'atencion_id');
    }

    public function vacunaciones(): HasMany
    {
        return $this->hasMany(Vacunacion::class, 'atencion_id');
    }

    public function cirugias(): HasMany
    {
        return $this->hasMany(Cirugia::class, 'atencion_id');
    }

    public function hospitalizaciones(): HasMany
    {
        return $this->hasMany(Hospitalizacion::class, 'atencion_id');
    }
}
