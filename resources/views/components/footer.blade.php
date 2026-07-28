{{-- Pie de página con información de contacto y enlaces --}}
<footer class="bg-slate-900 text-white pt-20 pb-10">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <!-- Logo y descripción -->
            <div class="col-span-1 md:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group mb-6">
                    <div class="w-12 h-12 border-2 border-mso-gold flex items-center justify-center overflow-hidden rounded-xl transition-all duration-300 group-hover:scale-105 bg-white">
                        <img src="{{ asset('favicon-96x96.png') }}"
                             alt="MSO Inmobiliaria"
                             class="w-10 h-10 object-contain transition-all duration-300"
                             loading="lazy"
                             width="40"
                             height="40"
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=MSO&background=c5a059&color=fff&size=96&bold=true';">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-serif font-bold text-xl text-white leading-none tracking-wide group-hover:text-mso-gold transition-colors">MSO</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Inmobiliaria</span>
                    </div>
                </a>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Redefiniendo el estándar inmobiliario en Venezuela con exclusividad y confianza.
                </p>
            </div>

            <!-- Explorar -->
            <div>
                <h4 class="font-bold text-lg mb-4">Explorar</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('catalogo.index') }}?type=venta" class="hover:text-mso-gold transition-colors">Comprar</a></li>
                    <li><a href="{{ route('catalogo.index') }}?type=alquiler" class="hover:text-mso-gold transition-colors">Alquilar</a></li>
                </ul>
            </div>

            <!-- Compañía -->
            <div>
                <h4 class="font-bold text-lg mb-4">Compañía</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('servicios.public') }}#sobre-nosotros" class="hover:text-mso-gold transition-colors">Sobre Nosotros</a></li>
                    <li><a href="{{ route('servicios.public') }}#equipo" class="hover:text-mso-gold transition-colors">Equipo</a></li>
            </div>

            <!-- Contacto -->
            <div>
                <h4 class="font-bold text-lg mb-4">Contacto</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li class="flex items-start gap-2">
                        <i class="ph ph-map-pin text-mso-gold mt-0.5"></i>
                        <span>Centro Comercial Buena Aventura</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="ph ph-phone text-mso-gold mt-0.5"></i>
                        <span>+58 426-9077422</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="ph ph-envelope text-mso-gold mt-0.5"></i>
                        <span>msogruoinmobilirio.2023@gmail.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer inferior -->
        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} MSO Grupo Inmobiliario. Todos los derechos reservados.</p>
            <div class="flex gap-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition-colors">Privacidad</a>
                <a href="#" class="hover:text-white transition-colors">Términos</a>
            </div>
        </div>
    </div>
</footer>
