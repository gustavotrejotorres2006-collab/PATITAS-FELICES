<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Cirugia;
use App\Models\Cita;
use App\Models\EspacioClinico;
use App\Models\Hospitalizacion;
use App\Models\Mascota;
use App\Models\MonitoreoHospitalario;
use App\Models\NotaClinica;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalizacionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_veterinarian_can_schedule_surgery_only_with_a_prior_note_signed_consent_and_available_room(): void
    {
        [$veterinarian, $professional, $owner, $attention] = $this->createClinicalCase();
        $room = $this->createSpace('Quirófano 1', 'quirofano');
        $anesthesiologist = $this->createProfessional('anesthesiologist@example.test');

        $response = $this->actingAs($veterinarian)
            ->post(route('portal.cirugias.store'), $this->surgeryData(
                $attention,
                $room,
                $professional,
                $anesthesiologist,
                $owner,
            ));

        $response->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasNoErrors();
        $surgery = Cirugia::query()->firstOrFail();

        $this->assertDatabaseHas('cirugias', [
            'id' => $surgery->id,
            'atencion_id' => $attention->id,
            'espacio_id' => $room->id,
            'cirujano_id' => $professional->id,
            'anestesiologo_id' => $anesthesiologist->id,
            'propietario_id' => $owner->id,
            'estado' => 'programada',
            'consentimiento_firmado_at' => now()->toDateTimeString(),
            'consentimiento_verificado_por' => $veterinarian->id,
        ]);

        $this->actingAs($anesthesiologist->user)
            ->get(route('portal.hospitalizaciones.index'))
            ->assertOk()
            ->assertSee($surgery->folio);
    }

    public function test_surgery_cannot_be_scheduled_without_signed_consent_or_a_prior_clinical_note(): void
    {
        [$veterinarian, $professional, $owner, $attention] = $this->createClinicalCase();
        $room = $this->createSpace('Quirófano 1', 'quirofano');
        $anesthesiologist = $this->createProfessional('anesthesiologist@example.test');

        $this->from(route('portal.hospitalizaciones.index'))
            ->actingAs($veterinarian)
            ->post(route('portal.cirugias.store'), $this->surgeryData(
                $attention,
                $room,
                $professional,
                $anesthesiologist,
                $owner,
                ['consentimiento_firmado' => '0'],
            ))
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('consentimiento_firmado');

        $attention->notas()->delete();

        $this->post(route('portal.cirugias.store'), $this->surgeryData(
            $attention,
            $room,
            $professional,
            $anesthesiologist,
            $owner,
        ))
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('atencion_id');

        $this->assertDatabaseCount('cirugias', 0);
    }

    public function test_surgery_reservations_cannot_overlap_the_room_or_either_veterinarians_schedule(): void
    {
        [$veterinarian, $professional, $owner, $attention] = $this->createClinicalCase();
        $room = $this->createSpace('Quirófano 1', 'quirofano');
        $anotherRoom = $this->createSpace('Quirófano 2', 'quirofano');
        $anesthesiologist = $this->createProfessional('anesthesiologist@example.test');
        $data = $this->surgeryData($attention, $room, $professional, $anesthesiologist, $owner);

        $this->actingAs($veterinarian)
            ->post(route('portal.cirugias.store'), $data)
            ->assertRedirect(route('portal.hospitalizaciones.index'));

        $this->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.cirugias.store'), $this->surgeryData(
                $attention,
                $anotherRoom,
                $professional,
                $anesthesiologist,
                $owner,
                ['programada_at' => now()->addDays(2)->setTime(10, 30)->format('Y-m-d\TH:i')],
            ))
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('programada_at');

        $anotherSurgeon = $this->createProfessional('surgeon-2@example.test');
        $anotherAnesthesiologist = $this->createProfessional('anesthesiologist-2@example.test');
        $this->post(route('portal.cirugias.store'), $this->surgeryData(
            $attention,
            $room,
            $anotherSurgeon,
            $anotherAnesthesiologist,
            $owner,
            ['programada_at' => now()->addDays(2)->setTime(10, 30)->format('Y-m-d\TH:i')],
        ))
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('programada_at');

        $this->assertDatabaseCount('cirugias', 1);
    }

    public function test_surgery_cannot_be_scheduled_over_an_existing_appointment(): void
    {
        [$veterinarian, $professional, $owner, $attention] = $this->createClinicalCase();
        $room = $this->createSpace('Quirófano 1', 'quirofano');
        $anesthesiologist = $this->createProfessional('anesthesiologist@example.test');
        $start = now()->addDays(2)->setTime(9, 0);
        $attention->cita()->update([
            'inicio' => $start->copy()->subMinutes(30),
            'fin' => $start->copy()->addMinutes(30),
            'estado' => 'confirmada',
        ]);

        $this->actingAs($veterinarian)
            ->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.cirugias.store'), $this->surgeryData(
                $attention,
                $room,
                $professional,
                $anesthesiologist,
                $owner,
                ['programada_at' => $start->format('Y-m-d\TH:i')],
            ))
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('programada_at');

        $this->assertDatabaseCount('cirugias', 0);
        $this->assertDatabaseHas('citas', [
            'id' => $attention->cita_id,
            'estado' => 'confirmada',
        ]);
    }

    public function test_surgery_consumes_supplies_atomically_and_marks_the_operating_room_for_disinfection(): void
    {
        [$veterinarian, $professional, $owner, $attention] = $this->createClinicalCase();
        $room = $this->createSpace('Quirófano 1', 'quirofano');
        $anesthesiologist = $this->createProfessional('anesthesiologist@example.test');
        $product = $this->createProduct();
        $product->lotes()->create([
            'numero_lote' => 'SUTURA-01',
            'cantidad_inicial' => 3,
            'cantidad_disponible' => 3,
            'estado' => 'disponible',
        ]);

        $this->actingAs($veterinarian)
            ->post(route('portal.cirugias.store'), $this->surgeryData(
                $attention,
                $room,
                $professional,
                $anesthesiologist,
                $owner,
            ))
            ->assertRedirect(route('portal.hospitalizaciones.index'));

        $surgery = Cirugia::query()->firstOrFail();
        $this->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.cirugias.completar', $surgery), [
                'insumos' => [
                    ['producto_id' => $product->id, 'cantidad' => 2],
                ],
            ])
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('cirugia');

        $this->assertDatabaseHas('lotes_inventario', [
            'producto_id' => $product->id,
            'cantidad_disponible' => 3,
        ]);
        $this->travel(2)->days();
        $this->post(route('portal.cirugias.completar', $surgery), [
            'insumos' => [
                ['producto_id' => $product->id, 'cantidad' => 2],
                ['producto_id' => '', 'cantidad' => ''],
                ['producto_id' => '', 'cantidad' => ''],
            ],
        ])->assertRedirect(route('portal.hospitalizaciones.index'));

        $this->assertDatabaseHas('cirugias', [
            'id' => $surgery->id,
            'estado' => 'completada',
        ]);
        $this->assertDatabaseHas('cirugia_insumos', [
            'cirugia_id' => $surgery->id,
            'producto_id' => $product->id,
            'numero_lote' => 'SUTURA-01',
            'cantidad' => 2,
        ]);
        $this->assertDatabaseHas('lotes_inventario', [
            'producto_id' => $product->id,
            'cantidad_disponible' => 1,
        ]);
        $this->assertDatabaseHas('espacios_clinicos', [
            'id' => $room->id,
            'estado' => 'limpieza',
        ]);
    }

    public function test_surgery_cannot_close_when_fifo_stock_is_insufficient(): void
    {
        [$veterinarian, $professional, $owner, $attention] = $this->createClinicalCase();
        $room = $this->createSpace('Quirófano 1', 'quirofano');
        $anesthesiologist = $this->createProfessional('anesthesiologist@example.test');
        $product = $this->createProduct();
        $product->lotes()->create([
            'numero_lote' => 'SUTURA-EMPTY',
            'cantidad_inicial' => 1,
            'cantidad_disponible' => 1,
            'estado' => 'disponible',
        ]);

        $this->actingAs($veterinarian)
            ->post(route('portal.cirugias.store'), $this->surgeryData(
                $attention,
                $room,
                $professional,
                $anesthesiologist,
                $owner,
            ))
            ->assertRedirect(route('portal.hospitalizaciones.index'));

        $surgery = Cirugia::query()->firstOrFail();
        $this->travel(2)->days();
        $this->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.cirugias.completar', $surgery), [
                'insumos' => [
                    ['producto_id' => $product->id, 'cantidad' => 2],
                ],
            ])
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('cantidad');

        $this->assertDatabaseHas('cirugias', [
            'id' => $surgery->id,
            'estado' => 'programada',
        ]);
        $this->assertDatabaseHas('lotes_inventario', [
            'producto_id' => $product->id,
            'cantidad_disponible' => 1,
        ]);
        $this->assertDatabaseCount('movimientos_inventario', 0);
        $this->assertDatabaseCount('cirugia_insumos', 0);
        $this->assertDatabaseHas('espacios_clinicos', [
            'id' => $room->id,
            'estado' => 'disponible',
        ]);
    }

    public function test_hospitalization_requires_a_recommendation_and_a_vacant_bed(): void
    {
        [$veterinarian, , , $attention] = $this->createClinicalCase();
        $bed = $this->createSpace('Hospitalización 1', 'hospitalizacion');
        $attention->update(['hospitalizacion_recomendada' => false]);

        $this->actingAs($veterinarian)
            ->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.hospitalizaciones.store'), [
                'atencion_id' => $attention->id,
                'espacio_id' => $bed->id,
            ])
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('atencion_id');

        $this->assertDatabaseCount('hospitalizaciones', 0);

        $attention->update(['hospitalizacion_recomendada' => true]);
        $attention->notas()->delete();
        $this->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.hospitalizaciones.store'), [
                'atencion_id' => $attention->id,
                'espacio_id' => $bed->id,
            ])
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('atencion_id');

        NotaClinica::query()->create([
            'atencion_id' => $attention->id,
            'profesional_id' => $attention->profesional_id,
            'autor_id' => $veterinarian->id,
            'contenido' => 'Nota médica previa al ingreso.',
            'version' => 1,
        ]);
        $this->post(route('portal.hospitalizaciones.store'), [
            'atencion_id' => $attention->id,
            'espacio_id' => $bed->id,
        ])->assertRedirect(route('portal.hospitalizaciones.index'));

        $this->assertDatabaseHas('espacios_clinicos', [
            'id' => $bed->id,
            'estado' => 'ocupado',
        ]);
        $this->assertDatabaseHas('hospitalizaciones', [
            'atencion_id' => $attention->id,
            'espacio_id' => $bed->id,
            'estado' => 'internada',
        ]);
    }

    public function test_due_monitoring_requires_a_progress_note_and_discharge_requires_that_record(): void
    {
        [$veterinarian, , , $attention] = $this->createClinicalCase();
        $bed = $this->createSpace('Hospitalización 1', 'hospitalizacion');
        $attention->update(['hospitalizacion_recomendada' => true]);

        $this->actingAs($veterinarian)
            ->post(route('portal.hospitalizaciones.store'), [
                'atencion_id' => $attention->id,
                'espacio_id' => $bed->id,
            ])
            ->assertRedirect(route('portal.hospitalizaciones.index'));

        $hospitalization = Hospitalizacion::query()->firstOrFail();
        $this->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.hospitalizaciones.monitoreos.store', $hospitalization), [
                'temperatura_c' => 38.5,
                'tratamiento' => 'Administrar medicamento prescrito.',
                'nota_progreso' => 'Paciente estable.',
            ])
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('monitoreo');
        $this->from(route('portal.hospitalizaciones.index'))
            ->post(route('portal.hospitalizaciones.alta', $hospitalization))
            ->assertRedirect(route('portal.hospitalizaciones.index'))
            ->assertSessionHasErrors('nota_progreso');

        $this->travel(4)->hours();
        $this->get(route('portal.hospitalizaciones.index'))
            ->assertOk()
            ->assertSee('Monitoreo hospitalario vencido')
            ->assertSee('Activar alarma sonora');

        $this->post(route('portal.hospitalizaciones.monitoreos.store', $hospitalization), [
            'temperatura_c' => 38.5,
            'frecuencia_cardiaca' => 90,
            'tratamiento' => 'Administrar medicamento prescrito.',
            'nota_progreso' => 'Paciente estable y responde al tratamiento.',
        ])->assertRedirect(route('portal.hospitalizaciones.index'));

        $monitoring = MonitoreoHospitalario::query()->firstOrFail();
        $this->assertDatabaseHas('notas_clinicas', [
            'atencion_id' => $attention->id,
            'contenido' => 'Paciente estable y responde al tratamiento.',
        ]);
        $this->assertDatabaseHas('monitoreos_hospitalarios', [
            'id' => $monitoring->id,
            'hospitalizacion_id' => $hospitalization->id,
            'tratamiento' => 'Administrar medicamento prescrito.',
        ]);

        $this->post(route('portal.hospitalizaciones.alta', $hospitalization))
            ->assertRedirect(route('portal.hospitalizaciones.index'));

        $this->assertDatabaseHas('hospitalizaciones', [
            'id' => $hospitalization->id,
            'estado' => 'alta',
            'dada_por' => $veterinarian->id,
        ]);
        $this->assertDatabaseHas('espacios_clinicos', [
            'id' => $bed->id,
            'estado' => 'limpieza',
        ]);

        $this->post(route('portal.espacios.desinfectar', $bed))
            ->assertRedirect(route('portal.hospitalizaciones.index'));

        $this->assertDatabaseHas('espacios_clinicos', [
            'id' => $bed->id,
            'estado' => 'disponible',
        ]);
    }

    public function test_only_administrators_and_veterinarians_can_access_the_surgery_and_hospitalization_module(): void
    {
        $receptionistRole = Role::query()->create(['nombre' => 'Recepcionista']);
        $receptionist = User::factory()->create([
            'role_id' => $receptionistRole->id,
            'estado' => 'activo',
        ]);

        $this->get(route('portal.hospitalizaciones.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($receptionist)
            ->get(route('portal.hospitalizaciones.index'))
            ->assertForbidden();

        $this->post(route('portal.espacios.store'), [
            'nombre' => 'Quirófano no autorizado',
            'tipo' => 'quirofano',
        ])->assertForbidden();

        $this->assertDatabaseCount('espacios_clinicos', 0);
    }

    public function test_veterinarian_cannot_schedule_surgery_for_another_professionals_attention(): void
    {
        [$veterinarian] = $this->createClinicalCase();
        [, , $otherOwner, $otherAttention] = $this->createClinicalCase(
            'M-002',
            'P-002',
            'other-veterinarian@example.test',
        );
        $room = $this->createSpace('Quirófano 1', 'quirofano');
        $surgeon = $this->createProfessional('surgeon@example.test');
        $anesthesiologist = $this->createProfessional('anesthesiologist@example.test');

        $this->actingAs($veterinarian)
            ->post(route('portal.cirugias.store'), $this->surgeryData(
                $otherAttention,
                $room,
                $surgeon,
                $anesthesiologist,
                $otherOwner,
            ))
            ->assertNotFound();

        $this->assertDatabaseCount('cirugias', 0);
    }

    /**
     * @return array{User, Profesional, Propietario, Atencion}
     */
    private function createClinicalCase(
        string $petCode = 'M-001',
        string $ownerCode = 'P-001',
        string $email = 'veterinarian@example.test',
    ): array {
        [$veterinarian, $professional] = $this->createVeterinarian($email);
        $owner = Propietario::query()->create([
            'codigo' => $ownerCode,
            'nombre' => 'Ana Pérez',
            'estado' => 'activo',
        ]);
        $pet = Mascota::query()->create([
            'codigo' => $petCode,
            'nombre' => 'Milo',
            'especie' => 'perro',
            'estado' => 'activo',
        ]);
        $appointment = Cita::query()->create([
            'folio' => 'CIT-'.$petCode,
            'mascota_id' => $pet->id,
            'propietario_id' => $owner->id,
            'profesional_id' => $professional->id,
            'inicio' => now()->subDay(),
            'fin' => now()->subDay()->addHour(),
            'estado' => 'completada',
        ]);
        $attention = Atencion::query()->create([
            'folio' => 'ATN-'.$petCode,
            'cita_id' => $appointment->id,
            'mascota_id' => $pet->id,
            'propietario_id' => $owner->id,
            'profesional_id' => $professional->id,
            'creado_por' => $veterinarian->id,
            'fecha_atencion' => now()->subHours(6),
            'estado' => 'abierta',
            'motivo' => 'Evaluación preoperatoria',
            'hospitalizacion_recomendada' => true,
        ]);
        NotaClinica::query()->create([
            'atencion_id' => $attention->id,
            'profesional_id' => $professional->id,
            'autor_id' => $veterinarian->id,
            'contenido' => 'Evaluación preoperatoria y autorización clínica.',
            'version' => 1,
        ]);

        return [$veterinarian, $professional, $owner, $attention];
    }

    /**
     * @return array{User, Profesional}
     */
    private function createVeterinarian(string $email = 'veterinarian@example.test'): array
    {
        $role = Role::query()->firstOrCreate(['nombre' => 'Veterinario']);
        $user = User::factory()->create([
            'email' => $email,
            'role_id' => $role->id,
            'estado' => 'activo',
        ]);
        $professional = Profesional::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Laura',
            'estado' => 'activo',
        ]);

        return [$user, $professional];
    }

    private function createProfessional(string $email): Profesional
    {
        [, $professional] = $this->createVeterinarian($email);

        return $professional;
    }

    private function createSpace(string $name, string $type): EspacioClinico
    {
        return EspacioClinico::query()->create([
            'nombre' => $name,
            'tipo' => $type,
            'estado' => 'disponible',
            'activo' => true,
        ]);
    }

    private function createProduct(): Producto
    {
        return Producto::query()->create([
            'codigo' => 'INS-001',
            'nombre' => 'Sutura quirúrgica',
            'unidad_base' => 'pieza',
            'costo_referencia' => 10,
            'precio_venta' => 20,
            'stock_minimo' => 1,
            'estado' => 'activo',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function surgeryData(
        Atencion $attention,
        EspacioClinico $room,
        Profesional $surgeon,
        Profesional $anesthesiologist,
        Propietario $owner,
        array $overrides = [],
    ): array {
        return array_merge([
            'atencion_id' => $attention->id,
            'espacio_id' => $room->id,
            'cirujano_id' => $surgeon->id,
            'anestesiologo_id' => $anesthesiologist->id,
            'propietario_id' => $owner->id,
            'programada_at' => now()->addDays(2)->setTime(9, 0)->format('Y-m-d\TH:i'),
            'duracion_minutos' => 120,
            'consentimiento_firmado' => '1',
            'observaciones' => 'Procedimiento programado.',
        ], $overrides);
    }
}
