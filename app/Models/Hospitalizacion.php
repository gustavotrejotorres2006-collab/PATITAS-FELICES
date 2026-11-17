<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hospitalizacion extends Model
{
    protected $table = 'hospitalizaciones';

    protected $fillable = [
        'folio',
        'atencion_id',
        'espacio_id',
        'ingresada_at',
        'ingresada_por',
        'estado',
        'alta_at',
        'dada_por',
    ];

    protected function casts(): array
    {
        return [
            'ingresada_at' => 'datetime',
            'alta_at' => 'datetime',
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

    public function monitoreos(): HasMany
    {
        return $this->hasMany(MonitoreoHospitalario::class, 'hospitalizacion_id');
    }

    public function ultimoMonitoreo(): HasOne
    {
        return $this->hasOne(MonitoreoHospitalario::class, 'hospitalizacion_id')
            ->latestOfMany('registrada_at');
    }

    public function proximoMonitoreoAt(): Carbon
    {
        $base = $this->ultimoMonitoreo?->registrada_at ?? $this->ingresada_at;

        return $base->copy()->addHours(4);
    }

    public function monitoreoVencido(): bool
    {
        return now()->greaterThanOrEqualTo($this->proximoMonitoreoAt());
    }
}
