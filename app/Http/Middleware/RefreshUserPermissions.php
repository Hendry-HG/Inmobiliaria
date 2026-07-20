<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Cache;

class RefreshUserPermissions
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = 'user_permissions_version_' . $user->id;
            $currentVersion = Cache::get($cacheKey, 0);
            $sessionVersion = session('permissions_version', 0);

            // Si hay una nueva versión de permisos, recargar
            if ($sessionVersion > $currentVersion) {
                // Limpiar caché del usuario
                Cache::forget('user_permissions_' . $user->id);
                Cache::put($cacheKey, $sessionVersion, 3600);

                // Recargar permisos en el servicio
                $permissionService = new PermissionService($user);
                $permissionService->refresh();

                // Limpiar caché de Spatie
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }
        }

        return $next($request);
    }
}
