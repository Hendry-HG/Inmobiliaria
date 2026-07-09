<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar si el usuario está activo
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        // Obtener roles del usuario directamente de la base de datos
        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        // Si no se especificaron roles, permitir acceso
        if (empty($roles) || (count($roles) === 1 && $roles[0] === '')) {
            return $next($request);
        }

        // Procesar roles (pueden venir como "Super Admin,Administrador" o como argumentos separados)
        $allowedRoles = [];
        foreach ($roles as $role) {
            // Si el rol contiene coma, dividirlo
            if (str_contains($role, ',')) {
                $splitRoles = explode(',', $role);
                foreach ($splitRoles as $splitRole) {
                    $allowedRoles[] = trim($splitRole);
                }
            } else {
                $allowedRoles[] = $role;
            }
        }

        // Verificar si tiene alguno de los roles requeridos
        foreach ($allowedRoles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        // Redirigir según el rol del usuario
        if (in_array('Super Admin', $userRoles)) {
            return redirect()->route('super-admin.dashboard');
        } elseif (in_array('Administrador', $userRoles)) {
            return redirect()->route('admin.dashboard');
        } elseif (in_array('Asesor Inmobiliario', $userRoles)) {
            return redirect()->route('asesor.dashboard');
        } elseif (in_array('Auditor', $userRoles)) {
            return redirect()->route('auditor.dashboard');
        } elseif (in_array('Cliente', $userRoles)) {
            return redirect()->route('cliente.dashboard');
        }

        abort(403, 'No tienes permiso para acceder a esta página.');
    }
}
