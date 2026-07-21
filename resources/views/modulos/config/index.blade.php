@extends('layouts.dashboard')

@section('title', 'Configuración del Sitio')
@section('header', 'Configuración de Plataforma')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        <div class="p-6 bg-slate-50 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="ph ph-gear text-mso-gold"></i>
                Configuración General
            </h3>
            <p class="text-sm text-slate-500 mt-1">Personaliza la apariencia y contenido de la página de inicio</p>
        </div>


        <form action="{{ route('admin.config.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
            @csrf
            @method('PUT')

            {{-- ============================================ --}}
            {{-- SECCIÓN: HERO --}}
            {{-- ============================================ --}}
            <div>
                <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b flex items-center gap-2">
                    <i class="ph ph-image text-mso-gold"></i>
                    Sección Hero (Principal)
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Badge (Etiqueta)</label>
                        <input type="text" name="hero_badge" value="{{ old('hero_badge', $config->hero_badge ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Título Línea 1</label>
                        <input type="text" name="hero_title_line1" value="{{ old('hero_title_line1', $config->hero_title_line1 ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Título Línea 2</label>
                        <input type="text" name="hero_title_line2" value="{{ old('hero_title_line2', $config->hero_title_line2 ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Subtítulo</label>
                        <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $config->hero_subtitle ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- SECCIÓN: IMÁGENES DEL HERO --}}
            {{-- ============================================ --}}
            <div>
                <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b flex items-center gap-2">
                    <i class="ph ph-images text-mso-gold"></i>
                    Imágenes del Hero (Carrusel)
                </h4>

                <div class="mb-4">
                    <p class="text-sm text-slate-500 mb-2">Sube nuevas imágenes o elimina las existentes. Mínimo 1, máximo 5 imágenes.</p>

                    {{-- Imágenes existentes --}}
                    <div id="existing-images-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        @php
                            $heroImages = $config->hero_images ?? [];
                            if (is_string($heroImages)) {
                                $heroImages = json_decode($heroImages, true) ?? [];
                            }
                        @endphp
                        @foreach($heroImages as $index => $image)
                        <div class="image-item relative group" data-index="{{ $index }}">
                            <img src="{{ $image }}" alt="Hero imagen {{ $index + 1 }}"
                                 class="w-full h-32 object-cover rounded-lg border border-slate-200">
                            <button type="button"
                                    onclick="deleteHeroImage({{ $index }})"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg opacity-0 group-hover:opacity-100">
                                <i class="ph ph-x text-lg"></i>
                            </button>
                            <span class="absolute bottom-2 left-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Input para subir nuevas imágenes --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Agregar nuevas imágenes</label>
                        <input type="file" name="hero_images_new[]" id="hero_images_input" multiple accept="image/*"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-mso-gold file:text-mso-blue hover:file:bg-mso-blue hover:file:text-white cursor-pointer">
                        <p class="text-xs text-slate-400 mt-1">Formatos: JPG, PNG, WebP. Máximo 2MB por imagen.</p>
                    </div>

                    {{-- Preview de nuevas imágenes --}}
                    <div id="new-images-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>

                    {{-- Campo oculto para imágenes eliminadas --}}
                    <input type="hidden" name="deleted_hero_images" id="deleted_hero_images" value="[]">
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- SECCIÓN: PROPIEDADES DESTACADAS --}}
            {{-- ============================================ --}}
            <div>
                <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b flex items-center gap-2">
                    <i class="ph ph-star text-mso-gold"></i>
                    Propiedades Destacadas
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Badge Destacadas</label>
                        <input type="text" name="featured_badge" value="{{ old('featured_badge', $config->featured_badge ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Título Destacadas</label>
                        <input type="text" name="featured_title" value="{{ old('featured_title', $config->featured_title ?? '') }}"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                </div>

                {{-- Panel de Estadísticas --}}
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider">Total Propiedades</span>
                        <p class="text-2xl font-bold text-slate-800">{{ $stats['total_properties'] ?? 0 }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider">Total Visitas</span>
                        <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_views'] ?? 0) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider">Más Visitada</span>
                        <p class="text-sm font-semibold text-mso-gold truncate" title="{{ $stats['most_viewed']->title ?? '' }}">
                            {{ $stats['most_viewed']->title ?? 'N/A' }}
                        </p>
                        <span class="text-xs text-slate-400">{{ number_format($stats['most_viewed']->views ?? 0) }} vistas</span>
                    </div>
                </div>

                {{-- Selector de Propiedades Destacadas --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Seleccionar Propiedades Destacadas ({{ count($featuredIds) }} seleccionadas)
                    </label>

                    {{-- Campo de búsqueda --}}
                    <div class="relative mb-3">
                        <input type="text" id="propertySearch"
                               placeholder=" Buscar propiedad por nombre, ubicación o ID..."
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>

                    {{-- Propiedades Destacadas Actuales --}}
                    @if($featuredProperties->isNotEmpty())
                        <div class="mb-3 p-3 bg-mso-gold/10 border border-mso-gold/30 rounded-lg">
                            <p class="text-sm font-semibold text-slate-700 mb-2"> Propiedades destacadas actualmente:</p>
                            <div class="flex flex-wrap gap-2" id="selected-properties-container">
                                @foreach($featuredProperties as $property)
                                    <span class="selected-property inline-flex items-center gap-2 bg-white border border-mso-gold text-mso-gold px-3 py-1.5 rounded-full text-sm" data-id="{{ $property->id }}">
                                        <span class="w-6 h-6 rounded-full bg-mso-gold/20 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            {{ $loop->iteration }}
                                        </span>
                                        <span class="truncate max-w-[150px]">{{ $property->title }}</span>
                                        <button type="button" onclick="removeFeaturedProperty({{ $property->id }})"
                                                class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full p-1">
                                            <i class="ph ph-x"></i>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Lista de todas las propiedades --}}
                    <div class="max-h-80 overflow-y-auto border border-slate-200 rounded-lg divide-y divide-slate-100" id="properties-list">
                        @forelse($allProperties as $property)
                            <label class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer transition-colors {{ in_array($property->id, $featuredIds) ? 'bg-mso-gold/5' : '' }}">
                                <input type="checkbox" name="featured_properties[]" value="{{ $property->id }}"
                                       {{ in_array($property->id, $featuredIds) ? 'checked' : '' }}
                                       class="property-checkbox rounded border-slate-300 text-mso-gold focus:ring-mso-gold w-4 h-4"
                                       data-search="{{ $property->title }} {{ $property->location }} {{ $property->id }}">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-slate-700 truncate">{{ $property->title }}</span>
                                        <span class="text-xs font-bold text-mso-gold ml-2 flex-shrink-0">
                                            {{ $property->formatted_price ?? '$' . number_format($property->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-500">
                                        <span class="truncate">{{ $property->location ?? 'Sin ubicación' }}</span>
                                        <span class="flex items-center gap-1">
                                            <i class="ph ph-eye"></i>
                                            {{ number_format($property->views ?? 0) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="ph ph-bed"></i>
                                            {{ $property->bedrooms ?? 0 }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="ph ph-bathtub"></i>
                                            {{ $property->bathrooms ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="p-6 text-center text-slate-400">
                                <i class="ph ph-house text-4xl block mb-2"></i>
                                <p>No hay propiedades publicadas disponibles.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="flex justify-between items-center mt-3">
                        <span class="text-xs text-slate-400">{{ $allProperties->count() }} propiedades disponibles</span>
                        <div class="flex gap-2">
                            <button type="button" onclick="selectAllProperties()" class="text-xs text-mso-gold hover:underline">
                                Seleccionar todas
                            </button>
                            <button type="button" onclick="deselectAllProperties()" class="text-xs text-red-500 hover:underline">
                                Deseleccionar todas
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- SECCIÓN: SOPORTE --}}
            {{-- ============================================ --}}
            <div>
                <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b flex items-center gap-2">
                    <i class="ph ph-headset text-mso-gold"></i>
                    Soporte y Contacto
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp</label>
                        <input type="text" name="support_whatsapp" value="{{ old('support_whatsapp', $config->support_whatsapp ?? '') }}"
                               placeholder="58XXXXXXXXX"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Instagram</label>
                        <input type="text" name="support_instagram" value="{{ old('support_instagram', $config->support_instagram ?? '') }}"
                               placeholder="usuario_instagram"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                        <input type="text" name="support_phone" value="{{ old('support_phone', $config->support_phone ?? '') }}"
                               placeholder="+58 412 1234567"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email de Soporte</label>
                        <input type="email" name="support_email" value="{{ old('support_email', $config->support_email ?? '') }}"
                               placeholder="contacto@mso.com"
                               class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                    </div>
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- SECCIÓN: FOOTER --}}
            {{-- ============================================ --}}
            <div>
                <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b flex items-center gap-2">
                    <i class="ph ph-article text-mso-gold"></i>
                    Footer
                </h4>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Texto del Footer</label>
                    <input type="text" name="footer_text" value="{{ old('footer_text', $config->footer_text ?? '') }}"
                           placeholder="© 2024 MSO Grupo Inmobiliario. Todos los derechos reservados."
                           class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- BOTONES --}}
            {{-- ============================================ --}}
            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-mso-blue text-white px-8 py-2.5 rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-all hover:-translate-y-0.5">
                    <i class="ph ph-floppy-disk mr-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    // ============================================================
    // CONFIGURACIÓN
    // ============================================================
    const deleteHeroImageUrl = '{{ route("admin.config.delete-image", ["index" => "PLACEHOLDER"]) }}';

    // ============================================================
    // ELIMINAR IMÁGENES DEL HERO
    // ============================================================
    function deleteHeroImage(index) {
        if (!confirm('¿Estás seguro de eliminar esta imagen?')) return;

        const url = deleteHeroImageUrl.replace('PLACEHOLDER', index);

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const imageElement = document.querySelector(`.image-item[data-index="${index}"]`);
                if (imageElement) {
                    imageElement.remove();
                }
                updateDeletedImages(index);
                // NOTIFICACIÓN ELIMINADA - Usa el layout
            } else {
                alert(data.message || 'Error al eliminar la imagen');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la imagen');
        });
    }

    function updateDeletedImages(index) {
        const hiddenInput = document.getElementById('deleted_hero_images');
        let deletedImages = [];

        try {
            deletedImages = JSON.parse(hiddenInput.value || '[]');
        } catch (e) {
            deletedImages = [];
        }

        if (!deletedImages.includes(index)) {
            deletedImages.push(index);
            hiddenInput.value = JSON.stringify(deletedImages);
        }
    }

    // ============================================================
    // PROPIEDADES DESTACADAS
    // ============================================================
    function removeFeaturedProperty(propertyId) {
        const checkbox = document.querySelector(`input[name="featured_properties[]"][value="${propertyId}"]`);
        if (checkbox) {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change'));
        }
    }

    function selectAllProperties() {
        document.querySelectorAll('input[name="featured_properties[]"]').forEach(cb => {
            if (!cb.disabled) cb.checked = true;
        });
        updateSelectedCount();
    }

    function deselectAllProperties() {
        document.querySelectorAll('input[name="featured_properties[]"]').forEach(cb => {
            cb.checked = false;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('input[name="featured_properties[]"]:checked').length;
        const label = document.querySelector('label[for="featured_properties"]');
        if (label) {
            label.textContent = `Seleccionar Propiedades Destacadas (${count} seleccionadas)`;
        }
    }

    // ============================================================
    // FILTRO DE BÚSQUEDA EN TIEMPO REAL
    // ============================================================
    document.getElementById('propertySearch')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const items = document.querySelectorAll('#properties-list label');

        items.forEach(item => {
            const searchData = item.querySelector('.property-checkbox')?.dataset.search || '';
            const shouldShow = searchData.toLowerCase().includes(searchTerm);
            item.style.display = shouldShow ? 'flex' : 'none';
        });
    });

    // ============================================================
    // ACTUALIZAR CONTADOR DE SELECCIONADAS
    // ============================================================
    document.querySelectorAll('input[name="featured_properties[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();

            // Actualizar estilo visual
            const label = this.closest('label');
            if (this.checked) {
                label?.classList.add('bg-mso-gold/5');
            } else {
                label?.classList.remove('bg-mso-gold/5');
            }

            // Actualizar contenedor de seleccionadas
            updateSelectedPropertiesContainer();
        });
    });

    function updateSelectedPropertiesContainer() {
        const container = document.getElementById('selected-properties-container');
        if (!container) return;

        const selectedIds = [];
        document.querySelectorAll('input[name="featured_properties[]"]:checked').forEach(cb => {
            selectedIds.push(cb.value);
        });

        // Actualizar el texto del contenedor padre
        const label = document.querySelector('label[for="featured_properties"]');
        if (label) {
            label.textContent = `Seleccionar Propiedades Destacadas (${selectedIds.length} seleccionadas)`;
        }
    }

    // ============================================================
    // PREVIEW DE NUEVAS IMÁGENES
    // ============================================================
    document.getElementById('hero_images_input')?.addEventListener('change', function(e) {
        const files = Array.from(this.files);
        const previewContainer = document.getElementById('new-images-preview');
        previewContainer.innerHTML = '';

        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${event.target.result}" class="w-full h-32 object-cover rounded-lg border border-mso-gold">
                    <span class="absolute top-2 left-2 bg-mso-gold text-black text-xs px-2 py-0.5 rounded font-bold">Nueva</span>
                    <span class="absolute bottom-2 left-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded">${index + 1}</span>
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // ============================================================
    // NOTIFICACIONES ELIMINADAS - Usan el layout
    // ============================================================

    // ============================================================
    // INICIALIZAR
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
    });
</script>
@endpush
@endsection