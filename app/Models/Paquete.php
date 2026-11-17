<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paquete extends Model
{
    use SoftDeletes;

    protected $table = 'paquetes';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'precio', 'activo'];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function servicios(): HasMany
    {
        return $this->hasMany(PaqueteServicio::class, 'paquete_id');
    }
}
