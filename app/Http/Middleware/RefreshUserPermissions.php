<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Cache;

/**
 * Middleware de actualización de permisos de usuario.
 *
 * Se ejecuta en cada petición autenticada para verificar si los permisos
 * del usuario han sido modificados desde la última sesión. Compara la versión
 * de permisos almacenada en caché con la versión de la sesión actual.
 *
 * Si se detecta una nueva versión, limpia las cachés de permisos del usuario
 * (tanto la caché personalizada como la de Spatie) y recarga los permisos
 * mediante el PermissionService.
 *
 * Esto garantiza que los usuarios obtengan permisos actualizados sin necesidad
 * de cerrar sesión, mejorando la experiencia en tiempo real.
 *
 * Se ejecuta en el ciclo de petición después de la autenticación y antes
 * que los middleware de autorización.
 *
 * @package App\Http\Middleware
 */
class RefreshUserPermissions
{
    /**
     * Maneja la petición verificando y actualizando los permisos del usuario si es necesario.
     *
     * Compara la versión de permisos en caché con la versión de la sesión.
     * Si la versión de sesión es mayor, limpia las cachés y recarga los permisos.
     *
     * @param Request $request Objeto de petición HTTP de Laravel.
     * @param Closure $next Callback que continúa el pipeline de middleware.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse Respuesta HTTP o redirección.
     */
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
