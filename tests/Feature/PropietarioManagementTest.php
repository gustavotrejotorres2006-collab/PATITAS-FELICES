<?php

namespace Tests\Feature;

use App\Models\Mascota;
use App\Models\Propietario;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropietarioManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_owner_list(): void
    {
        $this->get(route('portal.propietarios.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_administrator_cannot_manage_owners_or_pets(): void
    {
        $role = Role::query()->create(['nombre' => 'Recepcionista']);
        $user = User::factory()->create(['role_id' => $role->id, 'estado' => 'activo']);

        $this->actingAs($user)
            ->get(route('portal.propietarios.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('portal.mascotas.index'))
            ->assertForbidden();
    }

    public function test_owner_list_searches_by_name_and_displays_only_matching_owners(): void
    {
        $administrator = $this->createAdministrator();
        $this->assertSame('activo', $administrator->estado);
        $this->assertSame('Administrador', $administrator->role->nombre);
        $this->assertTrue($administrator->role->activo);
        $matchingOwner = $this->createOwner(['nombre' => 'María', 'apellido' => 'López']);
        $this->createOwner(['nombre' => 'Pedro', 'apellido' => 'Sánchez']);

        $this->actingAs($administrator)
            ->get(route('portal.propietarios.index', ['buscar' => 'María']))
            ->assertOk()
            ->assertSee('María')
            ->assertSee($matchingOwner->codigo)
            ->assertDontSee('Pedro');
    }

    public function test_administrator_can_create_an_owner_with_a_server_generated_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->post(route('portal.propietarios.store'), [
                'nombre' => 'Ana',
                'apellido' => 'Ramírez',
                'telefono' => '5551234567',
                'email' => 'ana@example.test',
                'direccion' => 'Calle del Parque 10',
                'estado' => 'inactivo',
                'codigo' => 'P-FAKE',
            ])
            ->assertRedirect(route('portal.propietarios.show', Propietario::query()->where('email', 'ana@example.test')->first()));

        $owner = Propietario::query()->where('email', 'ana@example.test')->firstOrFail();

        $this->assertModelExists($owner);
        $this->assertSame('activo', $owner->estado);
        $this->assertNotSame('P-FAKE', $owner->codigo);
        $this->assertNotSame($administrator->id, $owner->user_id);
    }

    public function test_owner_validation_keeps_missing_required_name_out_of_storage(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('portal.propietarios.create'))
            ->post(route('portal.propietarios.store'), [
                'apellido' => 'López',
            ])
            ->assertRedirect(route('portal.propietarios.create'))
            ->assertSessionHasErrors('nombre');

        $this->assertDatabaseCount('propietarios', 0);
    }

    public function test_administrator_can_update_owner_contact_data_without_changing_managed_fields(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner(['codigo' => 'P-1001']);

        $this->actingAs($administrator)
            ->put(route('portal.propietarios.update', $owner), [
                'nombre' => 'Luisa',
                'apellido' => 'Pérez',
                'telefono' => '5559876543',
                'email' => 'luisa@example.test',
                'direccion' => 'Avenida Central 20',
                'codigo' => 'P-ALTERADO',
                'estado' => 'inactivo',
                'user_id' => $administrator->id,
            ])
            ->assertRedirect(route('portal.propietarios.show', $owner));

        $owner->refresh();

        $this->assertSame('5559876543', $owner->telefono);
        $this->assertSame('P-1001', $owner->codigo);
        $this->assertSame('activo', $owner->estado);
        $this->assertNull($owner->user_id);
    }

    public function test_owner_detail_escapes_names_and_lists_associated_pets(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner(['nombre' => '<script>alert(1)</script>']);
        $pet = $this->createPet($owner, ['nombre' => 'Luna']);

        $response = $this->actingAs($administrator)
            ->get(route('portal.propietarios.show', $owner))
            ->assertOk()
            ->assertSee('Luna')
            ->assertSee($pet->codigo)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);

        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_administrator_can_register_a_pet_for_an_owner(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();

        $this->actingAs($administrator)
            ->post(route('portal.propietarios.mascotas.store', $owner), [
                'nombre' => 'Moka',
                'especie' => 'perro',
                'raza' => 'mestiza',
                'sexo' => 'hembra',
                'fecha_nacimiento' => '2023-02-10',
                'color' => 'café',
                'microchip' => 'CHIP-0001',
                'esterilizado' => '1',
                'observaciones' => 'Alergia estacional',
                'codigo' => 'M-FAKE',
                'estado' => 'inactivo',
                'validado_por' => 999,
            ])
            ->assertRedirect(route('portal.propietarios.show', $owner));

        $pet = Mascota::query()->where('microchip', 'CHIP-0001')->firstOrFail();

        $this->assertModelExists($pet);
        $this->assertTrue($pet->esterilizado);
        $this->assertSame('Moka', $pet->nombre);
        $this->assertSame('activo', $pet->estado);
        $this->assertNull($pet->validado_por);
        $this->assertSame(
            $owner->id,
            $pet->propietarios()->wherePivot('es_principal', true)->value('propietarios.id'),
        );
        $this->assertNotSame('M-FAKE', $pet->codigo);
    }

    public function test_pet_registration_requires_a_valid_species_and_future_dates_are_rejected(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();

        $this->actingAs($administrator)
            ->from(route('portal.propietarios.mascotas.create', $owner))
            ->post(route('portal.propietarios.mascotas.store', $owner), [
                'nombre' => 'Moka',
                'especie' => 'desconocida',
                'fecha_nacimiento' => now()->addDay()->toDateString(),
            ])
            ->assertRedirect(route('portal.propietarios.mascotas.create', $owner))
            ->assertSessionHasErrors(['especie', 'fecha_nacimiento']);

        $this->assertDatabaseCount('mascotas', 0);
        $this->assertDatabaseCount('mascota_propietario', 0);
    }

    public function test_pet_registration_rejects_a_microchip_already_used_by_another_pet(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();
        $this->createPet($owner, ['microchip' => 'CHIP-DUPLICADO']);

        $this->actingAs($administrator)
            ->from(route('portal.propietarios.mascotas.create', $owner))
            ->post(route('portal.propietarios.mascotas.store', $owner), [
                'nombre' => 'Otra mascota',
                'especie' => 'gato',
                'microchip' => 'CHIP-DUPLICADO',
            ])
            ->assertRedirect(route('portal.propietarios.mascotas.create', $owner))
            ->assertSessionHasErrors('microchip');

        $this->assertDatabaseCount('mascotas', 1);
        $this->assertDatabaseCount('mascota_propietario', 1);
    }

    public function test_pet_cannot_be_registered_for_an_inactive_owner(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner(['estado' => 'inactivo']);

        $this->actingAs($administrator)
            ->get(route('portal.propietarios.mascotas.create', $owner))
            ->assertNotFound();

        $this->assertDatabaseCount('mascotas', 0);
    }

    public function test_pet_search_matches_owner_and_pet_names(): void
    {
        $administrator = $this->createAdministrator();
        $matchingOwner = $this->createOwner(['nombre' => 'Camila', 'apellido' => 'Vega']);
        $this->createPet($matchingOwner, ['nombre' => 'Coco']);
        $otherOwner = $this->createOwner(['codigo' => 'P-1002', 'nombre' => 'Pedro']);
        $this->createPet($otherOwner, ['codigo' => 'M-002', 'nombre' => 'Nube']);

        $this->actingAs($administrator)
            ->get(route('portal.mascotas.index', ['buscar' => 'Camila']))
            ->assertOk()
            ->assertSee('Coco')
            ->assertDontSee('Nube');
    }

    public function test_administrator_can_view_a_pet_record_and_its_active_owners(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();
        $pet = $this->createPet($owner, [
            'nombre' => 'Nube',
            'especie' => 'perro',
            'observaciones' => 'Requiere dieta especial',
        ]);

        $this->actingAs($administrator)
            ->get(route('portal.mascotas.show', $pet))
            ->assertOk()
            ->assertSee('Nube')
            ->assertSee('Requiere dieta especial')
            ->assertSee($owner->nombre)
            ->assertSee('Principal')
            ->assertSee(route('portal.mascotas.edit', $pet));
    }

    public function test_administrator_can_update_pet_details_without_changing_system_fields_or_owners(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();
        $pet = $this->createPet($owner, [
            'codigo' => 'M-EDITAR',
            'microchip' => 'CHIP-EDITAR',
            'validado_por' => $administrator->id,
        ]);

        $this->actingAs($administrator)
            ->put(route('portal.mascotas.update', $pet), [
                'nombre' => 'Luna nueva',
                'especie' => 'gato',
                'raza' => 'Criolla',
                'sexo' => 'hembra',
                'color' => 'gris',
                'microchip' => 'CHIP-EDITAR',
                'esterilizado' => '1',
                'observaciones' => 'Control anual',
                'codigo' => 'M-ALTERADO',
                'estado' => 'inactivo',
                'validado_por' => null,
            ])
            ->assertRedirect(route('portal.mascotas.show', $pet));

        $pet->refresh();

        $this->assertSame('Luna nueva', $pet->nombre);
        $this->assertSame('gato', $pet->especie);
        $this->assertTrue($pet->esterilizado);
        $this->assertSame('M-EDITAR', $pet->codigo);
        $this->assertSame('activo', $pet->estado);
        $this->assertSame($administrator->id, $pet->validado_por);
        $this->assertDatabaseHas('mascota_propietario', [
            'mascota_id' => $pet->id,
            'propietario_id' => $owner->id,
            'es_principal' => true,
        ]);
    }

    public function test_pet_update_rejects_a_microchip_owned_by_another_pet(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();
        $petWithChip = $this->createPet($owner, ['microchip' => 'CHIP-EXISTENTE']);
        $petToUpdate = $this->createPet($owner, [
            'codigo' => 'M-002',
            'nombre' => 'Coco',
        ]);

        $this->actingAs($administrator)
            ->from(route('portal.mascotas.edit', $petToUpdate))
            ->put(route('portal.mascotas.update', $petToUpdate), [
                'nombre' => 'Coco',
                'especie' => 'gato',
                'microchip' => 'CHIP-EXISTENTE',
            ])
            ->assertRedirect(route('portal.mascotas.edit', $petToUpdate))
            ->assertSessionHasErrors('microchip');

        $this->assertSame('CHIP-EXISTENTE', $petWithChip->refresh()->microchip);
        $this->assertNull($petToUpdate->refresh()->microchip);
    }

    public function test_soft_deleted_pet_is_not_available_for_detail_or_update(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();
        $pet = $this->createPet($owner);
        $pet->delete();

        $this->actingAs($administrator)
            ->get(route('portal.mascotas.show', $pet->id))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('portal.mascotas.update', $pet->id), [
                'nombre' => 'No debe modificarse',
                'especie' => 'gato',
            ])
            ->assertNotFound();
    }

    public function test_owner_routes_do_not_resolve_soft_deleted_owners(): void
    {
        $administrator = $this->createAdministrator();
        $owner = $this->createOwner();
        $owner->delete();

        $this->actingAs($administrator)
            ->get(route('portal.propietarios.show', $owner->id))
            ->assertNotFound();
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
     * @param  array<string, mixed>  $attributes
     */
    private function createOwner(array $attributes = []): Propietario
    {
        return Propietario::query()->create(array_merge([
            'codigo' => 'P-'.Str::upper((string) Str::ulid()),
            'nombre' => 'María',
            'apellido' => 'López',
            'telefono' => '5551112233',
            'email' => 'maria@example.test',
            'direccion' => 'Calle 1',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPet(Propietario $owner, array $attributes = []): Mascota
    {
        $pet = Mascota::query()->create(array_merge([
            'codigo' => 'M-001',
            'nombre' => 'Luna',
            'especie' => 'gato',
        ], $attributes));

        $owner->mascotas()->attach($pet, [
            'es_principal' => true,
            'estado' => 'activo',
        ]);

        return $pet;
    }
}
