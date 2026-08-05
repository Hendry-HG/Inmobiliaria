{{-- Barra de navegación principal del sitio web --}}
<nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm transition-all duration-500" id="mainNavbar" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 sm:h-20 items-center">

            <!-- Logo Responsivo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 group flex-shrink-0 min-w-0">
                <div class="w-10 h-10 sm:w-16 sm:h-16 border-2 border-mso-gold flex items-center justify-center overflow-hidden rounded-xl transition-all duration-300 group-hover:scale-105 bg-white">
                    <img src="{{ asset('favicon-96x96.png') }}"
                         alt="MSO Inmobiliaria"
                         class="w-8 h-8 sm:w-14 sm:h-14 object-contain transition-all duration-300"
                         id="logoImage"
                         loading="eager"
                         width="56"
                         height="56"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=MSO&background=c5a059&color=fff&size=96&bold=true';">
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="font-serif font-bold text-base sm:text-xl text-slate-800 leading-none tracking-wide group-hover:text-mso-blue transition-colors">MSO</span>
                    {{-- TEXTO "Grupo Inmobiliario" - AHORA VISIBLE EN TODOS LOS DISPOSITIVOS --}}
                    <span class="text-[8px] sm:text-[10px] text-slate-500 uppercase tracking-[0.2em]">Grupo Inmobiliario</span>
                </div>
            </a>

            <!-- Desktop Menu - visible SOLO en lg (1024px) para arriba -->
            <div class="hidden lg:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-sm font-medium {{ request()->routeIs('home') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:bg-mso-gold after:left-0 after:-bottom-1 after:transition-all hover:after:w-full">
                    Inicio
                </a>
                <a href="{{ route('catalogo.index') }}" class="text-sm font-medium {{ request()->routeIs('catalogo*') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:bg-mso-gold after:left-0 after:-bottom-1 after:transition-all hover:after:w-full">
                    Propiedades
                </a>
                <a href="{{ route('servicios.public') }}" class="text-sm font-medium {{ request()->routeIs('servicios.public') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:bg-mso-gold after:left-0 after:-bottom-1 after:transition-all hover:after:w-full">
                    Servicios
                </a>

                @auth
                    @php
                        $user = Auth::user();
                        $permission = new \App\Services\PermissionService($user);
                        $dashboardRoute = $permission->getDashboardRoute();
                        $mainRole = $permission->getMainRole();
                        $roleDisplayName = $permission->getRoleDisplayName();
                    @endphp
                    <div class="flex items-center gap-3">
                        <a href="{{ $dashboardRoute }}" class="text-sm font-medium text-white bg-mso-gold px-5 py-2.5 rounded-full hover:bg-yellow-600 transition-colors shadow-lg">
                            <i class="ph ph-squares-four mr-1"></i> Dashboard
                        </a>

                        {{-- NOTIFICACIONES SOLO DESKTOP --}}
                        <div class="relative hidden md:block" x-data="{
                            open: false,
                            unreadCount: {{ Auth::user()->unreadNotificationsCount() ?? 0 }}
                        }"
                        x-init="
                            const updateUnreadCount = () => {
                                fetch('{{ route('notifications.unreadCount') }}')
                                    .then(res => res.json())
                                    .then(data => unreadCount = data.count)
                                    .catch(err => console.error('Error:', err));
                            };
                            setInterval(updateUnreadCount, 30000);
                            window.addEventListener('new-notification', updateUnreadCount);
                        ">
                            <button @click="open = !open" class="relative p-2 text-slate-600 hover:text-mso-gold transition-colors rounded-full hover:bg-slate-100">
                                <i class="ph ph-bell text-xl"></i>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute top-1 right-1 flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                    </span>
                                </template>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-lg shadow-2xl z-50 transform transition-all duration-200 origin-top-right">
                                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-lg">
                                    <h4 class="font-bold text-slate-800 text-sm">Notificaciones</h4>
                                    @if(Auth::user()->unreadNotificationsCount() > 0)
                                        <button @click="
                                            fetch('{{ route('notifications.markAllAsRead') }}', {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                            }).then(() => {
                                                unreadCount = 0;
                                                location.reload();
                                            });
                                        " class="text-xs text-mso-blue hover:underline font-medium">
                                            Marcar todas
                                        </button>
                                    @endif
                                </div>
                                <div class="max-h-64 overflow-y-auto custom-scroll">
                                    @php
                                        $notifications = Auth::user()->unreadNotifications()->limit(10)->get();
                                    @endphp
                                    @forelse($notifications as $notification)
                                    <a href="{{ $notification->url ?? '#' }}"
                                       onclick="event.preventDefault();
                                                fetch('{{ route('notifications.markAsRead', $notification->id) }}', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                                }).then(() => {
                                                    window.location.href = '{{ $notification->url ?? '#' }}';
                                                });"
                                       class="block p-3 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer">
                                        <div class="flex gap-3">
                                            <div class="w-8 h-8 rounded-full
                                                @if($notification->type === 'success') bg-green-100 text-green-600
                                                @elseif($notification->type === 'error') bg-red-100 text-red-600
                                                @elseif($notification->type === 'warning') bg-yellow-100 text-yellow-600
                                                @else bg-blue-100 text-blue-600 @endif
                                                flex items-center justify-center flex-shrink-0">
                                                <i class="ph
                                                    @if($notification->type === 'success') ph-check-circle
                                                    @elseif($notification->type === 'error') ph-x-circle
                                                    @elseif($notification->type === 'warning') ph-warning
                                                    @else ph-calendar-check @endif
                                                text-lg"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-slate-800 font-bold">{{ $notification->title }}</p>
                                                <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2 leading-tight">{{ $notification->message }}</p>
                                                <p class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                    @empty
                                    <div class="p-6 text-center">
                                        <i class="ph ph-bell-slash text-3xl text-slate-300 mb-2"></i>
                                        <p class="text-xs text-slate-500">No tienes notificaciones nuevas</p>
                                    </div>
                                    @endforelse
                                </div>
                                <div class="p-2 border-t border-slate-100 text-center bg-slate-50 rounded-b-lg">
                                    <a href="{{ route('profile.index') }}" class="text-xs text-slate-500 hover:text-mso-blue font-medium block py-1">
                                        Ver historial
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- FOTO DE PERFIL SOLO DESKTOP --}}
                        <a href="{{ route('profile.index') }}" class="relative hidden md:block">
                            <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=c5a059&color=fff&size=40' }}"
                                 class="w-10 h-10 rounded-full border-2 border-mso-gold object-cover hover:opacity-80 transition-opacity"
                                 alt="{{ $user->full_name }}">
                        </a>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 border border-slate-300 px-5 py-2.5 rounded-full hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all">
                        Ingresar
                    </a>
                @endauth
            </div>

            <!-- Mobile Buttons - visible en lg (1024px) para abajo -->
            <div class="lg:hidden flex items-center gap-1 sm:gap-2" x-data="{ mobileProfileOpen: false }">
                @auth
                    @php
                        $user = Auth::user();
                        $permission = new \App\Services\PermissionService($user);
                        $dashboardRoute = $permission->getDashboardRoute();
                        $roleDisplayName = $permission->getRoleDisplayName();
                    @endphp
                    {{-- Foto de perfil con menú desplegable en móvil --}}
                    <div class="relative inline-block">
                        <button @click="mobileProfileOpen = !mobileProfileOpen"
                                type="button"
                                class="relative focus:outline-none p-1 hover:bg-slate-100 rounded-full transition-colors">
                            <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=c5a059&color=fff&size=40' }}"
                                 class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border-2 border-mso-gold object-cover hover:opacity-80 transition-opacity"
                                 alt="{{ $user->full_name }}">
                        </button>

                        {{-- Menú desplegable móvil --}}
                        <div x-show="mobileProfileOpen"
                             @click.away="mobileProfileOpen = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-lg shadow-2xl z-[999]">

                            {{-- Información del usuario --}}
                            <div class="p-4 border-b border-slate-100 bg-slate-50 rounded-t-lg">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=c5a059&color=fff&size=40' }}"
                                         class="w-10 h-10 rounded-full border-2 border-mso-gold object-cover"
                                         alt="{{ $user->full_name }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ $user->full_name }}</p>
                                        <p class="text-[10px] text-slate-500 truncate">{{ $roleDisplayName }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Opciones del menú --}}
                            <div class="p-2">
                                <a href="{{ $dashboardRoute }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-700 hover:text-mso-gold hover:bg-slate-50 rounded-lg transition-colors">
                                    <i class="ph ph-squares-four text-lg"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-700 hover:text-mso-gold hover:bg-slate-50 rounded-lg transition-colors">
                                    <i class="ph ph-user text-lg"></i>
                                    Mi Perfil
                                </a>

                                {{-- Notificaciones en el menú móvil --}}
                                <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-700 hover:text-mso-gold hover:bg-slate-50 rounded-lg transition-colors">
                                    <div class="relative">
                                        <i class="ph ph-bell text-lg"></i>
                                        @if(Auth::user()->unreadNotificationsCount() > 0)
                                            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                                <span class="absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75 animate-ping"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                            </span>
                                        @endif
                                    </div>
                                    Notificaciones
                                    @if(Auth::user()->unreadNotificationsCount() > 0)
                                        <span class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full">
                                            {{ Auth::user()->unreadNotificationsCount() }}
                                        </span>
                                    @endif
                                </a>

                                <div class="border-t border-slate-100 my-1"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="ph ph-sign-out text-lg"></i>
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

                {{-- Botón hamburguesa --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden text-slate-600 hover:text-mso-blue p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none"
                        aria-label="Toggle menu">
                    <i class="ph text-xl sm:text-2xl" :class="mobileMenuOpen ? 'ph-x' : 'ph-list'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu - visible en lg (1024px) para abajo -->
    <div x-show="mobileMenuOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         @click.away="mobileMenuOpen = false"
         class="lg:hidden bg-white border-t border-slate-100 shadow-xl max-h-[80vh] overflow-y-auto">
        <div class="px-4 py-4 space-y-2">
            <a href="{{ route('home') }}"
               class="block text-sm font-medium {{ request()->routeIs('home') ? 'text-mso-gold bg-slate-50' : 'text-slate-600' }} hover:text-mso-gold hover:bg-slate-50 transition-colors py-2.5 px-3 rounded-lg"
               @click="mobileMenuOpen = false">
                Inicio
            </a>
            <a href="{{ route('catalogo.index') }}"
               class="block text-sm font-medium {{ request()->routeIs('catalogo*') ? 'text-mso-gold bg-slate-50' : 'text-slate-600' }} hover:text-mso-gold hover:bg-slate-50 transition-colors py-2.5 px-3 rounded-lg"
               @click="mobileMenuOpen = false">
                Propiedades
            </a>
            <a href="{{ route('servicios.public') }}"
               class="block text-sm font-medium {{ request()->routeIs('servicios.public') ? 'text-mso-gold bg-slate-50' : 'text-slate-600' }} hover:text-mso-gold hover:bg-slate-50 transition-colors py-2.5 px-3 rounded-lg"
               @click="mobileMenuOpen = false">
                Servicios
            </a>

            @guest
                <div class="border-t border-slate-100 pt-4 mt-2">
                    <a href="{{ route('login') }}"
                       class="w-full text-sm font-medium text-slate-600 border border-slate-300 px-5 py-2.5 rounded-full hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all block text-center"
                       @click="mobileMenuOpen = false">
                        Ingresar
                    </a>
                </div>
            @endguest
        </div>
    </div>
</nav>
