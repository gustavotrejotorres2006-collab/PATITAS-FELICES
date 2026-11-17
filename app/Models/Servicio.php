<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    use SoftDeletes;

    protected $table = 'servicios';

    protected $fillable = ['categoria_id', 'codigo', 'nombre', 'descripcion', 'duracion_minutos', 'precio', 'requiere_atencion', 'activo'];

    protected $casts = [
        'duracion_minutos' => 'integer',
        'precio' => 'decimal:2',
        'requiere_atencion' => 'boolean',
        'activo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaServicio::class, 'categoria_id');
    }

    public function detallesCita(): HasMany
    {
        return $this->hasMany(CitaServicio::class, 'servicio_id');
    }
}
