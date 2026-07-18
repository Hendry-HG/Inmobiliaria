@extends('layouts.dashboard')

@section('title', 'Mis Propiedades Favoritas')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                        Mis Propiedades Favoritas
                    </h1>
                    <p class="text-gray-600">
                        <span class="font-semibold">{{ $paginatedProperties->total() }}</span> propiedades guardadas
                    </p>
                </div>
                @if($paginatedProperties->count() > 0)
                <form action="{{ route('favorites.clear-all') }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar todas las propiedades de favoritos?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium flex items-center gap-2 shadow-sm">
                        <i class="ph ph-trash"></i> Eliminar todos
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Grid de propiedades favoritas --}}
        @if($paginatedProperties->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($paginatedProperties as $property)
                    <div class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300">

                        {{-- Imagen --}}
                        <a href="{{ route('catalogo.show', $property->id) }}" class="block relative h-48 overflow-hidden bg-gray-100">
                            <img src="{{ $property->primary_image_url }}"
                                 alt="{{ $property->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/800x600?text=Sin+Imagen'">

                            {{-- Badges --}}
                            <div class="absolute top-3 left-3 flex gap-2">
                                <span class="px-2.5 py-1 text-xs font-bold rounded shadow-sm
                                    @if($property->type == 'venta') bg-blue-600 text-white
                                    @elseif($property->type == 'alquiler') bg-green-600 text-white
                                    @else bg-purple-600 text-white @endif">
                                    {{ $property->type == 'venta' ? 'VENTA' : ($property->type == 'alquiler' ? 'ALQUILER' : 'VENTA/ALQ.') }}
                                </span>
                            </div>

                            {{-- Botón eliminar favorito --}}
                            <form action="{{ route('favorites.destroy', $property->id) }}" method="POST" class="absolute top-3 right-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-md"
                                        onclick="return confirm('¿Eliminar esta propiedad de favoritos?')"
                                        title="Eliminar de favoritos">
                                    <i class="ph-fill ph-heart text-base"></i>
                                </button>
                            </form>
                        </a>

                        {{-- Contenido --}}
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800 text-base mb-1 line-clamp-1">
                                <a href="{{ route('catalogo.show', $property->id) }}" class="hover:text-mso-blue transition-colors">
                                    {{ $property->title }}
                                </a>
                            </h3>

                            <p class="text-gray-500 text-xs mb-2 flex items-center gap-1">
                                <i class="ph ph-map-pin text-mso-gold text-sm"></i>
                                <span class="truncate">
                                    {{ $property->cityRelation->name ?? $property->municipalityRelation->name ?? $property->stateRelation->name ?? 'Venezuela' }}
                                </span>
                            </p>

                            <p class="text-xl font-bold text-mso-gold mb-2.5">
                                {{ $property->formatted_price }}
                                @if($property->type == 'alquiler')
                                    <span class="text-xs font-normal text-gray-500">/mes</span>
                                @endif
                            </p>

                            {{-- Características --}}
                            <div class="flex items-center flex-wrap gap-2 text-gray-500 text-xs border-t border-gray-100 pt-2.5">
                                @if($property->bedrooms)
                                <span class="flex items-center gap-1">
                                    <i class="ph ph-bed text-mso-gold"></i> {{ $property->bedrooms }}
                                </span>
                                @endif
                                @if($property->bathrooms)
                                <span class="flex items-center gap-1">
                                    <i class="ph ph-shower text-mso-gold"></i> {{ $property->bathrooms }}
                                </span>
                                @endif
                                @if($property->parking_spaces)
                                <span class="flex items-center gap-1">
                                    <i class="ph ph-car text-mso-gold"></i> {{ $property->parking_spaces }}
                                </span>
                                @endif
                                @if($property->area)
                                <span class="flex items-center gap-1">
                                    <i class="ph ph-ruler text-mso-gold"></i> {{ $property->area }}m²
                                </span>
                                @endif
                            </div>

                            {{-- Botón de acción --}}
                            <div class="mt-3">
                                <a href="{{ route('catalogo.show', $property->id) }}"
                                   class="w-full bg-mso-blue text-white text-center py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                                    <i class="ph ph-eye"></i> Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="mt-8">
                {{ $paginatedProperties->links() }}
            </div>
        @else
            {{-- Mensaje cuando no hay favoritos --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ph ph-heart-straight text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No tienes propiedades favoritas</h3>
                <p class="text-gray-500 mb-6">Explora nuestro catálogo y guarda las propiedades que más te gusten.</p>
                <a href="{{ route('catalogo.index') }}"
                   class="inline-flex items-center gap-2 bg-mso-blue text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors">
                    <i class="ph ph-buildings"></i>
                    Explorar Catálogo
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
