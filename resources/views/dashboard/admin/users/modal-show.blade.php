{{-- Modal con los detalles de un usuario --}}
<div class="bg-white rounded-lg shadow-xl overflow-hidden max-w-2xl w-full mx-4">
    <div class="bg-mso-blue px-6 py-4 flex justify-between items-center">
        <h3 class="text-lg font-bold text-white">Detalle de Usuario</h3>
        <button onclick="closeModal()" class="text-gray-300 hover:text-white">
            <i class="ph ph-x text-xl"></i>
        </button>
    </div>

    <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto custom-scroll">
        {{-- Header con Foto y Nombre --}}
        <div class="flex items-center gap-4 mb-6">
            <img src="{{ $user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                 class="w-20 h-20 rounded-full object-cover border-2 border-mso-gold"
                 alt="{{ $user->full_name }}">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $user->full_name }}</h2>
                <span class="text-sm text-gray-500">{{ $user->email }}</span>
                @if($user->last_name)
                    <p class="text-xs text-gray-400 mt-1">
                        <span class="font-medium">Nombre:</span> {{ $user->name }}
                        <span class="ml-2 font-medium">Apellido:</span> {{ $user->last_name }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Grid de Información --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Teléfono --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Teléfono</p>
                <p class="font-medium">{{ $user->phone ?? '-' }}</p>
            </div>

            {{-- Cédula --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Cédula</p>
                <p class="font-medium">{{ $user->full_id ?? '-' }}</p>
            </div>

            {{-- País --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">País</p>
                <p class="font-medium">{{ $user->country_name ?? $user->country ?? '-' }}</p>
            </div>

            {{-- Ciudad --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Ciudad</p>
                <p class="font-medium">{{ $user->city_name ?? $user->city ?? '-' }}</p>
            </div>

            {{-- Estado (ubicación) --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Estado (Ubicación)</p>
                <p class="font-medium">{{ $user->state_name ?? $user->state ?? '-' }}</p>
            </div>

            {{-- Rol Principal --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Rol Principal</p>
                <p class="font-medium">{{ $user->main_role ?? '-' }}</p>
            </div>

            {{-- Estado de Cuenta --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Estado de Cuenta</p>
                <p class="font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                </p>
            </div>

            {{-- Fecha de Registro --}}
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Miembro desde</p>
                <p class="font-medium">{{ $user->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Dirección --}}
        <div class="bg-gray-50 p-3 rounded">
            <p class="text-xs text-gray-500 uppercase">Dirección</p>
            <p class="font-medium text-sm break-words">{{ $user->address ?? 'No registrada' }}</p>
        </div>

        {{-- Ubicación completa (si tienes relaciones) --}}
        @if($user->full_location)
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-xs text-gray-500 uppercase">Ubicación Completa</p>
                <p class="font-medium text-sm break-words">{{ $user->full_location }}</p>
            </div>
        @endif
    </div>
</div>
