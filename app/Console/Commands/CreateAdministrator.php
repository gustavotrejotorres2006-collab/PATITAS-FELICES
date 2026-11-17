<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('app:create-administrator {name?} {email?}')]
#[Description('Create the initial administrator for the clinic portal')]
class CreateAdministrator extends Command
{
    public function handle(): int
    {
        $name = trim((string) ($this->argument('name') ?? $this->ask('Nombre del administrador')));
        $email = strtolower(trim((string) ($this->argument('email') ?? $this->ask('Correo electrónico'))));
        $password = (string) $this->secret('Contraseña del administrador (mínimo 12 caracteres)');
        $passwordConfirmation = (string) $this->secret('Confirma la contraseña');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ], [
            'email.unique' => 'Ya existe una cuenta con ese correo electrónico.',
            'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $role = Role::query()->firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Acceso administrador al portal', 'activo' => true],
        );

        if (! $role->activo) {
            $this->error('El rol Administrador está desactivado; actívalo antes de crear esta cuenta.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);

        $this->info('Cuenta de administrador creada correctamente.');

        return self::SUCCESS;
    }
}
