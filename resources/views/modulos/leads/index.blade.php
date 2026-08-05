{{-- Listado de leads inmobiliarios con seguimiento y estados --}}
@extends('layouts.dashboard')

@section('title', 'Gestión de Leads')
@section('header', 'Clientes Potenciales - Leads')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- Panel de Filtros --}}
    <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 md:p-6">
        <form action="{{ route('leads.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">
            <div>
                <label for="lead_search" class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 sm:mb-2">Buscar</label>
                <input type="text" name="search" id="lead_search" value="{{ request('search') }}"
                    placeholder="Nombre, email o teléfono..."
                    class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
            </div>
            <div>
                <label for="lead_status" class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 sm:mb-2">Estado</label>
                <select name="status" id="lead_status" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
                    <option value="">Todos</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="lead_asesor_id" class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 sm:mb-2">Asesor</label>
                <select name="asesor_id" id="lead_asesor_id" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
                    <option value="">Todos</option>
                    @foreach($asesores as $asesor)
                        <option value="{{ $asesor->id }}" {{ request('asesor_id') == $asesor->id ? 'selected' : '' }}>
                            {{ trim($asesor->name . ' ' . ($asesor->last_name ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 text-white bg-mso-blue hover:bg-slate-800 font-medium rounded-lg text-sm px-4 sm:px-5 py-2 sm:py-2.5 transition-colors shadow-lg shadow-blue-900/20 flex items-center justify-center gap-1 whitespace-nowrap">
                    <i class="ph ph-magnifying-glass"></i> Buscar
                </button>
                <a href="{{ route('leads.index') }}" class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center justify-center gap-1 whitespace-nowrap">
                    <i class="ph ph-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla de Leads --}}
    @canany(['ver leads', 'gestionar leads'])
    <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-[10px] sm:text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">ID</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Cliente</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Contacto</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Asesor</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Estado</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Fecha</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50 transition-colors" id="lead-row-{{ $lead->id }}">
                        <td class="px-3 sm:px-6 py-3 sm:py-4 font-mono text-[10px] sm:text-xs text-slate-400">{{ $lead->id }}</td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                            <div class="font-medium text-slate-900 text-sm">{{ $lead->name }}</div>
                            @if($lead->preferences && isset($lead->preferences['property_address']))
                                <div class="text-[10px] sm:text-xs text-slate-400">{{ Str::limit($lead->preferences['property_address'], 40) }}</div>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                            <div class="text-sm">{{ $lead->email }}</div>
                            <div class="text-[10px] sm:text-xs text-slate-400">{{ $lead->phone }}</div>
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                            @if($lead->asesor)
                                <span class="text-sm">{{ trim($lead->asesor->name . ' ' . ($lead->asesor->last_name ?? '')) }}</span>
                            @else
                                <span class="text-[10px] sm:text-xs text-slate-400">Sin asignar</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4">
                            <span id="status-badge-{{ $lead->id }}" class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider
                                @if($lead->status == 'nuevo') bg-blue-50 text-blue-700 border border-blue-200
                                @elseif($lead->status == 'contactado') bg-yellow-50 text-yellow-700 border border-yellow-200
                                @elseif($lead->status == 'calificado') bg-purple-50 text-purple-700 border border-purple-200
                                @elseif($lead->status == 'negociacion') bg-orange-50 text-orange-700 border border-orange-200
                                @elseif($lead->status == 'cerrado_ganado') bg-green-50 text-green-700 border border-green-200
                                @elseif($lead->status == 'cerrado_perdido') bg-red-50 text-red-700 border border-red-200
                                @else bg-slate-50 text-slate-700 border border-slate-200 @endif
                            ">
                                {{ $statuses[$lead->status] ?? $lead->status }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-slate-500">
                            {{ $lead->created_at->setTimezone('America/Caracas')->format('d/m/Y') }}
                        </td>
                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                            <div class="flex items-center justify-end gap-1 sm:gap-2">
                                {{-- VER - Solo con permiso --}}
                                @can('ver leads')
                                    <button onclick="openShowModal({{ $lead->id }})"
                                        class="text-slate-500 hover:text-mso-blue transition-colors p-1" title="Ver">
                                        <i class="ph ph-eye text-base sm:text-lg"></i>
                                    </button>
                                @else
                                    <span class="text-slate-300 cursor-not-allowed p-1" title="No tienes permiso para ver">
                                        <i class="ph ph-eye text-base sm:text-lg"></i>
                                    </span>
                                @endcan

                                {{-- EDITAR - Solo con permiso --}}
                                @can('editar lead')
                                    <button onclick="openEditModal({{ $lead->id }})"
                                        class="text-slate-500 hover:text-mso-blue transition-colors p-1" title="Editar">
                                        <i class="ph ph-pencil-simple text-base sm:text-lg"></i>
                                    </button>
                                @else
                                    <span class="text-slate-300 cursor-not-allowed p-1" title="No tienes permiso para editar">
                                        <i class="ph ph-pencil-simple text-base sm:text-lg"></i>
                                    </span>
                                @endcan

                                {{-- CAMBIAR ESTADO - Solo con permiso --}}
                                @can('cambiar estado lead')
                                    <button onclick="openStatusModal({{ $lead->id }}, '{{ $lead->status }}')"
                                        class="text-slate-500 hover:text-mso-blue transition-colors p-1" title="Cambiar Estado">
                                        <i class="ph ph-arrow-counter-clockwise text-base sm:text-lg"></i>
                                    </button>
                                @else
                                    <span class="text-slate-300 cursor-not-allowed p-1" title="No tienes permiso para cambiar estado">
                                        <i class="ph ph-arrow-counter-clockwise text-base sm:text-lg"></i>
                                    </span>
                                @endcan

                                {{-- ELIMINAR - Solo con permiso --}}
                                @can('eliminar lead')
                                    <button onclick="openDeleteModal({{ $lead->id }}, '{{ addslashes($lead->name) }}')"
                                        class="text-slate-500 hover:text-red-500 transition-colors p-1" title="Eliminar">
                                        <i class="ph ph-trash text-base sm:text-lg"></i>
                                    </button>
                                @else
                                    <span class="text-slate-300 cursor-not-allowed p-1" title="No tienes permiso para eliminar">
                                        <i class="ph ph-trash text-base sm:text-lg"></i>
                                    </span>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center">
                                <i class="ph ph-users text-4xl mb-2 text-slate-300"></i>
                                <p class="text-sm">No hay leads registrados</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="p-3 sm:p-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-3">
            <span class="text-[10px] sm:text-xs text-slate-500">
                Mostrando {{ $leads->firstItem() ?? 0 }} a {{ $leads->lastItem() ?? 0 }} de {{ $leads->total() }} registros
            </span>
            {{ $leads->links('pagination::tailwind') }}
        </div>
    </div>
    @else
    {{-- ============================================= --}}
    {{-- MENSAJE DE ACCESO DENEGADO --}}
    {{-- ============================================= --}}
    <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
        <i class="ph ph-lock-simple text-3xl mb-2 block"></i>
        <p class="font-bold">Acceso Denegado</p>
        <p class="text-sm">No tienes permisos para ver los leads.</p>
    </div>
    @endcanany
</div>

{{-- ============================================================ --}}
{{-- MODALES --}}
{{-- ============================================================ --}}

{{-- MODAL VER LEAD --}}
<div id="showModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="bg-mso-blue px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base sm:text-lg">
                <i class="ph ph-user text-xl mr-2"></i> Detalle del Lead
            </h3>
            <button onclick="closeShowModal()" class="hover:text-mso-gold transition-colors">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>
        <div id="showModalContent" class="p-4 sm:p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <div class="text-center text-slate-400 py-8">
                <i class="ph ph-spinner text-3xl animate-spin"></i>
                <p class="mt-2">Cargando...</p>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDITAR LEAD --}}
<div id="editModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="bg-mso-blue px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base sm:text-lg">
                <i class="ph ph-pencil-simple text-xl mr-2"></i> Editar Lead
            </h3>
            <button onclick="closeEditModal()" class="hover:text-mso-gold transition-colors">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>
        <div id="editModalContent" class="p-4 sm:p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <div class="text-center text-slate-400 py-8">
                <i class="ph ph-spinner text-3xl animate-spin"></i>
                <p class="mt-2">Cargando...</p>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CAMBIAR ESTADO --}}
<div id="statusModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-mso-blue px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base sm:text-lg">
                <i class="ph ph-arrow-counter-clockwise text-xl mr-2"></i> Cambiar Estado
            </h3>
            <button onclick="closeStatusModal()" class="hover:text-mso-gold transition-colors">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>
        <div class="p-4 sm:p-6">
            <form id="statusForm" method="POST">
                @csrf
                <input type="hidden" id="statusLeadId" name="lead_id" value="">
                <div class="mb-4">
                    <label for="statusSelect" class="block text-sm font-medium text-slate-700 mb-2">Selecciona el nuevo estado</label>
                    <select name="status" id="statusSelect" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeStatusModal()" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-mso-blue text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors shadow-lg shadow-blue-900/20">
                        Actualizar Estado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL ELIMINAR LEAD --}}
<div id="deleteModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-mso-blue px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base sm:text-lg">
                <i class="ph ph-trash text-xl mr-2"></i> Eliminar Lead
            </h3>
            <button onclick="closeDeleteModal()" class="hover:text-mso-gold transition-colors">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>
        <div class="p-4 sm:p-6">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                <i class="ph ph-trash text-3xl text-red-600"></i>
            </div>
            <h4 class="text-lg font-bold text-slate-900 text-center mb-2">
                ¿Eliminar lead?
            </h4>
            <p class="text-sm text-slate-500 text-center mb-6">
                ¿Estás seguro de eliminar el lead "<strong id="deleteLeadName" class="text-slate-700"></strong>"?
                <br><span class="text-xs text-red-500">Esta acción no se puede deshacer.</span>
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" onclick="closeDeleteModal()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                @can('eliminar lead')
                    <button type="submit" id="confirmDeleteBtn"
                            class="px-6 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="ph ph-trash"></i>
                        Sí, eliminar
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- Formulario oculto para eliminar --}}
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('js')
<script>
    (function() {
        'use strict';

        // ============================================================
        // FUNCIONES PARA MODAL VER
        // ============================================================
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        window.openShowModal = function(id) {
            const modal = document.getElementById('showModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const content = document.getElementById('showModalContent');
            if (!content) return;
            content.innerHTML = `
                <div class="text-center text-slate-400 py-8">
                    <i class="ph ph-spinner text-3xl animate-spin"></i>
                    <p class="mt-2">Cargando...</p>
                </div>
            `;

            fetch(`/leads/${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                let asesorFullName = 'Sin asignar';
                if (data.asesor) {
                    asesorFullName = data.asesor.name;
                    if (data.asesor.last_name) {
                        asesorFullName += ' ' + data.asesor.last_name;
                    }
                }

                let html = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Nombre completo</p>
                            <p class="text-slate-800 font-medium text-sm sm:text-base">${escapeHtml(data.name || 'No especificado')}</p>
                        </div>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Correo electrónico</p>
                            <p class="text-slate-800 text-sm sm:text-base">${escapeHtml(data.email || 'No especificado')}</p>
                        </div>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Teléfono</p>
                            <p class="text-slate-800 text-sm sm:text-base">${escapeHtml(data.phone || 'No especificado')}</p>
                        </div>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</p>
                            <p class="text-slate-800 text-sm sm:text-base">${escapeHtml(data.status_label || data.status || 'No especificado')}</p>
                        </div>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Asesor asignado</p>
                            <p class="text-slate-800 text-sm sm:text-base">${escapeHtml(asesorFullName)}</p>
                        </div>
                        <div>
                            <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha de solicitud</p>
                            <p class="text-slate-800 text-sm sm:text-base">${data.created_at ? new Date(data.created_at).toLocaleDateString('es-ES') : 'No disponible'}</p>
                        </div>
                `;

                if (data.notes) {
                    html += `
                        <div class="col-span-2">
                            <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Notas adicionales</p>
                            <p class="text-slate-800 bg-slate-50 p-3 rounded-lg text-sm">${escapeHtml(data.notes)}</p>
                        </div>
                    `;
                }

                if (data.preferences && Object.keys(data.preferences).length > 0) {
                    const excludeFields = ['last_name', 'name', 'email', 'phone', 'notes', 'asesor_id', 'user_id'];
                    const fieldLabels = {
                        'property_address': 'Dirección de la propiedad',
                        'property_type': 'Tipo de propiedad',
                        'bedrooms': 'Habitaciones',
                        'bathrooms': 'Baños',
                        'area': 'Área (m²)',
                        'budget': 'Presupuesto',
                        'property_details': 'Detalles de la propiedad',
                        'city': 'Ciudad',
                        'state': 'Estado',
                        'country': 'País',
                        'property_id': 'ID de propiedad'
                    };

                    let propertyFields = {};
                    for (const [key, value] of Object.entries(data.preferences)) {
                        if (!excludeFields.includes(key) && value) {
                            propertyFields[key] = value;
                        }
                    }

                    if (Object.keys(propertyFields).length > 0) {
                        let prefHtml = `
                            <div class="col-span-2">
                                <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Información de la propiedad</p>
                                <div class="bg-slate-50 p-3 sm:p-4 rounded-lg space-y-1 text-sm text-slate-700">
                        `;

                        for (const [key, value] of Object.entries(propertyFields)) {
                            const label = fieldLabels[key] || key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                            prefHtml += `<p><span class="font-medium">${escapeHtml(label)}:</span> ${escapeHtml(value)}</p>`;
                        }

                        prefHtml += `</div></div>`;
                        html += prefHtml;
                    }
                }

                html += `</div>`;
                content.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="text-center text-red-500 py-8">
                        <i class="ph ph-warning text-3xl"></i>
                        <p class="mt-2">Error al cargar los datos</p>
                        <p class="text-sm text-slate-400 mt-1">${error.message || 'Intenta nuevamente'}</p>
                    </div>
                `;
            });
        };

        window.closeShowModal = function() {
            const modal = document.getElementById('showModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        // ============================================================
        // FUNCIONES PARA MODAL EDITAR
        // ============================================================
        window.openEditModal = function(id) {
            const modal = document.getElementById('editModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const content = document.getElementById('editModalContent');
            if (!content) return;
            content.innerHTML = `
                <div class="text-center text-slate-400 py-8">
                    <i class="ph ph-spinner text-3xl animate-spin"></i>
                    <p class="mt-2">Cargando...</p>
                </div>
            `;

            fetch(`/leads/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                content.innerHTML = html;
                const form = content.querySelector('form');
                if (form) {
                    form.action = `/leads/${id}`;
                    form.onsubmit = function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + (data.message || 'Error desconocido'));
                            }
                        })
                        .catch(error => {
                            alert('Error al actualizar el lead');
                        });
                    };
                }
            })
            .catch(error => {
                content.innerHTML = `
                    <div class="text-center text-red-500 py-8">
                        <i class="ph ph-warning text-3xl"></i>
                        <p class="mt-2">Error al cargar el formulario</p>
                    </div>
                `;
            });
        };

        window.closeEditModal = function() {
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        // ============================================================
        // FUNCIONES PARA MODAL CAMBIAR ESTADO
        // ============================================================
        window.openStatusModal = function(id, currentStatus) {
            const modal = document.getElementById('statusModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.getElementById('statusLeadId').value = id;
            document.getElementById('statusForm').action = `/leads/${id}/change-status`;
            document.getElementById('statusSelect').value = currentStatus;

            const form = document.getElementById('statusForm');
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById(`status-badge-${id}`);
                        if (badge) {
                            const statusClasses = {
                                'nuevo': 'bg-blue-50 text-blue-700 border-blue-200',
                                'contactado': 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'calificado': 'bg-purple-50 text-purple-700 border-purple-200',
                                'negociacion': 'bg-orange-50 text-orange-700 border-orange-200',
                                'cerrado_ganado': 'bg-green-50 text-green-700 border-green-200',
                                'cerrado_perdido': 'bg-red-50 text-red-700 border-red-200',
                                'inactivo': 'bg-slate-50 text-slate-700 border-slate-200'
                            };
                            badge.className = `inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider ${statusClasses[data.status] || statusClasses['inactivo']}`;
                            badge.textContent = data.status_label || data.status;
                        }
                        closeStatusModal();
                        showToast('Estado actualizado correctamente', 'success');
                    } else {
                        alert('Error: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    alert('Error al actualizar el estado');
                });
            };
        };

        window.closeStatusModal = function() {
            const modal = document.getElementById('statusModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        // ============================================================
        // FUNCIONES PARA MODAL ELIMINAR
        // ============================================================
        let deleteLeadId = null;

        window.openDeleteModal = function(id, name) {
            deleteLeadId = id;
            const modal = document.getElementById('deleteModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.getElementById('deleteLeadName').textContent = name;
        };

        window.closeDeleteModal = function() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            deleteLeadId = null;
        };

        @can('eliminar lead')
        document.getElementById('confirmDeleteBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            const id = deleteLeadId;
            if (!id) return;

            const form = document.getElementById('deleteForm');
            form.action = `/leads/${id}`;

            fetch(form.action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.getElementById(`lead-row-${id}`);
                    if (row) {
                        row.remove();
                    }
                    closeDeleteModal();
                    showToast('Lead eliminado exitosamente', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                alert('Error al eliminar el lead');
            });
        });
        @endcan

        // ============================================================
        // TOAST NOTIFICATIONS
        // ============================================================
        function showToast(message, type = 'info') {
            const oldToasts = document.querySelectorAll('.custom-toast');
            oldToasts.forEach(t => t.remove());

            const toast = document.createElement('div');
            toast.className = `custom-toast fixed top-4 right-4 z-[999] px-6 py-4 rounded-lg shadow-lg text-white text-sm max-w-md transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-600' :
                type === 'error' ? 'bg-red-600' :
                'bg-mso-blue'
            }`;
            toast.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="ph ${type === 'success' ? 'ph-check-circle' : type === 'error' ? 'ph-warning-circle' : 'ph-info'} text-xl"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 10);

            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // ============================================================
        // CERRAR MODALES CON ESC
        // ============================================================
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeShowModal();
                closeEditModal();
                closeStatusModal();
                closeDeleteModal();
            }
        });

        // ============================================================
        // CERRAR MODALES CLICANDO FUERA
        // ============================================================
        document.addEventListener('click', function(event) {
            if (event.target.id === 'showModal') closeShowModal();
            if (event.target.id === 'editModal') closeEditModal();
            if (event.target.id === 'statusModal') closeStatusModal();
            if (event.target.id === 'deleteModal') closeDeleteModal();
        });

        // Inicializar el formulario de eliminación
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                deleteForm.action = '/leads/0';
            }
        });

    })();
</script>
@endpush