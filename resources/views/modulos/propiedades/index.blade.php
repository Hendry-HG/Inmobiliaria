@extends('layouts.dashboard')

@section('title', 'Gestión de Propiedades')
@section('header', 'Inventario de Propiedades')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header y Filtros -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                <h2 class="text-xl font-bold text-slate-800">Listado de Inmuebles</h2>

                {{-- ============================================= --}}
                {{-- BOTÓN NUEVA PROPIEDAD - Solo con permiso --}}
                {{-- ============================================= --}}
                @can('crear propiedad')
                    @php
                        $createRoute = (isset($isAdmin) && $isAdmin) ? route('admin.properties.create') : route('asesor.properties.create');
                    @endphp
                    <a href="{{ $createRoute }}"
                       class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors shadow-sm flex items-center gap-2">
                        <i class="ph ph-plus-circle text-lg"></i> Nueva Propiedad
                    </a>
                @else
                    <span class="text-sm text-slate-400 flex items-center gap-2">
                        <i class="ph ph-lock-simple"></i>
                        No tienes permiso para crear propiedades
                    </span>
                @endcan
            </div>

            <form action="{{ request()->url() }}" method="GET" class="space-y-3">
                <!-- Búsqueda -->
                <div class="flex flex-wrap gap-3">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Buscar por título, descripción o dirección..."
                           class="flex-1 min-w-[200px] border rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-mso-gold outline-none">

                    <select name="type" class="border rounded-lg px-4 py-2 text-sm bg-white">
                        <option value="">Todos los Tipos</option>
                        <option value="venta" {{ request('type') == 'venta' ? 'selected' : '' }}>Venta</option>
                        <option value="alquiler" {{ request('type') == 'alquiler' ? 'selected' : '' }}>Alquiler</option>
                        <option value="venta/alquiler" {{ request('type') == 'venta/alquiler' ? 'selected' : '' }}>Venta/Alquiler</option>
                    </select>

                    <select name="status" class="border rounded-lg px-4 py-2 text-sm bg-white">
                        <option value="">Todos los Estados</option>
                        <option value="borrador" {{ request('status') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                        <option value="pendiente" {{ request('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="publicada" {{ request('status') == 'publicada' ? 'selected' : '' }}>Publicada</option>
                        <option value="vendida" {{ request('status') == 'vendida' ? 'selected' : '' }}>Vendida</option>
                        <option value="alquilada" {{ request('status') == 'alquilada' ? 'selected' : '' }}>Alquilada</option>
                    </select>
                </div>

                <!-- Filtros de Ubicación -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <select name="country_id" id="filter_country" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold bg-white">
                        <option value="">Todos los Países</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="state_id" id="filter_state" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold bg-white" {{ request('country_id') ? '' : 'disabled' }}>
                        <option value="">Todos los Estados</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="municipality_id" id="filter_municipality" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold bg-white" {{ request('state_id') ? '' : 'disabled' }}>
                        <option value="">Todos los Municipios</option>
                        @foreach($municipalities as $municipality)
                            <option value="{{ $municipality->id }}" {{ request('municipality_id') == $municipality->id ? 'selected' : '' }}>
                                {{ $municipality->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="parish_id" id="filter_parish" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold bg-white" {{ request('municipality_id') ? '' : 'disabled' }}>
                        <option value="">Todas las Parroquias</option>
                        @foreach($parishes as $parish)
                            <option value="{{ $parish->id }}" {{ request('parish_id') == $parish->id ? 'selected' : '' }}>
                                {{ $parish->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="city_id" id="filter_city" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold bg-white" {{ request('parish_id') ? '' : 'disabled' }}>
                        <option value="">Todas las Ciudades</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtros adicionales -->
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Precio Mínimo</label>
                        <input type="number" name="min_price" value="{{ request('min_price') }}"
                               placeholder="$ Min" class="border rounded-lg px-4 py-2 text-sm w-32">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Precio Máximo</label>
                        <input type="number" name="max_price" value="{{ request('max_price') }}"
                               placeholder="$ Max" class="border rounded-lg px-4 py-2 text-sm w-32">
                    </div>

                    @can('ver usuarios')
                        @if(isset($isAdmin) && $isAdmin && isset($asesores) && $asesores->count() > 0)
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Asesor</label>
                                <select name="user_id" class="border rounded-lg px-4 py-2 text-sm bg-white">
                                    <option value="">Todos los Asesores</option>
                                    @foreach($asesores as $asesor)
                                        <option value="{{ $asesor->id }}" {{ request('user_id') == $asesor->id ? 'selected' : '' }}>
                                            {{ $asesor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endcan

                    <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-slate-700">
                        <i class="ph ph-funnel mr-1"></i> Filtrar
                    </button>
                    <a href="{{ request()->url() }}" class="border border-slate-300 text-slate-600 px-6 py-2 rounded-lg text-sm font-medium hover:bg-slate-50">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        {{-- ============================================= --}}
        {{-- TABLA - Solo con permiso --}}
        {{-- ============================================= --}}
        @canany(['ver propiedades', 'gestionar propiedades'])
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="p-4">Propiedad</th>
                                <th class="p-4">Precio</th>
                                <th class="p-4">Ubicación</th>
                                <th class="p-4">Estado</th>
                                @can('ver usuarios')
                                    @if(isset($isAdmin) && $isAdmin)
                                        <th class="p-4">Asesor</th>
                                    @endif
                                @endcan
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>

                        <tbody
                            x-data="{
                                rowsHtml: @js(View::make('modulos.propiedades._rows', ['properties' => $properties, 'isAdmin' => $isAdmin ?? false, 'isAsesor' => $isAsesor ?? false])->render()),
                                loading: false
                            }"
                            x-init="initTablePolling()"
                            x-html="rowsHtml"
                            class="divide-y divide-slate-100 text-sm relative transition-opacity duration-300"
                            :class="loading ? 'opacity-50' : 'opacity-100'"
                        >
                            <div x-show="loading" x-cloak class="absolute inset-0 bg-white/30 flex items-center justify-center z-10 backdrop-blur-sm pointer-events-none">
                                <div class="bg-white p-2 rounded-lg shadow-lg border border-slate-100">
                                    <i class="ph ph-spinner animate-spin text-xl text-mso-gold"></i>
                                </div>
                            </div>
                        </tbody>

                    </table>
                </div>
                <div class="p-4 border-t border-slate-100 flex justify-center">
                    {{ $properties->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
                <i class="ph ph-lock-simple text-3xl mb-2 block"></i>
                <p class="font-bold">Acceso Denegado</p>
                <p class="text-sm">No tienes permisos para ver las propiedades.</p>
            </div>
        @endcanany
    </div>

    <!-- MODAL DE CONFIRMACIÓN PARA ELIMINAR -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
                <div class="p-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <i class="ph ph-trash text-3xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 text-center mb-2">¿Eliminar propiedad?</h3>
                    <p class="text-sm text-slate-500 text-center mb-6">
                        ¿Estás seguro de eliminar la propiedad "<span id="propertyTitle" class="font-semibold text-slate-700"></span>"?
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
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterCountry = document.getElementById('filter_country');
    const filterState = document.getElementById('filter_state');
    const filterMunicipality = document.getElementById('filter_municipality');
    const filterParish = document.getElementById('filter_parish');
    const filterCity = document.getElementById('filter_city');

    if (filterCountry) {
        filterCountry.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/locations/states/${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        filterState.disabled = false;
                        filterState.innerHTML = '<option value="">Todos los Estados</option>';
                        states.forEach(state => {
                            filterState.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                        filterMunicipality.disabled = true;
                        filterMunicipality.innerHTML = '<option value="">Todos los Municipios</option>';
                        filterParish.disabled = true;
                        filterParish.innerHTML = '<option value="">Todas las Parroquias</option>';
                        filterCity.disabled = true;
                        filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
                    });
            } else {
                filterState.disabled = true;
                filterState.innerHTML = '<option value="">Todos los Estados</option>';
                filterMunicipality.disabled = true;
                filterMunicipality.innerHTML = '<option value="">Todos los Municipios</option>';
                filterParish.disabled = true;
                filterParish.innerHTML = '<option value="">Todas las Parroquias</option>';
                filterCity.disabled = true;
                filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
            }
        });
    }

    if (filterState) {
        filterState.addEventListener('change', function() {
            const stateId = this.value;
            if (stateId) {
                fetch(`/api/locations/municipalities/${stateId}`)
                    .then(response => response.json())
                    .then(municipalities => {
                        filterMunicipality.disabled = false;
                        filterMunicipality.innerHTML = '<option value="">Todos los Municipios</option>';
                        municipalities.forEach(municipality => {
                            filterMunicipality.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
                        });
                        filterParish.disabled = true;
                        filterParish.innerHTML = '<option value="">Todas las Parroquias</option>';
                        filterCity.disabled = true;
                        filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
                    });
            } else {
                filterMunicipality.disabled = true;
                filterMunicipality.innerHTML = '<option value="">Todos los Municipios</option>';
                filterParish.disabled = true;
                filterParish.innerHTML = '<option value="">Todas las Parroquias</option>';
                filterCity.disabled = true;
                filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
            }
        });
    }

    if (filterMunicipality) {
        filterMunicipality.addEventListener('change', function() {
            const municipalityId = this.value;
            if (municipalityId) {
                fetch(`/api/locations/parishes/${municipalityId}`)
                    .then(response => response.json())
                    .then(parishes => {
                        filterParish.disabled = false;
                        filterParish.innerHTML = '<option value="">Todas las Parroquias</option>';
                        parishes.forEach(parish => {
                            filterParish.innerHTML += `<option value="${parish.id}">${parish.name}</option>`;
                        });
                        filterCity.disabled = true;
                        filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
                    });
            } else {
                filterParish.disabled = true;
                filterParish.innerHTML = '<option value="">Todas las Parroquias</option>';
                filterCity.disabled = true;
                filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
            }
        });
    }

    if (filterParish) {
        filterParish.addEventListener('change', function() {
            const parishId = this.value;
            if (parishId) {
                fetch(`/api/locations/cities/${parishId}`)
                    .then(response => response.json())
                    .then(cities => {
                        filterCity.disabled = false;
                        filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
                        cities.forEach(city => {
                            filterCity.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                    });
            } else {
                filterCity.disabled = true;
                filterCity.innerHTML = '<option value="">Todas las Ciudades</option>';
            }
        });
    }

    function initTablePolling() {
        setInterval(() => {
            this.loading = true;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('ajax', '1');
            fetch(currentUrl.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        this.rowsHtml = data.html;
                    }
                })
                .catch(error => console.error('Error actualizando tabla:', error))
                .finally(() => {
                    this.loading = false;
                });
        }, 10000);
    }
});

// FUNCIONES DEL MODAL
let deletePropertyId = null;
let deleteRoute = '';

function openDeleteModal(id, title, route) {
    deletePropertyId = id;
    deleteRoute = route;
    document.getElementById('propertyTitle').textContent = title;
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
        deletePropertyId = null;
        deleteRoute = '';
    }, 300);
}

function confirmDelete() {
    if (!deletePropertyId || !deleteRoute) return;
    const form = document.getElementById('delete-form');
    form.action = deleteRoute;
    form.submit();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});

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
