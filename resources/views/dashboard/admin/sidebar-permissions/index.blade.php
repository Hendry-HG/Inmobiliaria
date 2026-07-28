{{-- Listado de permisos de visibilidad del sidebar --}}
@extends('layouts.dashboard')

@section('title', 'Permisos del Sidebar')
@section('header', 'Gestión de Visibilidad del Sidebar')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Visibilidad del Sidebar</h2>
            <p class="text-sm text-gray-500">Gestiona qué secciones del sidebar pueden ver los usuarios según su rol</p>
        </div>
    </div>



    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permisos del Sidebar</th>
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
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @php
                                $sidebarPerms = $role->permissions->filter(function($p) {
                                    return str_starts_with($p->name, 'sidebar.');
                                });
                            @endphp
                            @forelse($sidebarPerms as $perm)
                                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded">
                                    {{ str_replace('sidebar.', '', $perm->name) }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400">Sin permisos de sidebar</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($role->name === 'Super Admin')
                            <a href="{{ route('super-admin.sidebar-permissions.edit', $role) }}" class="text-mso-blue hover:text-mso-gold">
                                <i class="ph ph-pencil"></i> Editar
                            </a>
                        @else
                            <span class="text-gray-400 text-xs italic">
                                <i class="ph ph-lock-simple"></i> Solo Super Admin
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