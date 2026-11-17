<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaDetalleLote extends Model
{
    protected $table = 'venta_detalle_lotes';

    protected $fillable = ['venta_detalle_id', 'lote_id', 'cantidad'];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    public function ventaDetalle(): BelongsTo
    {
        return $this->belongsTo(VentaDetalle::class, 'venta_detalle_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteInventario::class, 'lote_id');
    }
}
