<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee('Correo electrónico')
            ->assertSee('Contraseña');
    }

    public function test_active_administrator_can_log_in_and_view_the_portal(): void
    {
        $administrator = $this->createAdministrator();

        $this->post(route('login.store'), [
            'email' => $administrator->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($administrator);
        $this->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Resumen del portal');
    }

    public function test_invalid_credentials_return_a_login_error(): void
    {
        $administrator = $this->createAdministrator();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $administrator->email,
                'password' => 'incorrecta',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $administrator = $this->createAdministrator(['estado' => 'inactivo']);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $administrator->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_forbids_users_without_the_administrator_role(): void
    {
        $role = Role::query()->create(['nombre' => 'Recepcionista']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_forbids_users_with_an_inactive_role(): void
    {
        $role = Role::query()->create([
            'nombre' => 'Administrador',
            'activo' => false,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertForbidden();
    }

    public function test_logout_ends_the_session_and_redirects_home(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_login_is_rate_limited_by_email_and_ip(): void
    {
        $credentials = [
            'email' => 'limit@patitasfelices.test',
            'password' => 'incorrecta',
        ];

        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))
                ->post(route('login.store'), $credentials)
                ->assertRedirect(route('login'));
        }

        $this->from(route('login'))
            ->post(route('login.store'), $credentials)
            ->assertTooManyRequests();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createAdministrator(array $attributes = []): User
    {
        $role = Role::query()->create(['nombre' => 'Administrador']);

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'password' => 'password',
        ], $attributes));
    }
}
