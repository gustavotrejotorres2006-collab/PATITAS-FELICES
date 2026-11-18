<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'estado',
        'telefono',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_id' => 'integer',
        ];
    }

    /**
     * Nombre de la ruta de inicio del portal a la que el rol del usuario tiene acceso.
     */
    public function portalHomeRouteName(): string
    {
        return match (strtolower($this->role?->nombre ?? '')) {
            'recepcionista' => 'portal.citas.index',
            'veterinario' => 'portal.atenciones.index',
            default => 'portal.dashboard',
        };
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function propietario(): HasMany
    {
        return $this->hasMany(Propietario::class, 'user_id');
    }

    public function profesional(): HasOne
    {
        return $this->hasOne(Profesional::class, 'user_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'user_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'user_id');
    }
}
