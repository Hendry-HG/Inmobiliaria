@extends('layouts.dashboard')

@section('title', $property->title)
@section('header', 'Detalle de Propiedad')

@push('css')
<style>
    .hero-img-transition {
        transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    .custom-prose { max-width: 100%; line-height: 1.6; color: #334155; }
    .custom-prose p { margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="max-w-full px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header con acciones -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ url()->previous() }}" class="text-slate-500 hover:text-slate-800 transition-colors">
                    <i class="ph ph-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $property->title }}</h2>
                    <div class="flex items-center gap-2 text-slate-500 mt-1">
                        <i class="ph ph-map-pin text-mso-gold"></i>
                        <span class="text-sm">{{ $property->full_location }}</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                {{-- ============================================= --}}
                {{-- EDITAR - SOLO ASESOR --}}
                {{-- ============================================= --}}
                @role('Asesor Inmobiliario')
                    @php
                        $editRoute = (isset($isAdmin) && $isAdmin)
                            ? route('admin.properties.edit', $property)
                            : route('asesor.properties.edit', $property);
                    @endphp
                    <a href="{{ $editRoute }}"
                       class="px-4 py-2 bg-mso-gold text-mso-blue rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-pencil"></i> Editar
                    </a>
                @endrole

                @php
                    $indexRoute = (isset($isAdmin) && $isAdmin)
                        ? route('admin.properties.index')
                        : route('asesor.properties.index');
                @endphp
                <a href="{{ $indexRoute }}"
                   class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2">
                    <i class="ph ph-list"></i> Volver
                </a>
            </div>
        </div>
    </div>

    {{-- GALERÍA DE IMÁGENES --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
        <div class="relative h-[500px] md:h-[600px] bg-slate-900">
            <img id="mainImage"
                 src="{{ $property->primary_image_url }}"
                 alt="{{ $property->title }}"
                 class="absolute inset-0 w-full h-full object-cover hero-img-transition">

            <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/60 pointer-events-none"></div>

            <div class="absolute top-4 left-4 z-20">
                @php
                    $statusColors = [
                        'publicada' => 'bg-green-500',
                        'borrador' => 'bg-gray-500',
                        'vendida' => 'bg-blue-500',
                        'alquilada' => 'bg-indigo-500',
                        'pendiente' => 'bg-yellow-500',
                        'inactiva' => 'bg-red-500',
                    ];
                    $statusColor = $statusColors[$property->status] ?? 'bg-gray-500';
                @endphp
                <span class="{{ $statusColor }} text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg">
                    {{ ucfirst($property->status) }}
                </span>
            </div>

            <div class="absolute top-4 right-4 z-20">
                @php
                    $typeColors = [
                        'venta' => 'bg-blue-600',
                        'alquiler' => 'bg-green-600',
                        'venta/alquiler' => 'bg-purple-600',
                    ];
                    $typeColor = $typeColors[$property->type] ?? 'bg-gray-600';
                @endphp
                <span class="{{ $typeColor }} text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg">
                    {{ ucfirst($property->type) }}
                </span>
            </div>

            @if($property->images && $property->images->count() > 0)
            <div class="absolute bottom-4 right-4 z-20 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs">
                <i class="ph ph-image mr-1"></i> {{ $property->images->count() }} fotos
            </div>
            @endif
        </div>

        @if($property->images && $property->images->count() > 1)
        <div class="p-4 bg-slate-50 border-t border-slate-200">
            <div class="flex items-center gap-3 overflow-x-auto hide-scroll pb-2">
                @foreach($property->images as $image)
                <div onclick="changeMainImage(this, '{{ asset('storage/' . $image->image_path) }}')"
                     class="thumbnail flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden cursor-pointer border-2 {{ $image->is_primary ? 'border-mso-gold opacity-100 shadow-lg' : 'border-transparent opacity-70' }} hover:opacity-100 hover:scale-105 transition-all relative">
                    <img src="{{ asset('storage/' . $image->image_path) }}"
                         class="w-full h-full object-cover"
                         alt="Miniatura">
                    @if($image->is_primary)
                    <div class="absolute bottom-1 left-1 bg-mso-gold text-white text-[10px] px-1.5 py-0.5 rounded">
                        Principal
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- PRECIO Y CARACTERÍSTICAS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-6">
                <div>
                    <p class="text-4xl font-bold text-mso-gold">{{ $property->formatted_price }}</p>
                    @if($property->price_currency)
                    <p class="text-xs text-slate-400 uppercase tracking-widest">{{ $property->price_currency }}</p>
                    @endif
                </div>

                <div class="h-12 w-px bg-slate-200 hidden lg:block"></div>

                <div class="flex flex-wrap gap-6">
                    @if($property->bedrooms)
                    <div class="flex items-center gap-2">
                        <i class="ph ph-bed text-2xl text-slate-400"></i>
                        <div>
                            <span class="block font-bold text-slate-900 text-lg">{{ $property->bedrooms }}</span>
                            <span class="text-xs text-slate-500">Habitaciones</span>
                        </div>
                    </div>
                    @endif

                    @if($property->bathrooms)
                    <div class="flex items-center gap-2">
                        <i class="ph ph-shower text-2xl text-slate-400"></i>
                        <div>
                            <span class="block font-bold text-slate-900 text-lg">{{ $property->bathrooms }}</span>
                            <span class="text-xs text-slate-500">Baños</span>
                        </div>
                    </div>
                    @endif

                    @if($property->parking_spaces)
                    <div class="flex items-center gap-2">
                        <i class="ph ph-car text-2xl text-slate-400"></i>
                        <div>
                            <span class="block font-bold text-slate-900 text-lg">{{ $property->parking_spaces }}</span>
                            <span class="text-xs text-slate-500">Estac.</span>
                        </div>
                    </div>
                    @endif

                    @if($property->area)
                    <div class="flex items-center gap-2">
                        <i class="ph ph-ruler text-2xl text-slate-400"></i>
                        <div>
                            <span class="block font-bold text-slate-900 text-lg">{{ number_format($property->area, 0) }}m²</span>
                            <span class="text-xs text-slate-500">Área</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex gap-4">
                <div class="text-center">
                    <p class="text-xl font-bold text-slate-800">{{ $property->views ?? 0 }}</p>
                    <p class="text-xs text-slate-500">Vistas</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-slate-800">{{ $property->inquiries ?? 0 }}</p>
                    <p class="text-xs text-slate-500">Consultas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Detalles -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Descripción -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph ph-article text-mso-gold"></i>
                    Descripción General
                </h3>
                <div class="prose prose-slate max-w-none">
                    {!! $property->description !!}
                </div>
            </div>

            <!-- Características Detalladas -->
            @if($property->features && is_array($property->features) && count($property->features) > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph ph-check-square text-mso-gold"></i>
                    Características Adicionales
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($property->features as $feature)
                    <div class="flex items-center gap-3 text-slate-600 bg-slate-50 px-3 py-2 rounded-lg">
                        <i class="ph ph-check-circle text-green-500"></i>
                        <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Ubicación -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph ph-map-pin text-mso-gold"></i>
                    Ubicación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-lg">
                        <i class="ph ph-globe text-slate-400 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase">País</p>
                            <p class="font-medium text-slate-800">{{ $property->countryRelation->name ?? $property->country ?? 'No especificado' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-lg">
                        <i class="ph ph-map-pin-area text-slate-400 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase">Estado</p>
                            <p class="font-medium text-slate-800">{{ $property->stateRelation->name ?? $property->state ?? 'No especificado' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-lg">
                        <i class="ph ph-buildings text-slate-400 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase">Municipio</p>
                            <p class="font-medium text-slate-800">{{ $property->municipalityRelation->name ?? 'No especificado' }}</p>
                        </div>
                    </div>
                    @if($property->parishRelation)
                    <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-lg">
                        <i class="ph ph-church text-slate-400 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase">Parroquia</p>
                            <p class="font-medium text-slate-800">{{ $property->parishRelation->name }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-lg">
                        <i class="ph ph-city text-slate-400 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase">Ciudad</p>
                            <p class="font-medium text-slate-800">{{ $property->cityRelation->name ?? $property->city ?? 'No especificado' }}</p>
                        </div>
                    </div>
                    @if($property->address)
                    <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-lg md:col-span-2">
                        <i class="ph ph-road-horizon text-slate-400 mt-0.5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase">Dirección</p>
                            <p class="font-medium text-slate-800">{{ $property->address }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph ph-info text-mso-gold"></i>
                    Información Adicional
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @if($property->category)
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-xs text-slate-500 uppercase">Categoría</p>
                        <p class="font-medium text-slate-800">{{ $property->category->name }}</p>
                    </div>
                    @endif

                    @if($property->year_built)
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-xs text-slate-500 uppercase">Año Construcción</p>
                        <p class="font-medium text-slate-800">{{ $property->year_built }}</p>
                    </div>
                    @endif

                    @if($property->floors)
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-xs text-slate-500 uppercase">Nº de Pisos</p>
                        <p class="font-medium text-slate-800">{{ $property->floors }}</p>
                    </div>
                    @endif

                    @if($property->land_area)
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-xs text-slate-500 uppercase">Área Terreno</p>
                        <p class="font-medium text-slate-800">{{ number_format($property->land_area, 0) }} m²</p>
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-slate-500">ID de Referencia</p>
                        <p class="font-mono text-slate-800">#{{ $property->id }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500">Publicado</p>
                        <p class="text-sm text-slate-600">{{ $property->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Asesor Asignado -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6">
                <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="ph ph-user-circle text-mso-gold"></i>
                    Asesor Asignado
                </h4>
                <div class="flex items-center gap-4 mb-4">
                    <img src="{{ $property->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($property->user->name ?? 'Asesor') . '&background=c5a059&color=fff&size=128' }}"
                         alt="{{ $property->user->name ?? 'Asesor' }}"
                         class="w-16 h-16 rounded-full object-cover border-2 border-mso-gold">
                    <div>
                        <p class="font-bold text-slate-900">{{ $property->user->name ?? 'No asignado' }}</p>
                    </div>
                </div>

                @php $socialLinks = $property->user->social_links ?? []; @endphp
                @if(!empty($socialLinks))
                <div class="mb-4">
                    <p class="text-xs text-slate-500 mb-2">Contacto directo:</p>
                    <div class="flex flex-wrap gap-2">
                        @if(isset($socialLinks['whatsapp']))
                        <a href="https://wa.me/{{ $socialLinks['whatsapp'] }}" target="_blank"
                           class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center hover:bg-green-600 transition-colors shadow-md">
                            <i class="ph ph-whatsapp-logo text-lg"></i>
                        </a>
                        @endif
                        @if(isset($socialLinks['instagram']))
                        <a href="https://instagram.com/{{ $socialLinks['instagram'] }}" target="_blank"
                           class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-full flex items-center justify-center hover:opacity-80 transition-opacity shadow-md">
                            <i class="ph ph-instagram-logo text-lg"></i>
                        </a>
                        @endif
                        @if(isset($socialLinks['facebook']))
                        <a href="{{ $socialLinks['facebook'] }}" target="_blank"
                           class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors shadow-md">
                            <i class="ph ph-facebook-logo text-lg"></i>
                        </a>
                        @endif
                        @if(isset($socialLinks['telegram']))
                        <a href="https://t.me/{{ $socialLinks['telegram'] }}" target="_blank"
                           class="w-10 h-10 bg-sky-500 text-white rounded-full flex items-center justify-center hover:bg-sky-600 transition-colors shadow-md">
                            <i class="ph ph-telegram-logo text-lg"></i>
                        </a>
                        @endif
                        @if(isset($socialLinks['linkedin']))
                        <a href="{{ $socialLinks['linkedin'] }}" target="_blank"
                           class="w-10 h-10 bg-blue-700 text-white rounded-full flex items-center justify-center hover:bg-blue-800 transition-colors shadow-md">
                            <i class="ph ph-linkedin-logo text-lg"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($property->user && $property->user->bio)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-500 mb-1">Sobre el asesor:</p>
                    <p class="text-sm text-slate-600 italic">"{{ Str::limit($property->user->bio, 150) }}"</p>
                </div>
                @endif
            </div>

            {{-- FORMULARIO DE AGENDAR CITA --}}
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 lg:sticky lg:top-24">
                <h3 class="text-xl font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <i class="ph ph-calendar-check text-mso-gold"></i>
                    Agendar Visita
                </h3>
                <p class="text-slate-500 text-sm mb-5">Déjanos tus datos y el asesor confirmará la cita contigo.</p>

                <form action="{{ route('citas.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <input type="hidden" name="date" id="fullDateTimeInput">

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nombre Completo *</label>
                        <input type="text" name="name" required placeholder="Tu nombre completo"
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Correo Electrónico *</label>
                        <input type="email" name="email" required placeholder="ejemplo@correo.com"
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Teléfono *</label>
                        <input type="tel" name="phone" required placeholder="Tu número de contacto"
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Fecha *</label>
                            <input type="date" name="date_picker" id="datePicker" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Hora *</label>
                            <select name="time_picker" id="timePicker" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                                <option value="">Seleccionar</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="13:00">01:00 PM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="15:00">03:00 PM</option>
                                <option value="16:00">04:00 PM</option>
                                <option value="17:00">05:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Mensaje (opcional)</label>
                        <textarea name="message" rows="2" placeholder="¿Alguna preferencia de horario o comentario adicional?"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm resize-none"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-mso-gold text-mso-blue font-bold py-3 rounded-lg hover:bg-mso-blue hover:text-white transition-all shadow-lg mt-2 flex items-center justify-center gap-2">
                        <i class="ph ph-calendar-plus"></i>
                        Solicitar Cita
                    </button>

                    <p class="text-xs text-slate-400 text-center mt-3">
                        <i class="ph ph-shield-check"></i> Tus datos están seguros
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Script para combinar fecha y hora
        const datePicker = document.getElementById('datePicker');
        const timePicker = document.getElementById('timePicker');
        const fullDateTimeInput = document.getElementById('fullDateTimeInput');
        const appointmentForm = document.querySelector('form[action="{{ route('citas.store') }}"]');

        if (appointmentForm) {
            appointmentForm.addEventListener('submit', function(e) {
                if(datePicker.value && timePicker.value) {
                    fullDateTimeInput.value = datePicker.value + 'T' + timePicker.value + ':00';
                } else {
                    e.preventDefault();
                    alert('Por favor selecciona fecha y hora.');
                }
            });
        }
    });

    // Función para cambiar imagen principal
    function changeMainImage(thumbnailElement, imageUrl) {
        const mainImage = document.getElementById('mainImage');

        if (!mainImage || !thumbnailElement) return;

        mainImage.style.opacity = '0';
        mainImage.classList.add('scale-105');

        setTimeout(() => {
            mainImage.src = imageUrl;

            const onImageLoad = () => {
                mainImage.style.opacity = '1';
                mainImage.classList.remove('scale-105');
                mainImage.removeEventListener('load', onImageLoad);
                mainImage.removeEventListener('error', onImageError);
            };

            const onImageError = () => {
                console.error('Error cargando imagen:', imageUrl);
                mainImage.src = '/images/placeholder.jpg';
                mainImage.style.opacity = '1';
                mainImage.classList.remove('scale-105');
            };

            if (mainImage.complete) {
                onImageLoad();
            } else {
                mainImage.addEventListener('load', onImageLoad);
                mainImage.addEventListener('error', onImageError);
            }
        }, 200);

        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach(t => {
            t.classList.remove('border-mso-gold', 'opacity-100', 'shadow-lg', 'ring-2', 'ring-mso-gold/50');
            t.classList.add('border-transparent', 'opacity-70');
        });

        thumbnailElement.classList.remove('border-transparent', 'opacity-70');
        thumbnailElement.classList.add('border-mso-gold', 'opacity-100', 'shadow-lg', 'ring-2', 'ring-mso-gold/50');
    }
</script>
@endpush
