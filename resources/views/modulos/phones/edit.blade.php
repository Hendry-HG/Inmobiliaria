@extends('layouts.dashboard')

@section('title', 'Configurar Teléfono - ' . $country->name)
@section('header', 'Configurar Formato Telefónico')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
        <span><i class="ph ph-check-circle mr-2"></i>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
        <span><i class="ph ph-warning-circle mr-2"></i>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
        <div class="bg-slate-50 p-6 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <i class="ph ph-phone text-2xl text-mso-gold"></i>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">{{ $country->name }}</h3>
                    <p class="text-sm text-slate-500">ID: {{ $country->id }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.phones.update', $country) }}" method="POST" class="p-6 space-y-6" autocomplete="off">
            @csrf
            @method('PUT')

            <!-- Código ISO -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    <i class="ph ph-identification-badge mr-1"></i>Código ISO
                </label>
                <input type="text"
                       name="code"
                       id="country_code"
                       value="{{ old('code', $country->code) }}"
                       placeholder="Ej: VEN, USA, ESP"
                       maxlength="3"
                       pattern="^[A-Z]{3}$"
                       title="Debe tener 3 letras mayúsculas"
                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-mso-gold outline-none font-mono text-lg uppercase @error('code') border-red-500 @enderror">
                <p class="text-xs text-slate-400 mt-1">Código ISO de 3 letras (Ej: VEN para Venezuela, USA para Estados Unidos, ESP para España)</p>
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Código Telefónico -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    <i class="ph ph-hash mr-1"></i>Código Telefónico *
                </label>
                <input type="text"
                       name="phone_code"
                       id="phone_code"
                       value="{{ old('phone_code', $country->phone_code) }}"
                       placeholder="+58"
                       required
                       pattern="^\+[0-9]{1,4}$"
                       title="Debe comenzar con + seguido de 1-4 números"
                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-mso-gold outline-none text-lg font-mono @error('phone_code') border-red-500 @enderror">
                <p class="text-xs text-slate-400 mt-1">Ejemplos: +58, +1, +34, +52</p>
                @error('phone_code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Formato de Máscara -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    <i class="ph ph-text-aa mr-1"></i>Formato de Máscara *
                </label>
                <input type="text"
                       name="phone_format"
                       id="phone_format"
                       value="{{ old('phone_format', $country->phone_format) }}"
                       placeholder="000-0000000"
                       required
                       pattern="^[0-9\-\(\)\s\+]+$"
                       title="Solo números, guiones, paréntesis y espacios"
                       class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-mso-gold outline-none font-mono text-lg @error('phone_format') border-red-500 @enderror">
                <p class="text-xs text-slate-400 mt-1">Usa el número <strong>0</strong> para representar cada dígito. Los demás caracteres se mantienen como separadores.</p>
                @error('phone_format')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Longitudes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="ph ph-arrow-down mr-1"></i>Longitud Mínima *
                    </label>
                    <input type="number"
                           name="phone_min_length"
                           id="phone_min_length"
                           value="{{ old('phone_min_length', $country->phone_min_length ?? 7) }}"
                           min="5"
                           max="15"
                           required
                           class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-mso-gold outline-none @error('phone_min_length') border-red-500 @enderror">
                    <p class="text-xs text-slate-400 mt-1">Mínimo de dígitos requeridos</p>
                    @error('phone_min_length')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="ph ph-arrow-up mr-1"></i>Longitud Máxima *
                    </label>
                    <input type="number"
                           name="phone_max_length"
                           id="phone_max_length"
                           value="{{ old('phone_max_length', $country->phone_max_length ?? 15) }}"
                           min="5"
                           max="15"
                           required
                           class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-mso-gold outline-none @error('phone_max_length') border-red-500 @enderror">
                    <p class="text-xs text-slate-400 mt-1">Máximo de dígitos permitidos</p>
                    @error('phone_max_length')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider">Vista Previa del Formato</p>
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-mono font-bold text-mso-blue" id="preview-code">{{ old('phone_code', $country->phone_code ?? '+58') }}</span>
                    <span class="text-2xl font-mono text-slate-600" id="preview-format">
                        {{ old('phone_format', $country->phone_format) ? str_replace('0', 'X', old('phone_format', $country->phone_format)) : 'XXX-XXXXXXX' }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    <i class="ph ph-info mr-1"></i>
                    Las X representan los dígitos que el usuario deberá ingresar
                </p>
            </div>

            <!-- Ejemplos comunes -->
            <div class="border-t border-slate-100 pt-4">
                <p class="text-sm font-medium text-slate-700 mb-3">Formatos comunes por país:</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="applyFormat('000-0000000')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-mono transition-colors" title="Venezuela">🇻🇪 000-0000000</button>
                    <button type="button" onclick="applyFormat('000-000-0000')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-mono transition-colors" title="USA/Colombia">🇺🇸 000-000-0000</button>
                    <button type="button" onclick="applyFormat('000-000-000')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-mono transition-colors" title="España">🇪🇸 000-000-000</button>
                    <button type="button" onclick="applyFormat('0-0000-0000')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-mono transition-colors" title="Chile">🇨🇱 0-0000-0000</button>
                    <button type="button" onclick="applyFormat('(00) 00000-0000')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-mono transition-colors" title="Brasil">🇧🇷 (00) 00000-0000</button>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.phones.index') }}"
                   class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-mso-blue text-white px-8 py-2.5 rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-transform hover:-translate-y-1">
                    <i class="ph ph-check mr-1"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <!-- Panel informativo -->
    <div class="mt-6 bg-blue-50 border border-blue-100 rounded-lg p-4">
        <h4 class="font-medium text-blue-800 mb-2 flex items-center gap-2">
            <i class="ph ph-info"></i>
            Información sobre formatos telefónicos
        </h4>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• El <strong>código ISO</strong> es el identificador internacional del país (3 letras).</li>
            <li>• El <strong>código telefónico</strong> debe incluir el signo <strong>+</strong> seguido del código del país.</li>
            <li>• La <strong>máscara</strong> define cómo se mostrará el número mientras el usuario escribe.</li>
            <li>• La <strong>longitud</strong> se refiere solo a los dígitos locales (sin el código de país).</li>
        </ul>
    </div>
</div>

@push('js')
<script>
// Elementos del DOM
const codeInput = document.getElementById('phone_code');
const formatInput = document.getElementById('phone_format');
const previewCode = document.getElementById('preview-code');
const previewFormat = document.getElementById('preview-format');
const minInput = document.getElementById('phone_min_length');
const maxInput = document.getElementById('phone_max_length');
const isoInput = document.getElementById('country_code');

// Convertir ISO a mayúsculas automáticamente
isoInput?.addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
});

// Actualizar preview en tiempo real
function updatePreview() {
    const code = codeInput.value || '+58';
    const format = formatInput.value || '000-0000000';

    previewCode.textContent = code.startsWith('+') ? code : '+' + code;
    previewFormat.textContent = format.replace(/0/g, 'X');
}

codeInput?.addEventListener('input', updatePreview);
formatInput?.addEventListener('input', updatePreview);

// Aplicar formato predefinido
function applyFormat(format) {
    formatInput.value = format;
    updatePreview();
}

// Validación de longitudes
function validateLengths() {
    const min = parseInt(minInput.value) || 7;
    const max = parseInt(maxInput.value) || 15;

    if (min > max) {
        maxInput.value = min;
    }
}

minInput?.addEventListener('input', validateLengths);
maxInput?.addEventListener('input', validateLengths);

// Verificar si hay un preset guardado en localStorage al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const savedPreset = localStorage.getItem('selectedPhonePreset');

    if (savedPreset) {
        try {
            const preset = JSON.parse(savedPreset);

            // Mostrar notificación para aplicar el preset
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-mso-gold text-mso-blue px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-up';
            toast.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="ph ph-download-simple text-xl"></i>
                    <div>
                        <p class="font-bold">¿Aplicar preset de ${preset.name}?</p>
                        <p class="text-sm opacity-90">${preset.code} ${preset.format.replace(/0/g, 'X')} (${preset.min}-${preset.max} dígitos)</p>
                        <div class="flex gap-2 mt-2">
                            <button onclick="applyPresetAndClose('${preset.iso || ''}', '${preset.code}', '${preset.format}', ${preset.min}, ${preset.max})"
                                    class="bg-mso-blue text-white px-3 py-1 rounded text-sm">Aplicar</button>
                            <button onclick="this.closest('.fixed').remove(); localStorage.removeItem('selectedPhonePreset');"
                                    class="bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm">Cancelar</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);

            // Auto-ocultar después de 15 segundos
            setTimeout(() => {
                toast.remove();
                localStorage.removeItem('selectedPhonePreset');
            }, 15000);

        } catch (e) {
            console.error('Error al leer preset:', e);
        }
    }

    // Inicializar preview
    updatePreview();
});

// Función para aplicar el preset y cerrar el toast
function applyPresetAndClose(iso, code, format, min, max) {
    // Aplicar valores
    if (iso && isoInput) isoInput.value = iso;
    codeInput.value = code;
    formatInput.value = format;
    minInput.value = min;
    maxInput.value = max;

    // Actualizar preview
    updatePreview();

    // Limpiar localStorage
    localStorage.removeItem('selectedPhonePreset');

    // Cerrar el toast
    const toast = document.querySelector('.fixed.bottom-4.right-4');
    if (toast) toast.remove();

    // Mostrar confirmación
    const confirmToast = document.createElement('div');
    confirmToast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-up';
    confirmToast.innerHTML = '<i class="ph ph-check-circle mr-2"></i>Preset aplicado correctamente';
    document.body.appendChild(confirmToast);
    setTimeout(() => confirmToast.remove(), 3000);
}

// Mostrar mensaje de éxito si existe
@if(session('success'))
    const successToast = document.createElement('div');
    successToast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-up';
    successToast.innerHTML = '<i class="ph ph-check-circle mr-2"></i>{{ session("success") }}';
    document.body.appendChild(successToast);
    setTimeout(() => successToast.remove(), 4000);
@endif
</script>

<style>
@keyframes slide-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>
@endpush
@endsection
