@extends('layouts.dashboard')

@section('title', isset($property) ? 'Editar Propiedad' : 'Nueva Propiedad')
@section('header', isset($property) ? 'Editar Propiedad' : 'Crear Nueva Propiedad')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- =============================================    --}}
    {{-- FORMULARIO - Solo con permisos de crear o editar --}}
    {{-- =============================================    --}}
    @canany(['crear propiedad', 'editar propiedad'])
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">

            <div class="p-6 bg-slate-50 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800">Información General</h3>
            </div>

            @php
                if (isset($property)) {
                    $storeRoute = (isset($isAdmin) && $isAdmin) ? route('admin.properties.update', $property) : route('asesor.properties.update', $property);
                } else {
                    $storeRoute = (isset($isAdmin) && $isAdmin) ? route('admin.properties.store') : route('asesor.properties.store');
                }
                $cancelRoute = (isset($isAdmin) && $isAdmin) ? route('admin.properties.index') : route('asesor.properties.index');
            @endphp

            <form action="{{ $storeRoute }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @if(isset($property))
                    @method('PUT')
                @endif

                {{-- Título y Descripción --}}
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="ph ph-text-t text-slate-400 mr-1"></i> Título de la Propiedad *
                        </label>
                        <input type="text" name="title" value="{{ old('title', $property->title ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" required>
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="ph ph-align-left text-slate-400 mr-1"></i> Descripción Completa *
                        </label>
                        <textarea name="description" rows="5" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" required>{{ old('description', $property->description ?? '') }}</textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Precio y Tipo --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="ph ph-currency-dollar text-slate-400 mr-1"></i> Precio (USD) *
                        </label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $property->price ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" required>
                        @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="ph ph-currency-circle-dollar text-slate-400 mr-1"></i> Moneda
                        </label>
                        <select name="price_currency" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white">
                            <option value="USD" {{ old('price_currency', $property->price_currency ?? 'USD') == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
                            <option value="EUR" {{ old('price_currency', $property->price_currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="VES" {{ old('price_currency', $property->price_currency ?? '') == 'VES' ? 'selected' : '' }}>VES - Bolívar</option>
                        </select>
                        @error('price_currency') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Tipo de Operación y Estado --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="ph ph-tag text-slate-400 mr-1"></i> Tipo de Operación *
                        </label>
                        <select name="type" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white" required>
                            <option value="venta" {{ old('type', $property->type ?? '') == 'venta' ? 'selected' : '' }}> Venta</option>
                            <option value="alquiler" {{ old('type', $property->type ?? '') == 'alquiler' ? 'selected' : '' }}> Alquiler</option>
                            <option value="venta/alquiler" {{ old('type', $property->type ?? '') == 'venta/alquiler' ? 'selected' : '' }}> Venta / Alquiler</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="ph ph-circle text-slate-400 mr-1"></i> Estado de Publicación *
                        </label>
                        <select name="status" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white" required>
                            <option value="borrador" {{ old('status', $property->status ?? '') == 'borrador' ? 'selected' : '' }}> Borrador</option>
                            <option value="pendiente" {{ old('status', $property->status ?? '') == 'pendiente' ? 'selected' : '' }}> Pendiente</option>
                            <option value="publicada" {{ old('status', $property->status ?? '') == 'publicada' ? 'selected' : '' }}>Publicada</option>
                            <option value="vendida" {{ old('status', $property->status ?? '') == 'vendida' ? 'selected' : '' }}> Vendida</option>
                            <option value="alquilada" {{ old('status', $property->status ?? '') == 'alquilada' ? 'selected' : '' }}> Alquilada</option>
                            <option value="inactiva" {{ old('status', $property->status ?? '') == 'inactiva' ? 'selected' : '' }}> Inactiva</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-folder text-slate-400 mr-1"></i> Categoría
                    </label>
                    <select name="category_id" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white">
                        <option value="">Seleccionar Categoría</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $property->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- ============================================ --}}
                {{-- UBICACIÓN COMPLETA                           --}}
                {{-- ============================================ --}}
                <div>
                    <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">
                        <i class="ph ph-map-pin text-slate-400 mr-2"></i> Ubicación
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">País *</label>
                            <select name="country_id" id="country_id" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white" required>
                                <option value="">Seleccionar país</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country_id', $property->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Estado *</label>
                            <select name="state_id" id="state_id" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white" {{ isset($property) && $property->country_id ? '' : 'disabled' }} required>
                                <option value="">Seleccionar estado</option>
                                @if(isset($states) && count($states) > 0)
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}" {{ old('state_id', $property->state_id ?? '') == $state->id ? 'selected' : '' }}>
                                            {{ $state->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('state_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Municipio *</label>
                            <select name="municipality_id" id="municipality_id" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white" {{ isset($property) && $property->state_id ? '' : 'disabled' }} required>
                                <option value="">Seleccionar municipio</option>
                                @if(isset($municipalities) && count($municipalities) > 0)
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality->id }}" {{ old('municipality_id', $property->municipality_id ?? '') == $municipality->id ? 'selected' : '' }}>
                                            {{ $municipality->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('municipality_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Parroquia</label>
                            <select name="parish_id" id="parish_id" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white" {{ isset($property) && $property->municipality_id ? '' : 'disabled' }}>
                                <option value="">Seleccionar parroquia</option>
                                @if(isset($parishes) && count($parishes) > 0)
                                    @foreach($parishes as $parish)
                                        <option value="{{ $parish->id }}" {{ old('parish_id', $property->parish_id ?? '') == $parish->id ? 'selected' : '' }}>
                                            {{ $parish->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('parish_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ciudad</label>
                            <select name="city_id" id="city_id" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition bg-white" {{ isset($property) && $property->parish_id ? '' : 'disabled' }}>
                                <option value="">Seleccionar ciudad</option>
                                @if(isset($cities) && count($cities) > 0)
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('city_id', $property->city_id ?? '') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('city_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                            <input type="text" name="address" value="{{ old('address', $property->address ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition"
                                   placeholder="Ej: Av. Principal, Edificio X, Piso 3">
                            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- CARACTERÍSTICAS --}}
                {{-- ============================================ --}}
                <div>
                    <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">
                        <i class="ph ph-house-line text-slate-400 mr-2"></i> Características
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                <i class="ph ph-bed text-slate-400 mr-1"></i> Habitaciones
                            </label>
                            <input type="number" name="bedrooms" value="{{ old('bedrooms', $property->bedrooms ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" min="0">
                            @error('bedrooms') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                <i class="ph ph-shower text-slate-400 mr-1"></i> Baños
                            </label>
                            <input type="number" name="bathrooms" value="{{ old('bathrooms', $property->bathrooms ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" min="0">
                            @error('bathrooms') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                <i class="ph ph-car text-slate-400 mr-1"></i> Estacionamientos
                            </label>
                            <input type="number" name="parking_spaces" value="{{ old('parking_spaces', $property->parking_spaces ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" min="0">
                            @error('parking_spaces') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                <i class="ph ph-ruler text-slate-400 mr-1"></i> Área (m²)
                            </label>
                            <input type="number" step="0.01" name="area" value="{{ old('area', $property->area ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" min="0">
                            @error('area') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                <i class="ph ph-arrows-out text-slate-400 mr-1"></i> Área de Terreno (m²)
                            </label>
                            <input type="number" step="0.01" name="land_area" value="{{ old('land_area', $property->land_area ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" min="0">
                            @error('land_area') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                <i class="ph ph-stairs text-slate-400 mr-1"></i> Pisos
                            </label>
                            <input type="number" name="floors" value="{{ old('floors', $property->floors ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" min="0">
                            @error('floors') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                <i class="ph ph-calendar text-slate-400 mr-1"></i> Año de Construcción
                            </label>
                            <input type="number" name="year_built" value="{{ old('year_built', $property->year_built ?? '') }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition" min="1900" max="{{ date('Y') }}">
                            @error('year_built') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- CARACTERÍSTICAS ADICIONALES (JSON) --}}
                {{-- ============================================ --}}
                <div>
                    <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">
                        <i class="ph ph-list-checks text-slate-400 mr-2"></i> Características Adicionales
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $features = old('features', $property->features ?? []);
                            if (is_string($features)) {
                                $features = json_decode($features, true) ?? [];
                            }
                            $featureOptions = [
                                'aire_acondicionado' => 'Aire Acondicionado',
                                'ascensor' => 'Ascensor',
                                'balcon' => 'Balcón',
                                'calefaccion' => 'Calefacción',
                                'camaras_seguridad' => 'Cámaras de Seguridad',
                                'casa_campo' => 'Casa de Campo',
                                'chimenea' => 'Chimenea',
                                'cisterna' => 'Cisterna',
                                'club_campo' => 'Club de Campo',
                                'gimnasio' => 'Gimnasio',
                                'jardin' => 'Jardín',
                                'piscina' => 'Piscina',
                                'placas_solares' => 'Placas Solares',
                                'playa' => 'Playa',
                                'porteria' => 'Portería',
                                'salon_eventos' => 'Salón de Eventos',
                                'terraza' => 'Terraza',
                                'vista_mar' => 'Vista al Mar',
                                'vista_montana' => 'Vista a la Montaña',
                                'zonas_verdes' => 'Zonas Verdes'
                            ];
                        @endphp

                        @foreach($featureOptions as $key => $label)
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="features[]" value="{{ $key }}"
                                    {{ (is_array($features) && in_array($key, $features)) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('features') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- ============================================ --}}
                {{-- IMÁGENES --}}
                {{-- ============================================ --}}
                <div>
                    <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">
                        <i class="ph ph-image text-slate-400 mr-2"></i> Imágenes de la Propiedad
                    </h4>
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 bg-slate-50">
                        <div class="mb-4">
                            <p class="text-sm text-slate-600">
                                <i class="ph ph-info mr-1"></i>
                                Mínimo 1 imagen, máximo 15. Formatos permitidos: JPG, PNG.
                                La <strong>primera imagen seleccionada</strong> será la foto de portada.
                            </p>
                        </div>

                        <div id="image-preview-container" class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4"></div>

                        <input type="file" name="images[]" id="images_input" multiple accept="image/jpeg,image/png,image/jpg"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">

                        <p id="image-error" class="text-red-500 text-xs mt-2 hidden"></p>

                        @if(isset($property) && $property->images && $property->images->count() > 0)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-slate-700 mb-2">Imágenes actuales:</p>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3" id="existing-images-container">
                                    @foreach($property->images as $img)
                                        <div class="relative group" data-image-id="{{ $img->id }}">
                                            <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-24 object-cover rounded-lg border border-slate-200">
                                            @if($img->is_primary)
                                                <span class="absolute top-1 left-1 bg-mso-gold text-xs text-black font-bold px-2 py-0.5 rounded">Portada</span>
                                            @endif
                                            <button type="button" onclick="deleteExistingImage({{ $img->id }}, this)"
                                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
                                                <i class="ph ph-x text-sm"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <input type="hidden" name="deleted_images" id="deleted_images" value="">
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- DESTACADA --}}
                {{-- ============================================ --}}
                <div>
                    <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">
                        <i class="ph ph-star text-slate-400 mr-2"></i> Destacada
                    </h4>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_featured" value="1"
                                {{ old('is_featured', $property->is_featured ?? false) ? 'checked' : '' }}
                                class="rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                            Marcar como propiedad destacada
                        </label>
                        @if(isset($property) && $property->featured_until)
                            <span class="text-xs text-slate-500">Hasta: {{ \Carbon\Carbon::parse($property->featured_until)->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de expiración (destacada)</label>
                        <input type="date" name="featured_until" value="{{ old('featured_until', isset($property) && $property->featured_until ? $property->featured_until->format('Y-m-d') : '') }}"
                               class="w-full md:w-64 border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition">
                        @error('featured_until') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                    <a href="{{ $cancelRoute }}" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" id="submit-btn" class="bg-mso-blue text-white px-8 py-2.5 rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-all hover:-translate-y-1">
                        {{ isset($property) ? 'Guardar Cambios' : 'Publicar Propiedad' }}
                    </button>
                </div>
            </form>
        </div>
    @else
        {{-- ============================================= --}}
        {{-- MENSAJE DE ACCESO DENEGADO --}}
        {{-- ============================================= --}}
        <div class="bg-red-50 border border-red-200 text-red-700 p-8 rounded-2xl text-center">
            <div class="flex flex-col items-center">
                <i class="ph ph-lock-simple text-5xl text-red-300 mb-4"></i>
                <h3 class="text-xl font-bold text-slate-800">Acceso Denegado</h3>
                <p class="text-slate-500 mt-2">No tienes permisos para {{ isset($property) ? 'editar' : 'crear' }} propiedades.</p>
                <p class="text-sm text-slate-400 mt-4">
                    <i class="ph ph-arrow-left mr-2"></i>
                    <a href="{{ route('admin.properties.index') }}" class="text-mso-blue hover:underline">
                        Volver al listado de propiedades
                    </a>
                </p>
            </div>
        </div>
    @endcanany
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === SELECTORES DE UBICACIÓN ===
    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const municipalitySelect = document.getElementById('municipality_id');
    const parishSelect = document.getElementById('parish_id');
    const citySelect = document.getElementById('city_id');

    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/locations/states/${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        stateSelect.disabled = false;
                        stateSelect.innerHTML = '<option value="">Seleccione un estado</option>';
                        states.forEach(state => {
                            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                        municipalitySelect.disabled = true;
                        municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                stateSelect.disabled = true;
                stateSelect.innerHTML = '<option value="">Primero seleccione un país</option>';
            }
        });
    }

    if (stateSelect) {
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            if (stateId) {
                fetch(`/api/locations/municipalities/${stateId}`)
                    .then(response => response.json())
                    .then(municipalities => {
                        municipalitySelect.disabled = false;
                        municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                        municipalities.forEach(municipality => {
                            municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
                        });
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                municipalitySelect.disabled = true;
                municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
            }
        });
    }

    if (municipalitySelect) {
        municipalitySelect.addEventListener('change', function() {
            const municipalityId = this.value;
            if (municipalityId) {
                fetch(`/api/locations/parishes/${municipalityId}`)
                    .then(response => response.json())
                    .then(parishes => {
                        parishSelect.disabled = false;
                        parishSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                        parishes.forEach(parish => {
                            parishSelect.innerHTML += `<option value="${parish.id}">${parish.name}</option>`;
                        });
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                parishSelect.disabled = true;
                parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
            }
        });
    }

    if (parishSelect) {
        parishSelect.addEventListener('change', function() {
            const parishId = this.value;
            if (parishId) {
                fetch(`/api/locations/cities/${parishId}`)
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.disabled = false;
                        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                        cities.forEach(city => {
                            citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                    });
            } else {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
            }
        });
    }

    // === GESTIÓN DE IMÁGENES ===
    const imageInput = document.getElementById('images_input');
    const previewContainer = document.getElementById('image-preview-container');
    const imageError = document.getElementById('image-error');
    const submitBtn = document.getElementById('submit-btn');
    const deletedImagesInput = document.getElementById('deleted_images');

    let selectedFiles = [];
    let deletedImages = [];

    document.querySelector('form')?.addEventListener('submit', function(e) {
        const existingImagesCount = document.querySelectorAll('#existing-images-container .relative').length;
        const totalImages = selectedFiles.length + existingImagesCount - deletedImages.length;

        if (totalImages < 1) {
            e.preventDefault();
            alert('Debes subir al menos 1 imagen de la propiedad.');
            return false;
        }

        if (totalImages > 15) {
            e.preventDefault();
            alert('Máximo 15 imágenes permitidas.');
            return false;
        }

        deletedImagesInput.value = deletedImages.join(',');
    });

    imageInput?.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const existingImagesCount = document.querySelectorAll('#existing-images-container .relative').length;
        const totalAfterAdd = selectedFiles.length + files.length + existingImagesCount - deletedImages.length;

        if (totalAfterAdd > 15) {
            imageError.textContent = 'Máximo 15 imágenes en total.';
            imageError.classList.remove('hidden');
            imageInput.value = '';
            return;
        }

        const invalidFiles = files.filter(f => !['image/jpeg', 'image/png', 'image/jpg'].includes(f.type));
        if (invalidFiles.length > 0) {
            imageError.textContent = 'Solo se permiten imágenes JPG y PNG.';
            imageError.classList.remove('hidden');
            imageInput.value = '';
            return;
        }

        const largeFiles = files.filter(f => f.size > 2 * 1024 * 1024);
        if (largeFiles.length > 0) {
            imageError.textContent = 'Cada imagen debe pesar menos de 2MB.';
            imageError.classList.remove('hidden');
            imageInput.value = '';
            return;
        }

        imageError.classList.add('hidden');

        files.forEach(file => {
            selectedFiles.push(file);
            addImagePreview(file);
        });

        updateFileInput();
    });

    function addImagePreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative group';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-24 object-cover rounded-lg border border-slate-200';

            const isFirst = previewContainer.children.length === 0;
            if (isFirst) {
                const badge = document.createElement('span');
                badge.className = 'absolute top-1 left-1 bg-mso-gold text-xs text-black font-bold px-2 py-0.5 rounded';
                badge.textContent = 'Portada';
                div.appendChild(badge);
            }

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors';
            removeBtn.innerHTML = '<i class="ph ph-x text-sm"></i>';
            removeBtn.onclick = function() {
                const index = Array.from(previewContainer.children).indexOf(div);
                selectedFiles.splice(index, 1);
                div.remove();
                updateFileInput();
                updatePortadaBadge();
            };

            div.appendChild(img);
            div.appendChild(removeBtn);
            previewContainer.appendChild(div);

            updatePortadaBadge();
        };
        reader.readAsDataURL(file);
    }

    function updatePortadaBadge() {
        const items = previewContainer.children;
        for (let i = 0; i < items.length; i++) {
            const existingBadge = items[i].querySelector('.bg-mso-gold');
            if (i === 0) {
                if (!existingBadge) {
                    const badge = document.createElement('span');
                    badge.className = 'absolute top-1 left-1 bg-mso-gold text-xs text-black font-bold px-2 py-0.5 rounded';
                    badge.textContent = 'Portada';
                    items[i].appendChild(badge);
                }
            } else {
                if (existingBadge) {
                    existingBadge.remove();
                }
            }
        }
    }

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;
    }

    window.deleteExistingImage = function(imageId, btn) {
        if (confirm('¿Eliminar esta imagen?')) {
            const container = btn.closest('.relative');
            const existingContainer = document.getElementById('existing-images-container');
            const remainingImages = existingContainer.querySelectorAll('.relative').length;

            if (remainingImages <= 1 && selectedFiles.length === 0) {
                alert('Debe haber al menos 1 imagen en la propiedad.');
                return;
            }

            deletedImages.push(imageId);
            container.remove();
        }
    };
});
</script>
@endpush
