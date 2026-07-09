@props([
    'properties' => null,
    'limit' => 8,
    'title' => 'Propiedades Recientes',
    'subtitle' => 'Las propiedades más recientes publicadas por nuestros asesores',
    'badge' => 'Recientes'
])

@php
    if (is_null($properties)) {
        $properties = App\Models\Property::with(['primaryImage', 'user', 'stateRelation', 'cityRelation'])
            ->published()
            ->latest()
            ->limit($limit)
            ->get();
    }
@endphp

<section id="recientes" class="py-8 sm:py-12 md:py-20 max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-4 sm:mb-6 md:mb-10">
        <div class="animate-fade-in-up w-full sm:w-auto">
            <span class="text-mso-gold font-bold tracking-widest uppercase text-[8px] sm:text-[10px] md:text-xs mb-1 sm:mb-2 block">
                {{ $badge }}
            </span>
            <h2 class="text-xl sm:text-2xl md:text-4xl lg:text-5xl font-serif text-slate-900">
                {{ $title }}
            </h2>
            <p class="text-slate-500 text-[10px] xs:text-xs sm:text-sm md:text-base mt-1 sm:mt-2 max-w-2xl">{{ $subtitle }}</p>
        </div>
        <a href="{{ route('catalogo.index') }}"
           class="hidden md:flex items-center text-slate-500 hover:text-mso-blue font-semibold transition-colors border-b border-slate-300 pb-1 hover:border-mso-blue mt-2 sm:mt-0">
            Ver catálogo completo <i class="ph ph-arrow-right ml-2"></i>
        </a>
    </div>

    @if($properties && $properties->count() > 0)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

        <div class="w-full relative">
            <div class="swiper recent-carousel swiper-container relative overflow-visible">
                <div class="swiper-wrapper">
                    @foreach($properties as $property)
                        <div class="swiper-slide h-auto !flex !justify-center">
                            <div class="bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 group h-full flex flex-col w-full"
                                 style="max-width: 320px; width: 100%;">

                                {{-- Imagen --}}
                                <div class="relative h-48 xs:h-52 sm:h-56 md:h-60 lg:h-64 overflow-hidden bg-slate-200 flex-shrink-0">
                                    <img src="{{ $property->primary_image_url }}"
                                         alt="{{ $property->title }}"
                                         loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                         onerror="this.src='https://via.placeholder.com/800x600?text=Sin+Imagen'">

                                    {{-- Badge de tipo --}}
                                    <div class="absolute top-2 left-2 sm:top-3 sm:left-3 bg-mso-gold text-mso-blue text-[8px] xs:text-[10px] sm:text-xs font-bold px-1.5 sm:px-2 md:px-3 py-0.5 sm:py-1 rounded-full z-10">
                                        {{ ucfirst($property->type) }}
                                    </div>

                                    {{-- Badge "Nuevo" --}}
                                    <div class="absolute top-2 right-2 sm:top-3 sm:right-3 bg-green-500 text-white text-[8px] xs:text-[10px] sm:text-xs font-bold px-1.5 sm:px-2 md:px-3 py-0.5 sm:py-1 rounded-full shadow-lg flex items-center gap-0.5 sm:gap-1 z-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 256 256" class="text-[8px] sm:text-[10px]">
                                            <path fill="currentColor" d="M128,24a104,104,0,1,0,104,104A104.2,104.2,0,0,0,128,24Zm8,104v44a8,8,0,0,1-16,0V128a8,8,0,0,1,0-16V80a8,8,0,0,1,16,0v32a8,8,0,0,1,0,16Z"/>
                                        </svg>
                                        <span class="hidden xs:inline">Nuevo</span>
                                    </div>

                                    {{-- Precio --}}
                                    @if($property->price)
                                        <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-black/60 backdrop-blur-sm text-white px-1.5 sm:px-2 md:px-3 py-0.5 sm:py-1 rounded-full text-[10px] xs:text-xs sm:text-sm font-bold z-10">
                                            ${{ number_format($property->price, 0, ',', '.') }}
                                            @if($property->type == 'alquiler') <span class="text-[8px] xs:text-[10px]">/mes</span> @endif
                                        </div>
                                    @endif

                                    {{-- Asesor --}}
                                    @if($property->user)
                                        <div class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 bg-white/95 backdrop-blur-sm px-2 sm:px-2.5 md:px-3 py-0.5 sm:py-1 rounded-full text-[8px] xs:text-[10px] sm:text-xs font-medium text-slate-700 shadow-lg flex items-center gap-1 sm:gap-1.5 z-10"
                                             style="max-width: 45%;">
                                            <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-mso-gold flex items-center justify-center text-white text-[7px] xs:text-[8px] sm:text-[10px] font-bold flex-shrink-0">
                                                {{ $property->user->full_name ? $property->user->full_name[0] : $property->user->name[0] }}
                                            </span>
                                            <span class="truncate" style="max-width: 60px; sm:max-width: 80px; md:max-width: 100px;">
                                                {{ $property->user->full_name ?? $property->user->name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Contenido --}}
                                <div class="p-3 xs:p-4 sm:p-5 flex-1 flex flex-col">
                                    <h3 class="text-sm xs:text-base sm:text-lg font-bold text-slate-800 group-hover:text-mso-gold transition-colors line-clamp-1">
                                        {{ $property->title }}
                                    </h3>

                                    <div class="flex items-center gap-0.5 xs:gap-1 text-[10px] xs:text-xs sm:text-sm text-slate-500 mt-0.5 xs:mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256" class="text-mso-gold flex-shrink-0">
                                            <path fill="currentColor" d="M128,64a40,40,0,1,0,40,40A40,40,0,0,0,128,64Zm0,64a24,24,0,1,1,24-24A24,24,0,0,1,128,128Zm0-112a88.1,88.1,0,0,0-88,88c0,31.4,14.5,64.7,42,96.4a216.6,216.6,0,0,0,36.6,30.6a8.1,8.1,0,0,0,8.9,0c4.2-2.8,12.8-8.8,24.6-20.4c14.2-13.9,27.4-31.4,38.1-50.4c9.9-17.5,15.3-34.2,15.8-50.2A88.1,88.1,0,0,0,128,16Zm0,160c-15.2,0-56-20.8-56-72a56,56,0,1,1,112,0C184,155.2,143.2,176,128,176Z"/>
                                        </svg>
                                        <span class="line-clamp-1">{{ $property->location ?? $property->address ?? $property->full_location ?? 'Ubicación no especificada' }}</span>
                                    </div>

                                    {{-- Características con SVG icons --}}
                                    <div class="flex flex-wrap items-center gap-1 xs:gap-2 sm:gap-3 md:gap-4 mt-2 xs:mt-3 text-[10px] xs:text-xs sm:text-sm text-slate-600">
                                        @if($property->bedrooms)
                                            <span class="flex items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256">
                                                    <path fill="currentColor" d="M216,72H176V56a16,16,0,0,0-16-16H96A16,16,0,0,0,80,56V72H40A16,16,0,0,0,24,88V176a16,16,0,0,0,16,16v16a8,8,0,0,0,16,0V192H200v16a8,8,0,0,0,16,0V192a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72ZM96,56h64V72H96ZM216,176H40V88H216v88Z"/>
                                                </svg>
                                                {{ $property->bedrooms }}
                                            </span>
                                        @endif
                                        @if($property->bathrooms)
                                            <span class="flex items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256">
                                                    <path fill="currentColor" d="M240,104H224V72a8,8,0,0,0-8-8H152V40a8,8,0,0,0-16,0V64H112a48,48,0,0,0-48,48v24H40a16,16,0,0,0-16,16v32a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V152a16,16,0,0,0-16-16H80V112a32,32,0,0,1,32-32h64a8,8,0,0,1,8,8v24H120a8,8,0,0,0,0,16h88v40H48V152H216v32H40V152H80v8a8,8,0,0,0,16,0V152h8v32a8,8,0,0,0,16,0V152H224v8a8,8,0,0,0,16,0V112C240,107.6,240,107.6,240,104Z"/>
                                                </svg>
                                                {{ $property->bathrooms }}
                                            </span>
                                        @endif
                                        @if($property->parking_spaces)
                                            <span class="flex items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256">
                                                    <path fill="currentColor" d="M248,136v40a8,8,0,0,1-8,8H216a8,8,0,0,1-8-8V168H48v8a8,8,0,0,1-8,8H16a8,8,0,0,1-8-8V136a8,8,0,0,1,8-8H80V64a8,8,0,0,1,8-8H200a8,8,0,0,1,8,8v64h32A8,8,0,0,1,248,136ZM88,72v56h56V72Zm112,56h16V72H200Zm-96-8h40V80H104Z"/>
                                                </svg>
                                                {{ $property->parking_spaces }}
                                            </span>
                                        @endif
                                        @if($property->area)
                                            <span class="flex items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256">
                                                    <path fill="currentColor" d="M232,80V200a8,8,0,0,1-16,0V80a8,8,0,0,1,16,0ZM40,40V184a8,8,0,0,0,16,0V40a8,8,0,0,0-16,0ZM216,40a8,8,0,0,0-8,8V184a8,8,0,0,0,16,0V48A8,8,0,0,0,216,40ZM56,216a8,8,0,0,0,8-8V48a8,8,0,0,0-16,0V208A8,8,0,0,0,56,216Z"/>
                                                </svg>
                                                {{ $property->area }}m²
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Fecha --}}
                                    <div class="mt-1 xs:mt-2 text-[8px] xs:text-[10px] sm:text-xs text-slate-400 flex items-center gap-0.5 xs:gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 256 256">
                                            <path fill="currentColor" d="M208,32H184V24a8,8,0,0,0-16,0v8H88V24a8,8,0,0,0-16,0v8H48A16,16,0,0,0,32,56V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V56A16,16,0,0,0,208,32Zm0,176H48V80H208ZM88,120a8,8,0,0,1,8-8h64a8,8,0,0,1,0,16H96A8,8,0,0,1,88,120Zm0,40a8,8,0,0,1,8-8h32a8,8,0,0,1,0,16H96A8,8,0,0,1,88,160Z"/>
                                        </svg>
                                        <span>{{ $property->created_at->format('d M, Y') }}</span>
                                    </div>

                                    <div class="mt-auto pt-2 xs:pt-3">
                                        <a href="{{ route('catalogo.show', $property) }}"
                                           class="inline-flex items-center gap-1 sm:gap-2 text-mso-gold font-semibold hover:text-mso-blue transition-colors text-[10px] xs:text-xs sm:text-sm">
                                            Ver detalles
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256">
                                                <path fill="currentColor" d="M221.7,133.7l-72,72A8.3,8.3,0,0,1,144,208a8.5,8.5,0,0,1-3.1-.6A8,8,0,0,1,136,200V136H40a8,8,0,0,1,0-16h96V56a8,8,0,0,1,4.9-7.4a8.4,8.4,0,0,1,8.8,1.7l72,72A8.1,8.1,0,0,1,221.7,133.7Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Paginación --}}
                <div class="swiper-pagination !bottom-0 !relative !mt-4 sm:!mt-6 md:!mt-8"></div>

                {{-- Navegación --}}
                <div class="swiper-button-next !hidden md:!flex !text-mso-gold !w-8 !h-8 lg:!w-10 lg:!h-10 !bg-white/80 !rounded-full !shadow-lg hover:!bg-white transition-colors after:!text-sm lg:after:!text-lg"></div>
                <div class="swiper-button-prev !hidden md:!flex !text-mso-gold !w-8 !h-8 lg:!w-10 lg:!h-10 !bg-white/80 !rounded-full !shadow-lg hover:!bg-white transition-colors after:!text-sm lg:after:!text-lg"></div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const swiper = new Swiper('.recent-carousel', {
                    slidesPerView: 1,
                    centeredSlides: true,
                    loop: false, // ❌ LOOP DESACTIVADO - Soluciona el problema en desktop
                    spaceBetween: 16,
                    slideToClickedSlide: true,
                    // Autoplay desactivado completamente
                    // autoplay: {
                    //     delay: 4000,
                    //     disableOnInteraction: true,
                    //     pauseOnMouseEnter: true,
                    // },
                    pagination: {
                        el: '.recent-carousel .swiper-pagination',
                        clickable: true,
                        dynamicBullets: true,
                    },
                    navigation: {
                        nextEl: '.recent-carousel .swiper-button-next',
                        prevEl: '.recent-carousel .swiper-button-prev',
                    },
                    breakpoints: {
                        320: {
                            slidesPerView: 1,
                            spaceBetween: 12,
                            centeredSlides: true,
                            loop: false,
                        },
                        480: {
                            slidesPerView: 1,
                            spaceBetween: 16,
                            centeredSlides: true,
                            loop: false,
                        },
                        640: {
                            slidesPerView: 1.5,
                            spaceBetween: 16,
                            centeredSlides: true,
                            loop: false,
                        },
                        768: {
                            slidesPerView: 2,
                            spaceBetween: 20,
                            centeredSlides: true,
                            loop: false,
                        },
                        992: {
                            slidesPerView: 2.5,
                            spaceBetween: 24,
                            centeredSlides: true,
                            loop: false,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 24,
                            centeredSlides: false,
                            loop: false, // ✅ Loop desactivado en desktop
                        },
                        1280: {
                            slidesPerView: 4,
                            spaceBetween: 30,
                            centeredSlides: false,
                            loop: false, // ✅ Loop desactivado en desktop
                        }
                    }
                });
            });
        </script>

        <style>
            .recent-carousel {
                overflow: visible !important;
            }

            .recent-carousel .swiper-wrapper {
                padding: 4px 0;
            }

            .recent-carousel .swiper-slide {
                height: auto !important;
                display: flex !important;
                justify-content: center !important;
                align-items: stretch !important;
            }

            .recent-carousel .swiper-slide > div {
                width: 100%;
                max-width: 340px;
                margin: 0 auto;
            }

            .recent-carousel .swiper-pagination-bullet {
                background: #c5a059;
                width: 8px;
                height: 8px;
            }

            .recent-carousel .swiper-pagination-bullet-active {
                background: #c5a059 !important;
                width: 24px;
                border-radius: 4px;
            }

            .recent-carousel .swiper-button-next,
            .recent-carousel .swiper-button-prev {
                color: #c5a059;
            }

            .recent-carousel .swiper-button-next:hover,
            .recent-carousel .swiper-button-prev:hover {
                color: #0f172a;
            }

            .recent-carousel .swiper-pagination {
                position: relative !important;
                margin-top: 16px;
            }

            @media (max-width: 480px) {
                .recent-carousel .swiper-slide > div {
                    max-width: 300px;
                }
                .recent-carousel .swiper-pagination-bullet {
                    width: 6px;
                    height: 6px;
                }
                .recent-carousel .swiper-pagination-bullet-active {
                    width: 18px;
                }
            }

            @media (min-width: 481px) and (max-width: 767px) {
                .recent-carousel .swiper-slide > div {
                    max-width: 320px;
                }
            }

            @media (min-width: 768px) and (max-width: 1023px) {
                .recent-carousel .swiper-slide > div {
                    max-width: 340px;
                }
            }

            @media (min-width: 1024px) {
                .recent-carousel .swiper-pagination {
                    display: none !important;
                }
                .recent-carousel .swiper-slide > div {
                    max-width: 100%;
                }
            }

            .recent-carousel .swiper-slide .absolute.bottom-2.left-2,
            .recent-carousel .swiper-slide .absolute.bottom-3.left-3 {
                max-width: 55%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .recent-carousel .swiper-slide .absolute.bottom-2.right-2,
            .recent-carousel .swiper-slide .absolute.bottom-3.right-3 {
                max-width: 45%;
            }
        </style>
    @else
        <div class="text-center py-8 sm:py-12 text-slate-400 bg-white rounded-2xl shadow-sm">
            <i class="ph ph-house text-4xl sm:text-5xl mb-2 block"></i>
            <p class="text-base sm:text-lg">No hay propiedades recientes disponibles.</p>
            <p class="text-sm sm:text-base text-slate-400">Pronto publicaremos nuevas propiedades.</p>
        </div>
    @endif
</section>
