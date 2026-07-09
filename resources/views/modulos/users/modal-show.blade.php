<div class="bg-white rounded-lg shadow-xl overflow-hidden max-w-2xl w-full mx-4">
    <div class="bg-mso-blue px-6 py-4 flex justify-between items-center">
        <h3 class="text-lg font-bold text-white">Detalle de Usuario</h3>
        <button onclick="closeModal()" class="text-gray-300 hover:text-white">
            <i class="ph ph-x text-xl"></i>
        </button>
    </div>

    <div class="p-6 space-y-4">
        <!-- Header con Foto y Nombre -->
        <div class="flex items-center gap-4 mb-6">
            <img src="{{ $user->profile_photo_url ?? asset('images/default-avatar.png') }}" class="w-20 h-20 rounded-full object-cover border-2 border-mso-gold">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
                <span class="text-sm text-gray-500">{{ $user->email }}</span>
            </div>
        </div>

        <!-- Grid de Información -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Teléfono -->
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Teléfono</p>
                <p class="font-medium">{{ $user->phone ?? '-' }}</p>
            </div>

            <!-- Cédula (Usando el accessor full_id del modelo) -->
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Cédula</p>
                <p class="font-medium">{{ $user->full_id ?? '-' }}</p>
            </div>

            <!-- País -->
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">País</p>
                <p class="font-medium">{{ $user->country ?? '-' }}</p>
            </div>

            <!-- Ciudad (AGREGADO) -->
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Ciudad</p>
                <p class="font-medium">{{ $user->city ?? '-' }}</p>
            </div>

            <!-- Rol Principal -->
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Rol Principal</p>
                <p class="font-medium">{{ $user->main_role ?? '-' }}</p>
            </div>

            <!-- Estado -->
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Estado</p>
                <p class="font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                </p>
            </div>
        </div>

        <!-- Dirección (Ocupa todo el ancho para que no se corte el texto) -->
        <div class="bg-gray-50 p-3 rounded">
            <p class="text-xs text-gray-500 uppercase">Dirección</p>
            <p class="font-medium text-sm break-words">{{ $user->address ?? 'No registrada' }}</p>
        </div>

        <!-- Fecha de Registro -->
        <div class="bg-gray-50 p-3 rounded">
            <p class="text-xs text-gray-500 uppercase">Miembro desde</p>
            <p class="font-medium">{{ $user->created_at->format('d/m/Y') }}</p>
        </div>
    </div>
</div>
