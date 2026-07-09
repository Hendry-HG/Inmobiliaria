<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Super Admin']);
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('dashboard.admin.roles.index', compact('roles'));
    }

    public function create()
    {
        // Cambiar $permissions a $allPermissions
        $allPermissions = Permission::all()->groupBy(function ($item) {
            return explode(' ', $item->name)[1] ?? 'system';
        });

        return view('dashboard.admin.roles.form', compact('allPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('super-admin.roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('super-admin.roles.index')->with('error', 'No puedes editar el rol Super Admin.');
        }

        $role->load('permissions');
        // Cambiar $allPermissions (consistente con create)
        $allPermissions = Permission::all()->groupBy(function ($item) {
            return explode(' ', $item->name)[1] ?? 'system';
        });

        return view('dashboard.admin.roles.form', compact('role', 'allPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('super-admin.roles.index')->with('error', 'No puedes editar el rol Super Admin.');
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('super-admin.roles.index')->with('success', 'Rol actualizado.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('super-admin.roles.index')->with('error', 'No puedes eliminar el rol Super Admin.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('super-admin.roles.index')->with('error', 'No puedes eliminar un rol que tiene usuarios asignados.');
        }

        $role->delete();
        return redirect()->route('super-admin.roles.index')->with('success', 'Rol eliminado.');
    }
}
