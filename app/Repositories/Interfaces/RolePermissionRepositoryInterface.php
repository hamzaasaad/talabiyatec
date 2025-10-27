<?php

namespace App\Repositories\Interfaces;

interface RolePermissionRepositoryInterface
{
    public function syncPermissions(array $permissions, string $guard): void;

    public function syncRoles(array $roles, string $guard): void;

    public function assignPermissionsToRoles(array $rolePermissions, string $guard): void;
}
