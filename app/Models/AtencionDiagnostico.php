<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtencionDiagnostico extends Model
{
    protected $table = 'atencion_diagnosticos';

    protected $fillable = ['atencion_id', 'diagnostico_id', 'diagnostico_personalizado', 'observaciones'];

    public function atencion(): BelongsTo
    {
        return $this->belongsTo(Atencion::class, 'atencion_id');
    }

    public function diagnostico(): BelongsTo
    {
        return $this->belongsTo(Diagnostico::class, 'diagnostico_id');
    }
}
