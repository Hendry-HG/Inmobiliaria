{{-- Vista pública de servicios inmobiliarios para visitantes --}}
@extends('layouts.landing')

@section('title', 'Servicios Inmobiliarios')

@section('content')
<section class="py-12 md:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Encabezado --}}
    <div class="text-center mb-12">
        <span class="text-mso-gold font-bold tracking-widest uppercase text-xs mb-2 block">Nuestros Servicios</span>
        <h1 class="text-3xl md:text-5xl font-serif text-slate-900">Soluciones Inmobiliarias</h1>
        <p class="text-slate-500 mt-3 max-w-2xl mx-auto">Ofrecemos un servicio integral para todas tus necesidades inmobiliarias</p>
    </div>

    {{-- Grid de Servicios --}}
    {{-- ============================================= --}}
    {{-- Los servicios públicos solo se muestran si están activos --}}
    {{-- ============================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
            <div class="bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 group border border-slate-100">
                {{-- Imagen del servicio --}}
                @if($service->image)
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ asset('storage/' . $service->image) }}"
                             alt="{{ $service->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @if($service->badge)
                            <span class="absolute top-3 right-3 bg-mso-gold text-mso-blue text-xs font-bold px-3 py-1 rounded-full">
                                {{ $service->badge }}
                            </span>
                        @endif
                    </div>
                @else
                    <div class="h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                        <i class="ph {{ $service->icon ?? 'ph-house' }} text-6xl"
                           style="color: {{ $service->color ?? '#c5a059' }}"></i>
                    </div>
                @endif

                {{-- Contenido --}}
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        @if($service->icon)
                            <i class="{{ $service->icon }} text-2xl" style="color: {{ $service->color ?? '#c5a059' }}"></i>
                        @endif
                        <h3 class="text-xl font-bold text-slate-800">{{ $service->title }}</h3>
                    </div>

                    <p class="text-slate-500 text-sm">{{ $service->description }}</p>

                    {{-- Características --}}
                    @if($service->features)
                        <div class="mt-4 space-y-1.5">
                            @foreach($service->features as $feature)
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <i class="ph ph-check-circle text-mso-gold"></i>
                                    <span>{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($service->external_url)
                        <a href="{{ $service->external_url }}"
                           target="_blank"
                           class="mt-4 inline-flex items-center gap-2 text-mso-gold font-semibold hover:text-mso-blue transition-colors">
                            Conocer más <i class="ph ph-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <i class="ph ph-warning text-4xl text-slate-300 block mb-3"></i>
                <p class="text-slate-500">No hay servicios disponibles en este momento.</p>
            </div>
        @endforelse
    </div>

    {{-- Sección de Asesores --}}
    @if(isset($asesores) && $asesores->isNotEmpty())
        <div class="mt-20">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-4xl font-serif text-slate-900">Nuestros Asesores</h2>
                <p class="text-slate-500 mt-2">Expertos listos para ayudarte a encontrar la propiedad perfecta</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($asesores as $asesor)
                    <div class="bg-white rounded-2xl p-6 text-center shadow-md hover:shadow-xl transition-all duration-300 border border-slate-100">
                        <img src="{{ $asesor->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($asesor->full_name ?? $asesor->name) . '&background=c5a059&color=fff&size=120' }}"
                             class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-mso-gold mb-4"
                             alt="{{ $asesor->full_name ?? $asesor->name }}">

                        <h4 class="font-bold text-slate-800 text-lg">{{ $asesor->full_name ?? $asesor->name }}</h4>

                        <p class="text-xs text-slate-500">{{ $asesor->email }}</p>

                        @if($asesor->phone)
                            <p class="text-sm text-slate-600 mt-1">
                                <i class="ph ph-phone text-mso-gold"></i> {{ $asesor->phone }}
                            </p>
                        @endif

                        @if($asesor->specialization)
                            <span class="inline-block mt-2 text-xs font-medium px-3 py-1 rounded-full bg-mso-gold/20 text-mso-gold">
                                {{ $asesor->specialization }}
                            </span>
                        @endif

                        @if($asesor->bio)
                            <div class="mt-3 text-sm text-slate-600 line-clamp-3">
                                {{ $asesor->bio }}
                            </div>
                        @endif

                        <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400">
                            <span class="flex items-center justify-center gap-1">
                                <i class="ph ph-buildings"></i>
                                {{ $asesor->properties->where('status', 'publicada')->count() }} propiedades
                            </span>
                        </div>

                        @if($asesor->social_links)
                            <div class="mt-3 flex justify-center gap-3">
                                @if(isset($asesor->social_links['whatsapp']))
                                    <a href="https://wa.me/{{ $asesor->social_links['whatsapp'] }}"
                                       target="_blank"
                                       class="text-green-600 hover:text-green-700 transition-colors">
                                        <i class="ph ph-whatsapp-logo text-xl"></i>
                                    </a>
                                @endif
                                @if(isset($asesor->social_links['instagram']))
                                    <a href="https://instagram.com/{{ $asesor->social_links['instagram'] }}"
                                       target="_blank"
                                       class="text-pink-600 hover:text-pink-700 transition-colors">
                                        <i class="ph ph-instagram-logo text-xl"></i>
                                    </a>
                                @endif
                                @if(isset($asesor->social_links['facebook']))
                                    <a href="https://facebook.com/{{ $asesor->social_links['facebook'] }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-700 transition-colors">
                                        <i class="ph ph-facebook-logo text-xl"></i>
                                    </a>
                                @endif
                                @if(isset($asesor->social_links['linkedin']))
                                    <a href="https://linkedin.com/in/{{ $asesor->social_links['linkedin'] }}"
                                       target="_blank"
                                       class="text-blue-800 hover:text-blue-900 transition-colors">
                                        <i class="ph ph-linkedin-logo text-xl"></i>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Preguntas Frecuentes --}}
    <div class="mt-20" id="faq">
        <div class="text-center mb-10">
            <span class="text-mso-gold font-bold tracking-widest uppercase text-xs mb-2 block">Resolvemos tus dudas</span>
            <h2 class="text-2xl md:text-4xl font-serif text-slate-900">Preguntas Frecuentes</h2>
            <p class="text-slate-500 mt-2 max-w-2xl mx-auto">Encuentra respuestas a las consultas más comunes sobre nuestros servicios inmobiliarios</p>
        </div>

        <div class="max-w-3xl mx-auto space-y-3">
            @php
                $faqs = [
                    [
                        'q' => '¿Cómo solicito una valoración de mi propiedad?',
                        'a' => 'Completa el formulario de "Solicitar Valoración" disponible en el sitio indicando la dirección y características de tu propiedad. Un asesor inmobiliario se comunicará contigo para coordinar la visita y entregarte una estimación del valor de mercado.',
                    ],
                    [
                        'q' => '¿El servicio de corretaje tiene algún costo para el cliente?',
                        'a' => 'La publicación de propiedades y la asesoría inicial no tienen costo. La comisión por corretaje solo aplica al concretarse una venta o alquiler y se pacta previamente con nuestro equipo conforme a la normativa venezolana.',
                    ],
                    [
                        'q' => '¿Cuánto tiempo tarda en venderse o alquilarse una propiedad?',
                        'a' => 'El tiempo depende del precio, la ubicación y las condiciones del mercado. Como referencia general, nuestras propiedades publicadas se destacan en el sitio y se difunden entre nuestros asesores para acelerar el proceso.',
                    ],
                    [
                        'q' => '¿Qué documentos necesito para publicar mi propiedad?',
                        'a' => 'Generalmente se requieren el título de propiedad, cédula de identidad del titular y un certificado de solvencia al día. Un asesor te indicará la documentación exacta según el caso.',
                    ],
                    [
                        'q' => '¿Cómo agendo una cita para ver una propiedad?',
                        'a' => 'Puedes solicitar una cita desde la ficha de cada propiedad o contactándonos por WhatsApp, teléfono o correo. Coordinaremos contigo y con el asesor el mejor horario.',
                    ],
                    [
                        'q' => '¿Las propiedades publicadas están verificadas?',
                        'a' => 'Nuestros asesores verifican la información de los inmuebles antes de publicarlos. Aun así, te recomendamos confirmar siempre los detalles directamente con el asesor asignado.',
                    ],
                    [
                        'q' => '¿Trabajan en todo Venezuela o solo en una zona?',
                        'a' => 'Tenemos presencia principalmente en la región donde operamos, pero gestionamos solicitudes en todo el país. Escríbenos y verificaremos si podemos atender tu caso.',
                    ],
                    [
                        'q' => '¿Cómo protegen mis datos personales?',
                        'a' => 'Tratamos tus datos conforme a nuestra Política de Privacidad y a la normativa venezolana e internacional aplicable. Consulta la sección de Privacidad en el pie de página para más detalles.',
                    ],
                ];
            @endphp

            @foreach($faqs as $faq)
                <div class="faq-item bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <button type="button"
                            onclick="toggleFaq(this)"
                            class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 hover:bg-slate-50 transition-colors">
                        <span class="font-semibold text-slate-800 text-sm sm:text-base">{{ $faq['q'] }}</span>
                        <span class="faq-icon flex-shrink-0 w-7 h-7 rounded-full bg-mso-gold/15 text-mso-gold flex items-center justify-center transition-transform duration-300">
                            <i class="ph ph-plus"></i>
                        </span>
                    </button>
                    <div class="faq-answer hidden px-5 pb-5">
                        <p class="text-sm text-slate-500 leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Contacto --}}
    <div class="mt-20 bg-mso-blue rounded-2xl p-8 md:p-12 text-center text-white">
        <h2 class="text-2xl md:text-4xl font-serif mb-4">¿Necesitas ayuda?</h2>
        <p class="text-slate-300 max-w-2xl mx-auto">Contáctanos y uno de nuestros asesores te atenderá personalmente</p>
        <div class="flex flex-wrap justify-center gap-4 mt-6">
            @if($config->support_whatsapp ?? false)
                <a href="https://wa.me/{{ $config->support_whatsapp }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full font-semibold transition-colors">
                    <i class="ph ph-whatsapp-logo text-xl"></i>
                    WhatsApp
                </a>
            @endif
            @if($config->support_phone ?? false)
                <a href="tel:{{ $config->support_phone }}"
                   class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-full font-semibold transition-colors">
                    <i class="ph ph-phone text-xl"></i>
                    {{ $config->support_phone }}
                </a>
            @endif
        </div>
    </div>
</section>

@push('css')
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@push('js')
<script>
    // ============================================================
    // ACORDEÓN DE PREGUNTAS FRECUENTES
    // ============================================================
    window.toggleFaq = function(button) {
        const item = button.closest('.faq-item');
        const answer = item.querySelector('.faq-answer');
        const icon = item.querySelector('.faq-icon');
        const isOpen = !answer.classList.contains('hidden');

        // Cerrar los demás
        document.querySelectorAll('.faq-item').forEach(function(el) {
            const a = el.querySelector('.faq-answer');
            const ic = el.querySelector('.faq-icon');
            if (!a.classList.contains('hidden')) {
                a.classList.add('hidden');
            }
            if (ic) {
                ic.style.transform = 'rotate(0deg)';
            }
        });

        // Abrir/cerrar el seleccionado
        if (isOpen) {
            answer.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        } else {
            answer.classList.remove('hidden');
            icon.style.transform = 'rotate(45deg)';
        }
    };
</script>
@endpush
@endsection
