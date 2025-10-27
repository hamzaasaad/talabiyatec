<?php

namespace App\Repositories;

use App\Repositories\Interfaces\RolePermissionRepositoryInterface;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionRepository implements RolePermissionRepositoryInterface
{
    public function syncPermissions(array $permissions, string $guard): void
{
    foreach ($permissions as $slug => $displayName) {
        Permission::firstOrCreate(
            [
                'name' => $displayName,
                'guard_name' => $guard,
            ],
            [
                'slug' => $slug,
            ]
        );
    }
}


   public function syncRoles(array $roles, string $guard): void
{
    foreach ($roles as $slug => $displayName) {
        Role::firstOrCreate(
            [
                'name' => $displayName,
                'guard_name' => $guard,
            ],
            [
                'slug' => $slug,
            ]
        );
    }
}


    public function assignPermissionsToRoles(array $rolePermissions, string $guard): void
    {
        foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)
                        ->where('guard_name', $guard)
                        ->first();

            if (!$role) continue;

            if (in_array('*', $permissionSlugs)) {
                $role->syncPermissions(Permission::where('guard_name', $guard)->get());
            } else {
                $role->syncPermissions($permissionSlugs);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
