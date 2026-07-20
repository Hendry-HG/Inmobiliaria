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
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    private function getPermissionService()
    {
        return new PermissionService(Auth::user());
    }

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

    public function index()
    {
        $this->checkSuperAdminAccess();
        $roles = Role::with('permissions', 'users')->get();
        return view('dashboard.admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->checkSuperAdminAccess();

        //  OBTENER SOLO PERMISOS DEL SISTEMA
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

        //  FILTRAR PERMISOS DEL SIDEBAR
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

        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        $this->checkSuperAdminAccess();

        $role->load('permissions');

        //  OBTENER SOLO PERMISOS DEL SISTEMA
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

    public function update(Request $request, Role $role)
    {
        $this->checkSuperAdminAccess();

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

        //  OBTENER PERMISOS ACTUALES DEL ROL
        $currentPermissions = $role->permissions->pluck('name')->toArray();


        $systemPermissions = array_filter($currentPermissions, function($p) {
            return !str_starts_with($p, 'sidebar.');
        });

        $sidebarPermissionsFromRequest = array_filter($request->permissions ?? [], function($p) {
            return str_starts_with($p, 'sidebar.');
        });

        //  FILTRAR PERMISOS DEL SISTEMA DEL REQUEST
        $systemPermissionsFromRequest = array_filter($request->permissions ?? [], function($p) {
            return !str_starts_with($p, 'sidebar.');
        });


        $allPermissions = array_merge(
            array_values($systemPermissions),
            array_values($systemPermissionsFromRequest),
            array_values($sidebarPermissionsFromRequest)
        );

        $role->update(['name' => $name]);
        $role->syncPermissions(array_unique($allPermissions));

        $user = Auth::user();
        $userEmail = $user ? $user->email : 'Sistema';

        Log::info('Rol actualizado', [
            'old_name' => $oldName,
            'new_name' => $name,
            'permissions' => $allPermissions,
            'updated_by' => $userEmail,
            'ip' => request()->ip()
        ]);

        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

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

        $role->delete();

        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    private function clearPermissionCache()
    {
        Cache::forget('permissions_grouped');
        Cache::forget('roles_with_permissions');
    }
}
