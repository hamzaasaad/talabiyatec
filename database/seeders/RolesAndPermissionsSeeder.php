<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\RolePermissionService;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(RolePermissionService::class)->syncFromConfig('api');

        $this->command->info('✅ Roles & Permissions synced successfully via Service + Repository pattern!');
    }
}
