@if(!isset($embedded))
    @extends('layouts.dashboard')
    @section('title', 'Gestión de Usuarios')
    @section('header', 'Gestión de Usuarios')
    @section('content')
@endif

<div class="space-y-6">
    {{-- ============================================= --}}
    {{-- BARRA SUPERIOR - Solo visible para Super Admin y Administrador --}}
    {{-- ============================================= --}}
    @hasanyrole(['Super Admin', 'Administrador'])
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex flex-wrap justify-between items-center gap-4">
                {{-- Botón Nuevo Usuario - Solo Super Admin y Administrador --}}
                @hasanyrole(['Super Admin', 'Administrador'])
                    <a href="{{ route('admin.users.create') }}"
                       class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg hover:bg-mso-blue hover:text-white transition-colors flex items-center gap-2 shadow-sm">
                        <i class="ph ph-user-plus"></i> Nuevo Usuario
                    </a>
                @endhasanyrole

                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-2" autocomplete="off">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar usuario..."
                           autocomplete="off"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none w-full sm:w-auto">

                    {{-- Filtro por rol - Solo Super Admin y Administrador --}}
                    @hasanyrole(['Super Admin', 'Administrador'])
                        <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold outline-none" autocomplete="off">
                            <option value="">Todos los roles</option>
                            @foreach($roles ?? [] as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    @endhasanyrole

                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold outline-none" autocomplete="off">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>

                    <button type="submit" class="bg-mso-blue text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                        <i class="ph ph-magnifying-glass"></i> Filtrar
                    </button>

                    @if(request()->anyFilled(['search', 'role', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-red-500 underline text-sm">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>
    @else
        {{-- Mensaje de acceso denegado para otros roles --}}
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="ph ph-warning-circle text-red-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium">Acceso Denegado</p>
                    <p class="text-sm text-red-600">No tienes permisos para gestionar usuarios.</p>
                </div>
            </div>
        </div>
    @endhasanyrole

    {{-- ============================================= --}}
    {{-- TABLA DE USUARIOS - Solo visible para Super Admin y Administrador --}}
    {{-- ============================================= --}}
    @hasanyrole(['Super Admin', 'Administrador'])
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users ?? [] as $userItem)
                            <tr class="hover:bg-gray-50 transition-colors {{ $userItem->is_active ? '' : 'bg-gray-50 opacity-75' }}">
                                {{-- # --}}
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>

                                {{-- Usuario con foto --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover"
                                                 src="{{ $userItem->profile_photo_url }}"
                                                 alt="{{ $userItem->full_name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium {{ $userItem->is_active ? 'text-gray-900' : 'text-gray-500' }}">
                                                {{ $userItem->full_name }}
                                            </div>
                                            <div class="text-xs text-gray-400">ID: {{ $userItem->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $userItem->is_active ? 'text-gray-500' : 'text-gray-400' }}">
                                    {{ $userItem->email }}
                                </td>

                                {{-- Teléfono --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $userItem->is_active ? 'text-gray-500' : 'text-gray-400' }}">
                                    {{ $userItem->phone ?? '-' }}
                                </td>

                                {{-- Rol con colores --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($userItem->main_role == 'Super Admin') bg-red-100 text-red-800
                                        @elseif($userItem->main_role == 'Administrador') bg-purple-100 text-purple-800
                                        @elseif($userItem->main_role == 'Asesor Inmobiliario') bg-blue-100 text-blue-800
                                        @elseif($userItem->main_role == 'Auditor') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ $userItem->main_role ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- Ubicación --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $userItem->is_active ? 'text-gray-500' : 'text-gray-400' }}">
                                    {{ $userItem->country_name }}{{ $userItem->state_name ? ', ' . $userItem->state_name : '' }}
                                </td>

                                {{-- Estado - Botón toggle de estado (Solo Super Admin y Administrador) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @hasanyrole(['Super Admin', 'Administrador'])
                                        <button onclick="toggleUserStatus({{ $userItem->id }}, this)"
                                                class="text-sm px-3 py-1 rounded-full border transition-colors
                                                {{ $userItem->is_active ? 'border-green-200 text-green-700 bg-green-50 hover:bg-red-50 hover:border-red-200 hover:text-red-700' : 'border-red-200 text-red-700 bg-red-50 hover:bg-green-50 hover:border-green-200 hover:text-green-700' }}">
                                            {{ $userItem->is_active ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    @else
                                        {{-- Si no tiene permisos, solo mostrar el estado sin botón --}}
                                        <span class="text-sm px-3 py-1 rounded-full border
                                            {{ $userItem->is_active ? 'border-green-200 text-green-700 bg-green-50' : 'border-red-200 text-red-700 bg-red-50' }}">
                                            {{ $userItem->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @endhasanyrole
                                </td>

                                {{-- Acciones - Solo Super Admin y Administrador --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @hasanyrole(['Super Admin', 'Administrador'])
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Ver --}}
                                            <button onclick="openModal('{{ route('admin.users.modal-show', $userItem) }}')"
                                                    class="text-gray-400 hover:text-mso-blue transition-colors p-1"
                                                    title="Ver detalle">
                                                <i class="ph ph-eye text-lg"></i>
                                            </button>

                                            {{-- Editar - No mostrar para clientes --}}
                                            @if(!$userItem->hasRole('Cliente'))
                                                <button onclick="openModal('{{ route('admin.users.modal-edit', $userItem) }}')"
                                                        class="text-gray-400 hover:text-yellow-500 transition-colors p-1"
                                                        title="Editar">
                                                    <i class="ph ph-pencil text-lg"></i>
                                                </button>
                                            @else
                                                {{-- Mostrar deshabilitado si es Cliente --}}
                                                <span class="text-gray-300 cursor-not-allowed p-1" title="No se puede editar un cliente">
                                                    <i class="ph ph-pencil text-lg"></i>
                                                </span>
                                            @endif

                                            {{-- Eliminar - No mostrar si es el mismo usuario --}}
                                            @if(auth()->id() != $userItem->id)
                                                <button onclick="openModal('{{ route('admin.users.modal-delete', $userItem) }}')"
                                                        class="text-gray-400 hover:text-red-600 transition-colors p-1"
                                                        title="Eliminar">
                                                    <i class="ph ph-trash text-lg"></i>
                                                </button>
                                            @else
                                                {{-- Mostrar deshabilitado si es el mismo usuario --}}
                                                <span class="text-gray-300 cursor-not-allowed p-1" title="No puedes eliminarte a ti mismo">
                                                    <i class="ph ph-trash text-lg"></i>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        {{-- Si no tiene permisos, mostrar iconos deshabilitados --}}
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-gray-300 cursor-not-allowed p-1" title="No tienes permisos">
                                                <i class="ph ph-eye text-lg"></i>
                                            </span>
                                            <span class="text-gray-300 cursor-not-allowed p-1" title="No tienes permisos">
                                                <i class="ph ph-pencil text-lg"></i>
                                            </span>
                                            <span class="text-gray-300 cursor-not-allowed p-1" title="No tienes permisos">
                                                <i class="ph ph-trash text-lg"></i>
                                            </span>
                                        </div>
                                    @endhasanyrole
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="ph ph-users text-4xl mb-2 text-gray-300"></i>
                                        <p>No se encontraron usuarios.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if(isset($users) && $users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    @else
        {{-- Mensaje cuando no tiene permisos para ver la tabla --}}
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="flex flex-col items-center">
                <i class="ph ph-lock-simple text-5xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-600">Acceso Restringido</h3>
                <p class="text-sm text-gray-400 mt-1">No tienes permisos para ver la lista de usuarios.</p>
                <p class="text-xs text-gray-400 mt-2">Contacta al administrador del sistema.</p>
            </div>
        </div>
    @endhasanyrole
</div>

{{-- ============================================= --}}
{{-- MODAL CONTAINER --}}
{{-- ============================================= --}}
<div id="modal-container" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity duration-300">
    <div id="modal-content-wrapper" class="transform transition-all scale-95 opacity-0 duration-300"></div>
</div>

{{-- ============================================= --}}
{{-- SCRIPTS --}}
{{-- ============================================= --}}
@push('js')
<script>
function openModal(url) {
    const container = document.getElementById('modal-container');
    const wrapper = document.getElementById('modal-content-wrapper');
    container.classList.remove('hidden');
    setTimeout(() => {
        wrapper.classList.remove('scale-95', 'opacity-0');
        wrapper.classList.add('scale-100', 'opacity-100');
    }, 10);
    fetch(url)
        .then(response => response.text())
        .then(html => { wrapper.innerHTML = html; })
        .catch(error => { console.error('Error:', error); closeModal(); alert('Error al cargar la vista.'); });
}

function closeModal() {
    const container = document.getElementById('modal-container');
    const wrapper = document.getElementById('modal-content-wrapper');
    wrapper.classList.remove('scale-100', 'opacity-100');
    wrapper.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        container.classList.add('hidden');
        wrapper.innerHTML = '';
    }, 300);
}

document.getElementById('modal-container')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

function toggleUserStatus(userId, buttonElement) {
    if (!confirm('¿Estás seguro de cambiar el estado de este usuario?')) return;

    fetch(`/admin/users/${userId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ _method: 'POST' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = buttonElement.closest('tr');
            const isActive = data.is_active;

            // Actualizar fila
            if (isActive) {
                row.classList.remove('bg-gray-50', 'opacity-75');
            } else {
                row.classList.add('bg-gray-50', 'opacity-75');
            }

            // Actualizar botón
            buttonElement.textContent = isActive ? 'Activo' : 'Inactivo';
            buttonElement.className = `text-sm px-3 py-1 rounded-full border transition-colors ${
                isActive
                    ? 'border-green-200 text-green-700 bg-green-50 hover:bg-red-50 hover:border-red-200 hover:text-red-700'
                    : 'border-red-200 text-red-700 bg-red-50 hover:bg-green-50 hover:border-green-200 hover:text-green-700'
            }`;

            // Actualizar textos de la fila
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (index > 0 && index < 6) {
                    const textElement = cell.querySelector('.text-sm, .text-gray-500, .text-gray-400');
                    if (textElement) {
                        if (isActive) {
                            textElement.classList.remove('text-gray-400');
                            textElement.classList.add('text-gray-500');
                        } else {
                            textElement.classList.remove('text-gray-500');
                            textElement.classList.add('text-gray-400');
                        }
                    }
                }
            });

            // Actualizar nombre
            const nameElement = row.querySelector('.text-sm.font-medium');
            if (nameElement) {
                if (isActive) {
                    nameElement.classList.remove('text-gray-500');
                    nameElement.classList.add('text-gray-900');
                } else {
                    nameElement.classList.remove('text-gray-900');
                    nameElement.classList.add('text-gray-500');
                }
            }

            // Mostrar mensaje
            const msg = document.createElement('div');
            msg.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ${isActive ? 'bg-green-600' : 'bg-red-600'} transition-all duration-500`;
            msg.textContent = `Usuario ${isActive ? 'activado' : 'desactivado'} exitosamente.`;
            document.body.appendChild(msg);
            setTimeout(() => { msg.remove(); }, 3000);
        } else {
            alert(data.error || 'Error al cambiar el estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}
</script>
@endpush

@if(!isset($embedded))
    @endsection
@endif
