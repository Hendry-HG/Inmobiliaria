{{-- Modal de edición de usuario --}}
<div class="bg-white rounded-lg shadow-xl overflow-hidden max-w-2xl w-full mx-4">
    <div class="bg-mso-blue px-6 py-4 flex justify-between items-center">
        <h3 class="text-lg font-bold text-white">Editar Usuario</h3>
        <button onclick="closeModal()" class="text-gray-300 hover:text-white">
            <i class="ph ph-x text-xl"></i>
        </button>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto custom-scroll">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- NOMBRE --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none" required>
            </div>

            {{-- APELLIDO --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            {{-- EMAIL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none" required>
            </div>

            {{-- TELÉFONO --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            {{-- ROL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                <select name="role" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role', $user->roles->first()->name ?? '') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- TIPO DE IDENTIFICACIÓN --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cédula</label>
                <select name="id_type" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                    <option value="">Seleccionar</option>
                    <option value="V" {{ old('id_type', $user->id_type) == 'V' ? 'selected' : '' }}>Venezolano (V)</option>
                    <option value="E" {{ old('id_type', $user->id_type) == 'E' ? 'selected' : '' }}>Extranjero (E)</option>
                    <option value="J" {{ old('id_type', $user->id_type) == 'J' ? 'selected' : '' }}>Jurídico (J)</option>
                    <option value="P" {{ old('id_type', $user->id_type) == 'P' ? 'selected' : '' }}>Pasaporte (P)</option>
                </select>
            </div>

            {{-- NÚMERO DE IDENTIFICACIÓN --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cédula</label>
                <input type="text" name="id_number" value="{{ old('id_number', $user->id_number) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            {{-- PAÍS --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                <select name="country_id" id="edit_country_id" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                    <option value="">Seleccionar país</option>
                    @foreach($countries ?? [] as $country)
                        <option value="{{ $country->id }}" {{ old('country_id', $user->country_id) == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ESTADO --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="state_id" id="edit_state_id" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                    <option value="">Seleccionar estado</option>
                    @foreach($states ?? [] as $state)
                        <option value="{{ $state->id }}" {{ old('state_id', $user->state_id) == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- MUNICIPIO --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Municipio</label>
                <select name="municipality_id" id="edit_municipality_id" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                    <option value="">Seleccionar municipio</option>
                    @foreach($municipalities ?? [] as $municipality)
                        <option value="{{ $municipality->id }}" {{ old('municipality_id', $user->municipality_id) == $municipality->id ? 'selected' : '' }}>{{ $municipality->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- PARROQUIA --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Parroquia</label>
                <select name="parish_id" id="edit_parish_id" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                    <option value="">Seleccionar parroquia</option>
                    @foreach($parishes ?? [] as $parish)
                        <option value="{{ $parish->id }}" {{ old('parish_id', $user->parish_id) == $parish->id ? 'selected' : '' }}>{{ $parish->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- CIUDAD --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                <select name="city_id" id="edit_city_id" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                    <option value="">Seleccionar ciudad</option>
                    @foreach($cities ?? [] as $city)
                        <option value="{{ $city->id }}" {{ old('city_id', $user->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- DIRECCIÓN --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <textarea name="address" rows="3"
                          class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">{{ old('address', $user->address) }}</textarea>
            </div>

            {{-- CONTRASEÑA --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                <input type="password" name="password" placeholder="Dejar vacío para mantener la actual"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                <p class="text-xs text-gray-500 mt-1">Mínimo 10 caracteres.</p>
            </div>

            {{-- CONFIRMAR CONTRASEÑA --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" placeholder="Repite la contraseña"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            {{-- FOTO DE PERFIL --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto de Perfil</label>
                <input type="file" name="profile_photo" accept="image/*"
                       class="w-full border border-gray-300 rounded p-2 text-sm focus:ring-2 focus:ring-mso-gold outline-none">
                @if($user->profile_photo)
                    <p class="text-xs text-gray-500 mt-1">Foto actual: <a href="{{ $user->profile_photo_url }}" target="_blank" class="text-mso-gold">Ver foto</a></p>
                @endif
            </div>

            {{-- ESTADO ACTIVO --}}
            <div class="col-span-1 md:col-span-2 flex items-center mt-2 p-3 bg-gray-50 rounded">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-mso-gold border-gray-300 rounded focus:ring-mso-gold">
                <label for="edit_is_active" class="ml-2 text-sm font-medium text-gray-700">Usuario Activo</label>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
            <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition-colors">Cancelar</button>
            <button type="submit" class="px-4 py-2 bg-mso-gold text-mso-blue rounded font-bold hover:bg-mso-blue hover:text-white transition-colors shadow-sm">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('edit_country_id');
    const stateSelect = document.getElementById('edit_state_id');
    const municipalitySelect = document.getElementById('edit_municipality_id');
    const parishSelect = document.getElementById('edit_parish_id');
    const citySelect = document.getElementById('edit_city_id');

    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/locations/states/${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        stateSelect.innerHTML = '<option value="">Seleccione un estado</option>';
                        states.forEach(state => {
                            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                        municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
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
                        municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                        municipalities.forEach(municipality => {
                            municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
                        });
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
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
                        parishSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                        parishes.forEach(parish => {
                            parishSelect.innerHTML += `<option value="${parish.id}">${parish.name}</option>`;
                        });
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
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
                        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                        cities.forEach(city => {
                            citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                    });
            }
        });
    }
});
</script>
@endpush
