<?php

use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('crea un rol y sincroniza permisos', function (): void {
    $service = new RoleService;
    $role = $service->create([
        'nombre' => 'veterinario',
        'descripcion' => 'Atención clínica',
        'activo' => true,
    ]);

    $permissionOne = Permission::query()->create([
        'clave' => 'ver_mascotas',
        'nombre' => 'Ver mascotas',
        'modulo' => 'mascotas',
    ]);
    $permissionTwo = Permission::query()->create([
        'clave' => 'editar_citas',
        'nombre' => 'Editar citas',
        'modulo' => 'citas',
    ]);

    $refreshedRole = $service->syncPermissions($role, [$permissionOne->id, $permissionTwo->id]);

    expect($refreshedRole->permissions)->toHaveCount(2)
        ->and($refreshedRole->permissions->pluck('clave')->all())->toContain('ver_mascotas', 'editar_citas');
});

it('asigna un permiso adicional sin duplicarlo', function (): void {
    $service = new RoleService;
    $role = $service->create(['nombre' => 'recepcionista', 'activo' => true]);
    $permission = Permission::query()->create([
        'clave' => 'agendar_citas',
        'nombre' => 'Agendar citas',
        'modulo' => 'citas',
    ]);

    $updatedRole = $service->assignPermissionToRole($role, $permission);

    expect($updatedRole->permissions)->toHaveCount(1)
        ->and($updatedRole->permissions->first()->clave)->toBe('agendar_citas');
});
