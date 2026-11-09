<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Profesional;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfesionalManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_non_administrators_cannot_manage_professionals_or_specialties(): void
    {
        $this->get(route('portal.profesionales.index'))
            ->assertRedirect(route('login'));

        $recepcionistRole = Role::query()->create(['nombre' => 'Recepcionista']);
        $recepcionist = User::factory()->create([
            'role_id' => $recepcionistRole->id,
            'estado' => 'activo',
        ]);

        $this->actingAs($recepcionist)
            ->get(route('portal.profesionales.index'))
            ->assertForbidden();

        $this->actingAs($recepcionist)
            ->get(route('portal.especialidades.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_register_professional_with_specialties(): void
    {
        $administrator = $this->createAdministrator();
        $specialty = Especialidad::query()->create(['nombre' => 'Medicina preventiva']);

        $response = $this->actingAs($administrator)
            ->post(route('portal.profesionales.store'), [
                'nombre' => 'Ana',
                'apellido' => 'Vera',
                'cedula_profesional' => 'VET-001',
                'telefono' => '5551234567',
                'estado' => 'activo',
                'especialidades' => [$specialty->id],
                'user_id' => $administrator->id,
            ]);

        $profesional = Profesional::query()->where('cedula_profesional', 'VET-001')->firstOrFail();
        $response->assertRedirect(route('portal.profesionales.edit', $profesional));

        $this->assertSame('Ana', $profesional->nombre);
        $this->assertNull($profesional->user_id);
        $this->assertDatabaseHas('profesional_especialidad', [
            'profesional_id' => $profesional->id,
            'especialidad_id' => $specialty->id,
        ]);
    }

    public function test_administrator_can_create_a_linked_veterinarian_account_from_a_professional_profile(): void
    {
        $administrator = $this->createAdministrator();

        $response = $this->actingAs($administrator)
            ->post(route('portal.profesionales.store'), [
                'nombre' => 'Laura',
                'apellido' => 'Méndez',
                'estado' => 'activo',
                'usuario_email' => 'laura@example.test',
                'usuario_password' => 'segura1234',
            ]);
        $response->assertSessionHasNoErrors();

        $professional = Profesional::query()->where('nombre', 'Laura')->firstOrFail();
        $response->assertRedirect(route('portal.profesionales.edit', $professional));
        $veterinarian = User::query()->where('email', 'laura@example.test')->firstOrFail();

        $this->assertSame($veterinarian->id, $professional->user_id);
        $this->assertSame('Veterinario', $veterinarian->role->nombre);
        $this->assertTrue(Hash::check('segura1234', $veterinarian->password));
        $this->assertDatabaseHas('roles', [
            'nombre' => 'Veterinario',
            'activo' => true,
        ]);
    }

    public function test_administrator_cannot_create_a_veterinarian_account_with_an_inactive_role(): void
    {
        $administrator = $this->createAdministrator();
        Role::query()->create([
            'nombre' => 'Veterinario',
            'activo' => false,
        ]);

        $response = $this->actingAs($administrator)
            ->from(route('portal.profesionales.create'))
            ->post(route('portal.profesionales.store'), [
                'nombre' => 'Laura',
                'apellido' => 'Méndez',
                'estado' => 'activo',
                'usuario_email' => 'laura@example.test',
                'usuario_password' => 'segura1234',
            ]);

        $response->assertRedirect(route('portal.profesionales.create'))
            ->assertSessionHasErrors([
                'usuario_email' => 'El rol Veterinario está inactivo. Actívalo antes de crear cuentas clínicas.',
            ]);
        $this->assertDatabaseMissing('users', ['email' => 'laura@example.test']);
        $this->assertDatabaseMissing('profesionales', ['nombre' => 'Laura']);
        $this->assertDatabaseHas('roles', [
            'nombre' => 'Veterinario',
            'activo' => false,
        ]);
    }

    public function test_professional_list_searches_by_name_and_displays_status(): void
    {
        $administrator = $this->createAdministrator();
        Profesional::query()->create([
            'nombre' => 'Ana',
            'apellido' => 'Vera',
            'cedula_profesional' => 'VET-001',
            'estado' => 'activo',
        ]);
        Profesional::query()->create([
            'nombre' => 'Oscar',
            'apellido' => 'Ríos',
            'cedula_profesional' => 'VET-002',
            'estado' => 'inactivo',
        ]);

        $this->actingAs($administrator)
            ->get(route('portal.profesionales.index', ['buscar' => 'VET-001']))
            ->assertOk()
            ->assertSee('Ana Vera')
            ->assertDontSee('Oscar Ríos')
            ->assertSee('Activo');
    }

    public function test_administrator_can_open_professional_and_specialty_screens(): void
    {
        $administrator = $this->createAdministrator();
        $specialty = Especialidad::query()->create(['nombre' => 'Medicina preventiva']);
        $profesional = Profesional::query()->create([
            'nombre' => 'Ana',
            'estado' => 'activo',
        ]);
        $profesional->especialidades()->attach($specialty);
        $profesional->horarios()->create([
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '17:00',
            'activo' => true,
        ]);

        $this->actingAs($administrator)
            ->get(route('portal.profesionales.create'))
            ->assertOk();

        $this->get(route('portal.profesionales.show', $profesional))
            ->assertOk()
            ->assertSee('Medicina preventiva')
            ->assertSee('09:00');

        $this->get(route('portal.profesionales.edit', $profesional))
            ->assertOk()
            ->assertSee('Horario semanal')
            ->assertSee('Medicina preventiva');

        $this->get(route('portal.especialidades.index'))
            ->assertOk()
            ->assertSee('Medicina preventiva');

        $this->get(route('portal.especialidades.create'))
            ->assertOk();

        $this->get(route('portal.especialidades.edit', $specialty))
            ->assertOk()
            ->assertSee('Medicina preventiva');
    }

    public function test_administrator_can_update_weekly_schedule_and_specialties(): void
    {
        $administrator = $this->createAdministrator();
        $oldSpecialty = Especialidad::query()->create(['nombre' => 'Cirugía']);
        $newSpecialty = Especialidad::query()->create(['nombre' => 'Dermatología']);
        $profesional = Profesional::query()->create([
            'nombre' => 'Luis',
            'estado' => 'activo',
            'cedula_profesional' => 'VET-003',
        ]);
        $profesional->especialidades()->attach($oldSpecialty);
        $profesional->horarios()->create([
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'activo' => true,
        ]);
        $horarios = $this->weeklySchedule([
            1 => ['activo' => '1', 'hora_inicio' => '09:00', 'hora_fin' => '17:00'],
            3 => ['activo' => '1', 'hora_inicio' => '10:00', 'hora_fin' => '15:30'],
        ]);

        $this->actingAs($administrator)
            ->put(route('portal.profesionales.update', $profesional), [
                'nombre' => 'Luis',
                'estado' => 'activo',
                'cedula_profesional' => 'VET-003',
                'especialidades' => [$newSpecialty->id],
                'horarios' => $horarios,
                'user_id' => $administrator->id,
            ])
            ->assertRedirect(route('portal.profesionales.show', $profesional));

        $this->assertDatabaseCount('horarios_profesional', 2);
        $this->assertDatabaseHas('horarios_profesional', [
            'profesional_id' => $profesional->id,
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '17:00',
            'activo' => true,
        ]);
        $this->assertDatabaseHas('profesional_especialidad', [
            'profesional_id' => $profesional->id,
            'especialidad_id' => $newSpecialty->id,
        ]);
        $this->assertDatabaseMissing('profesional_especialidad', [
            'profesional_id' => $profesional->id,
            'especialidad_id' => $oldSpecialty->id,
        ]);
        $this->assertNull($profesional->refresh()->user_id);
    }

    public function test_update_rejects_an_invalid_active_schedule_without_changing_existing_hours(): void
    {
        $administrator = $this->createAdministrator();
        $profesional = Profesional::query()->create([
            'nombre' => 'Ana',
            'estado' => 'activo',
        ]);
        $profesional->horarios()->create([
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '17:00',
            'activo' => true,
        ]);
        $horarios = $this->weeklySchedule([
            1 => ['activo' => '1', 'hora_inicio' => '17:00', 'hora_fin' => '09:00'],
        ]);

        $this->actingAs($administrator)
            ->from(route('portal.profesionales.edit', $profesional))
            ->put(route('portal.profesionales.update', $profesional), [
                'nombre' => 'Ana',
                'estado' => 'activo',
                'horarios' => $horarios,
            ])
            ->assertRedirect(route('portal.profesionales.edit', $profesional))
            ->assertSessionHasErrors('horarios.1.hora_fin');

        $this->assertDatabaseCount('horarios_profesional', 1);
        $this->assertDatabaseHas('horarios_profesional', [
            'profesional_id' => $profesional->id,
            'dia_semana' => 1,
            'hora_inicio' => '09:00',
            'hora_fin' => '17:00',
        ]);
    }

    public function test_administrator_can_create_and_deactivate_a_specialty(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->post(route('portal.especialidades.store'), [
                'nombre' => 'Nutrición',
                'descripcion' => 'Asesoría nutricional',
            ])
            ->assertRedirect(route('portal.especialidades.index'));

        $specialty = Especialidad::query()->where('nombre', 'Nutrición')->firstOrFail();
        $this->assertTrue($specialty->activo);

        $this->actingAs($administrator)
            ->put(route('portal.especialidades.update', $specialty), [
                'nombre' => 'Nutrición veterinaria',
                'descripcion' => 'Planes de alimentación',
                'activo' => '0',
            ])
            ->assertRedirect(route('portal.especialidades.index'));

        $this->assertDatabaseHas('especialidades', [
            'id' => $specialty->id,
            'nombre' => 'Nutrición veterinaria',
            'activo' => false,
        ]);
    }

    public function test_duplicate_professional_credentials_are_rejected(): void
    {
        $administrator = $this->createAdministrator();
        Profesional::query()->create([
            'nombre' => 'Ana',
            'cedula_profesional' => 'VET-001',
            'estado' => 'activo',
        ]);

        $this->actingAs($administrator)
            ->from(route('portal.profesionales.create'))
            ->post(route('portal.profesionales.store'), [
                'nombre' => 'Luis',
                'cedula_profesional' => 'VET-001',
                'estado' => 'activo',
            ])
            ->assertRedirect(route('portal.profesionales.create'))
            ->assertSessionHasErrors('cedula_profesional');

        $this->assertDatabaseCount('profesionales', 1);
    }

    private function createAdministrator(): User
    {
        $role = Role::query()->create(['nombre' => 'Administrador']);

        return User::factory()->create([
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);
    }

    /**
     * @param  array<int, array{activo: string, hora_inicio: string, hora_fin: string}>  $activeDays
     * @return array<int, array{activo: string, hora_inicio: string, hora_fin: string}>
     */
    private function weeklySchedule(array $activeDays = []): array
    {
        $schedule = [];

        for ($day = 1; $day <= 7; $day++) {
            $schedule[$day] = $activeDays[$day] ?? [
                'activo' => '0',
                'hora_inicio' => '',
                'hora_fin' => '',
            ];
        }

        return $schedule;
    }
}
