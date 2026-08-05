{{-- Listado de configuraciones telefónicas por país --}}
@extends('layouts.dashboard')

@section('title', 'Configuración Telefónica por País')
@section('header', 'Formatos Telefónicos Internacionales')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ============================================= --}}
    {{-- HEADER - Solo con permiso                     --}}
    {{-- ============================================= --}}
    @canany(['ver configuración', 'actualizar configuracion telefonica'])
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Configuración Telefónica por País</h2>
                <p class="text-sm text-slate-500 mt-1">Define el formato, código y longitud de teléfono para cada país</p>
            </div>

            {{-- BOTÓN CARGAR PRESETS - Solo con permiso --}}
            @can('actualizar configuracion telefonica')
                <button onclick="openPresetModal()" class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors flex items-center gap-2 shadow-sm">
                    <i class="ph ph-download-simple text-lg"></i>
                    Cargar Numeros
                </button>
            @endcan
        </div>
    </div>
    @endcanany

    {{-- ============================================= --}}
    {{-- TABLA DE PAÍSES - Solo con permiso            --}}
    {{-- ============================================= --}}
    @canany(['ver configuración', 'actualizar configuracion telefonica'])
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="p-4">#</th>
                        <th class="p-4">País</th>
                        <th class="p-4">Código ISO</th>
                        <th class="p-4">Código Tel.</th>
                        <th class="p-4">Formato</th>
                        <th class="p-4">Longitud</th>
                        <th class="p-4">Vista Previa</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @php $counter = ($countries->currentPage() - 1) * $countries->perPage() + 1; @endphp
                    @forelse($countries as $country)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-slate-500">{{ $counter++ }}</td>
                        <td class="p-4 font-medium text-slate-800">
                            <div class="flex items-center gap-2">
                                <i class="ph ph-globe text-slate-400"></i>
                                {{ $country->name }}
                            </div>
                        </td>
                        <td class="p-4 text-slate-500">
                            <span class="px-2 py-1 bg-slate-100 rounded text-xs font-mono">
                                {{ $country->code ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-3 py-1 bg-mso-gold/20 text-mso-blue rounded-full text-sm font-mono font-bold">
                                {{ $country->phone_code ?? '+??' }}
                            </span>
                        </td>
                        <td class="p-4 font-mono text-slate-600">
                            {{ $country->phone_format ?? 'No configurado' }}
                        </td>
                        <td class="p-4 text-slate-500">
                            <span class="text-xs">
                                {{ $country->phone_min_length ?? '?' }} - {{ $country->phone_max_length ?? '?' }} dígitos
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="text-slate-400 text-xs font-mono bg-slate-50 px-2 py-1 rounded">
                                {{ $country->phone_code ?? '+58' }} {{ $country->phone_format ? str_replace('0', 'X', $country->phone_format) : 'XXX-XXXXXXX' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            {{-- EDITAR - Solo con permiso --}}
                            @can('actualizar configuracion telefonica')
                                <a href="{{ route('admin.phones.edit', $country) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-pencil"></i>
                                    Configurar
                                </a>
                            @else
                                <span class="text-slate-400 text-sm" title="No tienes permiso para editar">
                                    <i class="ph ph-lock-simple"></i>
                                </span>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500">
                            <i class="ph ph-globe text-4xl mb-2 block"></i>
                            No se encontraron países.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $countries->links() }}
        </div>
    </div>
    @else
    {{-- ============================================= --}}
    {{-- MENSAJE DE ACCESO DENEGADO                    --}}
    {{-- ============================================= --}}
    <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
        <i class="ph ph-lock-simple text-3xl mb-2 block"></i>
        <p class="font-bold">Acceso Denegado</p>
        <p class="text-sm">No tienes permisos para ver la configuración telefónica.</p>
    </div>
    @endcanany
</div>

{{-- ============================================= --}}
{{-- MODAL DE PRESETS - Solo con permiso           --}}
{{-- ============================================= --}}
@can('actualizar configuracion telefonica')
<div id="preset-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col">
        <div class="bg-mso-blue px-6 py-4 flex justify-between items-center flex-shrink-0">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ph ph-download-simple text-xl"></i>
                Cargar Numeros
            </h3>
            <button onclick="closePresetModal()" class="text-white hover:text-slate-200 transition-colors">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1" id="preset-content">
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="ph ph-spinner animate-spin text-3xl text-mso-gold mb-2"></i>
                    <p class="text-slate-500">Cargando Numeros...</p>
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 flex-shrink-0 flex justify-end">
            <button onclick="closePresetModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>
@endcan

@push('js')
<script>
// Datos de países para acceso rápido
const countriesData = @json($countries->keyBy('id'));

// Modal de Presets
async function openPresetModal() {
    const modal = document.getElementById('preset-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    try {
        const response = await fetch('/api/phone/presets');
        const presets = await response.json();

        let html = '';
        for (const [region, items] of Object.entries(presets)) {
            html += `<div class="mb-6">`;
            html += `<h4 class="font-bold text-slate-800 mb-3 capitalize border-b border-slate-200 pb-2 flex items-center gap-2">`;

            const regionIcons = {
                'america': 'ph-globe-hemisphere-west',
                'europa': 'ph-globe-hemisphere-east',
                'asia': 'ph-globe'
            };
            html += `<i class="ph ${regionIcons[region] || 'ph-globe'} text-mso-gold"></i>`;
            html += `${region}</h4>`;
            html += `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">`;

            items.forEach(preset => {
                html += `
                    <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 cursor-pointer transition-colors hover:border-mso-gold"
                         onclick="selectPreset('${preset.name}', '${preset.iso || ''}', '${preset.code}', '${preset.format}', ${preset.min}, ${preset.max})">
                        <div class="font-medium text-slate-800 flex items-center justify-between">
                            ${preset.name}
                            <span class="text-xs bg-mso-gold/20 text-mso-blue px-2 py-0.5 rounded-full font-mono">${preset.code}</span>
                        </div>
                        <div class="text-sm text-slate-500 font-mono mt-1">${preset.code} ${preset.format.replace(/0/g, 'X')}</div>
                        <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                            <i class="ph ph-ruler"></i>
                            ${preset.min}-${preset.max} dígitos
                        </div>
                    </div>
                `;
            });

            html += `</div></div>`;
        }

        html += `<div class="mt-4 p-4 bg-slate-50 rounded-lg text-center">
            <p class="text-sm text-slate-600">
                <i class="ph ph-info mr-1"></i>
                Haz clic en cualquier numero para copiar los valores. Luego puedes pegarlos en la página de edición del país.
            </p>
        </div>`;

        document.getElementById('preset-content').innerHTML = html;
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('preset-content').innerHTML = `
            <div class="text-center py-12">
                <i class="ph ph-warning-circle text-3xl text-red-500 mb-2"></i>
                <p class="text-red-500">Error al cargar los presets</p>
                <p class="text-sm text-slate-400 mt-1">Intenta recargar la página</p>
            </div>
        `;
    }
}

function selectPreset(name, iso, code, format, min, max) {
    const presetData = { name, iso, code, format, min, max };
    localStorage.setItem('selectedPhonePreset', JSON.stringify(presetData));

    const presetText = `${code} | ${format} | ${min}-${max} dígitos`;
    navigator.clipboard?.writeText(presetText).catch(() => {});

    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-up';
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="ph ph-check-circle text-xl"></i>
            <div>
                <p class="font-bold">Preset de ${name} copiado</p>
                <p class="text-sm opacity-90">${code} ${format.replace(/0/g, 'X')} (${min}-${max} dígitos)</p>
                <p class="text-xs opacity-75 mt-1">Disponible al editar cualquier país</p>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);

    closePresetModal();
}

function closePresetModal() {
    const modal = document.getElementById('preset-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closePresetModal();
});

document.getElementById('preset-modal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('preset-modal')) closePresetModal();
});


</script>

<style>
@keyframes slide-up {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>
@endpush
@endsection