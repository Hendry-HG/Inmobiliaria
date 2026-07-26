@extends('layouts.dashboard')

@section('title', 'Auditoría - Logs de Propiedades')
@section('header', 'Logs de Propiedades')

@push('styles')
<style>
    .property-row:hover {
        background-color: #f8fafc;
        cursor: pointer;
    }
    .property-row.active {
        background-color: #fef3c7;
        border-left: 3px solid #c9a84c;
    }
    .timeline-item {
        border-left: 3px solid #e2e4ea;
        padding-left: 1rem;
        position: relative;
        padding-bottom: 1rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 8px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #c9a84c;
        border: 2px solid #fff;
        z-index: 1;
    }
    .timeline-item:last-child {
        border-left-color: transparent;
        padding-bottom: 0;
    }
    .timeline-item:last-child::before {
        background: #94a3b8;
    }
    .stat-badge {
        transition: all 0.3s ease;
    }
    .stat-badge:hover {
        transform: scale(1.05);
    }
    .property-card {
        transition: all 0.3s ease;
    }
    .property-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    #propertySearchResults::-webkit-scrollbar {
        width: 6px;
    }
    #propertySearchResults::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    #propertySearchResults::-webkit-scrollbar-thumb {
        background: #c9a84c;
        border-radius: 3px;
    }

    /* 🔥 CORRECCIÓN: Contenedor del buscador */
    .search-container {
        position: relative;
        width: 100%;
    }

    .search-container .clear-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: #94a3b8;
        transition: color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .search-container .clear-btn:hover {
        color: #ef4444;
    }

    .search-container input {
        padding-right: 35px !important;
        width: 100%;
    }

    /*  CORRECCIÓN: Tags de filtros */
    .filter-tags-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e2e4ea;
    }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: #fef3c7;
        color: #1e293b;
        font-size: 0.75rem;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        max-width: 100%;
        white-space: nowrap;
    }

    .filter-tag .remove-filter {
        background: none;
        border: none;
        padding: 0 2px;
        cursor: pointer;
        color: #94a3b8;
        transition: color 0.2s ease;
        font-size: 0.875rem;
        line-height: 1;
    }

    .filter-tag .remove-filter:hover {
        color: #ef4444;
    }

    .clear-all-filters {
        font-size: 0.75rem;
        color: #ef4444;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        transition: color 0.2s ease;
    }

    .clear-all-filters:hover {
        color: #dc2626;
        text-decoration: underline;
    }

    /*  CORRECCIÓN: Para que los resultados del buscador no se salgan */
    #propertySearchResults {
        left: 0;
        right: 0;
        width: 100%;
    }

    /*  NUEVO: Contenedor de botones de acción */
    .action-buttons {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
    }

    .action-buttons .btn-filter {
        background: #1e293b;
        color: white;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        transition: background 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        flex: 1;
        height: 42px;
    }

    .action-buttons .btn-filter:hover {
        background: #0f172a;
    }

    .action-buttons .btn-clear {
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e4ea;
        background: white;
        cursor: pointer;
        transition: background 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        width: 42px;
        flex-shrink: 0;
    }

    .action-buttons .btn-clear:hover {
        background: #f1f5f9;
    }

    .action-buttons .btn-clear i {
        font-size: 1.25rem;
        color: #64748b;
    }

    /*  Ajustes responsive */
    @media (max-width: 767px) {
        .action-buttons {
            width: 100%;
        }
        .action-buttons .btn-filter {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ============================================ --}}
    {{-- TOP 10 PROPIEDADES CON MÁS ACTIVIDAD --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-serif text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-fire text-orange-500"></i>
                    Propiedades con Más Actividad
                </h3>
                <p class="text-xs text-slate-400">Top 10 propiedades con más eventos registrados</p>
            </div>
            <span class="text-xs bg-slate-100 px-3 py-1 rounded-full text-slate-500">
                <i class="ph ph-clock"></i> Historial completo
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            @forelse($topProperties ?? [] as $item)
            <a href="{{ route('audit-logs.property-logs', ['property_id' => $item->subject_id]) }}"
               class="block p-4 bg-slate-50 rounded-xl hover:bg-mso-gold/10 transition-all duration-300 property-card border border-transparent hover:border-mso-gold/30">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate" title="{{ $item->subject ? $item->subject->title : 'Propiedad #' . $item->subject_id }}">
                            {{ $item->subject ? $item->subject->title : 'Propiedad #' . $item->subject_id }}
                        </p>
                        @if($item->subject && $item->subject->location)
                        <p class="text-xs text-slate-500 truncate">
                            <i class="ph ph-map-pin text-mso-gold"></i> {{ $item->subject->location }}
                        </p>
                        @endif
                    </div>
                    <span class="stat-badge bg-mso-gold text-mso-blue text-xs font-bold px-2.5 py-1 rounded-full ml-2 flex-shrink-0">
                        {{ $item->total }}
                    </span>
                </div>
                <div class="mt-2 flex items-center gap-2 text-[10px] text-slate-400">
                    <i class="ph ph-clock"></i>
                    <span>Última actividad: {{ $item->subject ? $item->subject->updated_at->setTimezone('America/Caracas')->format('d/m/Y') : 'N/A' }}</span>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center text-slate-400 py-8">
                <i class="ph ph-inbox text-4xl block mb-2"></i>
                <p>No hay actividad en propiedades</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- BUSCADOR Y FILTROS --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form action="{{ route('audit-logs.property-logs') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                {{-- Buscador de propiedades --}}
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-magnifying-glass text-mso-gold"></i> Buscar Propiedad
                    </label>
                    <div class="search-container">
                        <input type="text"
                               id="propertySearchInput"
                               placeholder="Buscar por título o ID..."
                               class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none"
                               autocomplete="off"
                               value="{{ request('property_id') && isset($selectedProperty) ? $selectedProperty->title : '' }}">
                        <div id="propertySearchResults" class="absolute z-50 w-full bg-white border rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                            <!-- Resultados dinámicos -->
                        </div>
                        <input type="hidden" name="property_id" id="selectedPropertyId" value="{{ request('property_id') }}">
                        @if(request('property_id'))
                        <button type="button"
                                onclick="limpiarPropiedadSeleccionada()"
                                class="clear-btn"
                                title="Limpiar selección">
                            <i class="ph ph-x-circle text-lg"></i>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Filtro de acción --}}
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-tag text-mso-gold"></i> Acción
                    </label>
                    <select name="action" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none">
                        <option value="">Todas las acciones</option>
                        @foreach($actions ?? [] as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fechas --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-calendar text-mso-gold"></i> Desde
                    </label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded-lg p-2.5">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-calendar text-mso-gold"></i> Hasta
                    </label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded-lg p-2.5">
                </div>

                {{-- Botones de acción --}}
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1 opacity-0">Acciones</label>
                    <div class="action-buttons">
                        <button type="submit" class="btn-filter" title="Aplicar filtros">
                            <i class="ph ph-funnel"></i>
                            <span class="hidden sm:inline">Filtrar</span>
                        </button>
                        <a href="{{ route('audit-logs.property-logs') }}" class="btn-clear" title="Limpiar filtros">
                            <i class="ph ph-x"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tags de filtros activos --}}
            @php
                $activeFilters = [];
                if(request('property_id')) {
                    $prop = $properties->firstWhere('id', request('property_id'));
                    if($prop) $activeFilters[] = ['label' => 'Propiedad: ' . $prop->title, 'param' => 'property_id'];
                }
                if(request('action')) $activeFilters[] = ['label' => 'Acción: ' . ucfirst(str_replace('_', ' ', request('action'))), 'param' => 'action'];
                if(request('date_from')) $activeFilters[] = ['label' => 'Desde: ' . date('d/m/Y', strtotime(request('date_from'))), 'param' => 'date_from'];
                if(request('date_to')) $activeFilters[] = ['label' => 'Hasta: ' . date('d/m/Y', strtotime(request('date_to'))), 'param' => 'date_to'];
            @endphp

            @if(count($activeFilters) > 0)
            <div class="filter-tags-container">
                <span class="text-xs text-slate-500 font-medium mr-1">Filtros activos:</span>
                @foreach($activeFilters as $filter)
                <span class="filter-tag">
                    {{ $filter['label'] }}
                    <button type="button"
                            onclick="eliminarFiltro('{{ $filter['param'] }}')"
                            class="remove-filter"
                            title="Eliminar filtro">
                        <i class="ph ph-x"></i>
                    </button>
                </span>
                @endforeach
                <button type="button"
                        onclick="limpiarTodosFiltros()"
                        class="clear-all-filters">
                    Limpiar todos
                </button>
            </div>
            @endif
        </form>
    </div>

    {{-- ============================================ --}}
    {{-- PROPIEDAD SELECCIONADA - HISTORIAL COMPLETO --}}
    {{-- ============================================ --}}
    @if(request('property_id') && isset($selectedProperty))
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-mso-blue to-slate-700 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-lg bg-white/20 flex items-center justify-center overflow-hidden flex-shrink-0">
                    @if($selectedProperty->primaryImage)
                        <img src="{{ $selectedProperty->primary_image_url }}" alt="{{ $selectedProperty->title }}" class="w-full h-full object-cover">
                    @else
                        <i class="ph ph-building text-3xl text-white/60"></i>
                    @endif
                </div>
                <div>
                    <h3 class="font-bold text-lg">{{ $selectedProperty->title }}</h3>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-blue-200">
                        <span><i class="ph ph-map-pin"></i> {{ $selectedProperty->full_location ?? 'Sin ubicación' }}</span>
                        <span class="w-1 h-1 bg-blue-300 rounded-full"></span>
                        <span><i class="ph ph-currency-dollar"></i> {{ $selectedProperty->formatted_price }}</span>
                        <span class="w-1 h-1 bg-blue-300 rounded-full"></span>
                        <span><i class="ph ph-eye"></i> {{ number_format($selectedProperty->views ?? 0) }} vistas</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-medium">
                    {{ $propertyHistory->total() ?? 0 }} eventos
                </span>
                <a href="{{ route('audit-logs.property-logs') }}" class="text-white/80 hover:text-white text-sm flex items-center gap-1 transition-colors">
                    <i class="ph ph-x"></i> Limpiar
                </a>
            </div>
        </div>

        <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto p-4">
            @forelse($propertyHistory ?? [] as $log)
            <div class="timeline-item hover:bg-slate-50 transition-colors px-3 rounded-lg">
                <div class="flex items-start gap-4 py-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                            @if(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-600
                            @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-600
                            @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-600
                            @elseif(str_contains($log->action ?? $log->event, 'view')) bg-purple-100 text-purple-600
                            @else bg-slate-100 text-slate-600 @endif">
                            <i class="ph
                                @if(str_contains($log->action ?? $log->event, 'create')) ph-plus-circle
                                @elseif(str_contains($log->action ?? $log->event, 'update')) ph-pencil-simple
                                @elseif(str_contains($log->action ?? $log->event, 'delete')) ph-trash
                                @elseif(str_contains($log->action ?? $log->event, 'view')) ph-eye
                                @else ph-dots-three @endif
                            "></i>
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="text-sm text-slate-700">
                                    <span class="font-medium">{{ $log->user ? $log->user->full_name : 'Sistema' }}</span>
                                    <span class="text-slate-500">{{ $log->description ?? 'Realizó una acción' }}</span>
                                </p>
                                @if($log->old_values || $log->new_values)
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @if($log->old_values)
                                    <span class="text-[10px] bg-red-50 text-red-600 px-2 py-0.5 rounded border border-red-200 max-w-xs truncate">
                                        <i class="ph ph-arrow-left"></i> {{ json_encode($log->old_values) }}
                                    </span>
                                    @endif
                                    @if($log->new_values)
                                    <span class="text-[10px] bg-green-50 text-green-600 px-2 py-0.5 rounded border border-green-200 max-w-xs truncate">
                                        <i class="ph ph-arrow-right"></i> {{ json_encode($log->new_values) }}
                                    </span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <span class="px-2 py-1 rounded text-xs font-medium whitespace-nowrap flex-shrink-0
                                @if(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-700
                                @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-700
                                @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-700
                                @elseif(str_contains($log->action ?? $log->event, 'view')) bg-purple-100 text-purple-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-[10px] text-slate-400 mt-1">
                            <span><i class="ph ph-clock"></i> {{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i:s') : '' }}</span>
                            @if($log->ip_address)
                            <span>· IP: {{ $log->ip_address }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center text-slate-400">
                <i class="ph ph-inbox text-4xl block mb-3"></i>
                <p class="font-medium">No hay historial para esta propiedad</p>
                <p class="text-sm">No se han registrado eventos para esta propiedad</p>
            </div>
            @endforelse
        </div>

        @if(isset($propertyHistory) && $propertyHistory->hasPages())
        <div class="px-6 py-3 border-t border-slate-200 bg-slate-50">
            {{ $propertyHistory->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- TABLA DE TODAS LAS PROPIEDADES --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h3 class="font-serif text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-list-checks text-mso-gold"></i>
                    Registro de Actividad de Propiedades
                </h3>
                <p class="text-xs text-slate-400">{{ count($propertyStats) }} propiedades registradas</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('audit-logs.export') }}?entity=Property&{{ http_build_query(request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs font-medium hover:bg-green-100 transition-colors border border-green-200">
                    <i class="ph ph-download-simple"></i> Exportar
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="propertyTable">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Propiedad</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Ubicación</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Precio</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Estado</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center">Vistas</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center">Eventos</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center">Última Actividad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($propertyStats ?? [] as $stat)
                    <tr class="property-row transition-colors {{ request('property_id') == $stat['id'] ? 'active' : '' }}"
                        onclick="verHistorialPropiedad({{ $stat['id'] }})">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if($stat['image'])
                                        <img src="{{ $stat['image'] }}" alt="{{ $stat['title'] }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="ph ph-building text-slate-400 text-xl"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800 text-sm hover:text-mso-gold transition-colors">
                                        {{ $stat['title'] }}
                                    </p>
                                    <p class="text-[10px] text-slate-400">ID: #{{ $stat['id'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-slate-600">
                            <span class="truncate max-w-[150px] inline-block">{{ $stat['location'] ?? 'Sin ubicación' }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-sm font-bold text-mso-gold">
                            {{ $stat['price'] }}
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium
                                @if($stat['status'] == 'publicada') bg-green-100 text-green-700
                                @elseif($stat['status'] == 'borrador') bg-yellow-100 text-yellow-700
                                @elseif($stat['status'] == 'vendida') bg-blue-100 text-blue-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ ucfirst($stat['status'] ?? 'Desconocido') }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="text-sm font-medium text-slate-700">{{ number_format($stat['views'] ?? 0) }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="inline-flex items-center gap-1 bg-mso-gold/10 text-mso-blue px-2.5 py-1 rounded-full text-xs font-bold">
                                {{ $stat['events_count'] ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-center text-xs text-slate-500">
                            {{ $stat['last_activity'] ?? 'Nunca' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                            <i class="ph ph-inbox text-5xl block mb-4 text-slate-300"></i>
                            <p class="font-medium text-slate-600">No hay propiedades registradas</p>
                            <p class="text-sm">Las propiedades aparecerán aquí cuando se creen</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($propertyStats) > 0)
        <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs text-slate-400">
            <span>Mostrando {{ count($propertyStats) }} propiedades</span>
            <span class="text-slate-500">
                <i class="ph ph-click"></i> Haz clic en una fila para ver su historial
            </span>
        </div>
        @endif
    </div>
</div>

@push('js')
<script>
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    // ============================================
    // BUSCADOR DE PROPIEDADES EN TIEMPO REAL
    // ============================================
    const searchInput = document.getElementById('propertySearchInput');
    const resultsContainer = document.getElementById('propertySearchResults');
    const selectedPropertyId = document.getElementById('selectedPropertyId');

    let searchTimeout;
    let currentAbortController = null;
    const searchUrl = '{{ route("audit-logs.search-properties") }}';

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            if (currentAbortController) {
                currentAbortController.abort();
                currentAbortController = null;
            }

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                resultsContainer.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                currentAbortController = new AbortController();

                fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                    signal: currentAbortController.signal
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta');
                    return response.json();
                })
                .then(properties => {
                    currentAbortController = null;

                    if (properties.length === 0) {
                        resultsContainer.innerHTML = `
                            <div class="px-4 py-3 text-sm text-slate-500 text-center">
                                No se encontraron propiedades
                            </div>
                        `;
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    let html = '';
                    properties.forEach(prop => {
                        const isSelected = selectedPropertyId.value == prop.id;
                        html += `
                            <div class="user-search-result px-4 py-2.5 cursor-pointer flex items-center gap-3 ${isSelected ? 'active' : ''} hover:bg-slate-50 transition-colors"
                                 data-property-id="${prop.id}"
                                 data-property-title="${escapeHtml(prop.title)}"
                                 onclick="selectProperty(this)">
                                ${prop.image ? `<img src="${prop.image}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">` : `<div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0"><i class="ph ph-building text-slate-400"></i></div>`}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">${escapeHtml(prop.title)}</p>
                                    <p class="text-xs text-slate-500 truncate">${escapeHtml(prop.location || 'Sin ubicación')}</p>
                                </div>
                                <span class="text-xs text-mso-gold font-bold">${escapeHtml(prop.price)}</span>
                                ${isSelected ? '<i class="ph ph-check-circle text-mso-gold ml-2 text-lg"></i>' : ''}
                            </div>
                        `;
                    });

                    resultsContainer.innerHTML = html;
                    resultsContainer.classList.remove('hidden');
                })
                .catch(error => {
                    if (error.name === 'AbortError') return;
                    console.error('Error:', error);
                    resultsContainer.innerHTML = `
                        <div class="px-4 py-3 text-sm text-red-500 text-center">
                            <i class="ph ph-warning-circle"></i> Error al buscar propiedades
                        </div>
                    `;
                    resultsContainer.classList.remove('hidden');
                });
            }, 400);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                resultsContainer.classList.add('hidden');
                searchInput.blur();
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const firstResult = resultsContainer.querySelector('.user-search-result');
                if (firstResult) {
                    e.preventDefault();
                    selectProperty(firstResult);
                }
            }
        });
    }

    function selectProperty(element) {
        const propertyId = element.dataset.propertyId;
        const propertyTitle = element.dataset.propertyTitle;

        selectedPropertyId.value = propertyId;
        searchInput.value = propertyTitle;
        resultsContainer.classList.add('hidden');

        const form = searchInput.closest('form');
        if (form) {
            form.submit();
        }
    }

    function limpiarPropiedadSeleccionada() {
        selectedPropertyId.value = '';
        searchInput.value = '';
        resultsContainer.classList.add('hidden');
        window.location.href = '{{ route("audit-logs.property-logs") }}';
    }

    // ============================================
    // VER HISTORIAL DE PROPIEDAD
    // ============================================
    function verHistorialPropiedad(propertyId) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('property_id', propertyId);
        currentUrl.searchParams.delete('page');
        window.location.href = currentUrl.toString();
    }

    // ============================================
    // ELIMINAR FILTROS
    // ============================================
    function eliminarFiltro(param) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete(param);
        urlParams.delete('page');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.location.href = newUrl;
    }

    function limpiarTodosFiltros() {
        window.location.href = '{{ route("audit-logs.property-logs") }}';
    }

    // ============================================
    // BÚSQUEDA EN TABLA
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const tableHeader = document.querySelector('.border-b.border-slate-200 .flex-col.sm\\:flex-row');
        if (tableHeader) {
            if (!tableHeader.querySelector('#tableQuickSearch')) {
                const searchWrapper = document.createElement('div');
                searchWrapper.className = 'relative';
                searchWrapper.innerHTML = `
                    <input type="text"
                           id="tableQuickSearch"
                           placeholder="Buscar en tabla..."
                           class="pl-9 pr-4 py-1.5 border rounded-lg text-sm focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none w-48">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                `;
                tableHeader.appendChild(searchWrapper);

                document.getElementById('tableQuickSearch')?.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#propertyTable tbody tr');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        }
    });
</script>
@endpush
@endsection
