<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaqueteServicio extends Model
{
    protected $table = 'paquete_servicios';

    protected $fillable = ['paquete_id', 'servicio_id', 'cantidad'];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class, 'paquete_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
