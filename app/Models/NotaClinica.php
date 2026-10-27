<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaClinica extends Model
{
    protected $table = 'notas_clinicas';

    protected $fillable = ['atencion_id', 'profesional_id', 'autor_id', 'contenido', 'version', 'nota_anterior_id'];

    protected $casts = [
        'version' => 'integer',
    ];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'atencion_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
