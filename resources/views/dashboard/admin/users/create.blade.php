@extends('layouts.dashboard')

@section('title', 'Crear Usuario')
@section('header', 'Crear Nuevo Usuario')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate>
        @csrf

        <!-- Campos falsos para engañar al autocompletado -->
        <div style="position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; opacity: 0;">
            <input type="text" name="fake_username" autocomplete="username" tabindex="-1">
            <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1">
            <input type="email" name="fake_email" autocomplete="email" tabindex="-1">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Columna izquierda -->
            <div class="space-y-4">
                {{-- NOMBRE --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- APELLIDO --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text"
                           name="last_name"
                           value="{{ old('last_name') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('last_name') border-red-500 @enderror">
                    @error('last_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- EMAIL --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('email') border-red-500 @enderror"
                           required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CONTRASEÑA --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
                    <input type="password"
                           name="password"
                           autocomplete="new-password"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('password') border-red-500 @enderror"
                           required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CONFIRMAR CONTRASEÑA --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña *</label>
                    <input type="password"
                           name="password_confirmation"
                           autocomplete="new-password"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold"
                           required>
                </div>

                {{-- TELÉFONO --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="space-y-4">
                {{-- TIPO DE IDENTIFICACIÓN --}}
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

                {{-- NÚMERO DE IDENTIFICACIÓN --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Identificación</label>
                    <input type="text"
                           name="id_number"
                           value="{{ old('id_number') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('id_number') border-red-500 @enderror">
                    @error('id_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ROL --}}
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

                {{-- PAÍS --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                    <select name="country_id" id="country_id" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                        <option value="">Seleccionar país</option>
                        @foreach($countries ?? [] as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ESTADO --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="state_id" id="state_id" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione un país</option>
                    </select>
                </div>

                {{-- MUNICIPIO --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municipio</label>
                    <select name="municipality_id" id="municipality_id" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione un estado</option>
                    </select>
                </div>

                {{-- PARROQUIA --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parroquia</label>
                    <select name="parish_id" id="parish_id" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione un municipio</option>
                    </select>
                </div>

                {{-- CIUDAD --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <select name="city_id" id="city_id" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione una parroquia</option>
                    </select>
                </div>

                {{-- DIRECCIÓN --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <textarea name="address"
                              rows="2"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">{{ old('address') }}</textarea>
                </div>

                {{-- FOTO DE PERFIL --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto de Perfil</label>
                    <input type="file"
                           name="profile_photo"
                           accept="image/*"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                    <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG. Máximo 2MB</p>
                </div>

                {{-- ESTADO ACTIVO --}}
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

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const municipalitySelect = document.getElementById('municipality_id');
    const parishSelect = document.getElementById('parish_id');
    const citySelect = document.getElementById('city_id');

    // Cargar estados al seleccionar país
    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/locations/states/${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        stateSelect.disabled = false;
                        stateSelect.innerHTML = '<option value="">Seleccione un estado</option>';
                        states.forEach(state => {
                            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                        municipalitySelect.disabled = true;
                        municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                stateSelect.disabled = true;
                stateSelect.innerHTML = '<option value="">Primero seleccione un país</option>';
            }
        });
    }

    if (stateSelect) {
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            if (stateId) {
                fetch(`/api/locations/municipalities/${stateId}`)
                    .then(response => response.json())
                    .then(municipalities => {
                        municipalitySelect.disabled = false;
                        municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                        municipalities.forEach(municipality => {
                            municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
                        });
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                municipalitySelect.disabled = true;
                municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
            }
        });
    }

    if (municipalitySelect) {
        municipalitySelect.addEventListener('change', function() {
            const municipalityId = this.value;
            if (municipalityId) {
                fetch(`/api/locations/parishes/${municipalityId}`)
                    .then(response => response.json())
                    .then(parishes => {
                        parishSelect.disabled = false;
                        parishSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                        parishes.forEach(parish => {
                            parishSelect.innerHTML += `<option value="${parish.id}">${parish.name}</option>`;
                        });
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                parishSelect.disabled = true;
                parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
            }
        });
    }

    if (parishSelect) {
        parishSelect.addEventListener('change', function() {
            const parishId = this.value;
            if (parishId) {
                fetch(`/api/locations/cities/${parishId}`)
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.disabled = false;
                        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                        cities.forEach(city => {
                            citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                    });
            } else {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
            }
        });
    }

    // Limpiar campos al cargar la página
    setTimeout(function() {
        document.querySelector('input[name="name"]').value = '';
        document.querySelector('input[name="last_name"]').value = '';
        document.querySelector('input[name="email"]').value = '';
        document.querySelector('input[name="password"]').value = '';
        document.querySelector('input[name="password_confirmation"]').value = '';
        document.querySelector('input[name="phone"]').value = '';
        document.querySelector('input[name="id_number"]').value = '';
    }, 100);
});
</script>
@endpush
@endsection
