<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\RolePermissionRepositoryInterface;
use App\Repositories\Interfaces\RefreshTokenRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\ActivityRepositoryInterface;

use App\Repositories\RolePermissionRepository;
use App\Repositories\RefreshTokenRepository;
use App\Repositories\UserRepository;
use App\Repositories\ActivityRepository;

use App\Services\Interfaces\RolePermissionServiceInterface;
use App\Services\Interfaces\JwtProviderInterface;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\ActivityServiceInterface;

use App\Services\LcobucciJwtProvider;
use App\Services\RolePermissionService;
use App\Services\AuthService;
use App\Services\DatabaseTransactionService;
use App\Services\ActivityService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->bind(RolePermissionRepositoryInterface::class,RolePermissionRepository::class );
         $this->app->bind(RolePermissionServiceInterface::class,RolePermissionService::class );

$this->app->bind(RefreshTokenRepositoryInterface::class,RefreshTokenRepository::class);
$this->app->bind(ActivityRepositoryInterface::class,ActivityRepository::class);

$this->app->bind(ActivityServiceInterface::class,ActivityService::class);

$this->app->bind(JwtProviderInterface::class,LcobucciJwtProvider::class);


$this->app->bind(AuthServiceInterface::class,AuthService::class);
$this->app->bind(TransactionServiceInterface::class,DatabaseTransactionService::class);
$this->app->bind(UserRepositoryInterface ::class,UserRepository::class);




    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
