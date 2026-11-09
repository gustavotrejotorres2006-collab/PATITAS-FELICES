<?php

namespace Tests\Feature;

use App\Models\CategoriaServicio;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Role;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CitaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_can_schedule_an_appointment_with_the_after_hours_surcharge(): void
    {
        $receptionist = $this->createUserWithRole('Recepcionista');
        [$owner, $pet] = $this->createPetAndOwner();
        $professional = $this->createProfessional();
        $service = $this->createService('Consulta', 'Consulta general', 30, 100);
        $start = now()->addDay()->setTime(20, 30);

        $response = $this->actingAs($receptionist)
            ->from(route('portal.citas.create'))
            ->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $service, $start));

        $appointment = Cita::query()->where('mascota_id', $pet->id)->firstOrFail();

        $response->assertRedirect(route('portal.citas.index'));
        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'estado' => 'pendiente',
            'creado_por' => $receptionist->id,
            'inicio' => $start->format('Y-m-d H:i:s'),
            'fin' => $start->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);
        $this->assertDatabaseHas('cita_servicios', [
            'cita_id' => $appointment->id,
            'servicio_id' => $service->id,
            'precio' => 130,
            'duracion_minutos' => 30,
        ]);
    }

    public function test_same_professional_must_have_thirty_minutes_between_regular_appointments(): void
    {
        $administrator = $this->createUserWithRole('Administrador');
        [$owner, $pet] = $this->createPetAndOwner();
        $professional = $this->createProfessional();
        $service = $this->createService('Consulta', 'Consulta general', 30, 100);
        $start = now()->addDay()->setTime(9, 0);

        $this->actingAs($administrator)
            ->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $service, $start))
            ->assertRedirect(route('portal.citas.index'));

        $this->from(route('portal.citas.create'))
            ->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $service, $start->copy()->addMinutes(59)))
            ->assertRedirect(route('portal.citas.create'))
            ->assertSessionHasErrors('inicio');

        $this->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $service, $start->copy()->addMinutes(60)))
            ->assertRedirect(route('portal.citas.index'));

        $this->assertDatabaseCount('citas', 2);
        $this->assertDatabaseHas('cita_servicios', [
            'servicio_id' => $service->id,
            'precio' => 100,
        ]);
    }

    public function test_surgery_requires_ninety_minutes_between_appointments(): void
    {
        $administrator = $this->createUserWithRole('Administrador');
        [$owner, $pet] = $this->createPetAndOwner();
        $professional = $this->createProfessional();
        $consultation = $this->createService('Consulta', 'Consulta general', 30, 100);
        $surgery = $this->createService('Cirugía', 'Cirugía menor', 30, 500);
        $start = now()->addDay()->setTime(9, 0);

        $this->actingAs($administrator)
            ->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $consultation, $start))
            ->assertRedirect(route('portal.citas.index'));

        $this->from(route('portal.citas.create'))
            ->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $surgery, $start->copy()->addMinutes(119)))
            ->assertRedirect(route('portal.citas.create'))
            ->assertSessionHasErrors('inicio');

        $this->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $surgery, $start->copy()->addMinutes(120)))
            ->assertRedirect(route('portal.citas.index'));

        $this->assertDatabaseCount('citas', 2);
    }

    public function test_cancelling_an_appointment_releases_its_time_slot(): void
    {
        $administrator = $this->createUserWithRole('Administrador');
        [$owner, $pet] = $this->createPetAndOwner();
        $professional = $this->createProfessional();
        $service = $this->createService('Consulta', 'Consulta general', 30, 100);
        $start = now()->addDay()->setTime(9, 0);

        $this->actingAs($administrator)
            ->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $service, $start))
            ->assertRedirect(route('portal.citas.index'));

        $appointment = Cita::query()->firstOrFail();

        $this->patch(route('portal.citas.cancelar', $appointment))
            ->assertRedirect(route('portal.citas.index'));

        $this->post(route('portal.citas.store'), $this->appointmentData($owner, $pet, $professional, $service, $start))
            ->assertRedirect(route('portal.citas.index'));

        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'estado' => 'cancelada',
        ]);
        $this->assertDatabaseCount('citas', 2);
    }

    public function test_receptionist_can_access_the_agenda_but_not_the_administrative_dashboard(): void
    {
        $receptionist = $this->createUserWithRole('Recepcionista');

        $this->actingAs($receptionist)
            ->get(route('portal.citas.index'))
            ->assertOk()
            ->assertSee('Agenda de citas')
            ->assertSee('Nueva cita')
            ->assertDontSee('Propietarios');

        $this->get(route('portal.citas.create'))
            ->assertOk()
            ->assertSee('Programar una cita')
            ->assertSee('datetime-local')
            ->assertSee('Urgencia (prioridad máxima)');

        $this->get(route('portal.dashboard'))
            ->assertForbidden();
    }

    public function test_appointment_rejects_an_owner_not_linked_to_the_selected_pet(): void
    {
        $administrator = $this->createUserWithRole('Administrador');
        [, $pet] = $this->createPetAndOwner();
        $otherOwner = Propietario::query()->create([
            'codigo' => 'P-OTHER',
            'nombre' => 'Otra persona',
            'estado' => 'activo',
        ]);
        $professional = $this->createProfessional();
        $service = $this->createService('Consulta', 'Consulta general', 30, 100);

        $this->actingAs($administrator)
            ->from(route('portal.citas.create'))
            ->post(route('portal.citas.store'), $this->appointmentData(
                $otherOwner,
                $pet,
                $professional,
                $service,
                now()->addDay()->setTime(9, 0),
            ))
            ->assertRedirect(route('portal.citas.create'))
            ->assertSessionHasErrors('propietario_id');

        $this->assertDatabaseCount('citas', 0);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::query()->create(['nombre' => $roleName]);

        return User::factory()->create([
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);
    }

    /**
     * @return array{Propietario, Mascota}
     */
    private function createPetAndOwner(): array
    {
        $owner = Propietario::query()->create([
            'codigo' => 'P-001',
            'nombre' => 'Ana Pérez',
            'estado' => 'activo',
        ]);
        $pet = Mascota::query()->create([
            'codigo' => 'M-001',
            'nombre' => 'Milo',
            'especie' => 'perro',
            'estado' => 'activo',
        ]);
        $pet->propietarios()->attach($owner, [
            'es_principal' => true,
            'fecha_inicio' => now()->toDateString(),
            'estado' => 'activo',
        ]);

        return [$owner, $pet];
    }

    private function createProfessional(): Profesional
    {
        return Profesional::query()->create([
            'nombre' => 'Silvia',
            'apellido' => 'Ruiz',
            'estado' => 'activo',
        ]);
    }

    private function createService(string $category, string $name, int $duration, int $price): Servicio
    {
        $serviceCategory = CategoriaServicio::query()->create([
            'nombre' => $category,
            'activo' => true,
        ]);

        return Servicio::query()->create([
            'categoria_id' => $serviceCategory->id,
            'codigo' => 'SERV-'.str_replace(' ', '-', strtoupper($name)),
            'nombre' => $name,
            'duracion_minutos' => $duration,
            'precio' => $price,
            'activo' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function appointmentData(
        Propietario $owner,
        Mascota $pet,
        Profesional $professional,
        Servicio $service,
        Carbon $start,
    ): array {
        return [
            'propietario_id' => $owner->id,
            'mascota_id' => $pet->id,
            'profesional_id' => $professional->id,
            'servicios' => [$service->id],
            'inicio' => $start->format('Y-m-d\TH:i'),
            'motivo' => 'Revisión general',
        ];
    }
}
