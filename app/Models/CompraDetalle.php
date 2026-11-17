<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    protected $table = 'compra_detalles';

    protected $fillable = ['compra_id', 'producto_id', 'presentacion_id', 'cantidad', 'cantidad_base', 'costo_unitario', 'subtotal'];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'cantidad_base' => 'decimal:3',
        'costo_unitario' => 'decimal:4',
        'subtotal' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
