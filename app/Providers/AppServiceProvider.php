<?php

namespace App\Providers;

use App\Contracts\Admin\PermissionServiceInterface;
use App\Contracts\Admin\RoleServiceInterface;
use App\Contracts\Admin\UserServiceInterface;
use App\Contracts\Base\BaseServiceInterface;
use App\Services\Admin\PermissionService;
use App\Services\Admin\RoleService;
use App\Services\Admin\UserService;
use App\Services\Operation\CentroComercialService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(BaseServiceInterface::class, CentroComercialService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
