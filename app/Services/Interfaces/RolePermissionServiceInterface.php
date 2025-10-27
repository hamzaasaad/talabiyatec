<?php

namespace App\Services\Interfaces;

interface RolePermissionServiceInterface
{
    public function syncFromConfig(string $guard = 'api'): void;
}
