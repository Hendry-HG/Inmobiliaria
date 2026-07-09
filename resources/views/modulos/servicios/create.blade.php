@extends('layouts.dashboard')

@section('title', 'Crear Servicio')
@section('header', 'Nuevo Servicio')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="ph ph-handshake text-mso-gold"></i>
                Crear Nuevo Servicio
            </h3>
            <p class="text-sm text-slate-500 mt-1">Completa la información del nuevo servicio</p>
        </div>

        <form action="{{ route('admin.servicios.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            {{-- Título --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Título del Servicio <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold @error('title') border-red-500 @enderror"
                       placeholder="Ej: Tasación de Inmuebles" required>
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <textarea name="description" rows="4"
                          class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold @error('description') border-red-500 @enderror"
                          placeholder="Describe el servicio que ofreces...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Icono --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Icono (Clase CSS)</label>
                <input type="text" name="icon" value="{{ old('icon', 'ph ph-house') }}"
                       class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold @error('icon') border-red-500 @enderror"
                       placeholder="ph ph-house">
                <p class="text-xs text-slate-400 mt-1">Ej: ph ph-house, ph ph-buildings, ph ph-handshake</p>
                @error('icon')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Color --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color" value="{{ old('color', '#c5a059') }}"
                           class="w-12 h-12 rounded-lg border border-slate-300 cursor-pointer @error('color') border-red-500 @enderror">
                    <input type="text" name="color_text" value="{{ old('color', '#c5a059') }}"
                           class="flex-1 border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold"
                           placeholder="#c5a059">
                </div>
                @error('color')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Badge --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Badge (Etiqueta)</label>
                <input type="text" name="badge" value="{{ old('badge') }}"
                       class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold @error('badge') border-red-500 @enderror"
                       placeholder="Ej: Destacado, Nuevo, Popular">
                @error('badge')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- URL Externa --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">URL Externa (opcional)</label>
                <input type="url" name="external_url" value="{{ old('external_url') }}"
                       class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold @error('external_url') border-red-500 @enderror"
                       placeholder="https://ejemplo.com/servicio">
                @error('external_url')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Características (Features) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Características del Servicio</label>
                <div id="features-container" class="space-y-2">
                    <div class="flex items-center gap-2 feature-item">
                        <input type="text" name="features[]" value="{{ old('features.0') }}"
                               class="flex-1 border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold"
                               placeholder="Ej: Asesoría personalizada">
                        <button type="button" onclick="removeFeature(this)" class="text-red-500 hover:text-red-700 p-2">
                            <i class="ph ph-x-circle text-xl"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addFeature()" class="mt-2 text-sm text-mso-gold hover:underline font-semibold">
                    <i class="ph ph-plus"></i> Agregar característica
                </button>
                @error('features')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Imagen Principal --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Imagen Principal</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-mso-gold file:text-mso-blue hover:file:bg-mso-blue hover:file:text-white cursor-pointer @error('image') border-red-500 @enderror">
                <p class="text-xs text-slate-400 mt-1">Formatos: JPG, PNG, WebP. Máximo 2MB.</p>
                @error('image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Galería de Imágenes --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Galería de Imágenes</label>
                <input type="file" name="gallery_images[]" accept="image/*" multiple
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-mso-gold file:text-mso-blue hover:file:bg-mso-blue hover:file:text-white cursor-pointer @error('gallery_images.*') border-red-500 @enderror">
                <p class="text-xs text-slate-400 mt-1">Puedes seleccionar múltiples imágenes. Máximo 2MB por imagen.</p>
                @error('gallery_images.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div id="gallery-preview" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3"></div>
            </div>

            {{-- Estado --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                    <select name="is_active" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                        <option value="1" {{ old('is_active') !== '0' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Destacado</label>
                    <select name="is_featured" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                        <option value="0" {{ old('is_featured') != '1' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('is_featured') == '1' ? 'selected' : '' }}>Sí</option>
                    </select>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="{{ route('admin.servicios.index') }}" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-mso-blue text-white px-8 py-2.5 rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-all hover:-translate-y-0.5">
                    <i class="ph ph-floppy-disk mr-1"></i> Guardar Servicio
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    // ============================================================
    // AGREGAR CARACTERÍSTICA
    // ============================================================
    function addFeature() {
        const container = document.getElementById('features-container');
        const item = document.createElement('div');
        item.className = 'flex items-center gap-2 feature-item';
        item.innerHTML = `
            <input type="text" name="features[]" class="flex-1 border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold" placeholder="Ej: Asesoría personalizada">
            <button type="button" onclick="removeFeature(this)" class="text-red-500 hover:text-red-700 p-2">
                <i class="ph ph-x-circle text-xl"></i>
            </button>
        `;
        container.appendChild(item);
    }

    // ============================================================
    // ELIMINAR CARACTERÍSTICA
    // ============================================================
    function removeFeature(button) {
        const items = document.querySelectorAll('.feature-item');
        if (items.length > 1) {
            button.closest('.feature-item').remove();
        } else {
            alert('Debe haber al menos una característica.');
        }
    }

    // ============================================================
    // PREVIEW DE GALERÍA
    // ============================================================
    document.querySelector('input[name="gallery_images[]"]')?.addEventListener('change', function(e) {
        const preview = document.getElementById('gallery-preview');
        preview.innerHTML = '';
        const files = Array.from(this.files);

        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'relative group rounded-lg overflow-hidden border border-slate-200 aspect-square';
                div.innerHTML = `
                    <img src="${event.target.result}" class="w-full h-full object-cover">
                    <span class="absolute bottom-1 right-1 bg-black/60 text-white text-xs px-2 py-0.5 rounded">${index + 1}</span>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endpush
@endsection
