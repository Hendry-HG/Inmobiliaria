@if(!isset($embedded))
    @extends('layouts.dashboard')

    @section('title', 'Gestión de Usuarios')
    @section('header', 'Usuarios')
    @section('content')
@endif

<div class="space-y-6">
    <!-- Botones de acción y filtros -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <a href="{{ route('admin.users.create') }}"
               class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg hover:bg-mso-blue hover:text-white transition-colors flex items-center gap-2 shadow-sm">
                <i class="ph ph-user-plus"></i> Nuevo Usuario
            </a>

            <form method="GET" action="{{ request()->is('admin/dashboard') ? route('admin.dashboard') : route('admin.users.index') }}"
                  class="flex flex-wrap gap-2">

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar usuario..."
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none w-full sm:w-auto">

                <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold outline-none">
                    <option value="">Todos los roles</option>
                    @foreach($roles ?? [] as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="bg-mso-blue text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                    <i class="ph ph-magnifying-glass"></i> Filtrar
                </button>

                @if(request()->anyFilled(['search', 'role']))
                    <a href="{{ request()->is('admin/dashboard') ? route('admin.dashboard') : route('admin.users.index') }}"
                       class="text-gray-500 hover:text-red-500 underline text-sm">
                        Limpiar
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">País</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users ?? [] as $userItem)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ $userItem->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                             alt="{{ $userItem->full_name }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $userItem->full_name }}
                                        </div>
                                        @if($userItem->last_name)
                                            <div class="text-xs text-gray-400">
                                                {{ $userItem->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $userItem->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $userItem->phone ?? '-' }}
                            </td>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $userItem->country ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="toggleUserStatus({{ $userItem->id }}, {{ $userItem->is_active ? 'false' : 'true' }})"
                                        class="text-sm px-3 py-1 rounded-full border transition-colors
                                        {{ $userItem->is_active ? 'border-green-200 text-green-700 bg-green-50' : 'border-red-200 text-red-700 bg-red-50' }}">
                                    {{ $userItem->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openModal('{{ route('admin.users.modal-show', $userItem) }}')"
                                            class="text-gray-400 hover:text-mso-blue transition-colors p-1" title="Ver detalle">
                                        <i class="ph ph-eye text-lg"></i>
                                    </button>

                                    <button onclick="openModal('{{ route('admin.users.modal-edit', $userItem) }}')"
                                            class="text-gray-400 hover:text-yellow-500 transition-colors p-1" title="Editar">
                                        <i class="ph ph-pencil text-lg"></i>
                                    </button>

                                    @if(auth()->id() != $userItem->id)
                                        <button onclick="openModal('{{ route('admin.users.modal-delete', $userItem) }}')"
                                                class="text-gray-400 hover:text-red-600 transition-colors p-1" title="Eliminar">
                                            <i class="ph ph-trash text-lg"></i>
                                        </button>
                                    @endif
                                </div>
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

        <!-- Paginación -->
        @if(isset($users) && $users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Contenedor Universal del Modal -->
<div id="modal-container" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity duration-300">
    <div id="modal-content-wrapper" class="transform transition-all scale-95 opacity-0 duration-300"></div>
</div>

@push('js')
<script>
// Función para abrir modal cargando contenido
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
        .then(html => {
            wrapper.innerHTML = html;
        })
        .catch(error => {
            console.error('Error cargando modal:', error);
            closeModal();
            alert('Error al cargar la vista.');
        });
}

// Función para cerrar modal
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

// Cerrar al hacer clic fuera
document.getElementById('modal-container').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Toggle Status
function toggleUserStatus(userId, newStatus) {
    if (confirm(`¿Estás seguro de cambiar el estado de este usuario?`)) {
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
                window.location.reload();
            } else {
                alert(data.error || 'Error al cambiar el estado');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    }
}
</script>
@endpush

@if(!isset($embedded))
    @endsection
@endif
