<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = ['usuario_id', 'abierta_at', 'monto_inicial', 'cerrada_at', 'monto_esperado', 'monto_contado', 'diferencia', 'observaciones_cierre', 'estado'];

    protected $casts = [
        'abierta_at' => 'datetime',
        'cerrada_at' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_esperado' => 'decimal:2',
        'monto_contado' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_id');
    }
}
