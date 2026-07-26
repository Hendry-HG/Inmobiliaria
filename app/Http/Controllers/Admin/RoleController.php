<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\AuditTrait;
use Illuminate\Validation\Rule;

/**
 * Controlador para gestionar roles y permisos del sistema.
 *
 * Este controlador gestiona el CRUD completo de roles, incluyendo la asignación
 * de permisos del sistema. Los permisos del sidebar se gestionan por separado
 * a través del SidebarPermissionController.
 *
 * SEPARACIÓN DE PERMISOS:
 * - Permisos del SISTEMA: Determinan qué acciones puede realizar un usuario
 *   (ej: "create Propiedades", "edit Usuarios"). NO comienzan con "sidebar."
 * - Permisos del SIDEBAR: Determinan qué menús puede ver un usuario
 *   (ej: "sidebar.propiedades"). Comienzan con "sidebar."
 *
 * REGLA FUNDAMENTAL: Los permisos del sistema del rol "Super Admin" NO pueden
 * ser editados desde este controlador. Solo el Super Admin puede modificar
 * sus propios permisos del sidebar (a través del SidebarPermissionController).
 *
 * FLUJO DE CACHÉ:
 * 1. Al consultar permisos, se cachean agrupados por categoría (1 hora)
 * 2. Al modificar, se invalida la caché de permisos agrupados
 * 3. Se invalida la caché de Spatie PermissionRegistrar
 * 4. Se limpian las cachés individuales del usuario autenticado
 */
class RoleController extends Controller
{
    use AuditTrait;

    /**
     * Constructor del controlador.
     *
     * Aplica el middleware 'auth' a todos los métodos del controlador,
     * asegurando que solo usuarios autenticados puedan acceder.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Crea y retorna una instancia de PermissionService para el usuario actual.
     *
     * PermissionService es un servicio personalizado que encapsula la lógica
     * de verificación de permisos y roles. Se utiliza para:
     * - Verificar si el usuario tiene un rol específico (hasRole)
     * - Obtener permisos agrupados del usuario
     * - Gestionar la caché de permisos del usuario
     *
     * @return \App\Services\PermissionService Instancia del servicio de permisos
     */
    private function getPermissionService()
    {
        return new PermissionService(Auth::user());
    }

    /**
     * Verifica que el usuario autenticado tenga el rol de Super Admin.
     *
     * Método de protección utilizado por todos los endpoints del controlador.
     * Implementa una doble verificación:
     * 1. Primero confirma que el usuario esté autenticado
     * 2. Luego verifica que tenga el rol 'Super Admin' a través de PermissionService
     *
     * Si cualquiera de las verificaciones falla, se aborta con código 403.
     *
     * @return bool Retorna true si el usuario tiene acceso (Super Admin)
     * @throws \Symfony\Component\HttpKernel\Exception\HttpHttpException 403 si no tiene acceso
     */
    private function checkSuperAdminAccess()
    {
        if (!Auth::check()) {
            abort(403, 'No autenticado.');
        }

        $permission = $this->getPermissionService();

        if (!$permission->hasRole('Super Admin')) {
            abort(403, 'No tienes permiso para acceder a esta página. Solo Super Admin.');
        }

        return true;
    }

    /**
     * Muestra la lista paginada de todos los roles del sistema.
     *
     * Flujo de datos:
     * 1. Verifica acceso de Super Admin
     * 2. Consulta todos los roles con eager loading de permisos y usuarios
     * 3. Pagina los resultados en 20 elementos por página
     * 4. Retorna la vista con la lista de roles
     *
     * La consulta incluye 'permissions' y 'users' para mostrar en la vista:
     * - Cuántos permisos tiene cada rol
     * - Cuántos usuarios tienen asignado cada rol
     *
     * @return \Illuminate\View\View Vista con la lista paginada de roles
     */
    public function index()
    {
        $this->checkSuperAdminAccess();
        $roles = Role::with('permissions', 'users')->paginate(20);
        return view('dashboard.admin.roles.index', compact('roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo rol.
     *
     * SEPARACIÓN DE PERMISOS:
     * Este método carga ÚNICAMENTE los permisos del sistema (sin prefijo "sidebar.").
     * Los permisos del sidebar NO se muestran aquí porque se gestionan
     * exclusivamente desde el SidebarPermissionController.
     *
     * Flujo de datos:
     * 1. Verifica acceso de Super Admin
     * 2. Consulta todos los permisos del sistema (filtra los que NO comienzan con 'sidebar.')
     * 3. Agrupa los permisos por categoría usando el segundo segmento del nombre
     *    (ej: "create Propiedades" -> categoría "Propiedades")
     * 4. Los permisos se cachean por 1 hora (3600 segundos) bajo la clave 'permissions_grouped'
     * 5. Retorna el formulario de creación con los permisos agrupados
     *
     * @return \Illuminate\View\View Vista del formulario de creación de rol
     */
    public function create()
    {
        $this->checkSuperAdminAccess();

        $allPermissions = Cache::remember('permissions_grouped', 3600, function () {
            return Permission::all()
                ->filter(function($permission) {
                    return !str_starts_with($permission->name, 'sidebar.');
                })
                ->groupBy(function ($item) {
                    $parts = explode(' ', $item->name);
                    return $parts[1] ?? 'sistema';
                });
        });

        return view('dashboard.admin.roles.form', compact('allPermissions'));
    }

    /**
     * Crea un nuevo rol en el sistema con sus permisos asignados.
     *
     * FLUJO DE PROTECCIÓN:
     * - Solo Super Admin puede crear roles (checkSuperAdminAccess)
     *
     * FLUJO DE CREACIÓN:
     * 1. Valida los datos del request:
     *    - name: requerido, string, máximo 255 chars, solo letras/espacios/guión/guión bajo, único
     *    - permissions: array opcional donde cada elemento debe existir en la tabla permissions
     * 2. Limpia el nombre con strip_tags y trim para prevenir XSS
     * 3. Crea el rol en la tabla 'roles'
     * 4. Filtra los permisos para incluir SOLO los del sistema (sin prefijo 'sidebar.')
     * 5. Sincroniza los permisos del sistema con el rol creado
     * 6. Registra la auditoría (log y AuditTrait)
     * 7. Invalida la caché de permisos agrupados
     *
     * SEPARACIÓN DE PERMISOS:
     * Los permisos del sidebar que pudieran enviarse en el formulario son
     * descartados intencionalmente. Los permisos del sidebar del nuevo rol
     * se gestionarán posteriormente desde el SidebarPermissionController.
     *
     * @param \Illuminate\Http\Request $request Request con 'name' y opcionalmente 'permissions'
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito
     */
    public function store(Request $request)
    {
        $this->checkSuperAdminAccess();

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZáéíóúñÑ\s\-_]+$/',
                'unique:roles,name'
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $name = strip_tags(trim($request->name));

        $role = Role::create(['name' => $name]);

        // Filtrar SOLO permisos del sistema (no sidebar)
        $filteredPermissions = array_filter($request->permissions ?? [], function($p) {
            return !str_starts_with($p, 'sidebar.');
        });

        if (!empty($filteredPermissions)) {
            $role->syncPermissions($filteredPermissions);
        }

        $user = Auth::user();
        $userEmail = $user ? $user->email : 'Sistema';

        Log::info('Rol creado', [
            'role' => $role->name,
            'permissions' => $filteredPermissions,
            'created_by' => $userEmail,
            'ip' => request()->ip()
        ]);

        $this->logCreated($role, Auth::user()->full_name . ' creó el rol \'' . $role->name . '\'');

        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Muestra el formulario de edición de un rol existente.
     *
     * Flujo de datos:
     * 1. Verifica acceso de Super Admin
     * 2. Carga los permisos actuales del rol (eager loading)
     * 3. Consulta todos los permisos del sistema agrupados (con caché de 1 hora)
     * 4. Retorna el formulario con el rol y los permisos disponibles
     *
     * IMPORTANTE: Este formulario solo muestra permisos del sistema.
     * Los permisos del sidebar del rol se gestionan desde el SidebarPermissionController.
     * La vista marca con checked los permisos que el rol ya tiene asignados.
     *
     * @param \Spatie\Permission\Models\Role $role El rol a editar (resuelto por route model binding)
     * @return \Illuminate\View\View Vista del formulario de edición
     */
    public function edit(Role $role)
    {
        $this->checkSuperAdminAccess();

        $role->load('permissions');

        $allPermissions = Cache::remember('permissions_grouped', 3600, function () {
            return Permission::all()
                ->filter(function($permission) {
                    return !str_starts_with($permission->name, 'sidebar.');
                })
                ->groupBy(function ($item) {
                    $parts = explode(' ', $item->name);
                    return $parts[1] ?? 'sistema';
                });
        });

        return view('dashboard.admin.roles.form', compact('role', 'allPermissions'));
    }

    /**
     * Actualiza un rol existente con sus permisos del sistema.
     *
     * ESTE ES EL MÉTODO MÁS COMPLEJO DEL CONTROLADOR.
     * Gestiona la actualización preservando los permisos del sidebar.
     *
     * FLUJO DE PROTECCIÓN DEL SUPER ADMIN:
     * - Solo Super Admin puede ejecutar este método (checkSuperAdminAccess)
     * - Si el rol a editar es "Super Admin", se redirige con error
     * - Los permisos del sistema del Super Admin NO se pueden editar desde aquí
     * - Solo el Super Admin puede modificar sus propios permisos del sidebar
     *   (a través del SidebarPermissionController)
     *
     * FLUJO DE ACTUALIZACIÓN:
     * 1. Verifica acceso de Super Admin
     * 2. Verifica que el rol NO sea "Super Admin" (protección)
     * 3. Valida nombre único (excepto el actual) y permisos
     * 4. Carga los permisos actuales del rol
     * 5. SEPARA los permisos del sidebar (existentes, que comienzan con 'sidebar.')
     * 6. SEPARA los permisos del sistema del request (nuevos, sin prefijo 'sidebar.')
     * 7. COMBINA: permisos del sistema (nuevos) + permisos del sidebar (existentes)
     * 8. Elimina duplicados con array_unique
     * 9. Actualiza el nombre del rol
     * 10. Sincroniza TODOS los permisos (sistema + sidebar)
     * 11. Registra auditoría y limpia caché
     *
     * SEPARACIÓN Y FUSIÓN DE PERMISOS:
     * - Los permisos del sidebar se PRESERVAN intactos (no se modifican)
     * - Solo se actualizan los permisos del sistema
     * - Se combinan ambos conjuntos antes de sincronizar
     *
     * INVALIDACIÓN DE CACHÉ:
     * 1. Limpia 'permissions_grouped' (cache de permisos agrupados)
     * 2. Limpia 'roles_with_permissions' (cache de roles con permisos)
     * 3. Limpia la caché interna de Spatie PermissionRegistrar
     * 4. Limpia las cachés individuales del usuario autenticado
     * 5. Recarga los permisos del usuario vía PermissionService
     *
     * @param \Illuminate\Http\Request $request Request con 'name' y 'permissions'
     * @param \Spatie\Permission\Models\Role $role El rol a actualizar (resuelto por route model binding)
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function update(Request $request, Role $role)
    {
       $this->checkSuperAdminAccess();

       // NO se pueden editar los permisos del sistema del Super Admin
       if ($role->name === 'Super Admin') {
           return redirect()->route('super-admin.roles.index')
               ->with('error', 'Los permisos del sistema del Super Admin no se pueden editar desde aquí. Solo los permisos del sidebar.');
       }

    $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-ZáéíóúñÑ\s\-_]+$/',
            Rule::unique('roles')->ignore($role->id)
        ],
        'permissions' => 'array',
        'permissions.*' => 'exists:permissions,name'
    ]);

    $name = strip_tags(trim($request->name));
    $oldName = $role->name;

    // ==========================================
    // SEPARACIÓN Y FUSIÓN DE PERMISOS
    // ==========================================
    // Este es el núcleo de la lógica de este método.
    // Se separan los permisos en dos categorías para preservar
    // los permisos del sidebar mientras se actualizan los del sistema.

    // Obtener permisos ACTUALES del rol desde la base de datos
    $role->load('permissions');
    $currentPermissions = $role->permissions->pluck('name')->toArray();

    // Extraer SOLO los permisos del sidebar (existentes, sin modificar)
    // Estos permisos NO se tocarán, se mantienen tal como están
    $sidebarPermissions = array_values(array_filter($currentPermissions, function($p) {
        return str_starts_with($p, 'sidebar.');
    }));

    // Extraer SOLO los permisos del sistema del request (nuevos)
    // Se filtran los que NO comienzan con 'sidebar.'
    $systemPermissionsFromRequest = array_values(array_filter($request->permissions ?? [], function($p) {
        return !str_starts_with($p, 'sidebar.');
    }));

    // COMBINAR: Permisos del sistema (nuevos del formulario) +
    //           Permisos del sidebar (existentes, preservados)
    // Resultado: el rol actualiza sus permisos del sistema pero mantiene
    // intactos sus permisos del sidebar
    $allPermissions = array_merge(
        $systemPermissionsFromRequest,
        $sidebarPermissions  //  MANTENER los permisos del sidebar
    );

    // Eliminar duplicados por si acaso
    $allPermissions = array_unique($allPermissions);

    // Guardar el estado anterior para auditoría
    $oldValues = $role->toArray();
    // Actualizar el nombre del rol
    $role->update(['name' => $name]);

    // Sincronizar TODOS los permisos (sistema + sidebar) en la base de datos
    $role->syncPermissions($allPermissions);

    // ==========================================
    // REGISTRAR AUDITORÍA
    // ==========================================
    // Se registra la operación en dos sistemas:
    // 1. AuditTrait: Historial de cambios para auditoría interna
    // 2. Log de Laravel: Registro operacional con detalles completos
    $this->logUpdated($role, $oldValues, $role->toArray(), Auth::user()->full_name . ' actualizó el rol \'' . $role->name . '\'');

    $user = Auth::user();
    $userEmail = $user ? $user->email : 'Sistema';

    Log::info('Rol actualizado', [
        'old_name' => $oldName,
        'new_name' => $name,
        'system_permissions' => $systemPermissionsFromRequest,
        'sidebar_permissions' => $sidebarPermissions,
        'total_permissions' => $allPermissions,
        'updated_by' => $userEmail,
        'ip' => request()->ip()
    ]);

    // ==========================================
    // INVALIDACIÓN DE CACHÉ
    // ==========================================
    // Se invalidan todas las capas de caché para reflejar los cambios:

    // Caché de Laravel: permisos agrupados por categoría
    Cache::forget('permissions_grouped');
    // Caché de Laravel: roles con sus permisos
    Cache::forget('roles_with_permissions');
    // Caché interna de Spatie Permission (en memoria del proceso)
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Recargar permisos del usuario autenticado para reflejar los cambios
    // en la sesión actual del administrador
    if (Auth::check()) {
        $user = Auth::user();
        // Limpiar caché individual del usuario
        Cache::forget('user_permissions_' . $user->id);

        // Recargar permisos desde la base de datos y actualizar caché
        $permissionService = new PermissionService($user);
        $permissionService->clearCache();
    }

    return redirect()->route('super-admin.roles.index')
        ->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Elimina un rol del sistema.
     *
     * PROTECCIONES:
     * - Solo Super Admin puede ejecutar este método
     * - No se puede eliminar el rol "Super Admin" (protección absoluta)
     * - No se puede eliminar un rol que tiene usuarios asignados
     *
     * Flujo de eliminación:
     * 1. Verifica acceso de Super Admin
     * 2. Verifica que el rol NO sea "Super Admin"
     * 3. Verifica que el rol NO tenga usuarios asignados
     * 4. Registra la eliminación en el log de Laravel (nivel warning)
     * 5. Registra la auditoría en el AuditTrait
     * 6. Elimina el rol de la base de datos (cascade de permisos)
     * 7. Invalida la caché de permisos agrupados
     * 8. Redirige con mensaje de éxito
     *
     * NOTA: Al eliminar un rol, Spatie Permission maneja automáticamente
     * la eliminación de los registros en la tabla pivot role_has_permissions.
     *
     * @param \Spatie\Permission\Models\Role $role El rol a eliminar (resuelto por route model binding)
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function destroy(Role $role)
    {
        $this->checkSuperAdminAccess();

        if ($role->name === 'Super Admin') {
            return redirect()->route('super-admin.roles.index')
                ->with('error', 'No puedes eliminar el rol Super Admin.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('super-admin.roles.index')
                ->with('error', 'No puedes eliminar un rol que tiene usuarios asignados.');
        }

        $roleName = $role->name;

        $user = Auth::user();
        $userEmail = $user ? $user->email : 'Sistema';

        Log::warning('Rol eliminado', [
            'role' => $roleName,
            'deleted_by' => $userEmail,
            'ip' => request()->ip()
        ]);

        $this->logDeleted($role, Auth::user()->full_name . ' eliminó el rol \'' . $role->name . '\'');

        $role->delete();

        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * Limpia las cachés de permisos de Laravel.
     *
     * Método utilitario que invalida las dos cachés principales
     * relacionadas con permisos:
     *
     * - 'permissions_grouped': Colección de permisos agrupados por categoría
     *   (utilizada en formularios de creación/edición de roles)
     * - 'roles_with_permissions': Colección de roles con sus permisos cargados
     *   (utilizada en listados y consultas frecuentes)
     *
     * Este método se utiliza después de cualquier operación que modifique
     * permisos o roles para asegurar que las vistas muestren datos actualizados.
     *
     * NOTA: Este método NO limpia la caché de Spatie PermissionRegistrar
     * ni las cachés individuales de usuarios. Para invalidaciones completas,
     * se recomienda usar el patrón aplicado en el método update().
     *
     * @return void
     */
    private function clearPermissionCache()
    {
        Cache::forget('permissions_grouped');
        Cache::forget('roles_with_permissions');
    }
}
