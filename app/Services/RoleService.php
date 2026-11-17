<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    /**
     * @return Collection<int, Role>
     */
    public function listActive(): Collection
    {
        return Role::query()->where('activo', true)->get();
    }

    /**
     * @param  array<int, int>  $permissionIds
     */
    public function syncPermissions(Role $role, array $permissionIds): Role
    {
        $role->permissions()->sync($permissionIds);

        return $role->fresh();
    }

    public function assignPermissionToRole(Role $role, Permission $permission): Role
    {
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        return $role->fresh();
    }
}
