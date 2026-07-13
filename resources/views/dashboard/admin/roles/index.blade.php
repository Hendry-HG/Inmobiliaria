@extends('layouts.dashboard')

@section('title', 'Gestión de Roles')
@section('header', 'Roles y Permisos')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Roles del Sistema</h2>
        <a href="{{ route('super-admin.roles.create') }}" class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg hover:bg-mso-blue hover:text-white transition-colors flex items-center gap-2">
            <i class="ph ph-plus"></i> Crear Rol
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
        <span><i class="ph ph-check-circle mr-2"></i>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
        <span><i class="ph ph-warning-circle mr-2"></i>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuarios Asignados</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permisos</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($roles as $role)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-bold text-gray-900">{{ $role->name }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                        {{ $role->users()->count() }}
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm max-w-xs truncate">
                        {{ $role->permissions->pluck('name')->implode(', ') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($role->name !== 'Super Admin')
                            <a href="{{ route('super-admin.roles.edit', $role) }}" class="text-mso-blue hover:text-mso-gold mr-3">
                                <i class="ph ph-pencil"></i> Editar
                            </a>
                            <form action="{{ route('super-admin.roles.destroy', $role) }}" method="POST" class="inline" 
                                  onsubmit="return confirm('¿Estás seguro de eliminar el rol "{{ addslashes($role->name) }}"?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-500 hover:text-red-700">
                                    <i class="ph ph-trash"></i> Eliminar
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 italic text-xs">
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