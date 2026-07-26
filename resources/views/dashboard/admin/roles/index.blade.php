@extends('layouts.dashboard')

@section('title', 'Gestión de Roles')
@section('header', 'Roles y Permisos del Sistema')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Roles del Sistema</h2>
        <a href="{{ route('super-admin.roles.create') }}" class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg hover:bg-mso-blue hover:text-white transition-colors flex items-center gap-2">
            <i class="ph ph-plus"></i> Crear Rol
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Usuarios Asignados</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Permisos</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($roles as $role)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-bold text-gray-900">{{ $role->name }}</span>
                        @if($role->name === 'Super Admin')
                            <span class="ml-2 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Protegido</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 hidden md:table-cell">
                        {{ $role->users()->count() }}
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm hidden lg:table-cell">
                        @php
                            // Contar permisos del sistema
                            $systemCount = $role->permissions->filter(function($p) {
                                return !str_starts_with($p->name, 'sidebar.');
                            })->count();

                            // Contar permisos del sidebar
                            $sidebarCount = $role->permissions->filter(function($p) {
                                return str_starts_with($p->name, 'sidebar.');
                            })->count();

                            $totalCount = $role->permissions->count();
                        @endphp

                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-gray-700">
                                <i class="ph ph-check-circle text-green-500"></i>
                                Sistema: <strong class="text-gray-900">{{ $systemCount }}</strong> permisos
                            </span>
                            @if($sidebarCount > 0)
                                <span class="text-xs font-medium text-gray-700">
                                    <i class="ph ph-layout text-mso-gold"></i>
                                    Sidebar: <strong class="text-gray-900">{{ $sidebarCount }}</strong> permisos
                                </span>
                            @endif
                            @if($totalCount > 0)
                                <span class="text-[10px] text-gray-400">
                                    Total: <strong>{{ $totalCount }}</strong> permisos
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Sin permisos asignados</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($role->name !== 'Super Admin')
                            <a href="{{ route('super-admin.roles.edit', $role) }}" class="text-mso-blue hover:text-mso-gold mr-3 inline-flex items-center gap-1">
                                <i class="ph ph-pencil"></i> Editar
                            </a>
                            <form action="{{ route('super-admin.roles.destroy', $role) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Estás seguro de eliminar el rol {{ addslashes($role->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 inline-flex items-center gap-1">
                                    <i class="ph ph-trash"></i> Eliminar
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 italic text-xs flex items-center justify-end gap-1">
                                <i class="ph ph-shield-check"></i> Protegido
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection