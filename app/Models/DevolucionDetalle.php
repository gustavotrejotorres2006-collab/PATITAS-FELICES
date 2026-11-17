<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionDetalle extends Model
{
    protected $table = 'devolucion_detalles';

    protected $fillable = ['devolucion_id', 'venta_detalle_id', 'cantidad', 'monto', 'condicion_producto', 'reingresa_inventario'];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'monto' => 'decimal:2',
        'reingresa_inventario' => 'boolean',
    ];

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(Devolucion::class, 'devolucion_id');
    }

    public function ventaDetalle(): BelongsTo
    {
        return $this->belongsTo(VentaDetalle::class, 'venta_detalle_id');
    }
}
