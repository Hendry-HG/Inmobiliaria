@extends('layouts.dashboard')

@php
    $pluralMap = [
        'Estado' => 'Estados',
        'Municipio' => 'Municipios',
        'Parroquia' => 'Parroquias',
        'Ciudad' => 'Ciudades',
        'País' => 'Países'
    ];
    $levelPlural = $pluralMap[$level] ?? $level . 'es';
    $levelLower = strtolower($level);
    $levelPluralLower = strtolower($levelPlural);
@endphp

@section('title', 'Gestión de ' . $levelPlural)
@section('header', $parentName . ': ' . $parent->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
        <span><i class="ph ph-check-circle mr-2"></i>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
        <span><i class="ph ph-warning-circle mr-2"></i>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    <!-- Breadcrumbs y Navegación -->
    <div class="flex items-center text-sm text-slate-500">
        <a href="{{ route('admin.locations.index') }}" class="hover:text-mso-blue">País</a>
        <span class="mx-2">/</span>
        <span class="text-slate-800 font-semibold">{{ $parent->name }}</span>
    </div>

    <!-- Panel Principal -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $levelPlural }}</h2>
                <p class="text-sm text-slate-500">Gestionando {{ $levelPluralLower }} de {{ $parent->name }}</p>
            </div>

            <form action="{{ $createRoute }}" method="POST" class="w-full md:w-auto flex gap-2" autocomplete="off">
                @csrf
                <input type="hidden" name="{{ strtolower($level) == 'estado' ? 'country_id' : (strtolower($level) == 'municipio' ? 'state_id' : (strtolower($level) == 'parroquia' ? 'municipality_id' : 'parish_id')) }}" value="{{ $parentId }}">
                <div class="relative flex-1">
                    <input type="text"
                           name="name"
                           placeholder="Nuevo {{ $levelLower }}..."
                           class="border rounded-lg px-4 py-2 flex-1 w-full focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none"
                           required
                           minlength="2"
                           maxlength="100"
                           pattern="^[a-zA-ZáéíóúñÑ\s\-\.]+$"
                           title="Solo letras, espacios, guiones y puntos">
                    <div class="text-xs text-slate-400 mt-1">Solo letras, espacios, guiones y puntos</div>
                </div>
                <button type="submit" class="bg-mso-gold text-mso-blue px-6 py-2 rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors whitespace-nowrap">
                    Agregar {{ $levelLower }}
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Hijos</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $counter = ($items->currentPage() - 1) * $items->perPage() + 1; @endphp
                    @foreach($items as $item)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4 text-slate-500">{{ $counter++ }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800">{{ $item->name }}</td>
                        <td class="px-6 py-4">
                            @if($level == 'Estado')
                                <a href="{{ route('admin.locations.municipalities.index', $item->id) }}" class="text-mso-blue hover:underline">
                                    Ver Municipios ({{ $item->municipalities()->count() }})
                                </a>
                            @elseif($level == 'Municipio')
                                <a href="{{ route('admin.locations.parishes.index', $item->id) }}" class="text-mso-blue hover:underline">
                                    Ver Parroquias ({{ $item->parishes()->count() }})
                                </a>
                            @elseif($level == 'Parroquia')
                                <a href="{{ route('admin.locations.cities.index', $item->id) }}" class="text-mso-blue hover:underline">
                                    Ver Ciudades ({{ $item->cities()->count() }})
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button data-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    data-type="{{ $level }}"
                                    onclick="openDeleteModal(this.dataset.id, this.dataset.name, this.dataset.type)"
                                    class="text-red-500 hover:text-red-700">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                            <form id="form-delete-{{ $item->id }}" action="{{ route($destroyRoute, $item) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Botón Atrás -->
    <a href="{{ $backRoute }}" class="inline-flex items-center text-slate-500 hover:text-slate-800 font-medium">
        <i class="ph ph-arrow-left mr-2"></i> Volver al nivel anterior
    </a>

</div>

<!-- Modal de Eliminación -->
<div id="modal-delete-wrapper" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 animate-modal-in">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="ph ph-warning text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800" id="modal-title">¿Eliminar?</h3>
            <p class="text-sm text-gray-500 mt-2" id="modal-message">¿Estás seguro de eliminar este elemento? Esta acción no se puede deshacer.</p>
            <div class="flex justify-center gap-3 mt-6">
                <button onclick="confirmDelete()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    Sí, eliminar
                </button>
                <button onclick="closeDeleteModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    let currentModalId = null;
    let currentModalName = '';
    let currentModalType = '';

    function openDeleteModal(id, name, type) {
        currentModalId = id;
        currentModalName = name || 'este elemento';
        currentModalType = type || 'elemento';

        document.getElementById('modal-title').textContent = `¿Eliminar "${currentModalName}"?`;
        document.getElementById('modal-message').innerHTML = `
            ¿Estás seguro de eliminar <strong>"${currentModalName}"</strong>?
            Esta acción no se puede deshacer y podría afectar a elementos relacionados.
        `;

        const modal = document.getElementById('modal-delete-wrapper');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('modal-delete-wrapper');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        currentModalId = null;
        currentModalName = '';
        currentModalType = '';
    }

    function confirmDelete() {
        if (currentModalId) {
            const form = document.getElementById('form-delete-' + currentModalId);
            if (form) {
                form.submit();
            }
        }
        closeDeleteModal();
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });

    document.getElementById('modal-delete-wrapper')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modal-delete-wrapper')) {
            closeDeleteModal();
        }
    });
</script>

<style>
    @keyframes modalIn {
        from {
            transform: scale(0.95) translateY(-20px);
            opacity: 0;
        }
        to {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
    }
    .animate-modal-in {
        animation: modalIn 0.2s ease-out;
    }
</style>
@endpush
@endsection
