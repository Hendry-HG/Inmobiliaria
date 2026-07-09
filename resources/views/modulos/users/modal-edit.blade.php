<div class="bg-white rounded-lg shadow-xl overflow-hidden max-w-2xl w-full mx-4">
    <div class="bg-mso-blue px-6 py-4 flex justify-between items-center">
        <h3 class="text-lg font-bold text-white">Editar Usuario</h3>
        <button onclick="closeModal()" class="text-gray-300 hover:text-white">
            <i class="ph ph-x text-xl"></i>
        </button>
    </div>

    <!-- enctype="multipart/form-data" es necesario si quieres subir foto -->
    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto custom-scroll">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none" required>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none" required>
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            <!-- Rol -->
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

            <!-- Tipo de Identificación -->
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

            <!-- Número de Identificación -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cédula</label>
                <input type="text" name="id_number" value="{{ old('id_number', $user->id_number) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            <!-- País -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                <input type="text" name="country" value="{{ old('country', $user->country) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            <!-- Ciudad -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                <input type="text" name="city" value="{{ old('city', $user->city) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
            </div>

            <!-- Dirección (Ocupa todo el ancho) -->
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <textarea name="address" rows="2"
                          class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">{{ old('address', $user->address) }}</textarea>
            </div>

            <!-- Contraseña (Ocupa todo el ancho) -->
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                <input type="password" name="password" placeholder="Dejar vacío para mantener la actual"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-mso-gold outline-none">
                <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres.</p>
            </div>

            <!-- Foto de Perfil -->
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto de Perfil</label>
                <input type="file" name="profile_photo" accept="image/*"
                       class="w-full border border-gray-300 rounded p-2 text-sm focus:ring-2 focus:ring-mso-gold outline-none">
                <p class="text-xs text-gray-500 mt-1">Dejar vacío para mantener la actual.</p>
            </div>

            <!-- Estado Activo -->
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
