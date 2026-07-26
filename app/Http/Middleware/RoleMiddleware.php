<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Middleware de verificación de roles de usuario.
 *
 * Valida que el usuario autenticado posea al menos uno de los roles requeridos
 * para acceder a la ruta protegida. Se ejecuta después del middleware de autenticación
 * en el ciclo de vida de la petición.
 *
 * Los roles pueden especificarse separados por pipes (|) o comas (,).
 * Consulta directamente la base de datos para obtener los roles del usuario.
 *
 * Registra intentos de acceso y denegaciones en el log de la aplicación.
 * Redirige al login si el usuario no está autenticado o desactiva la sesión
 * si la cuenta está desactivada.
 *
 * @package App\Http\Middleware
 */
class RoleMiddleware
{
    /**
     * Maneja la petición HTTP verificando los roles del usuario.
     *
     * @param Request $request Objeto de petición HTTP de Laravel.
     * @param Closure $next Callback que continúa el pipeline de middleware.
     * @param string  ...$roles Roles permitidos para acceder a la ruta (separados por | o ,).
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse Respuesta HTTP o redirección.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        // Procesar roles requeridos
        $allowedRoles = [];
        foreach ($roles as $role) {
            $splitRoles = preg_split('/[|,]/', $role);
            foreach ($splitRoles as $splitRole) {
                $trimmed = trim($splitRole);
                if (!empty($trimmed)) {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        $allowedRoles = array_unique($allowedRoles);

        if (empty($allowedRoles)) {
            abort(403, 'No se especificaron roles para esta ruta.');
        }

        // =============================================
        // OBTENER ROLES DIRECTAMENTE DE LA BASE DE DATOS
        // =============================================
        try {
            $userRoles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $user->id)
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->pluck('roles.name')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('RoleMiddleware - Error obteniendo roles', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            abort(403, 'Error al verificar permisos.');
        }

        // =============================================
        // LOG DE DEPURACIÓN
        // =============================================
        Log::info('RoleMiddleware - Verificando acceso', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles_db' => $userRoles,
            'required_roles' => $allowedRoles,
            'url' => $request->fullUrl()
        ]);

        // =============================================
        // VERIFICAR SI EL USUARIO TIENE ALGUNO DE LOS ROLES REQUERIDOS
        // =============================================
        foreach ($allowedRoles as $requiredRole) {
            if (in_array($requiredRole, $userRoles)) {
                Log::info('RoleMiddleware - Acceso permitido', [
                    'user' => $user->email,
                    'role' => $requiredRole
                ]);
                return $next($request);
            }
        }

        // =============================================
        // ACCESO DENEGADO
        // =============================================
        Log::warning('RoleMiddleware - Acceso denegado', [
            'user' => $user->email,
            'user_roles' => $userRoles,
            'required_roles' => $allowedRoles,
            'url' => $request->fullUrl()
        ]);

        abort(403, 'No tienes permiso para acceder a esta página.');
    }
}
