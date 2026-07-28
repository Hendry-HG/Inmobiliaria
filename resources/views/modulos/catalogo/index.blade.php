{{-- Catálogo público de propiedades disponibles --}}
@extends('layouts.landing')

@section('title', 'Catálogo de Propiedades en Venezuela')

@push('css')
<style>
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    select:disabled {
        background-color: #f9fafb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }

    .favorite-btn {
        transition: transform 0.2s ease;
    }
    .favorite-btn:hover {
        transform: scale(1.1);
    }

    #toast-container > div {
        transform: translateX(100%);
        transition: all 0.3s ease-in-out;
    }
    #toast-container > div:first-child {
        transform: translateX(0);
    }

    #filtersModal {
        transition: opacity 0.3s ease;
    }
    #filtersModalContent {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #filtersModal:not(.hidden) {
        opacity: 1;
    }
    #filtersModal.hidden {
        opacity: 0;
    }

    .modal-overlay {
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        transition: opacity 0.3s ease;
    }
    .modal-overlay.hidden {
        display: none;
    }
    .modal-content {
        transform: scale(0.9);
        transition: transform 0.3s ease, opacity 0.3s ease;
        opacity: 0;
    }
    .modal-content.show {
        transform: scale(1);
        opacity: 1;
    }
    .house-icon {
        font-size: 4rem;
        display: inline-block;
        animation: bounce 2s infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    /* =============================================
       TARJETA DE PROPIEDAD - ESTILO RECIENTES
       ============================================= */
    .property-card {
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .property-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
        border-color: #c5a059;
    }

    /* Imagen */
    .property-card .image-container {
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
        height: 200px;
        background-color: #f1f5f9;
    }
    .property-card .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .property-card:hover .image-container img {
        transform: scale(1.05);
    }

    /* Badges */
    .badge-type {
        font-size: 0.6rem !important;
        padding: 0.15rem 0.5rem !important;
    }
    .badge-category {
        font-size: 0.55rem !important;
        padding: 0.1rem 0.4rem !important;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    .badge-location {
        font-size: 0.55rem !important;
        padding: 0.1rem 0.4rem !important;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    .badge-price {
        font-size: 0.7rem !important;
        padding: 0.15rem 0.5rem !important;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    .badge-asesor {
        font-size: 0.6rem !important;
        padding: 0.1rem 0.4rem !important;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    /* Cuerpo - ESTILO RECIENTES */
    .property-card .card-body {
        display: flex;
        flex-direction: column;
        padding: 0.75rem;
        flex: 1;
        gap: 0.15rem;
    }

    .property-card .card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 1.1rem;
    }
    .property-card .card-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }
    .property-card .card-title a:hover {
        color: #c5a059;
    }

    .property-card .card-location {
        font-size: 0.7rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.2rem;
        min-height: 1.1rem;
    }
    .property-card .card-location i {
        color: #c5a059;
        font-size: 0.7rem;
        flex-shrink: 0;
    }
    .property-card .card-location span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Características con SVG */
    .property-card .card-features {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        padding-top: 0.4rem;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
        min-height: 1.6rem;
    }
    .property-card .card-features .feature-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.7rem;
        color: #475569;
        white-space: nowrap;
    }
    .property-card .card-features .feature-tag svg {
        width: 14px;
        height: 14px;
        color: #c5a059;
        flex-shrink: 0;
    }
    .property-card .card-features .feature-tag .feature-value {
        font-weight: 600;
        color: #0f172a;
    }

    .property-card .card-date {
        font-size: 0.6rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 0.2rem;
        min-height: 0.9rem;
    }
    .property-card .card-date svg {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
    }

    .property-card .card-footer {
        margin-top: auto;
        padding-top: 0.4rem;
    }
    .property-card .card-footer a {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: #c5a059;
        transition: color 0.2s;
        text-decoration: none;
    }
    .property-card .card-footer a:hover {
        color: #0f172a;
    }
    .property-card .card-footer a svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s;
    }
    .property-card .card-footer a:hover svg {
        transform: translateX(4px);
    }

    /* =============================================
       RESPONSIVE
       ============================================= */
    @media (max-width: 640px) {
        .property-card .image-container {
            height: 160px;
        }
        .property-card .card-body {
            padding: 0.6rem;
        }
        .property-card .card-title {
            font-size: 0.8rem;
            min-height: 1rem;
        }
        .property-card .card-location {
            font-size: 0.6rem;
            min-height: 0.9rem;
        }
        .property-card .card-features .feature-tag {
            font-size: 0.6rem;
        }
        .property-card .card-features .feature-tag svg {
            width: 12px;
            height: 12px;
        }
        .property-card .card-date {
            font-size: 0.55rem;
        }
        .property-card .card-footer a {
            font-size: 0.65rem;
        }
        .badge-type {
            font-size: 0.55rem !important;
            padding: 0.1rem 0.4rem !important;
        }
        .badge-category {
            font-size: 0.5rem !important;
            padding: 0.1rem 0.3rem !important;
        }
        .badge-location {
            font-size: 0.5rem !important;
            padding: 0.1rem 0.3rem !important;
        }
        .badge-price {
            font-size: 0.6rem !important;
            padding: 0.1rem 0.4rem !important;
        }
        .badge-asesor {
            font-size: 0.5rem !important;
            padding: 0.1rem 0.3rem !important;
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        .property-card .image-container {
            height: 180px;
        }
    }

    /* =============================================
       GRID RESPONSIVE
       ============================================= */
    .property-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: 1fr;
    }

    @media (min-width: 640px) {
        .property-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .property-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* =============================================
       FILTROS ACTIVOS
       ============================================= */
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 0.75rem;
    }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        background-color: rgba(197, 160, 89, 0.1);
        color: #0f172a;
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        border-radius: 9999px;
        border: 1px solid rgba(197, 160, 89, 0.2);
        white-space: nowrap;
    }
    .filter-tag button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        color: inherit;
    }
    .filter-tag button:hover {
        color: #ef4444;
    }

    @media (max-width: 640px) {
        .filter-tag {
            font-size: 0.6rem;
            padding: 0.15rem 0.4rem;
        }
    }

    /* =============================================
       TOASTS
       ============================================= */
    #toast-container {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-width: 90%;
        width: 100%;
        max-width: 400px;
    }

    @media (max-width: 640px) {
        #toast-container {
            bottom: 0.5rem;
            right: 0.5rem;
            max-width: calc(100% - 1rem);
        }
    }

    .toast-item {
        animation: slideInRight 0.3s ease-out;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 0.9rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        color: white;
    }
    .toast-item.success { background: #22c55e; }
    .toast-item.error { background: #ef4444; }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .smooth-scroll {
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }

    .custom-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #c5a059;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<div class="bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">

            {{-- SIDEBAR DE FILTROS (Desktop) --}}
            <aside class="lg:w-72 xl:w-80 flex-shrink-0 hidden lg:block">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 lg:p-6 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph ph-funnel text-mso-gold"></i>
                            Filtros
                        </h3>
                        <a href="{{ route('catalogo.index') }}" class="text-sm text-mso-blue hover:underline">
                            Limpiar todo
                        </a>
                    </div>

                    <form action="{{ route('catalogo.index') }}" method="GET" id="filter-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-tag mr-1 text-mso-gold"></i>Tipo de Operación
                            </label>
                            <select name="type" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                                <option value="">Todos los tipos</option>
                                <option value="venta" {{ request('type') == 'venta' ? 'selected' : '' }}>Venta</option>
                                <option value="alquiler" {{ request('type') == 'alquiler' ? 'selected' : '' }}>Alquiler</option>
                                <option value="venta/alquiler" {{ request('type') == 'venta/alquiler' ? 'selected' : '' }}>Ambos</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-folder mr-1 text-mso-gold"></i>Categoría
                            </label>
                            <select name="category_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                                <option value="">Todas las categorías</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        @if($category->icon) <i class="{{ $category->icon }} mr-1"></i> @endif
                                        {{ $category->name }}
                                        @if(isset($category->properties_count) && $category->properties_count > 0) ({{ $category->properties_count }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-currency-dollar mr-1 text-mso-gold"></i>Rango de Precio
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">$</span>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}"
                                           placeholder="Mínimo" class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-sm">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">$</span>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}"
                                           placeholder="Máximo" class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-map-pin mr-1 text-mso-gold"></i>Ubicación
                            </label>
                            <div class="space-y-2">
                                <select name="state_id" id="filter_state" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                                    <option value="">Todos los estados</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>
                                            {{ $state->name }}
                                            @if(isset($state->properties_count) && $state->properties_count > 0) ({{ $state->properties_count }}) @endif
                                        </option>
                                    @endforeach
                                </select>

                                <select name="municipality_id" id="filter_municipality" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('state_id') ? '' : 'disabled' }}>
                                    <option value="">Todos los municipios</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality->id }}" {{ request('municipality_id') == $municipality->id ? 'selected' : '' }}>
                                            {{ $municipality->name }}
                                            @if(isset($municipality->properties_count) && $municipality->properties_count > 0) ({{ $municipality->properties_count }}) @endif
                                        </option>
                                    @endforeach
                                </select>

                                <select name="city_id" id="filter_city" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('municipality_id') ? '' : 'disabled' }}>
                                    <option value="">Todas las ciudades</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                            @if(isset($city->properties_count) && $city->properties_count > 0) ({{ $city->properties_count }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="ph ph-house-line mr-1 text-mso-gold"></i>Características
                            </label>
                            <div class="space-y-2">
                                <select name="bedrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                                    <option value="">Cualquier nº de habitaciones</option>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>{{ $i }}+ habitaciones</option>
                                    @endfor
                                </select>
                                <select name="bathrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                                    <option value="">Cualquier nº de baños</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ request('bathrooms') == $i ? 'selected' : '' }}>{{ $i }}+ baños</option>
                                    @endfor
                                </select>
                                <select name="parking_spaces" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                                    <option value="">Cualquier nº de estacionamientos</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ request('parking_spaces') == $i ? 'selected' : '' }}>{{ $i }}+ estacionamientos</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-ruler mr-1 text-mso-gold"></i>Área (m²)
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="min_area" value="{{ request('min_area') }}" placeholder="Mínimo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                <input type="number" name="max_area" value="{{ request('max_area') }}" placeholder="Máximo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-magnifying-glass mr-1 text-mso-gold"></i>Buscar
                            </label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Título, descripción o dirección..."
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                        </div>

                        <button type="submit" class="w-full bg-mso-blue text-white py-3 rounded-xl font-bold hover:bg-slate-800 transition-colors flex items-center justify-center gap-2 text-sm">
                            <i class="ph ph-funnel"></i> Aplicar Filtros
                        </button>
                    </form>
                </div>
            </aside>

            {{-- GRID DE PROPIEDADES --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                    <p class="text-slate-600 text-sm">
                        <span class="font-bold text-slate-800">{{ $properties->total() }}</span> propiedades encontradas
                        @if(request('state_id') || request('municipality_id') || request('city_id') || request('category_id'))
                            <span class="text-xs text-slate-500 block sm:inline sm:ml-2">(Filtros activos)</span>
                        @endif
                    </p>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button onclick="openFiltersModal()"
                                class="lg:hidden flex items-center justify-center gap-2 bg-mso-blue text-white px-3 sm:px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all shadow-md w-full sm:w-auto">
                            <i class="ph ph-funnel text-base"></i>
                            <span>Filtros</span>
                            @if(request('state_id') || request('municipality_id') || request('city_id') || request('min_price') || request('max_price') || request('type') || request('category_id'))
                                <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            @endif
                        </button>

                        <select name="order_by" form="filter-form" class="px-3 sm:px-4 py-2 border border-slate-200 rounded-lg bg-white text-sm flex-1 sm:flex-none min-w-[140px]">
                            <option value="latest" {{ request('order_by', 'latest') == 'latest' ? 'selected' : '' }}>Más recientes</option>
                            <option value="oldest" {{ request('order_by') == 'oldest' ? 'selected' : '' }}>Más antiguas</option>
                            <option value="price_asc" {{ request('order_by') == 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                            <option value="price_desc" {{ request('order_by') == 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                        </select>
                    </div>
                </div>

                @php
                    $activeFilters = [];
                    if(request('state_id')) {
                        $state = $states->firstWhere('id', request('state_id'));
                        if($state) $activeFilters[] = ['label' => 'Estado: ' . $state->name, 'param' => 'state_id'];
                    }
                    if(request('municipality_id')) {
                        $municipality = $municipalities->firstWhere('id', request('municipality_id'));
                        if($municipality) $activeFilters[] = ['label' => 'Municipio: ' . $municipality->name, 'param' => 'municipality_id'];
                    }
                    if(request('city_id')) {
                        $city = $cities->firstWhere('id', request('city_id'));
                        if($city) $activeFilters[] = ['label' => 'Ciudad: ' . $city->name, 'param' => 'city_id'];
                    }
                    if(request('category_id')) {
                        $category = $categories->firstWhere('id', request('category_id'));
                        if($category) $activeFilters[] = ['label' => 'Categoría: ' . $category->name, 'param' => 'category_id'];
                    }
                    if(request('min_price')) $activeFilters[] = ['label' => 'Desde: $' . number_format(request('min_price'), 0, ',', '.'), 'param' => 'min_price'];
                    if(request('max_price')) $activeFilters[] = ['label' => 'Hasta: $' . number_format(request('max_price'), 0, ',', '.'), 'param' => 'max_price'];
                    if(request('type')) $activeFilters[] = ['label' => 'Tipo: ' . ucfirst(request('type')), 'param' => 'type'];
                    if(request('bedrooms')) $activeFilters[] = ['label' => request('bedrooms') . '+ habitaciones', 'param' => 'bedrooms'];
                    if(request('bathrooms')) $activeFilters[] = ['label' => request('bathrooms') . '+ baños', 'param' => 'bathrooms'];
                    if(request('parking_spaces')) $activeFilters[] = ['label' => request('parking_spaces') . '+ estacionamientos', 'param' => 'parking_spaces'];
                @endphp

                @if(count($activeFilters) > 0)
                    <div class="filter-tags">
                        <span class="text-xs text-slate-500 font-medium mr-1">Filtros:</span>
                        @foreach($activeFilters as $filter)
                            <span class="filter-tag">
                                {{ $filter['label'] }}
                                <button type="button" onclick="eliminarFiltro('{{ $filter['param'] }}')">
                                    <i class="ph ph-x text-xs font-bold"></i>
                                </button>
                            </span>
                        @endforeach
                        <button type="button" onclick="limpiarTodosFiltros()" class="text-xs text-red-500 hover:text-red-700 font-medium">
                            Limpiar todos
                        </button>
                    </div>
                @endif

                <div class="property-grid">
                    @forelse($properties as $property)
                    @php
                        $isFavorited = auth()->check() && $property->isFavoritedBy(auth()->user());
                    @endphp
                    <div class="property-card">

                        {{-- Imagen --}}
                        <a href="{{ route('catalogo.show', $property) }}" class="image-container">
                            <img src="{{ $property->primary_image_url }}"
                                 alt="{{ $property->title }}"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/800x600?text=Sin+Imagen'">

                            {{-- Badge Tipo --}}
                            <span class="absolute top-2 left-2 bg-mso-gold text-mso-blue text-[0.6rem] font-bold px-1.5 py-0.5 rounded-full z-10 badge-type">
                                {{ $property->type == 'venta' ? 'VENTA' : ($property->type == 'alquiler' ? 'ALQUILER' : 'VENTA/ALQ.') }}
                            </span>

                            {{-- Badge Categoría --}}
                            @if($property->category)
                            <span class="absolute top-2 left-1/2 -translate-x-1/2 bg-black/60 text-white text-[0.55rem] font-medium px-1.5 py-0.5 rounded-full shadow-md flex items-center gap-1 z-10 badge-category">
                                @if($property->category->icon) <i class="{{ $property->category->icon }} text-[0.55rem]"></i> @endif
                                {{ $property->category->name }}
                            </span>
                            @endif

                            {{-- Badge Ubicación --}}
                            <span class="absolute bottom-2 left-2 bg-black/60 text-white text-[0.55rem] font-medium px-1.5 py-0.5 rounded-md shadow-md flex items-center gap-1 z-10 badge-location">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 256 256" class="flex-shrink-0">
                                    <path fill="currentColor" d="M128,64a40,40,0,1,0,40,40A40,40,0,0,0,128,64Zm0,64a24,24,0,1,1,24-24A24,24,0,0,1,128,128Zm0-112a88.1,88.1,0,0,0-88,88c0,31.4,14.5,64.7,42,96.4a216.6,216.6,0,0,0,36.6,30.6a8.1,8.1,0,0,0,8.9,0c4.2-2.8,12.8-8.8,24.6-20.4c14.2-13.9,27.4-31.4,38.1-50.4c9.9-17.5,15.3-34.2,15.8-50.2A88.1,88.1,0,0,0,128,16Zm0,160c-15.2,0-56-20.8-56-72a56,56,0,1,1,112,0C184,155.2,143.2,176,128,176Z"/>
                                </svg>
                                {{ $property->stateRelation->name ?? $property->cityRelation->name ?? 'Venezuela' }}
                            </span>

                            {{-- Badge Precio --}}
                            @if($property->price)
                            <span class="absolute bottom-2 right-2 bg-black/60 backdrop-blur-sm text-white text-[0.7rem] font-bold px-1.5 py-0.5 rounded-full z-10 badge-price">
                                {{ $property->formatted_price }}
                                @if($property->type == 'alquiler') <span class="text-[0.55rem] font-normal">/mes</span> @endif
                            </span>
                            @endif

                            {{-- Botón Favoritos --}}
                            <button class="favorite-btn absolute top-2 right-2 w-6 h-6 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-md z-10 hover:scale-110
                                {{ auth()->check() ? '' : 'cursor-pointer' }}"
                                    data-property-id="{{ $property->id }}"
                                    onclick="event.preventDefault(); handleFavoriteClick(this, {{ $property->id }})">
                                <i class="favorite-icon {{ $isFavorited ? 'ph-fill text-red-500' : 'ph text-slate-500' }} ph-heart text-[0.85rem]"></i>
                            </button>
                        </a>

                        {{-- Body --}}
                        <div class="card-body">
                            {{-- Título --}}
                            <h3 class="card-title">
                                <a href="{{ route('catalogo.show', $property) }}">
                                    {{ $property->title }}
                                </a>
                            </h3>

                            {{-- Ubicación --}}
                            <div class="card-location">
                                <i class="ph ph-map-pin"></i>
                                <span>{{ $property->cityRelation->name ?? $property->municipalityRelation->name ?? $property->stateRelation->name ?? 'Venezuela' }}</span>
                            </div>

                            {{-- Características --}}
                            <div class="card-features">
                                @if($property->bedrooms)
                                <span class="feature-tag">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                        <path fill="currentColor" d="M216,72H176V56a16,16,0,0,0-16-16H96A16,16,0,0,0,80,56V72H40A16,16,0,0,0,24,88V176a16,16,0,0,0,16,16v16a8,8,0,0,0,16,0V192H200v16a8,8,0,0,0,16,0V192a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72ZM96,56h64V72H96ZM216,176H40V88H216v88Z"/>
                                    </svg>
                                    <span class="feature-value">{{ $property->bedrooms }}</span>
                                </span>
                                @endif
                                @if($property->bathrooms)
                                <span class="feature-tag">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                        <path fill="currentColor" d="M240,104H224V72a8,8,0,0,0-8-8H152V40a8,8,0,0,0-16,0V64H112a48,48,0,0,0-48,48v24H40a16,16,0,0,0-16,16v32a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V152a16,16,0,0,0-16-16H80V112a32,32,0,0,1,32-32h64a8,8,0,0,1,8,8v24H120a8,8,0,0,0,0,16h88v40H48V152H216v32H40V152H80v8a8,8,0,0,0,16,0V152h8v32a8,8,0,0,0,16,0V152H224v8a8,8,0,0,0,16,0V112C240,107.6,240,107.6,240,104Z"/>
                                    </svg>
                                    <span class="feature-value">{{ $property->bathrooms }}</span>
                                </span>
                                @endif
                                @if($property->parking_spaces)
                                <span class="feature-tag">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                        <path fill="currentColor" d="M248,136v40a8,8,0,0,1-8,8H216a8,8,0,0,1-8-8V168H48v8a8,8,0,0,1-8,8H16a8,8,0,0,1-8-8V136a8,8,0,0,1,8-8H80V64a8,8,0,0,1,8-8H200a8,8,0,0,1,8,8v64h32A8,8,0,0,1,248,136ZM88,72v56h56V72Zm112,56h16V72H200Zm-96-8h40V80H104Z"/>
                                    </svg>
                                    <span class="feature-value">{{ $property->parking_spaces }}</span>
                                </span>
                                @endif
                                @if($property->area)
                                <span class="feature-tag">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                        <path fill="currentColor" d="M232,80V200a8,8,0,0,1-16,0V80a8,8,0,0,1,16,0ZM40,40V184a8,8,0,0,0,16,0V40a8,8,0,0,0-16,0ZM216,40a8,8,0,0,0-8,8V184a8,8,0,0,0,16,0V48A8,8,0,0,0,216,40ZM56,216a8,8,0,0,0,8-8V48a8,8,0,0,0-16,0V208A8,8,0,0,0,56,216Z"/>
                                    </svg>
                                    <span class="feature-value">{{ $property->area }}m²</span>
                                </span>
                                @endif
                            </div>

                            {{-- Fecha --}}
                            <div class="card-date">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                    <path fill="currentColor" d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,56V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V56A16,16,0,0,0,208,32Zm0,176H48V80H208ZM88,120a8,8,0,0,1,8-8h64a8,8,0,0,1,0,16H96A8,8,0,0,1,88,120Zm0,40a8,8,0,0,1,8-8h32a8,8,0,0,1,0,16H96A8,8,0,0,1,88,160Z"/>
                                </svg>
                                <span>{{ $property->created_at->format('d M, Y') }}</span>
                            </div>

                            {{-- Footer --}}
                            <div class="card-footer">
                                <a href="{{ route('catalogo.show', $property) }}">
                                    Ver detalles
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                        <path fill="currentColor" d="M221.7,133.7l-72,72A8.3,8.3,0,0,1,144,208a8.5,8.5,0,0,1-3.1-.6A8,8,0,0,1,136,200V136H40a8,8,0,0,1,0-16h96V56a8,8,0,0,1,4.9-7.4a8.4,8.4,0,0,1,8.8,1.7l72,72A8.1,8.1,0,0,1,221.7,133.7Z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 text-center">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="ph ph-house-line text-3xl sm:text-4xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-700 mb-2">No se encontraron propiedades</h3>
                            <p class="text-slate-500 text-sm mb-4">Intenta ajustar los filtros para ver más resultados.</p>
                            <a href="{{ route('catalogo.index') }}" class="inline-block bg-mso-blue text-white px-6 py-2.5 rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
                                <i class="ph ph-arrow-counter-clockwise mr-1"></i> Limpiar filtros
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>

                @if($properties->hasPages())
                <div class="mt-6">
                    {{ $properties->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE FILTROS PARA MÓVIL --}}
<div id="filtersModal"
     class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm transition-all duration-300 lg:hidden"
     onclick="if(event.target === this) closeFiltersModal()">

    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[92vh] overflow-y-auto smooth-scroll custom-scroll transform transition-transform duration-300 translate-y-full" id="filtersModalContent">

        <div class="sticky top-0 bg-white z-10 px-4 sm:px-5 py-4 border-b border-slate-200 rounded-t-3xl flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="ph ph-funnel text-mso-gold"></i>
                Filtros
            </h3>
            <div class="flex items-center gap-2">
                <button onclick="limpiarTodosFiltros()" class="text-sm text-red-500 hover:text-red-700 font-medium">
                    Limpiar todo
                </button>
                <button onclick="closeFiltersModal()" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-4 sm:p-5">
            <form action="{{ route('catalogo.index') }}" method="GET" id="filter-form-modal" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de Operación</label>
                    <select name="type" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                        <option value="">Todos los tipos</option>
                        <option value="venta" {{ request('type') == 'venta' ? 'selected' : '' }}>Venta</option>
                        <option value="alquiler" {{ request('type') == 'alquiler' ? 'selected' : '' }}>Alquiler</option>
                        <option value="venta/alquiler" {{ request('type') == 'venta/alquiler' ? 'selected' : '' }}>Ambos</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Categoría</label>
                    <select name="category_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                @if($category->icon) <i class="{{ $category->icon }} mr-1"></i> @endif
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Rango de Precio</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">$</span>
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Mínimo" class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-sm">
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">$</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Máximo" class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ubicación</label>
                    <div class="space-y-2">
                        <select name="state_id" id="filter_state_modal" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                            <option value="">Todos los estados</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                            @endforeach
                        </select>
                        <select name="municipality_id" id="filter_municipality_modal" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('state_id') ? '' : 'disabled' }}>
                            <option value="">Todos los municipios</option>
                            @foreach($municipalities as $municipality)
                                <option value="{{ $municipality->id }}" {{ request('municipality_id') == $municipality->id ? 'selected' : '' }}>{{ $municipality->name }}</option>
                            @endforeach
                        </select>
                        <select name="city_id" id="filter_city_modal" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('municipality_id') ? '' : 'disabled' }}>
                            <option value="">Todas las ciudades</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Características</label>
                    <div class="space-y-2">
                        <select name="bedrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                            <option value="">Cualquier nº de habitaciones</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>{{ $i }}+ habitaciones</option>
                            @endfor
                        </select>
                        <select name="bathrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                            <option value="">Cualquier nº de baños</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ request('bathrooms') == $i ? 'selected' : '' }}>{{ $i }}+ baños</option>
                            @endfor
                        </select>
                        <select name="parking_spaces" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                            <option value="">Cualquier nº de estacionamientos</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ request('parking_spaces') == $i ? 'selected' : '' }}>{{ $i }}+ estacionamientos</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Área (m²)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_area" value="{{ request('min_area') }}" placeholder="Mínimo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                        <input type="number" name="max_area" value="{{ request('max_area') }}" placeholder="Máximo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Título, descripción o dirección..." class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                </div>

                <button type="submit" class="w-full bg-mso-blue text-white py-3 rounded-xl font-bold hover:bg-slate-800 transition-colors flex items-center justify-center gap-2 text-sm">
                    <i class="ph ph-funnel"></i> Aplicar Filtros
                </button>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DE AUTENTICACIÓN --}}
<div id="authModal" class="modal-overlay fixed inset-0 z-[200] hidden items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden text-center p-6 sm:p-8">
        <div class="mb-4">
            <span class="house-icon">🏠</span>
        </div>
        <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mb-2">¡Guarda tus propiedades favoritas!</h3>
        <p class="text-slate-500 text-sm mb-6">Para poder guardar propiedades y recibir notificaciones de cambios de precio, necesitas tener una cuenta activa en nuestra plataforma.</p>
        <div class="flex flex-col gap-3">
            <a href="{{ route('login') }}" class="w-full bg-mso-blue text-white text-center font-bold py-3.5 rounded-xl hover:bg-slate-800 transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="ph ph-sign-in text-lg"></i> Iniciar Sesión
            </a>
            <a href="{{ route('register') }}" class="w-full border-2 border-slate-200 text-slate-700 text-center font-medium py-3.5 rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                <i class="ph ph-user-plus text-lg"></i> Crear Cuenta Gratis
            </a>
        </div>
        <button onclick="closeAuthModal()" class="mt-6 text-sm text-slate-400 hover:text-slate-600 transition-colors">
            <i class="ph ph-x mr-1"></i> Seguir explorando
        </button>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-container"></div>

@push('js')
<script>
function openAuthModal() {
    const modal = document.getElementById('authModal');
    const content = modal.querySelector('.modal-content');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    setTimeout(() => content.classList.add('show'), 10);
    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    const content = modal.querySelector('.modal-content');
    content.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

document.getElementById('authModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAuthModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAuthModal();
});

function handleFavoriteClick(btn, propertyId) {
    @if(auth()->check())
        toggleFavorite(btn, propertyId);
    @else
        openAuthModal();
    @endif
}

function toggleFavorite(btn, propertyId) {
    const icon = btn.querySelector('.favorite-icon');
    const isFavorite = icon.classList.contains('ph-fill');
    const originalIconClass = icon.className;

    icon.className = 'favorite-icon ph ph-spinner ph-spin text-[0.85rem] text-slate-500';

    fetch(`/favorites/${propertyId}`, {
        method: isFavorite ? 'DELETE' : 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ property_id: propertyId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_favorite) {
                icon.className = 'favorite-icon ph-fill text-red-500 ph-heart text-[0.85rem]';
                showToast('✓ Propiedad agregada a favoritos', 'success');
            } else {
                icon.className = 'favorite-icon ph text-slate-500 ph-heart text-[0.85rem]';
                showToast('✓ Propiedad eliminada de favoritos', 'success');
            }
        } else {
            icon.className = originalIconClass;
            showToast(data.message || 'Error al procesar la solicitud', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        icon.className = originalIconClass;
        showToast('Error de conexión. Intenta nuevamente.', 'error');
    });
}

function openFiltersModal() {
    const modal = document.getElementById('filtersModal');
    const content = document.getElementById('filtersModalContent');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        content.classList.remove('translate-y-full');
        content.classList.add('translate-y-0');
    }, 10);
}

function closeFiltersModal() {
    const modal = document.getElementById('filtersModal');
    const content = document.getElementById('filtersModalContent');
    content.classList.remove('translate-y-0');
    content.classList.add('translate-y-full');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

function limpiarTodosFiltros() {
    if (confirm('¿Estás seguro de limpiar todos los filtros?')) {
        window.location.href = '{{ route("catalogo.index") }}';
    }
}

function eliminarFiltro(param) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.delete(param);
    urlParams.delete('page');
    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
    window.location.href = newUrl;
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast-item ${type}`;
    toast.innerHTML = `
        <i class="ph ${type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'} text-xl"></i>
        <span class="text-sm font-medium">${message}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    const filterState = document.getElementById('filter_state');
    const filterMunicipality = document.getElementById('filter_municipality');
    const filterCity = document.getElementById('filter_city');

    const filterStateModal = document.getElementById('filter_state_modal');
    const filterMunicipalityModal = document.getElementById('filter_municipality_modal');
    const filterCityModal = document.getElementById('filter_city_modal');

    function loadMunicipalities(stateId, municipalitySelect, citySelect) {
        if (stateId) {
            fetch(`/api/locations/municipalities/${stateId}`)
                .then(response => response.json())
                .then(municipalities => {
                    municipalitySelect.disabled = false;
                    municipalitySelect.innerHTML = '<option value="">Todos los municipios</option>';
                    municipalities.forEach(municipality => {
                        municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
                    });
                    citySelect.disabled = true;
                    citySelect.innerHTML = '<option value="">Todas las ciudades</option>';
                })
                .catch(error => console.error('Error cargando municipios:', error));
        } else {
            municipalitySelect.disabled = true;
            municipalitySelect.innerHTML = '<option value="">Todos los municipios</option>';
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">Todas las ciudades</option>';
        }
    }

    function loadCities(municipalityId, citySelect) {
        if (municipalityId) {
            fetch(`/api/locations/parishes/${municipalityId}`)
                .then(response => response.json())
                .then(parishes => {
                    const parishIds = parishes.map(p => p.id);
                    if (parishIds.length > 0) {
                        fetch(`/api/locations/cities/${parishIds[0]}`)
                            .then(response => response.json())
                            .then(cities => {
                                citySelect.disabled = false;
                                citySelect.innerHTML = '<option value="">Todas las ciudades</option>';
                                cities.forEach(city => {
                                    citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                                });
                            });
                    } else {
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Todas las ciudades</option>';
                    }
                })
                .catch(error => {
                    console.error('Error cargando ciudades:', error);
                    citySelect.disabled = true;
                    citySelect.innerHTML = '<option value="">Todas las ciudades</option>';
                });
        } else {
            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">Todas las ciudades</option>';
        }
    }

    if (filterState) {
        filterState.addEventListener('change', function() {
            loadMunicipalities(this.value, filterMunicipality, filterCity);
        });
    }
    if (filterMunicipality) {
        filterMunicipality.addEventListener('change', function() {
            loadCities(this.value, filterCity);
        });
    }

    if (filterStateModal) {
        filterStateModal.addEventListener('change', function() {
            loadMunicipalities(this.value, filterMunicipalityModal, filterCityModal);
        });
    }
    if (filterMunicipalityModal) {
        filterMunicipalityModal.addEventListener('change', function() {
            loadCities(this.value, filterCityModal);
        });
    }

    document.querySelector('select[name="order_by"]')?.addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
});
</script>
@endpush
@endsection
