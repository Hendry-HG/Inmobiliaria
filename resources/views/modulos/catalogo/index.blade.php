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

    .property-card {
        transition: all 0.3s ease;
    }
    .property-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
    }
    .property-card .image-container {
        overflow: hidden;
    }
    .property-card .image-container img {
        transition: transform 0.5s ease;
    }
    .property-card:hover .image-container img {
        transform: scale(1.05);
    }

    .status-badge {
        backdrop-filter: blur(4px);
    }

    .feature-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background-color: #f8fafc;
        padding: 0.3rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.7rem;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out forwards;
    }
</style>
@endpush

@section('content')
<div class="bg-slate-50 min-h-screen">
    {{-- CONTENIDO PRINCIPAL: Sidebar + Grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- SIDEBAR DE FILTROS (Desktop) --}}
            <aside class="lg:w-80 flex-shrink-0 hidden lg:block">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:sticky lg:top-24">

                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph ph-funnel text-mso-gold"></i>
                            Filtros
                        </h3>
                        <a href="{{ route('catalogo.index') }}" class="text-sm text-mso-blue hover:underline">
                            Limpiar todo
                        </a>
                    </div>

                    <form action="{{ route('catalogo.index') }}" method="GET" id="filter-form" class="space-y-5">

                        {{-- Tipo de Operación --}}
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

                        {{-- CATEGORÍA --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-folder mr-1 text-mso-gold"></i>Categoría
                            </label>
                            <select name="category_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                                <option value="">Todas las categorías</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        @if($category->icon)
                                            <i class="{{ $category->icon }} mr-1"></i>
                                        @endif
                                        {{ $category->name }}
                                        @if(isset($category->properties_count) && $category->properties_count > 0)
                                            ({{ $category->properties_count }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rango de Precio --}}
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

                        {{-- Ubicación --}}
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
                                            @if(isset($state->properties_count) && $state->properties_count > 0)
                                                ({{ $state->properties_count }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                <select name="municipality_id" id="filter_municipality" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('state_id') ? '' : 'disabled' }}>
                                    <option value="">Todos los municipios</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality->id }}" {{ request('municipality_id') == $municipality->id ? 'selected' : '' }}>
                                            {{ $municipality->name }}
                                            @if(isset($municipality->properties_count) && $municipality->properties_count > 0)
                                                ({{ $municipality->properties_count }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                <select name="city_id" id="filter_city" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('municipality_id') ? '' : 'disabled' }}>
                                    <option value="">Todas las ciudades</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                            @if(isset($city->properties_count) && $city->properties_count > 0)
                                                ({{ $city->properties_count }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Características --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="ph ph-house-line mr-1 text-mso-gold"></i>Características
                            </label>
                            <div class="space-y-2">
                                <select name="bedrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                                    <option value="">Cualquier nº de habitaciones</option>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>
                                            {{ $i }}+ habitaciones
                                        </option>
                                    @endfor
                                </select>

                                <select name="bathrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                                    <option value="">Cualquier nº de baños</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ request('bathrooms') == $i ? 'selected' : '' }}>
                                            {{ $i }}+ baños
                                        </option>
                                    @endfor
                                </select>

                                <select name="parking_spaces" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                                    <option value="">Cualquier nº de estacionamientos</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ request('parking_spaces') == $i ? 'selected' : '' }}>
                                            {{ $i }}+ estacionamientos
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Área en m² --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                <i class="ph ph-ruler mr-1 text-mso-gold"></i>Área (m²)
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="min_area" value="{{ request('min_area') }}"
                                       placeholder="Mínimo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                <input type="number" name="max_area" value="{{ request('max_area') }}"
                                       placeholder="Máximo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            </div>
                        </div>

                        {{-- Búsqueda por texto --}}
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
            <div class="flex-1">

                {{-- Barra de ordenamiento y resultados --}}
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                    <p class="text-slate-600 text-sm">
                        <span class="font-bold text-slate-800">{{ $properties->total() }}</span> propiedades encontradas
                        @if(request('state_id') || request('municipality_id') || request('city_id') || request('category_id'))
                            <span class="text-xs text-slate-500 block sm:inline sm:ml-2">
                                (Filtros activos)
                            </span>
                        @endif
                    </p>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button onclick="openFiltersModal()"
                                class="lg:hidden flex items-center justify-center gap-2 bg-mso-blue text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all shadow-md w-full sm:w-auto">
                            <i class="ph ph-funnel text-base"></i>
                            <span>Filtros</span>
                            @if(request('state_id') || request('municipality_id') || request('city_id') || request('min_price') || request('max_price') || request('type') || request('category_id'))
                                <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            @endif
                        </button>

                        <select name="order_by" form="filter-form" class="px-4 py-2 border border-slate-200 rounded-lg bg-white text-sm flex-1 sm:flex-none">
                            <option value="latest" {{ request('order_by', 'latest') == 'latest' ? 'selected' : '' }}>Más recientes</option>
                            <option value="oldest" {{ request('order_by') == 'oldest' ? 'selected' : '' }}>Más antiguas</option>
                            <option value="price_asc" {{ request('order_by') == 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                            <option value="price_desc" {{ request('order_by') == 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                        </select>
                    </div>
                </div>

                {{-- Tags de filtros activos --}}
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
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="text-xs text-slate-500 font-medium mr-1">Filtros:</span>
                        @foreach($activeFilters as $filter)
                            <span class="inline-flex items-center gap-1 bg-mso-gold/10 text-mso-blue text-xs px-2.5 py-1 rounded-full">
                                {{ $filter['label'] }}
                                <button type="button"
                                        onclick="eliminarFiltro('{{ $filter['param'] }}')"
                                        class="hover:text-red-500 transition-colors ml-0.5">
                                    <i class="ph ph-x text-xs font-bold"></i>
                                </button>
                            </span>
                        @endforeach
                        <button type="button"
                                onclick="limpiarTodosFiltros()"
                                class="text-xs text-red-500 hover:text-red-700 font-medium">
                            Limpiar todos
                        </button>
                    </div>
                @endif

                {{-- Grid de Tarjetas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @forelse($properties as $property)
                    @php
                        $isFavorited = auth()->check() && $property->isFavoritedBy(auth()->user());
                    @endphp
                    <div class="property-card bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300">

                        {{-- Imagen --}}
                        <a href="{{ route('catalogo.show', $property) }}" class="block relative image-container h-52 overflow-hidden bg-slate-100">
                            <img src="{{ $property->primary_image_url }}"
                                 alt="{{ $property->title }}"
                                 class="w-full h-full object-cover"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/800x600?text=Sin+Imagen'">

                            {{-- Badge de Tipo --}}
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full shadow-md status-badge
                                    @if($property->type == 'venta') bg-blue-600 text-white
                                    @elseif($property->type == 'alquiler') bg-green-600 text-white
                                    @else bg-purple-600 text-white @endif">
                                    {{ $property->type == 'venta' ? 'VENTA' : ($property->type == 'alquiler' ? 'ALQUILER' : 'VENTA/ALQ.') }}
                                </span>
                            </div>

                            {{-- Badge de Categoría --}}
                            @if($property->category)
                            <div class="absolute top-3 left-1/2 -translate-x-1/2">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full shadow-md bg-black/50 text-white backdrop-blur-sm flex items-center gap-1">
                                    @if($property->category->icon)
                                        <i class="{{ $property->category->icon }} text-xs"></i>
                                    @endif
                                    {{ $property->category->name }}
                                </span>
                            </div>
                            @endif

                            {{-- Badge de Ubicación --}}
                            <div class="absolute bottom-3 left-3">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-black/50 text-white backdrop-blur-sm flex items-center gap-1">
                                    <i class="ph ph-map-pin text-xs"></i>
                                    {{ $property->stateRelation->name ?? $property->cityRelation->name ?? 'Venezuela' }}
                                </span>
                            </div>

                            {{-- Botón Favorito --}}
                            <button class="favorite-btn absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center transition-colors shadow-md z-10 hover:scale-110
                                {{ auth()->check() ? '' : 'cursor-pointer' }}"
                                    data-property-id="{{ $property->id }}"
                                    onclick="event.preventDefault(); handleFavoriteClick(this, {{ $property->id }})">
                                <i class="favorite-icon {{ $isFavorited ? 'ph-fill text-red-500' : 'ph text-slate-500' }} ph-heart text-lg"></i>
                            </button>
                        </a>

                        {{-- Contenido --}}
                        <div class="p-4">
                            <h3 class="font-bold text-slate-800 text-base mb-1 line-clamp-1">
                                <a href="{{ route('catalogo.show', $property) }}" class="hover:text-mso-blue transition-colors">
                                    {{ $property->title }}
                                </a>
                            </h3>

                            <p class="text-slate-500 text-xs mb-3 flex items-center gap-1">
                                <i class="ph ph-map-pin text-mso-gold text-sm"></i>
                                <span class="truncate">
                                    {{ $property->cityRelation->name ?? $property->municipalityRelation->name ?? $property->stateRelation->name ?? 'Venezuela' }}
                                </span>
                            </p>

                            <p class="text-xl font-bold text-mso-gold mb-3">
                                {{ $property->formatted_price }}
                                @if($property->type == 'alquiler')
                                    <span class="text-xs font-normal text-slate-500">/mes</span>
                                @endif
                            </p>

                            {{-- Características --}}
                            <div class="flex flex-wrap items-center gap-2 text-slate-500 text-xs border-t border-slate-100 pt-3">
                                @if($property->bedrooms)
                                <span class="feature-tag">
                                    <i class="ph ph-bed text-mso-gold"></i> {{ $property->bedrooms }}
                                </span>
                                @endif
                                @if($property->bathrooms)
                                <span class="feature-tag">
                                    <i class="ph ph-shower text-mso-gold"></i> {{ $property->bathrooms }}
                                </span>
                                @endif
                                @if($property->parking_spaces)
                                <span class="feature-tag">
                                    <i class="ph ph-car text-mso-gold"></i> {{ $property->parking_spaces }}
                                </span>
                                @endif
                                @if($property->area)
                                <span class="feature-tag">
                                    <i class="ph ph-ruler text-mso-gold"></i> {{ $property->area }}m²
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center">
                            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="ph ph-house-line text-4xl text-slate-400"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-700 mb-2">No se encontraron propiedades</h3>
                            <p class="text-slate-500 text-sm mb-4">Intenta ajustar los filtros para ver más resultados.</p>
                            <a href="{{ route('catalogo.index') }}" class="inline-block bg-mso-blue text-white px-6 py-2.5 rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
                                <i class="ph ph-arrow-counter-clockwise mr-1"></i> Limpiar filtros
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>

                {{-- Paginación --}}
                @if($properties->hasPages())
                <div class="mt-8">
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

    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[90vh] overflow-y-auto transform transition-transform duration-300 translate-y-full" id="filtersModalContent">

        <div class="sticky top-0 bg-white z-10 px-5 py-4 border-b border-slate-200 rounded-t-3xl flex justify-between items-center">
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

        <div class="p-5">
            <form action="{{ route('catalogo.index') }}" method="GET" id="filter-form-modal" class="space-y-5">
                {{-- Tipo de Operación --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de Operación</label>
                    <select name="type" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                        <option value="">Todos los tipos</option>
                        <option value="venta" {{ request('type') == 'venta' ? 'selected' : '' }}>Venta</option>
                        <option value="alquiler" {{ request('type') == 'alquiler' ? 'selected' : '' }}>Alquiler</option>
                        <option value="venta/alquiler" {{ request('type') == 'venta/alquiler' ? 'selected' : '' }}>Ambos</option>
                    </select>
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Categoría</label>
                    <select name="category_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                @if($category->icon)
                                    <i class="{{ $category->icon }} mr-1"></i>
                                @endif
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Rango de Precio --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Rango de Precio</label>
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

                {{-- Ubicación --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ubicación</label>
                    <div class="space-y-2">
                        <select name="state_id" id="filter_state_modal" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm">
                            <option value="">Todos los estados</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="municipality_id" id="filter_municipality_modal" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('state_id') ? '' : 'disabled' }}>
                            <option value="">Todos los municipios</option>
                            @foreach($municipalities as $municipality)
                                <option value="{{ $municipality->id }}" {{ request('municipality_id') == $municipality->id ? 'selected' : '' }}>
                                    {{ $municipality->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="city_id" id="filter_city_modal" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent bg-white text-sm" {{ request('municipality_id') ? '' : 'disabled' }}>
                            <option value="">Todas las ciudades</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Características --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Características</label>
                    <div class="space-y-2">
                        <select name="bedrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                            <option value="">Cualquier nº de habitaciones</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>
                                    {{ $i }}+ habitaciones
                                </option>
                            @endfor
                        </select>
                        <select name="bathrooms" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                            <option value="">Cualquier nº de baños</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ request('bathrooms') == $i ? 'selected' : '' }}>
                                    {{ $i }}+ baños
                                </option>
                            @endfor
                        </select>
                        <select name="parking_spaces" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
                            <option value="">Cualquier nº de estacionamientos</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ request('parking_spaces') == $i ? 'selected' : '' }}>
                                    {{ $i }}+ estacionamientos
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Área --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Área (m²)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_area" value="{{ request('min_area') }}"
                               placeholder="Mínimo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                        <input type="number" name="max_area" value="{{ request('max_area') }}"
                               placeholder="Máximo" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                </div>

                {{-- Búsqueda --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Título, descripción o dirección..."
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm">
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
    <div class="modal-content bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden text-center p-8">
        <div class="mb-4">
            <span class="house-icon">🏠</span>
        </div>

        <h3 class="text-2xl font-bold text-slate-800 mb-2">
            ¡Guarda tus propiedades favoritas!
        </h3>
        <p class="text-slate-500 text-sm mb-6">
            Para poder guardar propiedades y recibir notificaciones de cambios de precio, necesitas tener una cuenta activa en nuestra plataforma.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('login') }}"
               class="w-full bg-mso-blue text-white text-center font-bold py-3.5 rounded-xl hover:bg-slate-800 transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="ph ph-sign-in text-lg"></i>
                Iniciar Sesión
            </a>
            <a href="{{ route('register') }}"
               class="w-full border-2 border-slate-200 text-slate-700 text-center font-medium py-3.5 rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                <i class="ph ph-user-plus text-lg"></i>
                Crear Cuenta Gratis
            </a>
        </div>

        <button onclick="closeAuthModal()"
                class="mt-6 text-sm text-slate-400 hover:text-slate-600 transition-colors">
            <i class="ph ph-x mr-1"></i> Seguir explorando
        </button>
    </div>
</div>

{{-- Toast Notifications --}}
<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@push('js')
<script>
// ============================================================
// MODAL DE AUTENTICACIÓN
// ============================================================
function openAuthModal() {
    const modal = document.getElementById('authModal');
    const content = modal.querySelector('.modal-content');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';

    setTimeout(() => {
        content.classList.add('show');
    }, 10);

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
    if (e.target === this) {
        closeAuthModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAuthModal();
    }
});

// ============================================================
// FAVORITOS
// ============================================================
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

    icon.className = 'favorite-icon ph ph-spinner ph-spin text-lg text-slate-500';

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
                icon.className = 'favorite-icon ph-fill text-red-500 ph-heart text-lg';
                showToast('✓ Propiedad agregada a favoritos', 'success');
            } else {
                icon.className = 'favorite-icon ph text-slate-500 ph-heart text-lg';
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

// ============================================================
// MODAL DE FILTROS
// ============================================================
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

// ============================================================
// FILTROS
// ============================================================
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

// ============================================================
// TOAST
// ============================================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
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

// ============================================================
// UBICACIONES DINÁMICAS
// ============================================================
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
            // Obtener parroquias del municipio primero
            fetch(`/api/locations/parishes/${municipalityId}`)
                .then(response => response.json())
                .then(parishes => {
                    // Luego obtener ciudades de esas parroquias
                    const parishIds = parishes.map(p => p.id);
                    if (parishIds.length > 0) {
                        // Llamar al endpoint que obtiene ciudades por parroquia
                        // Usamos la primera parroquia para obtener ciudades
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
