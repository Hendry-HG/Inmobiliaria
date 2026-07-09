@extends('layouts.dashboard')

@section('title', 'Servicios Inmobiliarios')
@section('header', 'Gestión de Servicios')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header y Botón Crear --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Listado de Servicios</h2>
                <p class="text-sm text-slate-500 mt-1">Gestiona los servicios que ofrece tu inmobiliaria</p>
            </div>
            <a href="{{ route('admin.servicios.create') }}"
               class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors shadow-sm flex items-center gap-2 whitespace-nowrap">
                <i class="ph ph-plus-circle text-lg"></i> Nuevo Servicio
            </a>
        </div>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r shadow-sm">
            <div class="flex items-center">
                <i class="ph ph-check-circle text-xl mr-2"></i>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Tabla de Servicios --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4">Servicio</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-center">Imágenes</th>
                        <th class="p-4 text-center">Orden</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm" id="services-list">
                    @forelse($services as $service)
                        <tr class="hover:bg-slate-50 transition-colors group" data-id="{{ $service->id }}">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm flex-shrink-0"
                                         style="background-color: {{ $service->color ?? '#f3f4f6' }}; color: {{ $service->color ? '#ffffff' : '#6b7280' }}">
                                        @if($service->icon)
                                            <i class="{{ $service->icon }}"></i>
                                        @else
                                            <i class="ph ph-house"></i>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-slate-800">{{ $service->title }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-slate-500 max-w-[200px] truncate">
                                {{ $service->description ?? 'Sin descripción' }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1">
                                    @if($service->badge)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-mso-gold/20 text-mso-gold whitespace-nowrap">
                                            {{ $service->badge }}
                                        </span>
                                    @endif
                                    @if($service->is_featured)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-600 whitespace-nowrap">
                                            <i class="ph ph-star"></i>
                                        </span>
                                    @endif
                                    @if(!$service->is_active)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/20 text-red-600 whitespace-nowrap">
                                            Inactivo
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-center text-slate-500">
                                <i class="ph ph-image"></i> {{ $service->gallery->count() }}
                            </td>
                            <td class="p-4 text-center text-slate-500">
                                {{ $service->order }}
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.servicios.edit', $service->id) }}"
                                       class="p-1.5 text-slate-400 hover:text-mso-gold hover:bg-mso-gold/10 rounded transition-colors">
                                        <i class="ph ph-pencil text-sm"></i>
                                    </a>
                                    <button onclick="deleteService({{ $service->id }})"
                                            class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors">
                                        <i class="ph ph-trash text-sm"></i>
                                    </button>
                                    <span class="drag-handle p-1.5 text-slate-300 hover:text-slate-500 cursor-grab transition-colors">
                                        <i class="ph ph-dots-six text-sm"></i>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                <i class="ph ph-buildings text-4xl text-slate-300 mb-3 block"></i>
                                <p>No hay servicios creados aún.</p>
                                <a href="{{ route('admin.servicios.create') }}" class="text-mso-gold hover:underline font-semibold">
                                    Crear el primer servicio
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- SECCIÓN: ASESORES (igual que en Propiedades) --}}
    {{-- ============================================ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-users text-mso-gold"></i>
                    Asesores Disponibles
                    <span class="text-sm font-normal text-slate-400 ml-2">({{ $asesores->count() }})</span>
                </h3>
                <p class="text-sm text-slate-500 mt-1">Asesores registrados en la plataforma</p>
            </div>
            <a href="{{ route('admin.users.index') }}?role=Asesor Inmobiliario"
               class="text-sm text-mso-gold hover:underline font-semibold whitespace-nowrap">
                Gestionar Asesores
            </a>
        </div>

        <div class="p-4 sm:p-6">
            @if($asesores->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <i class="ph ph-user-plus text-4xl block mb-2"></i>
                    <p>No hay asesores registrados.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                    @foreach($asesores as $asesor)
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 hover:shadow-md transition-shadow text-center">
                            <img src="{{ $asesor->profile_photo_url }}"
                                 class="w-12 h-12 rounded-full object-cover border-2 border-mso-gold mx-auto"
                                 alt="{{ $asesor->full_name }}">
                            <p class="font-semibold text-slate-800 text-xs truncate mt-1">{{ $asesor->full_name }}</p>
                            @if($asesor->specialization)
                                <span class="text-[8px] font-medium px-1.5 py-0.5 rounded-full bg-mso-gold/20 text-mso-gold truncate block max-w-full">
                                    {{ $asesor->specialization }}
                                </span>
                            @endif
                            <div class="mt-1 pt-1 border-t border-slate-200 flex justify-center gap-2 text-[9px] text-slate-400">
                                <span><i class="ph ph-buildings text-[9px]"></i> {{ $asesor->properties->where('status', 'publicada')->count() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Formulario para eliminar --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function deleteService(id) {
        if (!confirm('¿Estás seguro de eliminar este servicio?')) return;
        const form = document.getElementById('delete-form');
        form.action = `{{ route('admin.servicios.destroy', ['id' => '__ID__']) }}`.replace('__ID__', id);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const list = document.getElementById('services-list');
        if (!list) return;

        new Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                const items = list.querySelectorAll('tr[data-id]');
                const order = [];
                items.forEach(item => {
                    order.push(parseInt(item.dataset.id));
                });

                fetch('{{ route("admin.servicios.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Orden actualizado correctamente', 'success');
                    }
                })
                .catch(() => showToast('Error al actualizar el orden', 'error'));
            }
        });
    });

    function showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' :
                        type === 'error' ? 'bg-red-500' :
                        'bg-mso-blue';

        toast.className = `${bgColor} text-white px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transform transition-all duration-300 translate-x-full text-sm`;
        toast.innerHTML = `
            <i class="ph ${type === 'success' ? 'ph-check-circle' : type === 'error' ? 'ph-warning-circle' : 'ph-info'}"></i>
            <span>${message}</span>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 10);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 2500);
    }
</script>
@endpush
@endsection
