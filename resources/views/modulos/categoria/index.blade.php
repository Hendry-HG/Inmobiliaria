@extends('layouts.dashboard')

@section('title', 'Gestión de Categorías')
@section('header', 'Categorías de Propiedades')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-xl font-bold text-slate-800">Listado de Categorías</h2>
            <a href="{{ route('admin.categories.create') }}"
               class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors shadow-sm flex items-center gap-2">
                <i class="ph ph-plus-circle text-lg"></i> Nueva Categoría
            </a>
        </div>
    </div>

    <!-- Tabla de Categorías -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4">ID</th>
                        <th class="p-4">Nombre</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4">Icono</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Propiedades</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($categories as $category)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-slate-500">{{ $category->id }}</td>
                        <td class="p-4 font-medium text-slate-800">{{ $category->name }}</td>
                        <td class="p-4 text-slate-500">{{ $category->slug }}</td>
                        <td class="p-4 text-slate-500 max-w-xs truncate">{{ $category->description ?? 'Sin descripción' }}</td>
                        <td class="p-4">
                            @if($category->icon)
                                <i class="{{ $category->icon }} text-xl text-mso-gold"></i>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-500">{{ $category->properties_count ?? 0 }}</td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="text-slate-400 hover:text-mso-blue transition-colors p-1">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <button onclick="openDeleteModal({{ $category->id }}, '{{ $category->name }}', '{{ route('admin.categories.destroy', $category) }}')"
                                        class="text-slate-400 hover:text-red-500 transition-colors p-1">
                                    <i class="ph ph-trash text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500">
                            <div class="flex flex-col items-center gap-2">
                                <i class="ph ph-folder-open text-4xl text-slate-300"></i>
                                <p>No hay categorías registradas</p>
                                <a href="{{ route('admin.categories.create') }}" class="text-mso-blue hover:underline text-sm">
                                    Crear la primera categoría
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $categories->links() }}
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="p-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                    <i class="ph ph-trash text-3xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 text-center mb-2">¿Eliminar categoría?</h3>
                <p class="text-sm text-slate-500 text-center mb-6">
                    ¿Estás seguro de eliminar la categoría "<span id="categoryName" class="font-semibold text-slate-700"></span>"?
                    @if(isset($category) && $category->properties_count > 0)
                        <br><span class="text-xs text-red-500">Tiene {{ $category->properties_count }} propiedades asociadas.</span>
                    @endif
                    <br><span class="text-xs text-red-500">Esta acción no se puede deshacer.</span>
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="closeDeleteModal()" class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button onclick="confirmDelete()" class="px-6 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="ph ph-trash"></i> Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('js')
<script>
let deleteCategoryId = null;
let deleteRoute = '';

function openDeleteModal(id, name, route) {
    deleteCategoryId = id;
    deleteRoute = route;
    document.getElementById('categoryName').textContent = name;
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
        deleteCategoryId = null;
        deleteRoute = '';
    }, 300);
}

function confirmDelete() {
    if (!deleteCategoryId || !deleteRoute) return;
    const form = document.getElementById('delete-form');
    form.action = deleteRoute;
    form.submit();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endpush
@endsection
