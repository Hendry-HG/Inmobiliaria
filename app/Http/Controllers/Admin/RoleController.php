<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Super Admin']);
        
        //  PERMISOS ESPECÍFICOS
        $this->middleware(['permission:ver roles'])->only(['index']);
        $this->middleware(['permission:crear rol'])->only(['create', 'store']);
        $this->middleware(['permission:editar rol'])->only(['edit', 'update']);
        $this->middleware(['permission:eliminar rol'])->only(['destroy']);
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('dashboard.admin.roles.index', compact('roles'));
    }

    public function create()
    {
        //  OPTIMIZADO CON CACHÉ
        $allPermissions = Cache::remember('permissions_grouped', 3600, function () {
            return Permission::all()->groupBy(function ($item) {
                $parts = explode(' ', $item->name);
                return $parts[1] ?? 'sistema';
            });
        });

        return view('dashboard.admin.roles.form', compact('allPermissions'));
    }

    public function store(Request $request)
    {
        //  VALIDACIÓN MEJORADA
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

        //  SANITIZAR NOMBRE
        $name = strip_tags(trim($request->name));

        $role = Role::create(['name' => $name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        //  LOG DE AUDITORÍA
        Log::info('Rol creado', [
            'role' => $role->name,
            'permissions' => $request->permissions ?? [],
            'created_by' => auth()->user()->email,
            'ip' => request()->ip()
        ]);

        //  LIMPIAR CACHÉ DE PERMISOS
        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('super-admin.roles.index')
                ->with('error', 'No puedes editar el rol Super Admin.');
        }

        $role->load('permissions');

        //  OPTIMIZADO CON CACHÉ
        $allPermissions = Cache::remember('permissions_grouped', 3600, function () {
            return Permission::all()->groupBy(function ($item) {
                $parts = explode(' ', $item->name);
                return $parts[1] ?? 'sistema';
            });
        });

        return view('dashboard.admin.roles.form', compact('role', 'allPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('super-admin.roles.index')
                ->with('error', 'No puedes editar el rol Super Admin.');
        }

        //  VALIDACIÓN MEJORADA
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

        //  SANITIZAR NOMBRE
        $name = strip_tags(trim($request->name));
        $oldName = $role->name;

        $role->update(['name' => $name]);
        $role->syncPermissions($request->permissions ?? []);

        //  LOG DE AUDITORÍA
        Log::info('Rol actualizado', [
            'old_name' => $oldName,
            'new_name' => $name,
            'permissions' => $request->permissions ?? [],
            'updated_by' => auth()->user()->email,
            'ip' => request()->ip()
        ]);

        //  LIMPIAR CACHÉ DE PERMISOS
        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('super-admin.roles.index')
                ->with('error', 'No puedes eliminar el rol Super Admin.');
        }

        //  CORREGIDO - Usar $role->users en lugar de $role->users()
        if ($role->users()->count() > 0) {
            return redirect()->route('super-admin.roles.index')
                ->with('error', 'No puedes eliminar un rol que tiene usuarios asignados.');
        }

        $roleName = $role->name;

        //  LOG DE AUDITORÍA ANTES DE ELIMINAR
        Log::warning('Rol eliminado', [
            'role' => $roleName,
            'deleted_by' => auth()->user()->email,
            'ip' => request()->ip()
        ]);

        $role->delete();

        //  LIMPIAR CACHÉ DE PERMISOS
        Cache::forget('permissions_grouped');

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    //  MÉTODO AUXILIAR PARA LIMPIAR CACHÉ
    private function clearPermissionCache()
    {
        Cache::forget('permissions_grouped');
        Cache::forget('roles_with_permissions');
    }
}