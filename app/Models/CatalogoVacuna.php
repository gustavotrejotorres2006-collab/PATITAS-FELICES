<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogoVacuna extends Model
{
    protected $table = 'catalogo_vacunas';

    protected $fillable = ['nombre', 'especie', 'descripcion', 'activo', 'producto_id'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
