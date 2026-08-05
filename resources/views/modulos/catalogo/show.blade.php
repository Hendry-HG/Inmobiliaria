{{-- Detalle de propiedad en catálogo público --}}
@extends('layouts.landing')

@section('title', $property->title . ' - ' . config('app.name'))

@push('css')
<style>
    .hero-img-transition {
        transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }

    .modal-overlay {
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        transition: opacity 0.3s ease;
    }
    .modal-content {
        transform: scale(0.9);
        transition: transform 0.3s ease, opacity 0.3s ease;
        opacity: 0;
    }
    .modal-content.show {
        transform: scale(1);
        opacity: 1;
    }
    .modal-overlay.hidden {
        display: none;
    }
    .house-icon {
        font-size: 4rem;
        display: inline-block;
        animation: bounce 2s infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .feature-icon {
        width: 1.25rem;
        height: 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .feature-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background-color: #f8fafc;
        padding: 0.4rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .info-card {
        background-color: #f8fafc;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
    }
    .custom-prose {
        max-width: 100%;
        line-height: 1.8;
        color: #475569;
    }
    .custom-prose p {
        margin-bottom: 1rem;
    }
    .ph-spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
{{-- HERO SECTION: GALERÍA INMERSIVA --}}
<section class="relative pt-20 h-[60vh] md:h-[70vh] w-full bg-slate-900 overflow-hidden">
    <img id="mainImage"
         src="{{ $property->primary_image_url }}"
         alt="{{ $property->title }}"
         class="absolute inset-0 w-full h-full object-cover hero-img-transition"
         onerror="this.src='https://ui-avatars.com/api/?name='+encodeURIComponent('{{ $property->title }}')+'&background=c5a059&color=fff&size=400'">
    <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-transparent to-black/60 pointer-events-none"></div>

    {{-- Badges --}}
    <div class="absolute top-24 left-4 md:left-10 z-30 flex flex-wrap gap-2">
        <span class="bg-mso-blue text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg border border-white/20">
            {{ ucfirst($property->status) }}
        </span>
        <span class="px-4 py-2 text-xs font-bold rounded-full shadow-lg
            @if($property->type == 'venta') bg-blue-600 text-white
            @elseif($property->type == 'alquiler') bg-green-600 text-white
            @else bg-purple-600 text-white @endif">
            {{ ucfirst($property->type) }}
        </span>
        @if($property->category)
        <span class="px-4 py-2 text-xs font-bold rounded-full shadow-lg bg-amber-600 text-white flex items-center gap-1.5">
            @if($property->category->icon)
                <i class="{{ $property->category->icon }}"></i>
            @else
                <i class="ph ph-folder"></i>
            @endif
            {{ $property->category->name }}
        </span>
        @endif
        @if($property->is_featured)
        <span class="px-4 py-2 text-xs font-bold rounded-full shadow-lg bg-mso-gold text-mso-blue flex items-center gap-1.5">
            <i class="ph ph-star"></i> Destacada
        </span>
        @endif
    </div>

    {{-- Miniaturas --}}
    @if($property->images && $property->images->count() > 1)
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-30 w-[90%] md:w-[500px]">
        <div class="flex items-center gap-3 overflow-x-auto hide-scroll p-3 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl">
            @foreach($property->images as $image)
            <div onclick="changeMainImage(this, '{{ asset('storage/' . $image->image_path) }}')"
                 class="thumbnail flex-shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden cursor-pointer border-2 {{ $image->is_primary ? 'border-mso-gold opacity-100' : 'border-transparent opacity-60' }} hover:opacity-100 hover:scale-105 transition-all relative">
                <img src="{{ asset('storage/' . $image->image_path) }}"
                     class="w-full h-full object-cover"
                     alt="Miniatura"
                     onerror="this.src='https://ui-avatars.com/api/?name=Imagen&background=c5a059&color=fff&size=400'">
                @if($image->is_primary)
                <div class="absolute inset-0 bg-mso-gold/20"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</section>

{{-- DETALLES DE LA PROPIEDAD --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
    <div class="flex flex-col lg:flex-row gap-10 lg:gap-12">

        {{-- Columna Izquierda --}}
        <div class="lg:w-2/3 space-y-8 animate-fade-in">

            {{-- Encabezado --}}
            <div class="border-b border-slate-200 pb-6">
                <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 leading-tight mb-2">{{ $property->title }}</h1>
                        <div class="flex items-center gap-2 text-slate-500">
                            <i class="ph-fill ph-map-pin text-mso-gold text-lg"></i>
                            <span class="text-sm font-medium">{{ $property->full_location ?? 'Ubicación no especificada' }}</span>
                        </div>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-3xl md:text-4xl text-mso-gold font-bold">{{ $property->formatted_price }}</p>
                        @if($property->price_currency)
                        <p class="text-xs text-slate-400 uppercase tracking-widest mt-1">{{ $property->price_currency }}</p>
                        @endif
                        <div class="mt-2 flex items-center justify-end gap-4 text-xs text-slate-400">
                            <span class="flex items-center gap-1">
                                <i class="ph ph-eye"></i> {{ $property->views ?? 0 }} vistas
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Características principales --}}
                <div class="flex flex-wrap gap-6 md:gap-8 mt-6 pt-6 border-t border-slate-100">
                    @if($property->bedrooms)
                    <div class="flex items-center gap-3">
                        <i class="ph-fill ph-bed text-2xl text-slate-400"></i>
                        <div><span class="block font-bold text-slate-900 text-lg">{{ $property->bedrooms }}</span><span class="text-xs text-slate-500 uppercase">Habitaciones</span></div>
                    </div>
                    @endif
                    @if($property->bathrooms)
                    <div class="flex items-center gap-3">
                        <i class="ph-fill ph-shower text-2xl text-slate-400"></i>
                        <div><span class="block font-bold text-slate-900 text-lg">{{ $property->bathrooms }}</span><span class="text-xs text-slate-500 uppercase">Baños</span></div>
                    </div>
                    @endif
                    @if($property->area)
                    <div class="flex items-center gap-3">
                        <i class="ph-fill ph-ruler text-2xl text-slate-400"></i>
                        <div><span class="block font-bold text-slate-900 text-lg">{{ number_format($property->area, 0) }}m²</span><span class="text-xs text-slate-500 uppercase">Construcción</span></div>
                    </div>
                    @endif
                    @if($property->land_area)
                    <div class="flex items-center gap-3">
                        <i class="ph-fill ph-arrows-out text-2xl text-slate-400"></i>
                        <div><span class="block font-bold text-slate-900 text-lg">{{ number_format($property->land_area, 0) }}m²</span><span class="text-xs text-slate-500 uppercase">Terreno</span></div>
                    </div>
                    @endif
                    @if($property->parking_spaces)
                    <div class="flex items-center gap-3">
                        <i class="ph-fill ph-car text-2xl text-slate-400"></i>
                        <div><span class="block font-bold text-slate-900 text-lg">{{ $property->parking_spaces }}</span><span class="text-xs text-slate-500 uppercase">Estac.</span></div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Categoría --}}
            @if($property->category)
            <div class="bg-gradient-to-r from-amber-50 to-slate-50 p-4 rounded-2xl border border-amber-200/50 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-mso-gold/20 flex items-center justify-center text-3xl flex-shrink-0">
                    <i class="{{ $property->category->icon ?? 'ph-folder' }} text-mso-blue"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Categoría de la propiedad</p>
                    <p class="font-bold text-slate-800 text-lg">{{ $property->category->name }}</p>
                    @if($property->category->description)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $property->category->description }}</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Descripción --}}
            <div>
                <h3 class="text-2xl font-bold text-slate-900 mb-4">Descripción General</h3>
                <div class="custom-prose">
                    {!! nl2br(e($property->description)) !!}
                </div>
            </div>

            {{-- CARACTERÍSTICAS ADICIONALES CON ICONOS --}}
            @php
                $features = $property->features;
                if (is_string($features)) {
                    $features = json_decode($features, true);
                }
                $featureIcons = [
                    'aire_acondicionado' => 'ph-snowflake',
                    'ascensor' => 'ph-elevator',
                    'balcon' => 'ph-balcony',
                    'calefaccion' => 'ph-thermometer-hot',
                    'camaras_seguridad' => 'ph-camera',
                    'casa_campo' => 'ph-tree',
                    'chimenea' => 'ph-fireplace',
                    'cisterna' => 'ph-drop',
                    'club_campo' => 'ph-golf',
                    'gimnasio' => 'ph-dumbbell',
                    'jardin' => 'ph-flower',
                    'piscina' => 'ph-swimming-pool',
                    'placas_solares' => 'ph-sun',
                    'playa' => 'ph-beach-ball',
                    'porteria' => 'ph-shield-check',
                    'salon_eventos' => 'ph-cake',
                    'terraza' => 'ph-sun-horizon',
                    'vista_mar' => 'ph-wave',
                    'vista_montana' => 'ph-mountains',
                    'zonas_verdes' => 'ph-tree-palm'
                ];
            @endphp
            @if($features && is_array($features) && count($features) > 0)
            <div>
                <h3 class="text-2xl font-bold text-slate-900 mb-4">Características de la Propiedad</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($features as $feature)
                    @php
                        $icon = $featureIcons[$feature] ?? 'ph-check-circle';
                        $label = ucfirst(str_replace('_', ' ', $feature));
                    @endphp
                    <span class="feature-tag">
                        <i class="ph {{ $icon }} text-mso-gold text-base"></i>
                        {{ $label }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- INFORMACIÓN DETALLADA --}}
            <div>
                <h3 class="text-2xl font-bold text-slate-900 mb-4">Información Detallada</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @if($property->category)
                    <div class="info-card">
                        <p class="text-xs text-slate-500 uppercase">Categoría</p>
                        <p class="font-medium text-slate-800">{{ $property->category->name }}</p>
                    </div>
                    @endif
                    @if($property->floors)
                    <div class="info-card">
                        <p class="text-xs text-slate-500 uppercase">Nº de Pisos</p>
                        <p class="font-medium text-slate-800">{{ $property->floors }}</p>
                    </div>
                    @endif
                    @if($property->year_built)
                    <div class="info-card">
                        <p class="text-xs text-slate-500 uppercase">Año Construcción</p>
                        <p class="font-medium text-slate-800">{{ $property->year_built }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Ubicación Completa --}}
            <div>
                <h3 class="text-2xl font-bold text-slate-900 mb-4">Ubicación</h3>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
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
            </div>
        </div>

        {{-- Columna Derecha --}}
        <div class="lg:w-1/3 space-y-6">

            {{-- Tarjeta del Asesor --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="bg-gradient-to-r from-mso-blue to-slate-700 h-16"></div>
                <div class="px-6 pb-6">
                    <div class="flex justify-center -mt-12">
                        <img src="{{ $property->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($property->user->full_name ?? $property->user->name ?? 'Asesor').'&background=c5a059&color=fff&size=128' }}"
                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-xl"
                             alt="{{ $property->user->full_name ?? $property->user->name ?? 'Asesor' }}"
                             onerror="this.src='https://ui-avatars.com/api/?name='+encodeURIComponent('{{ $property->user->full_name ?? $property->user->name ?? 'Asesor' }}')+'&background=c5a059&color=fff&size=128'">
                    </div>
                    <div class="text-center mt-3">
                        <h4 class="font-bold text-slate-900 text-lg">{{ $property->user->full_name ?? $property->user->name ?? 'Asesor no asignado' }}</h4>
                        <p class="text-sm text-slate-500">{{ $property->user->specialization ?? 'Asesor Inmobiliario' }}</p>
                    </div>
                    <div class="flex gap-2 mt-5 pt-4 border-t border-slate-100">
                        @php $socialLinks = $property->user->social_links ?? []; @endphp
                        @if(isset($socialLinks['whatsapp']))
                        <a href="https://wa.me/{{ $socialLinks['whatsapp'] }}" target="_blank" class="flex-1 bg-green-500 text-white text-center py-2.5 rounded-lg text-sm font-medium hover:bg-green-600 transition-colors flex items-center justify-center gap-2 shadow-md">
                            <i class="ph-fill ph-whatsapp-logo text-lg"></i> WhatsApp
                        </a>
                        @endif
                        <a href="mailto:{{ $property->user->email ?? '#' }}" class="flex-1 border border-slate-200 text-slate-700 text-center py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                            <i class="ph-fill ph-envelope-simple"></i> Email
                        </a>
                    </div>
                </div>
            </div>

            {{-- FORMULARIO DE AGENDAR CITA CON CALENDARIO --}}
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 lg:sticky lg:top-24">
                <h3 class="text-xl font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <i class="ph ph-calendar-check text-mso-gold"></i>
                    Agendar Visita
                </h3>
                <p class="text-slate-500 text-sm mb-5">Selecciona una fecha y hora disponible para tu visita.</p>

                @auth
                <form id="appointmentForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <input type="hidden" name="asesor_id" value="{{ $property->user_id }}">
                    <input type="hidden" name="date" id="fullDateTimeInput">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Nombre *</label>
                            <input type="text" name="name" id="formName" required data-label="Nombre" placeholder="Nombre"
                                   value="{{ auth()->user()->name ?? '' }}"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Apellido *</label>
                            <input type="text" name="last_name" id="formLastName" required data-label="Apellido" placeholder="Apellido"
                                   value="{{ auth()->user()->last_name ?? '' }}"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Correo Electrónico *</label>
                        <input type="email" name="email" id="formEmail" required data-label="Correo Electrónico" placeholder="ejemplo@correo.com"
                               value="{{ auth()->user()->email ?? '' }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Teléfono *</label>
                        <input type="tel" name="phone" id="formPhone" required data-label="Teléfono" placeholder="Tu número de contacto"
                               value="{{ auth()->user()->phone ?? '' }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                    </div>

                    {{-- CALENDARIO DE SELECCIÓN DE FECHA --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Selecciona una fecha *</label>
                        <div id="calendarContainer" class="bg-white border border-slate-200 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-3">
                                <button type="button" id="prevMonth" class="p-1 hover:bg-slate-100 rounded">
                                    <i class="ph ph-caret-left text-lg"></i>
                                </button>
                                <span id="currentMonthDisplay" class="font-bold text-slate-800"></span>
                                <button type="button" id="nextMonth" class="p-1 hover:bg-slate-100 rounded">
                                    <i class="ph ph-caret-right text-lg"></i>
                                </button>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-slate-400 mb-2">
                                <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                            </div>
                            <div id="calendarDays" class="grid grid-cols-7 gap-1">
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-3 pt-2 border-t border-slate-100 text-xs">
                                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-mso-gold rounded-full"></span> Disponible</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-slate-200 rounded-full"></span> No disponible</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-200 rounded-full"></span> Sin cupos</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-slate-300 rounded-full"></span> Fecha pasada</span>
                            </div>
                        </div>
                        <input type="hidden" name="date_picker" id="datePicker" required data-label="Fecha de la visita">
                    </div>

                    {{-- Selector de horas --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Hora disponible *</label>
                        <select name="time_picker" id="timePicker" required data-label="Hora disponible"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm">
                            <option value="">Primero selecciona una fecha</option>
                        </select>
                        <div id="slotsInfo" class="text-xs text-slate-400 mt-1 hidden">
                            <i class="ph ph-info"></i>
                            <span id="slotsCount">0</span> cupos disponibles para esta fecha
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Mensaje (opcional)</label>
                        <textarea name="message" id="formMessage" rows="2" placeholder="¿Alguna preferencia de horario o comentario adicional?"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-mso-gold/50 outline-none text-sm resize-none"></textarea>
                    </div>

                    <button type="submit" id="submitAppointmentBtn"
                            class="w-full bg-mso-gold text-mso-blue font-bold py-3 rounded-lg hover:bg-mso-blue hover:text-white transition-all shadow-lg mt-2 flex items-center justify-center gap-2">
                        <i class="ph ph-calendar-plus"></i>
                        Solicitar Cita
                    </button>

                    <div id="formMessageError" class="text-red-500 text-xs text-center hidden"></div>
                    <div id="formMessageSuccess" class="text-green-600 text-xs text-center hidden"></div>
                </form>
                @else
                <div class="space-y-4 opacity-60">
                    <div><input type="text" placeholder="Nombre completo" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm" disabled></div>
                    <div><input type="email" placeholder="Correo electrónico" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm" disabled></div>
                    <div><input type="tel" placeholder="Teléfono" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm" disabled></div>
                    <button type="button" onclick="openAuthModal()"
                            class="w-full bg-mso-gold text-mso-blue font-bold py-3 rounded-lg hover:bg-mso-blue hover:text-white transition-all shadow-lg mt-2 flex items-center justify-center gap-2">
                        <i class="ph ph-calendar-plus"></i>
                        Solicitar Cita
                    </button>
                </div>
                @endauth

                <p class="text-xs text-slate-400 text-center mt-3">
                    <i class="ph ph-shield-check"></i> Tus datos están seguros
                </p>
            </div>
        </div>
    </div>

    {{-- Propiedades similares --}}
    @if(isset($similarProperties) && $similarProperties->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-slate-800 mb-6">Propiedades similares</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($similarProperties as $similar)
            <a href="{{ route('catalogo.show', $similar) }}" class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-all group">
                <div class="h-44 bg-slate-100 overflow-hidden relative">
                    <img src="{{ $similar->primary_image_url }}"
                         alt="{{ $similar->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         onerror="this.src='https://ui-avatars.com/api/?name='+encodeURIComponent('{{ $similar->title }}')+'&background=c5a059&color=fff&size=400'">
                    @if($similar->category)
                    <div class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] font-medium px-2 py-0.5 rounded-full backdrop-blur-sm flex items-center gap-1">
                        @if($similar->category->icon)
                            <i class="{{ $similar->category->icon }} text-xs"></i>
                        @endif
                        {{ $similar->category->name }}
                    </div>
                    @endif
                </div>
                <div class="p-3">
                    <h3 class="font-semibold text-slate-800 text-sm line-clamp-1">{{ $similar->title }}</h3>
                    <p class="text-mso-gold font-bold text-sm">{{ $similar->formatted_price }}</p>
                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                        <i class="ph ph-map-pin"></i>
                        <span>{{ $similar->stateRelation->name ?? 'Venezuela' }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</section>

{{-- MODAL DE AUTENTICACIÓN --}}
<div id="authModal" class="modal-overlay fixed inset-0 z-[200] hidden items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden text-center p-8">
        <div class="mb-4">
            <div class="w-20 h-20 bg-mso-gold/10 rounded-full flex items-center justify-center mx-auto">
                <i class="ph ph-house text-5xl text-mso-blue"></i>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-slate-800 mb-2">¡Regístrate para agendar tu visita!</h3>
        <p class="text-slate-500 text-sm mb-6">
            Para poder solicitar una cita y recibir la confirmación del asesor, necesitas tener una cuenta activa en nuestra plataforma.
        </p>
        <div class="flex flex-col gap-3">
            <a href="{{ route('login') }}" class="w-full bg-mso-blue text-white text-center font-bold py-3.5 rounded-xl hover:bg-slate-800 transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="ph ph-sign-in text-lg"></i> Iniciar Sesión
            </a>
            <a href="{{ route('register') }}" class="w-full border-2 border-slate-200 text-slate-700 text-center font-medium py-3.5 rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                <i class="ph ph-user-plus text-lg"></i> Crear Cuenta
            </a>
        </div>
        <button onclick="closeAuthModal()" class="mt-6 text-sm text-slate-400 hover:text-slate-600 transition-colors">
            <i class="ph ph-x mr-1"></i> Cerrar
        </button>
    </div>
</div>

@push('js')
@include('components.appointment-success-modal')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.changeMainImage = function(element, src) {
        const mainImage = document.getElementById('mainImage');
        if (!mainImage) return;

        mainImage.style.opacity = '0';
        mainImage.classList.add('scale-105');

        setTimeout(() => {
            mainImage.src = src;
            mainImage.onload = function() {
                mainImage.style.opacity = '1';
                mainImage.classList.remove('scale-105');
            };
            mainImage.onerror = function() {
                mainImage.src = 'https://ui-avatars.com/api/?name=Imagen&background=c5a059&color=fff&size=400';
                mainImage.style.opacity = '1';
                mainImage.classList.remove('scale-105');
            };
        }, 200);

        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active', 'border-mso-gold', 'opacity-100');
            thumb.classList.add('opacity-60');
        });
        element.classList.remove('opacity-60');
        element.classList.add('active', 'border-mso-gold', 'opacity-100');
    };

    window.openAuthModal = function() {
        const modal = document.getElementById('authModal');
        const content = modal.querySelector('.modal-content');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        setTimeout(() => content.classList.add('show'), 10);
        document.body.style.overflow = 'hidden';
    };

    window.closeAuthModal = function() {
        const modal = document.getElementById('authModal');
        const content = modal.querySelector('.modal-content');
        content.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    };

    document.getElementById('authModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeAuthModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAuthModal();
    });

    const asesorId = document.querySelector('input[name="asesor_id"]')?.value;
    let currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth() + 1;
    let selectedDate = null;
    let selectedSlots = [];

    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    function loadCalendar(year, month) {
        const url = `/api/appointments/available-days?year=${year}&month=${month}&asesor_id=${asesorId}`;

        document.getElementById('calendarDays').innerHTML = '<div class="col-span-7 text-center py-4 text-slate-400"><i class="ph ph-spinner ph-spin"></i> Cargando...</div>';
        document.getElementById('currentMonthDisplay').textContent = `${monthNames[month - 1]} ${year}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('calendarDays').innerHTML = `
                    <div class="col-span-7 text-center py-4 text-red-500">
                        <i class="ph ph-warning-circle"></i> ${data.message || 'Error al cargar días'}
                    </div>
                `;
                return;
            }
            renderCalendar(data.days);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('calendarDays').innerHTML = `
                <div class="col-span-7 text-center py-4 text-red-500">
                    <i class="ph ph-warning-circle"></i> Error al cargar el calendario
                </div>
            `;
        });
    }

    function renderCalendar(days) {
        const container = document.getElementById('calendarDays');
        let html = '';

        const firstDayOfMonth = new Date(currentYear, currentMonth - 1, 1).getDay();
        let startOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < startOffset; i++) {
            html += '<div class="h-10"></div>';
        }

        days.forEach(dayData => {
            const date = new Date(dayData.date);
            const isToday = date.toDateString() === new Date().toDateString();
            const isPast = dayData.is_past;
            const isAvailable = dayData.is_available;
            const hasSlots = dayData.slots && dayData.slots.length > 0;
            const isException = dayData.is_exception;

            let classes = 'h-10 rounded-lg cursor-pointer flex items-center justify-center text-sm transition-all duration-200 relative';

            if (isPast) {
                classes += ' text-slate-300 cursor-not-allowed bg-slate-50';
            } else if (isException) {
                classes += ' text-red-400 cursor-not-allowed bg-red-50 line-through';
            } else if (isAvailable && hasSlots) {
                classes += ' bg-mso-gold text-white hover:bg-mso-blue hover:scale-105 font-medium shadow-sm';
            } else if (isAvailable && !hasSlots) {
                classes += ' bg-red-100 text-red-400 cursor-not-allowed';
            } else {
                classes += ' bg-slate-100 text-slate-400 cursor-not-allowed';
            }

            if (isToday && !isPast) {
                classes += ' ring-2 ring-mso-blue ring-offset-2';
            }

            html += `<div class="${classes}" data-date="${dayData.date}" data-available="${isAvailable && hasSlots}" data-slots='${JSON.stringify(dayData.slots || [])}'>`;
            html += `<span>${dayData.day}</span>`;
            if (isAvailable && hasSlots && !isPast && !isException) {
                html += `<span class="absolute -bottom-1 -right-1 text-[8px] bg-white text-mso-blue rounded-full px-1 py-0.5 shadow-sm font-bold">${dayData.slots.length}</span>`;
            }
            html += '</div>';
        });

        container.innerHTML = html;

        container.querySelectorAll('[data-available="true"]').forEach(element => {
            element.addEventListener('click', function() {
                const date = this.dataset.date;
                const slots = JSON.parse(this.dataset.slots);
                selectDate(date, slots);
            });
        });
    }

    function selectDate(date, slots) {
        selectedDate = date;
        selectedSlots = slots || [];

        document.querySelectorAll('#calendarDays > div[data-date]').forEach(el => {
            el.classList.remove('ring-2', 'ring-mso-gold', 'ring-offset-2', 'scale-105');
            if (el.dataset.date === date) {
                el.classList.add('ring-2', 'ring-mso-gold', 'ring-offset-2', 'scale-105');
            }
        });

        document.getElementById('datePicker').value = date;
        populateTimeSlots(date, slots);

        const slotsInfo = document.getElementById('slotsInfo');
        const slotsCount = document.getElementById('slotsCount');
        if (slots && slots.length > 0) {
            slotsCount.textContent = slots.length;
            slotsInfo.classList.remove('hidden');
        } else {
            slotsInfo.classList.add('hidden');
        }
    }

    function populateTimeSlots(date, slots) {
        const timePicker = document.getElementById('timePicker');

        if (!slots || slots.length === 0) {
            timePicker.innerHTML = '<option value="">No hay horas disponibles</option>';
            timePicker.disabled = true;
            return;
        }

        let options = '<option value="">Seleccionar hora</option>';
        slots.forEach(slot => {
            const timeParts = slot.split(':');
            const hour = parseInt(timeParts[0]);
            const minutes = timeParts[1];
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            const display = `${hour12}:${minutes} ${ampm}`;
            options += `<option value="${slot}">${display}</option>`;
        });

        timePicker.innerHTML = options;
        timePicker.disabled = false;
    }

    document.getElementById('prevMonth')?.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        loadCalendar(currentYear, currentMonth);
    });

    document.getElementById('nextMonth')?.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        loadCalendar(currentYear, currentMonth);
    });

    document.getElementById('appointmentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const datePicker = document.getElementById('datePicker');
        const timePicker = document.getElementById('timePicker');
        const fullDateTimeInput = document.getElementById('fullDateTimeInput');

        if (!datePicker.value || !timePicker.value || timePicker.value === '') {
            showFormMessage('Por favor selecciona fecha y hora válidas.', true);
            return;
        }

        fullDateTimeInput.value = datePicker.value + 'T' + timePicker.value + ':00';

        const selectedDate = new Date(fullDateTimeInput.value);
        if (selectedDate <= new Date()) {
            showFormMessage('La fecha debe ser posterior a la fecha actual.', true);
            return;
        }

        const data = {
            name: (document.getElementById('formName').value + ' ' + document.getElementById('formLastName').value).trim(),
            email: document.getElementById('formEmail').value,
            phone: document.getElementById('formPhone').value,
            date: fullDateTimeInput.value,
            property_id: document.querySelector('input[name="property_id"]').value,
            message: document.getElementById('formMessage').value,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        };

        setLoading(true);

        fetch('{{ route("citas.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': data._token
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            setLoading(false);
            if (result.success) {
                showFormMessage(result.message, false);
                window.showAppointmentSuccessModal && window.showAppointmentSuccessModal(result.message);
                document.getElementById('appointmentForm').reset();
                document.getElementById('timePicker').innerHTML = '<option value="">Primero selecciona una fecha</option>';
                document.getElementById('timePicker').disabled = true;
                document.getElementById('slotsInfo').classList.add('hidden');
                setTimeout(() => loadCalendar(currentYear, currentMonth), 1000);
            } else {
                showFormMessage(result.message || 'Error al agendar la cita.', true);
            }
        })
        .catch(error => {
            setLoading(false);
            console.error('Error:', error);
            showFormMessage('Error de conexión. Intenta de nuevo más tarde.', true);
        });
    });

    function showFormMessage(message, isError = false) {
        const errorDiv = document.getElementById('formMessageError');
        const successDiv = document.getElementById('formMessageSuccess');
        if (isError) {
            errorDiv.innerText = message;
            errorDiv.classList.remove('hidden');
            successDiv.classList.add('hidden');
        } else {
            successDiv.innerText = message;
            successDiv.classList.remove('hidden');
            errorDiv.classList.add('hidden');
        }
        setTimeout(() => {
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
        }, 5000);
    }

    function setLoading(loading) {
        const btn = document.getElementById('submitAppointmentBtn');
        if (loading) {
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Enviando...';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-calendar-plus"></i> Solicitar Cita';
        }
    }

    if (asesorId) {
        loadCalendar(currentYear, currentMonth);
    }
});
</script>
@endpush
@endsection
