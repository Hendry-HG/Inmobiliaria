<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    protected $user;

    protected array $dashboardMap = [
        'Super Admin' => 'super-admin.dashboard',
        'Administrador' => 'admin.dashboard',
        'Asesor Inmobiliario' => 'asesor.dashboard',
        'Auditor' => 'auditor.dashboard',
        'Cliente' => 'cliente.dashboard',
    ];

    protected array $sidebarPermissions = [
        'sidebar.dashboard' => 'Dashboard',
        'sidebar.catalogo' => 'Catálogo',
        'sidebar.users' => 'Gestión de Usuarios',
        'sidebar.roles' => 'Roles y Permisos',
        'sidebar.properties' => 'Propiedades',
        'sidebar.leads' => 'Leads',
        'sidebar.appointments' => 'Citas',
        'sidebar.chat' => 'Chat',
        'sidebar.audit' => 'Auditoría',
        'sidebar.reports' => 'Reportes Gerenciales',
        'sidebar.config' => 'Configuración',
        'sidebar.favorites' => 'Mis Favoritos',
        'sidebar.servicios' => 'Servicios',
        'sidebar.categorias' => 'Categorías',
        'sidebar.ubicaciones' => 'Ubicaciones',
        'sidebar.telefonos' => 'Formato Telefónico',
        'sidebar.profile' => 'Mi Perfil',
    ];

    public function __construct($user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    /**
     * Actualizar el usuario del servicio
     */
    public function setUser($user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Obtener el usuario actual
     * Siempre intenta obtener el usuario más actualizado
     */
    protected function getUser()
    {
        // Si ya tenemos un usuario en la propiedad, usarlo
        if ($this->user) {
            return $this->user;
        }

        // Si no hay usuario, intentar obtener de Auth
        return Auth::user();
    }



    public function hasPermission(string $permission): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        // Super Admin tiene todos los permisos
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasAnyPermission($permissions);
    }

    public function hasAllPermissions(array $permissions): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasAllPermissions($permissions);
    }

    public function hasRole(string $role): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        return $user->hasRole($role);
    }

    public function hasAnyRole(array $roles): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        return $user->hasAnyRole($roles);
    }

    public function getRoles(): array
    {
        $user = $this->getUser();

        if (!$user) {
            return [];
        }

        return $user->roles()->pluck('name')->toArray();
    }

    public function getMainRole(): ?string
    {
        $roles = $this->getRoles();
        return $roles[0] ?? null;
    }

    public function getRoleDisplayName(): string
    {
        $roleNames = [
            'Super Admin' => 'Super Administrador',
            'Administrador' => 'Administrador',
            'Asesor Inmobiliario' => 'Asesor Inmobiliario',
            'Auditor' => 'Auditor',
            'Cliente' => 'Cliente',
        ];

        $role = $this->getMainRole();
        return $roleNames[$role] ?? $role ?? 'Usuario';
    }

    public function getDashboardRoute(): string
    {
        $role = $this->getMainRole();

        if (isset($this->dashboardMap[$role])) {
            return route($this->dashboardMap[$role]);
        }

        return route('dashboard.generic');
    }

    // ==========================================
    // MÉTODOS DEL SIDEBAR
    // ==========================================

    public function canSeeSidebarItem(string $permission): bool
    {
        return $this->hasPermission($permission);
    }

    public function getSidebarItems(): array
    {
        $items = [];

        foreach ($this->sidebarPermissions as $permission => $label) {
            $items[$permission] = [
                'label' => $label,
                'visible' => $this->canSeeSidebarItem($permission),
                'permission' => $permission,
            ];
        }

        return $items;
    }

    // ==========================================
    // PERMISOS COMPUESTOS
    // ==========================================

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador']) ||
               $this->hasAnyPermission(['ver usuarios', 'gestionar usuarios', 'crear usuario', 'editar usuario', 'eliminar usuario']);
    }

    public function canManageRoles(): bool
    {
        return $this->hasRole('Super Admin') ||
               $this->hasAnyPermission(['ver roles', 'crear rol', 'editar rol', 'eliminar rol']);
    }

    public function canManageProperties(): bool
    {
        return !$this->hasRole('Cliente') ||
               $this->hasAnyPermission(['ver propiedades', 'gestionar propiedades', 'crear propiedad', 'editar propiedad', 'eliminar propiedad']);
    }

    public function canViewLeads(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador', 'Asesor Inmobiliario', 'Auditor']) ||
               $this->hasAnyPermission(['ver leads', 'gestionar leads', 'crear lead', 'editar lead', 'eliminar lead']);
    }

    public function canManageLeads(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador', 'Asesor Inmobiliario']) ||
               $this->hasAnyPermission(['crear lead', 'editar lead', 'eliminar lead', 'gestionar leads']);
    }

    public function canViewAudit(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador', 'Auditor']) ||
               $this->hasAnyPermission(['ver logs de auditoria', 'acceso auditoria', 'ver reportes']);
    }

    public function canViewReports(): bool
    {
        return $this->hasAnyPermission(['ver reportes', 'exportar reportes']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador', 'Auditor']);
    }

    public function canChat(): bool
    {
        if ($this->hasAnyRole(['Asesor Inmobiliario', 'Cliente'])) {
            return true;
        }

        if ($this->hasPermission('chat access') && $this->hasAnyRole(['Asesor Inmobiliario', 'Cliente'])) {
            return true;
        }

        return false;
    }

    public function canManageAppointments(): bool
    {
        return $this->hasAnyPermission(['ver citas', 'crear cita', 'editar cita', 'eliminar cita', 'gestionar citas']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador', 'Asesor Inmobiliario']);
    }

    public function canManageServices(): bool
    {
        return $this->hasAnyPermission(['ver servicios', 'crear servicios', 'editar servicios', 'eliminar servicios']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    public function canManageCategories(): bool
    {
        return $this->hasAnyPermission(['ver categorias', 'crear categoria', 'editar categoria', 'eliminar categoria']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    public function canManageLocations(): bool
    {
        return $this->hasAnyPermission(['ver paises', 'crear paises', 'eliminar paises', 'ver estados', 'crear estados', 'eliminar estados']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    public function canManageConfig(): bool
    {
        return $this->hasAnyPermission(['ver configuración', 'editar configuración', 'actualizar configuracion telefonica']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    public function canViewFavorites(): bool
    {
        return true;
    }

    public function canAccessDashboard(): bool
    {
        return true;
    }

    public function getSidebarPermissions(): array
    {
        return [
            'canManageUsers' => $this->canManageUsers(),
            'canManageRoles' => $this->canManageRoles(),
            'canManageProperties' => $this->canManageProperties(),
            'canViewLeads' => $this->canViewLeads(),
            'canManageLeads' => $this->canManageLeads(),
            'canViewAudit' => $this->canViewAudit(),
            'canViewReports' => $this->canViewReports(),
            'canChat' => $this->canChat(),
            'canManageAppointments' => $this->canManageAppointments(),
            'canManageServices' => $this->canManageServices(),
            'canManageCategories' => $this->canManageCategories(),
            'canManageLocations' => $this->canManageLocations(),
            'canManageConfig' => $this->canManageConfig(),
            'canViewFavorites' => $this->canViewFavorites(),
            'canAccessDashboard' => $this->canAccessDashboard(),
        ];
    }

    public function clearCache(): void
    {
        if ($this->user) {
            Cache::forget('user_permissions_' . $this->user->id);
        }
    }

    // ==========================================
    // 🔍 MÉTODO DE DEPURACIÓN
    // ==========================================

    public function debug(string $permission): array
    {
        $user = $this->getUser();

        if (!$user) {
            return [
                'success' => false,
                'error' => 'No user found',
                'user_id' => null,
            ];
        }

        $permissionExists = \Spatie\Permission\Models\Permission::where('name', $permission)
            ->where('guard_name', 'web')
            ->exists();

        $hasDirect = $user->hasDirectPermission($permission);
        $hasViaRole = false;
        $roles = $user->roles;

        foreach ($roles as $role) {
            if ($role->hasPermissionTo($permission)) {
                $hasViaRole = true;
                break;
            }
        }

        return [
            'success' => true,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_is_active' => $user->is_active ?? false,
            'roles' => $roles->pluck('name')->toArray(),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'permission_exists' => $permissionExists,
            'has_direct_permission' => $hasDirect,
            'has_via_role' => $hasViaRole,
            'has_permission_spatie' => $user->hasPermissionTo($permission),
            'is_super_admin' => $user->hasRole('Super Admin'),
            'guard_name' => $permissionExists ? \Spatie\Permission\Models\Permission::where('name', $permission)->first()->guard_name : null,
        ];
    }
}
