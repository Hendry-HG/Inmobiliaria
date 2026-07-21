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

    {{-- Galería de imágenes publicitarias --}}
    @if(isset($galleryImages) && $galleryImages->isNotEmpty())
        <div class="mb-12">
            <div class="swiper service-gallery swiper-container rounded-2xl overflow-hidden shadow-xl">
                <div class="swiper-wrapper">
                    @foreach($galleryImages as $image)
                        <div class="swiper-slide">
                            <img src="{{ $image->image_url }}"
                                 alt="{{ $image->alt_text ?? 'Servicio inmobiliario' }}"
                                 class="w-full h-64 md:h-96 object-cover">
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    @endif

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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
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
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($galleryImages) && $galleryImages->isNotEmpty())
        new Swiper('.service-gallery', {
            slidesPerView: 1,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.service-gallery .swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            navigation: {
                nextEl: '.service-gallery .swiper-button-next',
                prevEl: '.service-gallery .swiper-button-prev',
            },
        });
        @endif
    });
</script>
@endpush
@endsection
