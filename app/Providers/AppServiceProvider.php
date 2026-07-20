<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->bind(PermissionService::class, function ($app) {
            return new PermissionService(Auth::user());
        });
    }

    public function boot(): void
    {
        // Compartir el servicio de permisos con todas las vistas
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $view->with('permissionService', app(PermissionService::class));
            }
        });
    }
}
