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

class SidebarPermissionController extends Controller
{
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

    public function index()
    {
        $this->checkSuperAdminAccess();

        $sidebarPermissions = Permission::where('name', 'LIKE', 'sidebar.%')->get();
        $roles = Role::with('permissions')->get();

        return view('dashboard.admin.sidebar-permissions.index', compact('sidebarPermissions', 'roles'));
    }

    public function edit(Role $role)
    {
        $this->checkSuperAdminAccess();

        //  PERMITIR EDITAR EL ROL SUPER ADMIN EN SIDEBAR PERMISSIONS
        // No bloqueamos la edición del Super Admin aquí
        $role->load('permissions');

        $sidebarPermissions = Permission::where('name', 'LIKE', 'sidebar.%')->get();

        return view('dashboard.admin.sidebar-permissions.edit', compact('role', 'sidebarPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->checkSuperAdminAccess();

        try {
            // ==========================================
            //  OBTENER PERMISOS DEL FORMULARIO
            // ==========================================
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
            //  OBTENER PERMISOS ACTUALES DEL ROL
            // ==========================================
            $role->load('permissions');
            $currentPermissions = $role->permissions->pluck('name')->toArray();

            // ==========================================
            //  SEPARAR PERMISOS DEL SISTEMA
            // ==========================================
            $systemPermissions = array_values(array_filter($currentPermissions, function($p) {
                return !str_starts_with($p, 'sidebar.');
            }));

            // ==========================================
            // 4COMBINAR PERMISOS DEL SISTEMA + SIDEBAR
            // ==========================================
            $allPermissions = array_merge($systemPermissions, $sidebarPermissions);
            // ==========================================
            //  GUARDAR PERMISOS
            // ==========================================
            $role->syncPermissions($allPermissions);
            // Limpiar caché de Spatie
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Limpiar caché de Laravel
            Cache::forget('permissions_grouped');
            Cache::forget('roles_with_permissions');

            //  INVALIDAR CACHÉ DE TODOS LOS USUARIOS
            $globalVersion = Cache::get('permissions_global_version', 0) + 1;
            Cache::put('permissions_global_version', $globalVersion, 86400);

            //  RECARGAR PERMISOS DEL USUARIO AUTENTICADO
            if (Auth::check()) {
                $user = Auth::user();

                Cache::forget('user_permissions_' . $user->id);
                Cache::forget('user_permissions_version_' . $user->id);

                $permissionService = new PermissionService($user);
                $permissionService->refresh();

                session(['permissions_version' => $globalVersion]);
            }

            // ==========================================
            //  REGISTRAR CAMBIOS
            // ==========================================
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
