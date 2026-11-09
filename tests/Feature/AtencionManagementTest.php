<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\CatalogoVacuna;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\NotaClinica;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtencionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_veterinarian_can_record_an_appointment_and_only_applied_vaccines_enter_the_history(): void
    {
        [$veterinarian, $professional] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPetAndOwner();
        $appointment = $this->createAppointment($owner, $pet, $professional);
        $vaccineProduct = Producto::query()->create([
            'codigo' => 'VAC-RABIA',
            'nombre' => 'Vacuna contra rabia',
            'unidad_base' => 'dosis',
            'precio_venta' => 100,
            'stock_minimo' => 2,
            'stock_optimo' => 20,
            'estado' => 'activo',
        ]);
        $vaccineProduct->lotes()->create([
            'numero_lote' => 'RABIA-001',
            'cantidad_inicial' => 2,
            'cantidad_disponible' => 2,
            'estado' => 'disponible',
        ]);
        $vaccine = CatalogoVacuna::query()->create([
            'nombre' => 'Rabia',
            'especie' => 'perro',
            'activo' => true,
            'producto_id' => $vaccineProduct->id,
        ]);
        $unusedVaccine = CatalogoVacuna::query()->create([
            'nombre' => 'Moquillo',
            'especie' => 'perro',
            'activo' => true,
        ]);

        $response = $this->actingAs($veterinarian)
            ->post(route('portal.atenciones.store', $appointment), [
                'fecha_atencion' => now()->format('Y-m-d\TH:i'),
                'motivo' => 'Revisión anual',
                'diagnostico' => 'Paciente en buen estado general.',
                'nota' => 'Sin hallazgos clínicos relevantes.',
                'peso_kg' => '12.50',
                'temperatura_c' => '38.40',
                'frecuencia_cardiaca' => '90',
                'hospitalizacion_recomendada' => '0',
                'vacunas_aplicadas' => [$vaccine->id],
            ]);

        $attention = Atencion::query()->firstOrFail();
        $note = NotaClinica::query()->firstOrFail();

        $response->assertRedirect(route('portal.atenciones.show', $attention));
        $this->assertDatabaseHas('atenciones', [
            'id' => $attention->id,
            'cita_id' => $appointment->id,
            'mascota_id' => $pet->id,
            'profesional_id' => $professional->id,
            'creado_por' => $veterinarian->id,
            'hospitalizacion_recomendada' => false,
        ]);
        $this->assertDatabaseHas('atencion_diagnosticos', [
            'atencion_id' => $attention->id,
            'diagnostico_personalizado' => 'Paciente en buen estado general.',
        ]);
        $this->assertDatabaseHas('signos_vitales', [
            'atencion_id' => $attention->id,
            'peso_kg' => 12.5,
            'temperatura_c' => 38.4,
            'frecuencia_cardiaca' => 90,
        ]);
        $this->assertDatabaseHas('notas_clinicas', [
            'id' => $note->id,
            'atencion_id' => $attention->id,
            'profesional_id' => $professional->id,
            'autor_id' => $veterinarian->id,
            'contenido' => 'Sin hallazgos clínicos relevantes.',
            'version' => 1,
        ]);
        $this->assertDatabaseHas('vacunaciones', [
            'mascota_id' => $pet->id,
            'vacuna_id' => $vaccine->id,
            'atencion_id' => $attention->id,
            'lote' => 'RABIA-001',
            'aplicada_por' => $veterinarian->id,
            'fecha_aplicacion' => now()->startOfDay()->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('lotes_inventario', [
            'producto_id' => $vaccineProduct->id,
            'cantidad_disponible' => 1,
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $vaccineProduct->id,
            'tipo' => 'salida',
            'cantidad' => 1,
        ]);
        $this->assertDatabaseMissing('vacunaciones', [
            'mascota_id' => $pet->id,
            'vacuna_id' => $unusedVaccine->id,
        ]);
        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'estado' => 'completada',
        ]);
    }

    public function test_a_clinical_record_cannot_be_saved_without_a_diagnosis(): void
    {
        [$veterinarian, $professional] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPetAndOwner();
        $appointment = $this->createAppointment($owner, $pet, $professional);

        $this->actingAs($veterinarian)
            ->from(route('portal.atenciones.create', $appointment))
            ->post(route('portal.atenciones.store', $appointment), [
                'fecha_atencion' => now()->format('Y-m-d\TH:i'),
                'motivo' => 'Consulta',
                'nota' => 'Se revisó a la mascota.',
                'diagnostico' => '',
            ])
            ->assertRedirect(route('portal.atenciones.create', $appointment))
            ->assertSessionHasErrors('diagnostico');

        $this->assertDatabaseCount('atenciones', 0);
        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_vaccine_application_fails_atomically_when_inventory_is_empty(): void
    {
        [$veterinarian, $professional] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPetAndOwner();
        $appointment = $this->createAppointment($owner, $pet, $professional);
        $vaccineProduct = Producto::query()->create([
            'codigo' => 'VAC-EMPTY',
            'nombre' => 'Vacuna sin existencias',
            'unidad_base' => 'dosis',
            'precio_venta' => 100,
            'stock_minimo' => 1,
            'stock_optimo' => 10,
            'estado' => 'activo',
        ]);
        $vaccine = CatalogoVacuna::query()->create([
            'nombre' => 'Vacuna sin existencias',
            'activo' => true,
            'producto_id' => $vaccineProduct->id,
        ]);

        $response = $this->actingAs($veterinarian)
            ->from(route('portal.atenciones.create', $appointment))
            ->post(route('portal.atenciones.store', $appointment), [
                'fecha_atencion' => now()->format('Y-m-d\TH:i'),
                'diagnostico' => 'Paciente estable.',
                'nota' => 'Se revisó al paciente.',
                'vacunas_aplicadas' => [$vaccine->id],
            ]);

        $response->assertRedirect(route('portal.atenciones.create', $appointment))
            ->assertSessionHasErrors('cantidad');
        $this->assertDatabaseCount('atenciones', 0);
        $this->assertDatabaseCount('vacunaciones', 0);
        $this->assertDatabaseCount('movimientos_inventario', 0);
        $this->assertDatabaseHas('citas', [
            'id' => $appointment->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_veterinarian_can_append_a_note_without_overwriting_clinical_history(): void
    {
        [$veterinarian, $professional] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPetAndOwner();
        $appointment = $this->createAppointment($owner, $pet, $professional);

        $this->actingAs($veterinarian)->post(route('portal.atenciones.store', $appointment), [
            'fecha_atencion' => now()->format('Y-m-d\TH:i'),
            'motivo' => 'Consulta',
            'diagnostico' => 'Dermatitis leve.',
            'nota' => 'Primera valoración.',
        ]);

        $attention = Atencion::query()->firstOrFail();
        $firstNote = NotaClinica::query()->firstOrFail();

        $this->post(route('portal.atenciones.notas.store', $attention), [
            'contenido' => 'Se observa mejoría tras el tratamiento.',
            'hospitalizacion_recomendada' => '1',
        ])->assertRedirect(route('portal.atenciones.show', $attention));

        $this->get(route('portal.atenciones.show', $attention))
            ->assertOk()
            ->assertSee('Primera valoración.')
            ->assertSee('Se observa mejoría tras el tratamiento.')
            ->assertSee('Hospitalización recomendada');

        $this->assertDatabaseHas('notas_clinicas', [
            'id' => $firstNote->id,
            'version' => 1,
            'contenido' => 'Primera valoración.',
            'nota_anterior_id' => null,
        ]);
        $this->assertDatabaseHas('notas_clinicas', [
            'atencion_id' => $attention->id,
            'version' => 2,
            'contenido' => 'Se observa mejoría tras el tratamiento.',
            'nota_anterior_id' => $firstNote->id,
        ]);
        $this->assertDatabaseHas('atenciones', [
            'id' => $attention->id,
            'hospitalizacion_recomendada' => true,
        ]);

        $this->delete(route('portal.atenciones.notas.store', $attention))->assertMethodNotAllowed();
    }

    public function test_clinical_records_are_available_only_to_the_veterinarian_assigned_to_the_appointment(): void
    {
        [$assignedVeterinarian, $assignedProfessional] = $this->createVeterinarian('vet1@example.test');
        [, $otherProfessional] = $this->createVeterinarian('vet2@example.test');
        [$owner, $pet] = $this->createPetAndOwner();
        $appointment = $this->createAppointment($owner, $pet, $assignedProfessional);
        [, $otherPet] = $this->createPetAndOwner('P-OTHER', 'M-OTHER', 'Otro paciente');
        $otherAppointment = $this->createAppointment($owner, $otherPet, $otherProfessional, 'CITA-002');

        $this->get(route('portal.atenciones.index'))
            ->assertRedirect(route('login'));

        $receptionistRole = Role::query()->create(['nombre' => 'Recepcionista']);
        $receptionist = User::factory()->create([
            'role_id' => $receptionistRole->id,
            'estado' => 'activo',
        ]);

        $this->actingAs($receptionist)
            ->get(route('portal.atenciones.index'))
            ->assertForbidden();

        $this->actingAs($assignedVeterinarian)
            ->get(route('portal.atenciones.index'))
            ->assertOk()
            ->assertSee('Milo')
            ->assertDontSee('Otro paciente');

        $this->get(route('portal.atenciones.create', $otherAppointment))
            ->assertNotFound();

        $this->assertDatabaseMissing('atenciones', ['cita_id' => $appointment->id]);
    }

    /**
     * @return array{User, Profesional}
     */
    private function createVeterinarian(string $email = 'veterinarian@example.test'): array
    {
        $role = Role::query()->firstOrCreate(['nombre' => 'Veterinario']);
        $user = User::factory()->create([
            'name' => 'Dra. Laura Méndez',
            'email' => $email,
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);
        $professional = Profesional::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Laura',
            'apellido' => 'Méndez',
            'estado' => 'activo',
        ]);

        return [$user, $professional];
    }

    /**
     * @return array{Propietario, Mascota}
     */
    private function createPetAndOwner(
        string $ownerCode = 'P-CLIN',
        string $petCode = 'M-CLIN',
        string $petName = 'Milo',
    ): array {
        $owner = Propietario::query()->create([
            'codigo' => $ownerCode,
            'nombre' => 'Ana Pérez',
            'estado' => 'activo',
        ]);
        $pet = Mascota::query()->create([
            'codigo' => $petCode,
            'nombre' => $petName,
            'especie' => 'perro',
            'estado' => 'activo',
        ]);

        return [$owner, $pet];
    }

    private function createAppointment(
        Propietario $owner,
        Mascota $pet,
        Profesional $professional,
        string $folio = 'CITA-001',
    ): Cita {
        return Cita::query()->create([
            'folio' => $folio,
            'mascota_id' => $pet->id,
            'propietario_id' => $owner->id,
            'profesional_id' => $professional->id,
            'inicio' => now()->subMinutes(30),
            'fin' => now(),
            'estado' => 'pendiente',
        ]);
    }
}
