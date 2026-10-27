<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteInventario extends Model
{
    protected $table = 'lotes_inventario';

    protected $fillable = ['producto_id', 'compra_detalle_id', 'numero_lote', 'fecha_caducidad', 'cantidad_inicial', 'cantidad_disponible', 'costo_unitario', 'estado'];

    protected $casts = [
        'fecha_caducidad' => 'date',
        'cantidad_inicial' => 'decimal:3',
        'cantidad_disponible' => 'decimal:3',
        'costo_unitario' => 'decimal:4',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
