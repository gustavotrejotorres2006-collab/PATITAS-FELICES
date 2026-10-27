<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisoMascota extends Model
{
    protected $table = 'permisos_mascota';

    protected $fillable = ['mascota_id', 'propietario_id', 'permiso', 'permitido'];

    protected $casts = [
        'permitido' => 'boolean',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class, 'propietario_id');
    }
}
