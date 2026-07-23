@php
    $config = App\Models\SiteConfiguration::getConfig();
    $slides = $config->hero_images ?? [];

    //  Usar imágenes optimizadas
    if (empty($slides)) {
        $slides = [
            'https://images.unsplash.com/photo-1600596542815-2495db0c5903?w=800&h=500&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=500&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=500&fit=crop&auto=format'
        ];
    }

    // Limpiar URLs
    $slides = array_map(function($slide) {
        if (str_contains($slide, '127.0.0.1:8000/storage')) {
            $path = parse_url($slide, PHP_URL_PATH);
            return $path;
        }
        if (str_contains($slide, 'unsplash.com')) {
            if (!str_contains($slide, 'w=')) {
                $slide = $slide . '?w=800&h=500&fit=crop&auto=format';
            }
        }
        return $slide;
    }, $slides);
@endphp

<section class="relative h-[500px] md:h-[650px] flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-slate-900">
        @if(!empty($slides))
            @foreach($slides as $index => $slide)
                <div class="hero-slider {{ $index === 0 ? 'active' : '' }}"
                     style="background-image: url('{{ $slide }}');">
                </div>
            @endforeach
        @endif
    </div>

    <div class="absolute inset-0 hero-overlay z-10"></div>

    <div class="relative z-20 max-w-5xl mx-auto px-4 sm:px-6 text-center text-white mt-10 md:mt-20">
        <div class="animate-fade-in-up">
            <span class="inline-block py-1 px-3 border border-mso-gold/50 rounded-full text-mso-gold text-[10px] sm:text-xs tracking-[0.2em] uppercase mb-4 sm:mb-6 backdrop-blur-sm">
                {{ $config->hero_badge }}
            </span>
            <h1 class="font-serif text-3xl sm:text-5xl md:text-7xl font-bold leading-tight mb-4 sm:mb-6 drop-shadow-lg">
                {{ $config->hero_title_line1 }} <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-mso-gold to-yellow-200">
                    {{ $config->hero_title_line2 }}
                </span>
            </h1>
            <p class="text-slate-200 text-sm sm:text-lg md:text-xl font-light max-w-2xl mx-auto mb-8 sm:mb-12 leading-relaxed opacity-90 px-2">
                {{ $config->hero_subtitle }}
            </p>
        </div>
    </div>

    @if(count($slides) > 1)
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 flex gap-2">
            @foreach($slides as $index => $slide)
                <button onclick="goToSlide({{ $index }})"
                        class="w-2 h-2 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-mso-gold w-8' : 'bg-white/50 hover:bg-white/80' }}"></button>
            @endforeach
        </div>
    @endif
</section>

@push('js')
<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slider');
    const indicators = document.querySelectorAll('.bottom-8 button');
    let interval;

    function showSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        if (indicators.length) {
            indicators.forEach((ind, i) => {
                ind.classList.toggle('bg-mso-gold', i === index);
                ind.classList.toggle('w-8', i === index);
                ind.classList.toggle('w-2', i !== index);
                ind.classList.toggle('bg-white/50', i !== index);
                ind.classList.toggle('hover:bg-white/80', i !== index);
            });
        }
        slides[index].classList.add('active');
        currentSlide = index;
    }

    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    function goToSlide(index) {
        clearInterval(interval);
        showSlide(index);
        startInterval();
    }

    function startInterval() {
        if (slides.length > 1) {
            interval = setInterval(nextSlide, 5000);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (slides.length > 0) {
            showSlide(0);
            startInterval();
        }
    });
</script>
@endpush

<style>
.hero-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 1s ease-in-out;
    transform: scale(1.05);
}

.hero-slider.active {
    opacity: 1;
    transform: scale(1);
}

.hero-overlay {
    background: linear-gradient(
        135deg,
        rgba(15, 23, 42, 0.85) 0%,
        rgba(15, 23, 42, 0.40) 50%,
        rgba(15, 23, 42, 0.85) 100%
    );
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}
</style>
