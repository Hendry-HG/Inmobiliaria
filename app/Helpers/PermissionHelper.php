<?php
// app/Helpers/PermissionHelper.php

namespace App\Helpers;

use App\Services\PermissionService;

if (!function_exists('permission')) {
    /**
     * Obtener una instancia del servicio de permisos
     */
    function permission($user = null)
    {
        if ($user) {
            return new PermissionService($user);
        }
        return app(PermissionService::class);
    }
}

if (!function_exists('canAccess')) {
    /**
     * Verificar si el usuario puede acceder a una sección
     */
    function canAccess($section, $user = null)
    {
        $permission = permission($user);

        $sections = [
            'dashboard' => $permission->canAccessDashboard(),
            'users' => $permission->canManageUsers(),
            'roles' => $permission->canManageRoles(),
            'properties' => $permission->canManageProperties(),
            'leads' => $permission->canViewLeads(),
            'audit' => $permission->canViewAudit(),
            'reports' => $permission->canViewReports(),
            'chat' => $permission->canChat(),
            'appointments' => $permission->canManageAppointments(),
            'services' => $permission->canManageServices(),
            'categories' => $permission->canManageCategories(),
            'locations' => $permission->canManageLocations(),
            'config' => $permission->canManageConfig(),
            'favorites' => $permission->canViewFavorites(),
        ];

        return $sections[$section] ?? false;
    }
}

if (!function_exists('userHasRole')) {
    /**
     * Verificar si el usuario tiene un rol específico
     */
    function userHasRole($role, $user = null)
    {
        return permission($user)->hasRole($role);
    }
}

if (!function_exists('userHasPermission')) {
    /**
     * Verificar si el usuario tiene un permiso específico
     */
    function userHasPermission($permission, $user = null)
    {
        return permission($user)->hasPermission($permission);
    }
}
