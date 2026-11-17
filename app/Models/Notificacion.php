<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = ['user_id', 'tipo', 'titulo', 'mensaje', 'programada_at', 'leida_at', 'canal', 'estado', 'datos'];

    protected $casts = [
        'programada_at' => 'datetime',
        'leida_at' => 'datetime',
        'datos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
