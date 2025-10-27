<?php

namespace App\Services;

use App\Repositories\Interfaces\RolePermissionRepositoryInterface;
use App\Services\Interfaces\RolePermissionServiceInterface;

class RolePermissionService implements RolePermissionServiceInterface
{
    protected RolePermissionRepositoryInterface $repository;

    public function __construct(RolePermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function syncFromConfig(string $guard = 'api'): void
    {
        $roles = config('roles.roles');
        $permissions = config('roles.permissions');
        $rolePermissions = config('roles.role_permissions');

        $this->repository->syncPermissions($permissions, $guard);
        $this->repository->syncRoles($roles, $guard);
        $this->repository->assignPermissionsToRoles($rolePermissions, $guard);
    }
}
