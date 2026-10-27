<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentacionProducto extends Model
{
    protected $table = 'presentaciones_producto';

    protected $fillable = ['producto_id', 'nombre', 'factor_a_unidad_base', 'codigo_barras', 'precio_venta', 'activa'];

    protected $casts = [
        'factor_a_unidad_base' => 'decimal:4',
        'precio_venta' => 'decimal:2',
        'activa' => 'boolean',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
