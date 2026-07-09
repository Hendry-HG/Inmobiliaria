{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Crear Usuario')
@section('header', 'Crear Nuevo Usuario')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Columna izquierda -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo *</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('email') border-red-500 @enderror"
                           required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
                    <input type="password"
                           name="password"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('password') border-red-500 @enderror"
                           required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña *</label>
                    <input type="password"
                           name="password_confirmation"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Identificación</label>
                    <select name="id_type" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                        <option value="">Seleccionar</option>
                        <option value="V" {{ old('id_type') == 'V' ? 'selected' : '' }}>Venezolano (V)</option>
                        <option value="E" {{ old('id_type') == 'E' ? 'selected' : '' }}>Extranjero (E)</option>
                        <option value="J" {{ old('id_type') == 'J' ? 'selected' : '' }}>Jurídico (J)</option>
                        <option value="P" {{ old('id_type') == 'P' ? 'selected' : '' }}>Pasaporte (P)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Identificación</label>
                    <input type="text"
                           name="id_number"
                           value="{{ old('id_number') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('id_number') border-red-500 @enderror">
                    @error('id_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                    <select name="role" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" required>
                        <option value="">Seleccionar rol</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                    <input type="text"
                           name="country"
                           value="{{ old('country', 'Venezuela') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text"
                           name="city"
                           value="{{ old('city') }}"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <textarea name="address"
                              rows="2"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto de Perfil</label>
                    <input type="file"
                           name="profile_photo"
                           accept="image/*"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                    <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG. Máximo 2MB</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-mso-gold focus:ring-mso-gold">
                        <span class="ml-2 text-sm text-gray-700">Usuario activo</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-mso-gold text-mso-blue rounded-lg hover:bg-mso-blue hover:text-white transition-colors">
                Crear Usuario
            </button>
        </div>
    </form>
</div>
@endsection
