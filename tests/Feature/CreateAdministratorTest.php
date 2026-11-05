<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdministratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_administrator_with_a_hashed_password(): void
    {
        $this->artisan('app:create-administrator', [
            'name' => 'Administradora',
            'email' => 'admin@patitasfelices.test',
        ])
            ->expectsQuestion('Contraseña del administrador (mínimo 12 caracteres)', 'una-clave-segura-123')
            ->expectsQuestion('Confirma la contraseña', 'una-clave-segura-123')
            ->expectsOutputToContain('Cuenta de administrador creada correctamente.')
            ->assertSuccessful();

        $administrator = User::query()->where('email', 'admin@patitasfelices.test')->firstOrFail();

        $this->assertModelExists($administrator);
        $this->assertSame('activo', $administrator->estado);
        $this->assertSame('Administrador', $administrator->role->nombre);
        $this->assertTrue(Hash::check('una-clave-segura-123', $administrator->password));
    }

    public function test_command_does_not_create_an_account_with_a_short_password(): void
    {
        $this->artisan('app:create-administrator', [
            'name' => 'Administradora',
            'email' => 'admin@patitasfelices.test',
        ])
            ->expectsQuestion('Contraseña del administrador (mínimo 12 caracteres)', 'corta')
            ->expectsQuestion('Confirma la contraseña', 'corta')
            ->expectsOutputToContain('La contraseña debe tener al menos 12 caracteres.')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'admin@patitasfelices.test']);
        $this->assertDatabaseMissing('roles', ['nombre' => 'Administrador']);
    }

    public function test_command_does_not_create_a_second_administrator_for_the_same_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'admin@patitasfelices.test']);

        $this->artisan('app:create-administrator', [
            'name' => 'Otra administradora',
            'email' => 'admin@patitasfelices.test',
        ])
            ->expectsQuestion('Contraseña del administrador (mínimo 12 caracteres)', 'una-clave-segura-123')
            ->expectsQuestion('Confirma la contraseña', 'una-clave-segura-123')
            ->expectsOutputToContain('Ya existe una cuenta con ese correo electrónico.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertModelExists($existingUser);
        $this->assertDatabaseMissing('roles', ['nombre' => 'Administrador']);
    }
}
