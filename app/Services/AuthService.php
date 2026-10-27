<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function login(array $credentials): User
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son válidas.'],
            ]);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'email' => ['No se pudo recuperar la sesión del usuario.'],
            ]);
        }

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'estado' => $data['estado'] ?? 'activo',
            'telefono' => $data['telefono'] ?? null,
        ]);
    }
}
