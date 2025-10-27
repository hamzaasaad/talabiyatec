<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Interfaces\RolePermissionServiceInterface;

class SyncRolesCommand extends Command
{
    /**
     * اسم الأمر في Artisan
     *
     * @var string
     */
    protected $signature = 'roles:sync {guard=api}';

    protected $description = 'Synchronize roles and permissions from config/roles.php';

    protected RolePermissionServiceInterface $rolePermissionService;

    public function __construct(RolePermissionServiceInterface $rolePermissionService)
    {
        parent::__construct();
        $this->rolePermissionService = $rolePermissionService;
    }

    public function handle(): int
    {
        $guard = $this->argument('guard');

        $this->info('🔁 Synchronizing roles and permissions...');
        $this->rolePermissionService->syncFromConfig($guard);
        $this->info("✅ Roles & Permissions synced successfully for guard: {$guard}");

        return Command::SUCCESS;
    }
}
