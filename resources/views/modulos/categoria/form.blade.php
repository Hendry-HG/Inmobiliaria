@extends('layouts.dashboard')

@section('title', isset($category) ? 'Editar Categoría' : 'Nueva Categoría')
@section('header', isset($category) ? 'Editar Categoría' : 'Crear Categoría')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ============================================= --}}
    {{-- VERIFICAR PERMISOS PARA ACCEDER AL FORMULARIO --}}
    {{-- ============================================= --}}
    @canany(['crear categoria', 'editar categoria'])

        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">

            <div class="p-6 bg-slate-50 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800">Información de la Categoría</h3>
            </div>

            <form action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
                  method="POST" class="p-6 space-y-6">
                @csrf
                @if(isset($category))
                    @method('PUT')
                @endif

                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-tag text-slate-400 mr-1"></i> Nombre de la Categoría *
                    </label>
                    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition"
                           placeholder="Ej: Casa, Apartamento, Oficina..." required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1">El slug se generará automáticamente a partir del nombre.</p>
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-align-left text-slate-400 mr-1"></i> Descripción
                    </label>
                    <textarea name="description" rows="3"
                              class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition"
                              placeholder="Breve descripción de la categoría...">{{ old('description', $category->description ?? '') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Icono -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-address-book text-slate-400 mr-1"></i> Icono (clase Phosphor)
                    </label>
                    <input type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition"
                           placeholder="Ej: ph-house, ph-buildings, ph-storefront...">
                    <div class="mt-2 flex items-center gap-3">
                        <span class="text-sm text-slate-500">Vista previa:</span>
                        <span id="icon-preview" class="text-2xl text-mso-gold">
                            <i class="{{ old('icon', $category->icon ?? 'ph-identification-card') }}"></i>
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Usa clases de <a href="https://phosphoricons.com/" target="_blank" class="text-mso-blue hover:underline">Phosphor Icons</a> (ej: ph-house, ph-buildings).</p>
                    @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Orden -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-list-numbers text-slate-400 mr-1"></i> Orden de Visualización
                    </label>
                    <input type="number" name="order" value="{{ old('order', $category->order ?? 0) }}"
                           class="w-32 border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold outline-none transition"
                           min="0">
                    @error('order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1">Número menor = aparece primero en la lista.</p>
                </div>

                <!-- Estado Activo -->
                <div>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                            class="rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                        <i class="ph ph-check-circle text-slate-400 mr-1"></i> Categoría activa
                    </label>
                    <p class="text-xs text-slate-400 mt-1">Las categorías inactivas no aparecerán en el formulario de propiedades.</p>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.categories.index') }}" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-mso-blue text-white px-8 py-2.5 rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-all hover:-translate-y-1">
                        {{ isset($category) ? 'Actualizar Categoría' : 'Crear Categoría' }}
                    </button>
                </div>
            </form>
        </div>

    @else
        {{-- ============================================= --}}
        {{-- MENSAJE DE ACCESO DENEGADO                    --}}
        {{-- ============================================= --}}
        <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
            <i class="ph ph-lock-simple text-3xl mb-2 block"></i>
            <p class="font-bold">Acceso Denegado</p>
            <p class="text-sm">No tienes permisos para {{ isset($category) ? 'editar' : 'crear' }} categorías.</p>
            <a href="{{ route('admin.categories.index') }}" class="text-mso-blue hover:underline mt-2 inline-block">
                Volver al listado de categorías
            </a>
        </div>
    @endcanany
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.querySelector('input[name="icon"]');
    const preview = document.getElementById('icon-preview');

    if (iconInput && preview) {
        iconInput.addEventListener('input', function() {
            preview.innerHTML = `<i class="${this.value || 'ph-identification-card'}"></i>`;
        });
    }
});
</script>
@endpush
@endsection