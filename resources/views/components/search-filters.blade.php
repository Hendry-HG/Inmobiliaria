{{-- Filtros de búsqueda de propiedades en la landing page --}}
@props(['states' => []])

<div class="relative z-30 -mt-8 px-4 lg:px-0 max-w-6xl mx-auto">
    <div class="glass-filters rounded-2xl p-4 sm:p-6">
        <form action="{{ route('catalogo.index') }}" method="GET" id="quick-filter-form" autocomplete="off">

            {{-- Grid responsive para los filtros --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

                {{-- Estado --}}
                <div class="relative">
                    <select name="state_id" id="quick_state_id" autocomplete="off" aria-label="Estado"
                            class="w-full pl-10 pr-8 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none transition-all appearance-none">
                        <option value="">Todos los estados</option>
                        @if(count($states) > 0)
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }} ({{ $state->properties_count ?? 0 }})
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>No hay estados disponibles</option>
                        @endif
                    </select>
                    <i class="ph ph-map-pin-area absolute left-3 top-1/2 -translate-y-1/2 text-mso-gold text-lg pointer-events-none"></i>
                    <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                </div>

                {{-- Precio Mínimo --}}
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="ph ph-currency-dollar text-base"></i>
                    </span>
                    <input type="number"
                           name="min_price"
                           id="quick_min_price"
                           value="{{ e(request('min_price')) }}"
                           placeholder="Precio mínimo"
                           autocomplete="off"
                           min="0"
                           step="1000"
                           class="w-full pl-9 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none transition-all">
                </div>

                {{-- Precio Máximo --}}
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="ph ph-currency-dollar text-base"></i>
                    </span>
                    <input type="number"
                           name="max_price"
                           id="quick_max_price"
                           value="{{ e(request('max_price')) }}"
                           placeholder="Precio máximo"
                           autocomplete="off"
                           min="0"
                           step="1000"
                           class="w-full pl-9 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none transition-all">
                </div>

                {{-- Botones --}}
                <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit"
                            class="w-full bg-mso-blue text-white px-4 py-3 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all flex items-center justify-center gap-2 shadow-md">
                        <i class="ph ph-magnifying-glass text-base"></i>
                        <span class="hidden xs:inline">Buscar</span>
                    </button>

                    <button type="button"
                            onclick="limpiarFiltrosRapidos()"
                            class="w-full border border-slate-300 text-slate-600 px-4 py-3 rounded-xl text-sm font-medium hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                        <i class="ph ph-x-circle text-base"></i>
                        <span class="hidden xs:inline">Limpiar</span>
                    </button>
                </div>

            </div>

            {{-- Filtros activos (tags) --}}
            @php
                $activeFilters = [];
                if(request('state_id')) {
                    $state = $states->firstWhere('id', request('state_id'));
                    if($state) $activeFilters[] = ['label' => 'Estado: ' . e($state->name), 'param' => 'state_id'];
                }
                if(request('min_price')) {
                    $activeFilters[] = ['label' => 'Desde: $' . number_format(request('min_price'), 0, ',', '.'), 'param' => 'min_price'];
                }
                if(request('max_price')) {
                    $activeFilters[] = ['label' => 'Hasta: $' . number_format(request('max_price'), 0, ',', '.'), 'param' => 'max_price'];
                }
            @endphp

            @if(count($activeFilters) > 0)
                <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-slate-200">
                    <span class="text-xs text-slate-500 font-medium mr-1">Filtros activos:</span>
                    @foreach($activeFilters as $filter)
                        <span class="inline-flex items-center gap-1 bg-mso-gold/10 text-mso-blue text-xs px-3 py-1.5 rounded-full">
                            {{ $filter['label'] }}
                            <button type="button"
                                    onclick="eliminarFiltro('{{ $filter['param'] }}')"
                                    class="hover:text-red-500 transition-colors ml-1">
                                <i class="ph ph-x text-xs font-bold"></i>
                            </button>
                        </span>
                    @endforeach
                    <button type="button"
                            onclick="limpiarFiltrosRapidos()"
                            class="text-xs text-red-500 hover:text-red-700 font-medium ml-1">
                        Limpiar todos
                    </button>
                </div>
            @endif

        </form>
    </div>
</div>

<script>
function limpiarFiltrosRapidos() {
    // Limpiar los campos del formulario
    document.querySelector('select[name="state_id"]').value = '';
    document.getElementById('quick_min_price').value = '';
    document.getElementById('quick_max_price').value = '';

    // Obtener los parámetros actuales de la URL
    const urlParams = new URLSearchParams(window.location.search);

    // Eliminar solo los filtros del quick-filter
    urlParams.delete('state_id');
    urlParams.delete('min_price');
    urlParams.delete('max_price');
    urlParams.delete('page');

    // Construir nueva URL
    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');

    // Redirigir
    window.location.href = newUrl;
}

function eliminarFiltro(param) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.delete(param);
    urlParams.delete('page');

    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
    window.location.href = newUrl;
}

// Validación de precios
document.getElementById('quick-filter-form')?.addEventListener('submit', function(e) {
    const minPrice = parseFloat(document.getElementById('quick_min_price')?.value) || 0;
    const maxPrice = parseFloat(document.getElementById('quick_max_price')?.value) || Infinity;

    if (minPrice > maxPrice && maxPrice > 0) {
        e.preventDefault();
        alert('El precio mínimo no puede ser mayor que el precio máximo');
        return false;
    }
});

// Auto-submit al cambiar el estado (opcional)
document.querySelector('select[name="state_id"]')?.addEventListener('change', function() {
    document.getElementById('quick-filter-form').submit();
});
</script>

<style>
    /* Quitar flechas del input number */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }

    /* Estilos para móvil muy pequeño */
    @media (max-width: 480px) {
        .xs\:inline {
            display: inline !important;
        }
        .glass-filters {
            padding: 12px;
        }
        .glass-filters select,
        .glass-filters input {
            font-size: 14px;
            padding-top: 12px;
            padding-bottom: 12px;
        }
    }

    /* Estilos para tablet */
    @media (min-width: 481px) and (max-width: 768px) {
        .glass-filters {
            padding: 16px;
        }
    }

    /* Select personalizado sin flecha nativa */
    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Efecto hover en los botones */
    .glass-filters button[type="submit"]:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 25px -5px rgba(15, 23, 42, 0.2);
    }

    .glass-filters button[type="button"]:hover {
        transform: translateY(-1px);
    }

    /* Animación de los tags de filtros activos */
    .glass-filters .inline-flex {
        animation: fadeInScale 0.2s ease-out;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
