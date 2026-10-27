<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioProfesional extends Model
{
    protected $table = 'horarios_profesional';

    protected $fillable = ['profesional_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'activo'];

    protected $casts = [
        'dia_semana' => 'integer',
        'activo' => 'boolean',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }
}
