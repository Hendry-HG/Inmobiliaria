<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    protected $user;
    protected $permissions = [];
    protected $roles = [];
    protected $isLoaded = false;

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
        if ($this->user) {
            $this->loadUserData();
        }
    }

    protected function loadUserData(): void
    {
        if (!$this->user) {
            Log::warning('PermissionService - No hay usuario para cargar datos');
            return;
        }

        try {
            $roles = $this->user->roles()->pluck('name')->toArray();


            $permissions = $this->user->getAllPermissions()->pluck('name')->toArray();

            $this->roles = $roles;
            $this->permissions = $permissions;
            $this->isLoaded = true;

            Log::info('PermissionService - Datos cargados', [
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
                'roles' => $this->roles,
                'permissions_count' => count($this->permissions),
                'is_super_admin' => in_array('Super Admin', $roles)
            ]);

        } catch (\Exception $e) {
            Log::error('PermissionService - Error cargando datos', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);

            $this->roles = [];
            $this->permissions = [];
            $this->isLoaded = true;
        }
    }

    public function refresh(): void
    {
        $this->isLoaded = false;
        $this->roles = [];
        $this->permissions = [];
        $this->loadUserData();

        if ($this->user) {
            Cache::forget('user_permissions_' . $this->user->id);
        }
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getMainRole(): ?string
    {
        return $this->roles[0] ?? null;
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

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function hasAnyRole(array $roles): bool
    {
        return !empty(array_intersect($roles, $this->roles));
    }

    public function hasAllRoles(array $roles): bool
    {
        return empty(array_diff($roles, $this->roles));
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->permissions));
    }

    public function hasAllPermissions(array $permissions): bool
    {
        return empty(array_diff($permissions, $this->permissions));
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

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

        if ($this->hasRole('Super Admin')) {
            return false;
        }

        if ($this->hasRole('Administrador')) {
            return false;
        }

        if ($this->hasRole('Auditor')) {
            return false;
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
}
