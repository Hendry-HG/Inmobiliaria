@extends('layouts.dashboard')

@section('title', 'Gestión de Servicios')
@section('header', 'Servicios')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-xl font-bold text-slate-800">Listado de Servicios</h2>

            {{-- ============================================= --}}
            {{-- BOTÓN NUEVO SERVICIO - Solo con permiso       --}}
            {{-- ============================================= --}}
            @can('crear servicios')
                <a href="{{ route('admin.servicios.create') }}"
                   class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors shadow-sm flex items-center gap-2">
                    <i class="ph ph-plus-circle text-lg"></i> Nuevo Servicio
                </a>
            @else
                <span class="text-sm text-slate-400 flex items-center gap-2">
                    <i class="ph ph-lock-simple"></i>
                    No tienes permiso para crear servicios
                </span>
            @endcan
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- TABLA - Solo con permiso                      --}}
    {{-- ============================================= --}}
    @canany(['ver servicios', 'gestionar servicios'])
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="p-4">Servicio</th>
                            <th class="p-4">Icono</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4">Destacado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($services as $service)
                            <tr>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        @if($service->image)
                                            <img src="{{ asset('storage/' . $service->image) }}"
                                                 alt="{{ $service->title }}"
                                                 class="w-12 h-12 rounded-lg object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center">
                                                <i class="ph ph-image text-slate-400 text-xl"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-slate-800">{{ $service->title }}</div>
                                            <div class="text-xs text-slate-500">{{ Str::limit($service->description, 60) }}</div>
                                            @if($service->badge)
                                                <span class="text-xs px-2 py-0.5 rounded-full bg-mso-gold/20 text-mso-gold">
                                                    {{ $service->badge }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($service->icon)
                                        <i class="{{ $service->icon }} text-2xl" style="color: {{ $service->color ?? '#000' }}"></i>
                                    @else
                                        <span class="text-slate-400 text-sm">Sin icono</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $service->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($service->is_featured)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                            <i class="ph ph-star"></i> Destacado
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-sm">-</span>
                                    @endif
                                </td>

                                {{-- ============================================= --}}
                                {{-- ACCIONES - Con permisos específicos           --}}
                                {{-- ============================================= --}}
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        {{-- VER - Solo con permiso --}}
                                        @can('ver servicios')
                                            <a href="{{ route('admin.servicios.show', $service) }}"
                                               class="text-slate-400 hover:text-blue-500 transition-colors">
                                                <i class="ph ph-eye text-lg"></i>
                                            </a>
                                        @else
                                            <span class="text-slate-300 cursor-not-allowed" title="No tienes permiso para ver">
                                                <i class="ph ph-eye text-lg"></i>
                                            </span>
                                        @endcan

                                        {{-- EDITAR - Solo con permiso --}}
                                        @can('editar servicios')
                                            <a href="{{ route('admin.servicios.edit', $service) }}"
                                               class="text-slate-400 hover:text-mso-gold transition-colors">
                                                <i class="ph ph-pencil text-lg"></i>
                                            </a>
                                        @else
                                            <span class="text-slate-300 cursor-not-allowed" title="No tienes permiso para editar">
                                                <i class="ph ph-pencil text-lg"></i>
                                            </span>
                                        @endcan

                                        {{-- ELIMINAR - Solo con permiso --}}
                                        @can('eliminar servicios')
                                            <button onclick="deleteService({{ $service->id }}, '{{ $service->title }}')"
                                                    class="text-slate-400 hover:text-red-500 transition-colors">
                                                <i class="ph ph-trash text-lg"></i>
                                            </button>
                                        @else
                                            <span class="text-slate-300 cursor-not-allowed" title="No tienes permiso para eliminar">
                                                <i class="ph ph-trash text-lg"></i>
                                            </span>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    <i class="ph ph-warning text-3xl block mb-2"></i>
                                    No hay servicios registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
            <i class="ph ph-lock-simple text-3xl mb-2 block"></i>
            <p class="font-bold">Acceso Denegado</p>
            <p class="text-sm">No tienes permisos para ver los servicios.</p>
        </div>
    @endcanany
</div>

<!-- Modal de confirmación para eliminar -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="p-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                    <i class="ph ph-trash text-3xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 text-center mb-2">¿Eliminar servicio?</h3>
                <p class="text-sm text-slate-500 text-center mb-6">
                    ¿Estás seguro de eliminar el servicio "<span id="serviceTitle" class="font-semibold text-slate-700"></span>"?
                    <br><span class="text-xs text-red-500">Esta acción no se puede deshacer.</span>
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="closeDeleteModal()"
                            class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button onclick="confirmDelete()"
                            class="px-6 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="ph ph-trash"></i> Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('js')
<script>
let deleteServiceId = null;
let deleteRoute = '';

function deleteService(id, title) {
    deleteServiceId = id;
    deleteRoute = "{{ route('admin.servicios.destroy', ':id') }}".replace(':id', id);
    document.getElementById('serviceTitle').textContent = title;

    const modal = document.getElementById('deleteModal');
    const content = document.getElementById('modalContent');

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const content = document.getElementById('modalContent');

    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        deleteServiceId = null;
        deleteRoute = '';
    }, 300);
}

function confirmDelete() {
    if (!deleteServiceId || !deleteRoute) return;
    const form = document.getElementById('delete-form');
    form.action = deleteRoute;
    form.submit();
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (event.target === modal || event.target === document.querySelector('.fixed.inset-0.bg-slate-900\\/50')) {
            closeDeleteModal();
        }
    }
});
</script>
@endpush
@endsection
