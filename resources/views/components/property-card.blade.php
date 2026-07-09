@props([
    'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1000&auto=format&fit=crop',
    'title' => 'Propiedad',
    'location' => 'Ubicación',
    'price' => '$0',
    'type' => 'En Venta', // 'En Venta' or 'Alquiler'
    'featured' => false
])

<div class="group cursor-pointer">
    <div class="relative overflow-hidden rounded-2xl mb-4 shadow-lg aspect-[4/3]">
        <img src="{{ $image }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="absolute top-4 left-4">
            <span class="bg-white/90 backdrop-blur-md text-slate-900 px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide shadow-lg">
                {{ $type }}
            </span>
        </div>
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white hover:bg-white hover:text-red-500 transition-all transform hover:scale-110">
            <i class="ph-fill ph-heart text-xl"></i>
        </button>
    </div>
    <h3 class="font-serif text-2xl font-bold text-slate-900 mb-1 group-hover:text-mso-blue transition-colors">{{ $title }}</h3>
    <p class="text-slate-500 text-sm mb-3">{{ $location }}</p>
    <p class="text-mso-gold font-serif text-2xl font-semibold">{{ $price }}</p>
</div>