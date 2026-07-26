<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar los permisos del sidebar (menú lateral).
 *
 * Este controlador gestiona exclusivamente los permisos visuales del sidebar
 * (permisos cuyo nombre comienza con "sidebar."). Estos permisos determinan
 * qué elementos del menú lateral puede ver cada rol.
 *
 * IMPORTANTE: Este controlador solo permite modificar los permisos del sidebar
 * del rol "Super Admin". Los permisos del sidebar de otros roles se gestionan
 * a través del RoleController.
 *
 * Flujo de datos:
 * - Los permisos del sidebar se almacenan en la tabla 'permissions' con prefijo 'sidebar.'
 * - Se separan de los permisos del sistema (que no tienen ese prefijo)
 * - Al guardar, se preservan los permisos del sistema y solo se actualizan los del sidebar
 * - La caché se invalida en múltiples niveles para asegurar consistencia
 */
class SidebarPermissionController extends Controller
{
    /**
     * Verifica que el usuario autenticado tenga el rol de Super Admin.
     *
     * Este método actúa como puerta de acceso (gate) para todos los endpoints
     * del controlador. Si el usuario no está autenticado o no tiene el rol
     * "Super Admin", se aborta con código 403.
     *
     * Flujo de protección:
     * 1. Verifica autenticación del usuario (Auth::check)
     * 2. Crea una instancia de PermissionService con el usuario actual
     * 3. Utiliza PermissionService::hasRole() para verificar el rol 'Super Admin'
     * 4. Si no tiene el rol, aborta con mensaje descriptivo
     *
     * @return bool Retorna true si el usuario tiene acceso (Super Admin)
     * @throws \Symfony\Component\HttpKernel\Exception\HttpHttpException 403 si no tiene acceso
     */
    private function checkSuperAdminAccess()
    {
        if (!Auth::check()) {
            abort(403, 'No autenticado.');
        }

        $permission = new PermissionService(Auth::user());

        if (!$permission->hasRole('Super Admin')) {
            abort(403, 'No tienes permiso para acceder a esta página. Solo Super Admin.');
        }

        return true;
    }

    /**
     * Muestra la lista de permisos del sidebar y los roles.
     *
     * Flujo de datos:
     * 1. Verifica acceso de Super Admin mediante checkSuperAdminAccess()
     * 2. Consulta todos los permisos cuyo nombre comienza con 'sidebar.' de la tabla permissions
     * 3. Consulta todos los roles con sus permisos relacionados (eager loading)
     * 4. Retorna la vista con ambas colecciones para mostrar el estado actual
     *
     * La vista muestra qué permisos del sidebar tiene asignado cada rol,
     * permitiendo al Super Admin visualizar el estado general de permisos del menú.
     *
     * @return \Illuminate\View\View Vista con los permisos del sidebar y roles
     */
    public function index()
    {
        $this->checkSuperAdminAccess();

        $sidebarPermissions = Permission::where('name', 'LIKE', 'sidebar.%')->get();
        $roles = Role::with('permissions')->get();

        return view('dashboard.admin.sidebar-permissions.index', compact('sidebarPermissions', 'roles'));
    }

    /**
     * Muestra el formulario de edición de permisos del sidebar para el rol Super Admin.
     *
     * FLUJO DE PROTECCIÓN DEL SUPER ADMIN:
     * Este método implementa una doble capa de protección:
     * 1. Primero verifica que el usuario autenticado sea Super Admin (checkSuperAdminAccess)
     * 2. Luego verifica que el rol a editar sea exactamente "Super Admin"
     *
     * Esto garantiza que solo el Super Admin puede modificar sus propios permisos
     * del sidebar. No es posible modificar los permisos del sidebar de otros roles
     * desde este controlador; eso se gestiona a través del RoleController.
     *
     * Flujo de datos:
     * 1. Verifica acceso de Super Admin
     * 2. Valida que el rol recibido sea "Super Admin" (si no, aborta 403)
     * 3. Carga los permisos actuales del rol (eager loading)
     * 4. Consulta todos los permisos disponibles del sidebar (prefijo 'sidebar.')
     * 5. Retorna el formulario de edición con el rol y los permisos del sidebar
     *
     * @param \Spatie\Permission\Models\Role $role El rol a editar (resuelto por route model binding)
     * @return \Illuminate\View\View Vista del formulario de edición
     * @throws \Symfony\Component\HttpKernel\Exception\HttpHttpException 403 si el rol no es Super Admin
     */
    public function edit(Role $role)
    {
        $this->checkSuperAdminAccess();

        // SOLO el Super Admin puede editar sus propios permisos del sidebar
        if ($role->name !== 'Super Admin') {
            abort(403, 'Solo puedes editar los permisos del sidebar del rol Super Admin.');
        }

        $role->load('permissions');

        $sidebarPermissions = Permission::where('name', 'LIKE', 'sidebar.%')->get();

        return view('dashboard.admin.sidebar-permissions.edit', compact('role', 'sidebarPermissions'));
    }

    /**
     * Actualiza los permisos del sidebar del rol Super Admin.
     *
     * ESTE ES EL MÉTODO MÁS CRÍTICO DEL CONTROLADOR.
     * Gestiona la separación y fusión de permisos del sistema y del sidebar,
     * además de la invalidación completa de caché.
     *
     * FLUJO DE PROTECCIÓN DEL SUPER ADMIN:
     * - Verifica que el usuario autenticado sea Super Admin
     * - Verifica que el rol a modificar sea exactamente "Super Admin"
     * - Si el rol no es Super Admin, redirige con mensaje de error
     *
     * SEPARACIÓN DE PERMISOS:
     * - Los permisos del sidebar comienzan con "sidebar." (ej: "sidebar.propiedades")
     * - Los permisos del sistema NO comienzan con "sidebar." (ej: "create Propiedades")
     * - Ambos tipos coexisten en la misma tabla 'permissions'
     *
     * FLUJO DE ACTUALIZACIÓN:
     * 1. Se obtienen los permisos del sidebar desde el formulario
     *    (acepta JSON en 'permissions_json' o array en 'permissions')
     * 2. Se cargan los permisos actuales del rol
     * 3. Se separan los permisos del sistema (que NO comienzan con 'sidebar.')
     * 4. Se combinan: permisos del sistema (existentes) + permisos del sidebar (nuevos)
     * 5. Se sincronizan todos los permisos con syncPermissions()
     *
     * INVALIDACIÓN DE CACHÉ (estrategia en capas):
     * 1. Se limpia la caché interna de Spatie PermissionRegistrar
     * 2. Se limpia la caché de Laravel 'permissions_grouped'
     * 3. Se limpia la caché de Laravel 'roles_with_permissions'
     * 4. Se incrementa la versión global de permisos ('permissions_global_version')
     *    para que todos los usuarios detecten el cambio en su próxima petición
     * 5. Se limpian las cachés individuales del usuario autenticado
     * 6. Se recargan los permisos del usuario vía PermissionService::refresh()
     * 7. Se actualiza la versión de permisos en la sesión
     *
     * @param \Illuminate\Http\Request $request Request HTTP con los permisos del sidebar
     * @param \Spatie\Permission\Models\Role $role El rol a actualizar (debe ser Super Admin)
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function update(Request $request, Role $role)
    {
        $this->checkSuperAdminAccess();

        // SOLO el Super Admin puede actualizar sus propios permisos del sidebar
        if ($role->name !== 'Super Admin') {
            return redirect()->route('super-admin.sidebar-permissions.index')
                ->with('error', 'Solo puedes editar los permisos del sidebar del rol Super Admin.');
        }

        try {
            // ==========================================
            // PASO 1: OBTENER PERMISOS DEL SIDEBAR DEL FORMULARIO
            // ==========================================
            // El formulario puede enviar los permisos en dos formatos:
            // - 'permissions_json': Cadena JSON con el array de permisos
            // - 'permissions': Array directo de permisos
            // Ambos casos filtran SOLO permisos que comienzan con 'sidebar.'
            $sidebarPermissions = [];

            if ($request->has('permissions_json') && !empty($request->permissions_json)) {
                $decoded = json_decode($request->permissions_json, true);
                if (is_array($decoded)) {
                    $sidebarPermissions = array_values(array_filter($decoded, function($p) {
                        return is_string($p) && str_starts_with($p, 'sidebar.');
                    }));
                }
            }

            if (empty($sidebarPermissions) && $request->has('permissions')) {
                $permissionsInput = $request->input('permissions');

                if (is_array($permissionsInput)) {
                    $sidebarPermissions = array_values(array_filter($permissionsInput, function($p) {
                        return is_string($p) && str_starts_with($p, 'sidebar.');
                    }));
                }
            }

            // ==========================================
            // PASO 2: OBTENER PERMISOS ACTUALES DEL ROL
            // ==========================================
            // Se cargan los permisos actuales del rol desde la base de datos
            // para conocer el estado previo a la actualización
            $role->load('permissions');
            $currentPermissions = $role->permissions->pluck('name')->toArray();

            // ==========================================
            // PASO 3: SEPARAR PERMISOS DEL SISTEMA
            // ==========================================
            // Se filtran los permisos que NO comienzan con 'sidebar.'
            // Estos son los permisos funcionales del sistema (crear, editar, eliminar, etc.)
            // que NO deben ser modificados desde este controlador
            $systemPermissions = array_values(array_filter($currentPermissions, function($p) {
                return !str_starts_with($p, 'sidebar.');
            }));

            // ==========================================
            // PASO 4: COMBINAR PERMISOS DEL SISTEMA + SIDEBAR
            // ==========================================
            // Se fusionan los permisos del sistema (existentes, sin modificar)
            // con los permisos del sidebar (nuevos, proporcionados desde el formulario)
            // Resultado: el rol mantiene sus permisos del sistema y actualiza los del sidebar
            $allPermissions = array_merge($systemPermissions, $sidebarPermissions);
            // ==========================================
            // PASO 5: GUARDAR PERMISOS EN LA BASE DE DATOS
            // ==========================================
            // syncPermissions() reemplaza TODOS los permisos del rol con la nueva lista
            // Esto elimina permisos anteriores y asigna los nuevos
            $role->syncPermissions($allPermissions);
            // ==========================================
            // PASO 6: INVALIDACIÓN DE CACHÉ EN CAPAS
            // ==========================================
            // Se invalida la caché en múltiples niveles para garantizar
            // que todos los usuarios vean los cambios lo antes posible:

            // Nivel 1: Caché interna de Spatie Permission
            // Limpia la caché en memoria del paquete de permisos
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Nivel 2: Caché de Laravel (datos agrupados)
            // Limpia las colecciones cacheadas de permisos y roles
            Cache::forget('permissions_grouped');
            Cache::forget('roles_with_permissions');

            // Nivel 3: Versión global de permisos
            // Se incrementa un contador global para que TODOS los usuarios
            // detecten el cambio. TTL de 24 horas (86400 segundos)
            $globalVersion = Cache::get('permissions_global_version', 0) + 1;
            Cache::put('permissions_global_version', $globalVersion, 86400);

            // Nivel 4: Recarga de permisos del usuario autenticado
            // Se limpian las cachés individuales y se refrescan
            // los permisos del usuario que realizó el cambio
            if (Auth::check()) {
                $user = Auth::user();

                Cache::forget('user_permissions_' . $user->id);
                Cache::forget('user_permissions_version_' . $user->id);

                $permissionService = new PermissionService($user);
                $permissionService->refresh();

                // Se actualiza la versión en sesión para comparación posterior
                session(['permissions_version' => $globalVersion]);
            }

            // ==========================================
            // PASO 7: REGISTRAR CAMBIOS EN LOG
            // ==========================================
            // Se registra la auditoría completa de la operación con:
            // - Rol modificado
            // - Email del usuario que realizó el cambio
            // - Conteo de permisos por categoría
            // - Lista final de permisos asignados
            $role->refresh();
            Log::info('Sidebar permissions updated successfully', [
                'role' => $role->name,
                'role_id' => $role->id,
                'user' => Auth::user()->email,
                'sidebar_permissions_count' => count($sidebarPermissions),
                'system_permissions_count' => count($systemPermissions),
                'total_permissions_count' => count($allPermissions),
                'final_permissions' => $role->permissions->pluck('name')->toArray()
            ]);

            return redirect()->route('super-admin.sidebar-permissions.index')
                ->with('success', ' Permisos del sidebar actualizados correctamente.');

        } catch (\Exception $e) {
            // ==========================================
            // MANEJO DE ERRORES
            // ==========================================
            // Si ocurre cualquier excepción, se registra en el log
            // con traza completa para diagnóstico, y se redirige
            // al usuario con el mensaje de error
            Log::error('Error al actualizar permisos del sidebar', [
                'role' => $role->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', ' Error al actualizar los permisos: ' . $e->getMessage())
                ->withInput();
        }
    }
}
