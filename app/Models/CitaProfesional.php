<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitaProfesional extends Model
{
    protected $table = 'cita_profesionales';

    protected $fillable = ['cita_id', 'profesional_id', 'responsable'];

    protected $casts = [
        'responsable' => 'boolean',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }
}
