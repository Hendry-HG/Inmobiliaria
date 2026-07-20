@extends('layouts.dashboard')

@section('title', isset($role) ? 'Editar Rol' : 'Crear Rol')
@section('header', isset($role) ? 'Editar Rol' : 'Crear Rol')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">

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

        <form action="{{ isset($role) ? route('super-admin.roles.update', $role) : route('super-admin.roles.store') }}" method="POST">
            @csrf
            @if(isset($role))
                @method('PUT')
            @endif

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Rol *</label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $role->name ?? '') }}"
                       pattern="^[a-zA-ZáéíóúñÑ\s\-_]+$"
                       title="Solo letras, espacios, guiones y guiones bajos"
                       class="w-full border rounded p-2 {{ $errors->has('name') ? 'border-red-500' : '' }}"
                       required>
                <p class="text-xs text-gray-400 mt-1">Solo letras, espacios, guiones y guiones bajos</p>
                @error('name')
                    <p class="text-red-500 text-xs mt-1"><i class="ph ph-warning-circle mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Permisos del Sistema</h3>
            <p class="text-sm text-gray-500 mb-4">Los permisos del sidebar se gestionan en la sección <strong>Sidebar Permissions</strong>.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $perms = isset($allPermissions) ? $allPermissions : (isset($permissions) ? $permissions : []);
                @endphp

                @foreach($perms as $group => $permissionsList)
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="font-bold text-gray-700 mb-3 capitalize text-sm uppercase tracking-wide">{{ $group }}</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto custom-scroll">
                            @foreach($permissionsList as $permission)
                                <label class="flex items-start space-x-2 cursor-pointer hover:text-mso-blue">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $permission->name }}"
                                           @if(isset($role) && $role->hasPermissionTo($permission->name)) checked @endif
                                           class="mt-1 rounded text-mso-gold focus:ring-mso-gold h-4 w-4">
                                    <span class="text-sm text-gray-600 leading-tight">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- NOTA SOBRE PERMISOS DEL SIDEBAR --}}
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <i class="ph ph-info text-amber-600 text-xl mt-0.5"></i>
                    <div>
                        <p class="text-sm text-amber-700">
                            Los permisos de visibilidad del sidebar se gestionan en
                            <strong>Sidebar Permissions</strong>.
                        </p>
                        <a href="{{ route('super-admin.sidebar-permissions.index') }}"
                           class="inline-flex items-center gap-1 text-sm text-mso-blue hover:underline font-medium mt-1">
                            <i class="ph ph-arrow-right"></i> Ir a Sidebar Permissions
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('super-admin.roles.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition-colors">
                    <i class="ph ph-x mr-1"></i> Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-mso-blue text-white rounded hover:bg-slate-800 font-bold shadow-sm transition-colors">
                    <i class="ph ph-check mr-1"></i>
                    {{ isset($role) ? 'Actualizar Rol' : 'Crear Rol' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    // VALIDACIÓN DE NOMBRE DE ROL EN TIEMPO REAL
    document.querySelector('input[name="name"]')?.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúñÑ\s\-_]/g, '');
    });

    // CONTADOR DE PERMISOS SELECCIONADOS
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('input[name="permissions[]"]:checked');
        document.getElementById('selectedCount').textContent = checkboxes.length;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
        updateSelectedCount();
    });
</script>

<style>
    .custom-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush
