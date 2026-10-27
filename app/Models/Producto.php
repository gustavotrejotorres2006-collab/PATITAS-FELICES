<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = ['categoria_id', 'codigo', 'codigo_barras', 'nombre', 'descripcion', 'unidad_base', 'costo_referencia', 'precio_venta', 'stock_minimo', 'venta_publico', 'uso_interno', 'maneja_lote', 'maneja_caducidad', 'estado'];

    protected $casts = [
        'costo_referencia' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'decimal:3',
        'venta_publico' => 'boolean',
        'uso_interno' => 'boolean',
        'maneja_lote' => 'boolean',
        'maneja_caducidad' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    public function presentaciones(): HasMany
    {
        return $this->hasMany(PresentacionProducto::class, 'producto_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class, 'producto_id');
    }
}
