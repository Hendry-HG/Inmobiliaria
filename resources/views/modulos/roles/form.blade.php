@extends('layouts.dashboard')

@section('title', isset($role) ? 'Editar Rol' : 'Crear Rol')
@section('header', isset($role) ? 'Editar Rol' : 'Crear Rol')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ isset($role) ? route('super-admin.roles.update', $role) : route('super-admin.roles.store') }}" method="POST">
            @csrf
            @if(isset($role))
                @method('PUT')
            @endif

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Rol</label>
                <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}"
                       class="w-full border rounded p-2 {{ $errors->has('name') ? 'border-red-500' : '' }}" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Asignar Permisos</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Cambiar $allPermissions por $permissions (en create) o $allPermissions (en edit) --}}
                @php
                    $perms = isset($allPermissions) ? $allPermissions : (isset($permissions) ? $permissions : []);
                @endphp

                @foreach($perms as $group => $permissionsList)
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="font-bold text-gray-700 mb-3 capitalize text-sm uppercase tracking-wide">{{ $group }}</h4>
                        <div class="space-y-2">
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

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('super-admin.roles.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-mso-blue text-white rounded hover:bg-slate-800 font-bold shadow-sm">
                    {{ isset($role) ? 'Actualizar Rol' : 'Crear Rol' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
