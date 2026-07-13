<?php
// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        //  Verificar si el usuario está activo
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        //  OBTENER ROLES DEL USUARIO (USANDO RELACIÓN)
        $userRoles = $user->roles->pluck('name')->toArray();

        //  PROCESAR ROLES REQUERIDOS
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (str_contains($role, ',')) {
                $splitRoles = explode(',', $role);
                foreach ($splitRoles as $splitRole) {
                    $allowedRoles[] = trim($splitRole);
                }
            } else {
                $allowedRoles[] = $role;
            }
        }

        //  ELIMINAR ROLES VACÍOS
        $allowedRoles = array_filter($allowedRoles, function($role) {
            return !empty($role);
        });

        //  VALIDAR QUE HAYA ROLES REQUERIDOS
        if (empty($allowedRoles)) {
            Log::warning('Intento de acceso sin roles especificados', [
                'user' => $user->email,
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);
            abort(403, 'No se especificaron roles para esta ruta.');
        }

        //  VERIFICAR SI EL USUARIO TIENE ALGUNO DE LOS ROLES REQUERIDOS
        foreach ($allowedRoles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        //  LOG DE ACCESO DENEGADO
        Log::warning('Acceso denegado por rol', [
            'user' => $user->email,
            'user_roles' => $userRoles,
            'required_roles' => $allowedRoles,
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);

        //  REDIRIGIR SEGÚN EL ROL DEL USUARIO
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