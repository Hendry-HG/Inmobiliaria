<nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm transition-all duration-500" id="navbar"
     x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="w-10 h-10 border-2 border-mso-gold flex items-center justify-center text-mso-gold font-serif font-bold text-xl group-hover:bg-mso-gold group-hover:text-white transition-all duration-300">M</div>
                <div class="flex flex-col">
                    <span class="font-serif font-bold text-xl text-slate-800 leading-none tracking-wide group-hover:text-mso-blue transition-colors">MSO</span>
                    <span class="text-[10px] text-slate-500 uppercase tracking-[0.2em]">Inmobiliaria</span>
                </div>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-sm font-medium {{ request()->routeIs('home') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:bg-mso-gold after:left-0 after:-bottom-1 after:transition-all hover:after:w-full">
                    Inicio
                </a>
                <a href="{{ route('catalogo.index') }}" class="text-sm font-medium {{ request()->routeIs('catalogo*') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:bg-mso-gold after:left-0 after:-bottom-1 after:transition-all hover:after:w-full">
                    Propiedades
                </a>
                <a href="{{ route('servicios.public') }}" class="text-sm font-medium {{ request()->routeIs('servicios.public') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:bg-mso-gold after:left-0 after:-bottom-1 after:transition-all hover:after:w-full">
                    Servicios
                </a>
                <a href="#vender" class="text-sm font-medium text-white bg-mso-blue px-5 py-2.5 rounded-full hover:bg-slate-800 transition-colors shadow-lg shadow-blue-900/20">
                    Vender/Alquilar
                </a>

                @auth
                    @php
                        $user = Auth::user();
                        $dashboardRoute = route('dashboard');
                        if ($user->hasRole('Super Admin')) {
                            $dashboardRoute = route('super-admin.dashboard');
                        } elseif ($user->hasRole('Administrador')) {
                            $dashboardRoute = route('admin.dashboard');
                        } elseif ($user->hasRole('Asesor Inmobiliario')) {
                            $dashboardRoute = route('asesor.dashboard');
                        } elseif ($user->hasRole('Auditor')) {
                            $dashboardRoute = route('auditor.dashboard');
                        } elseif ($user->hasRole('Cliente')) {
                            $dashboardRoute = route('cliente.dashboard');
                        }
                    @endphp
                    <div class="flex items-center gap-3">
                        <a href="{{ $dashboardRoute }}" class="text-sm font-medium text-white bg-mso-gold px-5 py-2.5 rounded-full hover:bg-yellow-600 transition-colors shadow-lg">
                            <i class="ph ph-squares-four mr-1"></i> Dashboard
                        </a>

                        {{-- NOTIFICACIONES --}}
                        <div class="relative" x-data="{
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

                        <a href="{{ route('profile.index') }}" class="relative">
                            <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=c5a059&color=fff&size=40' }}"
                                 class="w-10 h-10 rounded-full border-2 border-mso-gold object-cover hover:opacity-80 transition-opacity"
                                 alt="{{ $user->name }}">
                        </a>
                    </div>
                @else
                    <button onclick="openModal('auth-modal')" class="text-sm font-medium text-slate-600 border border-slate-300 px-5 py-2.5 rounded-full hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all">
                        Ingresar
                    </button>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center gap-3">
                @auth
                    <a href="{{ $dashboardRoute ?? route('dashboard') }}" class="text-mso-blue">
                        <i class="ph ph-squares-four text-xl"></i>
                    </a>
                @endauth
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="text-slate-600 hover:text-mso-blue p-2 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none">
                    <i class="ph" :class="mobileMenuOpen ? 'ph-x text-2xl' : 'ph-list text-2xl'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="md:hidden bg-white border-t border-slate-100 shadow-lg max-h-[80vh] overflow-y-auto">
        <div class="px-4 py-4 space-y-3">
            <a href="{{ route('home') }}" class="block text-sm font-medium {{ request()->routeIs('home') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors py-2 px-3 rounded-lg hover:bg-slate-50">Inicio</a>
            <a href="{{ route('catalogo.index') }}" class="block text-sm font-medium {{ request()->routeIs('catalogo*') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors py-2 px-3 rounded-lg hover:bg-slate-50">Propiedades</a>
            <a href="{{ route('servicios.public') }}" class="block text-sm font-medium {{ request()->routeIs('servicios.public') ? 'text-mso-gold' : 'text-slate-600' }} hover:text-mso-gold transition-colors py-2 px-3 rounded-lg hover:bg-slate-50">Servicios</a>
            <a href="#vender" class="block text-sm font-medium text-white bg-mso-blue px-5 py-2.5 rounded-full hover:bg-slate-800 transition-colors text-center">Vender/Alquilar</a>

            <div class="border-t border-slate-100 pt-3 mt-2">
                @auth
                    <a href="{{ $dashboardRoute ?? route('dashboard') }}" class="block text-sm font-medium text-white bg-mso-gold px-5 py-2.5 rounded-full hover:bg-yellow-600 transition-colors text-center">Dashboard</a>
                    <a href="{{ route('profile.index') }}" class="block text-sm font-medium text-slate-600 hover:text-mso-gold transition-colors py-2 px-3 rounded-lg hover:bg-slate-50 text-center">Mi Perfil</a>
                    <form method="POST" action="{{ route('logout') }}" class="block mt-1">
                        @csrf
                        <button type="submit" class="w-full text-sm font-medium text-red-500 hover:text-red-700 transition-colors py-2 px-3 rounded-lg hover:bg-red-50">Cerrar Sesión</button>
                    </form>
                @else
                    <button onclick="openModal('auth-modal')"
                            class="w-full text-sm font-medium text-slate-600 border border-slate-300 px-5 py-2.5 rounded-full hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all">
                        Ingresar
                    </button>
                @endauth
            </div>
        </div>
    </div>
</nav>

<style>
    [x-cloak] { display: none !important; }
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

    .x-transition-enter {
        transition: all 0.2s ease-out;
    }
    .x-transition-leave {
        transition: all 0.15s ease-in;
    }
</style>
