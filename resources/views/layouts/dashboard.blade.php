<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - MSO Inmobiliaria</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- ============================================= -->
    <!-- SCRIPTS PARA WEBSOCKETS -->
    <!-- ============================================= -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.14.0/dist/echo.iife.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <!-- Iconos Phosphor -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mso: { blue: '#0f172a', gold: '#c5a059' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f1f5f9; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scroll::-webkit-scrollbar-track { background-color: transparent; }
        .nav-item.active { background-color: rgba(197, 160, 89, 0.1); color: #c5a059; border-right: 3px solid #c5a059; }
        [x-cloak] { display: none !important; }

        @keyframes fade-in-down {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down { animation: fade-in-down 0.3s ease-out; }

        /* Transición para el sidebar */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
    @stack('css')
</head>
<body class="bg-slate-50 font-sans antialiased">

    {{-- Contenedor principal con Alpine.js para manejar el sidebar --}}
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">

        {{-- Sidebar --}}
        <x-sidebar :user="Auth::user()" />

        {{-- Contenido Principal --}}
        <main class="flex-1 flex flex-col h-full overflow-hidden transition-all duration-300">

            {{-- Header --}}
            <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shadow-sm flex-shrink-0 relative z-20">
                <div class="flex items-center gap-2">
                    {{-- BOTÓN PARA MÓVIL (abre sidebar overlay) --}}
                    <button id="mobile-menu-btn" class="lg:hidden text-slate-600 hover:text-mso-gold transition-colors p-2 rounded-lg hover:bg-slate-100">
                        <i class="ph ph-list text-2xl"></i>
                    </button>

                    {{-- BOTÓN TOGGLE PARA DESKTOP (abre/cierra sidebar) --}}
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="hidden lg:flex text-slate-400 hover:text-mso-gold transition-colors p-2 rounded-lg hover:bg-slate-100"
                            title="Toggle sidebar">
                        <i class="ph text-xl" :class="sidebarOpen ? 'ph-caret-left' : 'ph-caret-right'"></i>
                    </button>

                    <h2 class="font-serif text-xl md:text-2xl font-bold text-slate-800">
                        @yield('header', 'Dashboard')
                    </h2>
                </div>

                <div class="flex items-center gap-3 md:gap-4">

                    {{-- NOTIFICACIONES --}}
                    @php
                        $notifications = Auth::user()->unreadNotifications()->limit(10)->get();
                    @endphp

                    <div x-data="{
                        open: false,
                        unreadCount: {{ Auth::user()->unreadNotificationsCount() ?? 0 }},
                        updateUnreadCount() {
                            fetch('{{ route('notifications.unreadCount') }}')
                                .then(res => res.json())
                                .then(data => { this.unreadCount = data.count; })
                                .catch(err => console.error('Error:', err));
                        },
                        init() {
                            setInterval(() => { this.updateUnreadCount(); }, 30000);
                            window.addEventListener('new-notification', () => { this.updateUnreadCount(); });
                        }
                    }" x-init="init()" class="relative">
                        <button @click="open = !open" class="relative p-2 text-slate-600 hover:text-mso-gold transition-colors rounded-full hover:bg-slate-100 focus:outline-none">
                            <i class="ph ph-bell text-xl"></i>
                            <template x-if="unreadCount > 0">
                                <span class="absolute top-1 right-1 flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                            </template>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-lg shadow-2xl z-50">
                            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-lg">
                                <h4 class="font-bold text-slate-800 text-sm">Notificaciones</h4>
                                @if(Auth::user()->unreadNotificationsCount() > 0)
                                <button @click="
                                    fetch('{{ route('notifications.markAllAsRead') }}', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                    }).then(() => { unreadCount = 0; location.reload(); });
                                " class="text-xs text-mso-blue hover:underline font-medium">Marcar todas</button>
                                @endif
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scroll">
                                @forelse($notifications as $notification)
                                <a href="{{ $notification->url ?? '#' }}"
                                   @click.prevent="
                                        fetch('{{ route('notifications.markAsRead', $notification->id) }}', {
                                            method: 'POST',
                                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                        }).then(() => { window.location.href = '{{ $notification->url ?? '#' }}'; });
                                   "
                                   class="block p-3 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer group">
                                    <div class="flex gap-3">
                                        <div class="w-8 h-8 rounded-full
                                            @if($notification->type === 'success') bg-green-100 text-green-600
                                            @elseif($notification->type === 'error') bg-red-100 text-red-600
                                            @elseif($notification->type === 'warning') bg-yellow-100 text-yellow-600
                                            @else bg-blue-100 text-blue-600 @endif
                                            flex items-center justify-center flex-shrink-0">
                                            <i class="ph @if($notification->type === 'success') ph-check-circle @elseif($notification->type === 'error') ph-x-circle @elseif($notification->type === 'warning') ph-warning @else ph-calendar-check @endif text-lg"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-slate-800 font-bold group-hover:text-mso-blue transition-colors">{{ $notification->title }}</p>
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
                                <a href="{{ route('profile.index') }}" class="text-xs text-slate-500 hover:text-mso-blue font-medium block py-1">Ver historial completo</a>
                            </div>
                        </div>
                    </div>

                    {{-- Perfil --}}
                    <a href="{{ route('profile.index') }}" class="relative hidden sm:block group">
                        <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=c5a059&color=fff&size=40' }}"
                             class="w-9 h-9 rounded-full border-2 border-slate-200 object-cover group-hover:border-mso-gold transition-colors"
                             alt="{{ Auth::user()->name }}">
                    </a>

                    {{-- Cerrar Sesión --}}
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="flex items-center gap-1 text-sm text-slate-500 hover:text-red-500 transition-colors">
                            <span class="hidden md:inline">Salir</span>
                            <i class="ph ph-sign-out text-lg"></i>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Contenido --}}
            <div class="flex-1 overflow-y-auto p-4 md:p-8 custom-scroll">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r shadow-sm animate-fade-in-down">
                        <div class="flex items-center"><i class="ph ph-check-circle text-xl mr-2"></i><p>{{ session('success') }}</p></div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r shadow-sm animate-fade-in-down">
                        <div class="flex items-center"><i class="ph ph-warning-circle text-xl mr-2"></i><p>{{ session('error') }}</p></div>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <!-- INICIALIZACIÓN DE ECHO -->
    <script>
        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: '{{ env("REVERB_HOST", "127.0.0.1") }}',
            wsPort: {{ env("REVERB_PORT", 8080) }},
            wssPort: {{ env("REVERB_PORT", 8080) }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
            cluster: 'mt1'
        });

        console.log(' Echo inicializado con key: {{ env("REVERB_APP_KEY") }}');

        if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
            window.Echo.connector.pusher.connection.bind('connected', function() {
                console.log(' WebSocket conectado en puerto {{ env("REVERB_PORT", 8080) }}');
            });
            window.Echo.connector.pusher.connection.bind('error', function(err) {
                console.error('❌ Error WebSocket:', err);
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Móvil: abrir/cerrar sidebar
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileSidebar = document.getElementById('mobile-sidebar');

            if (mobileBtn && mobileSidebar) {
                mobileBtn.addEventListener('click', () => {
                    mobileSidebar.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                });
            }

            // Cerrar móvil con el botón X
            const closeBtn = document.getElementById('close-mobile-menu');
            if (closeBtn && mobileSidebar) {
                closeBtn.addEventListener('click', () => {
                    mobileSidebar.classList.add('hidden');
                    document.body.style.overflow = '';
                });
            }

            // Cerrar móvil al hacer clic fuera
            if (mobileSidebar) {
                mobileSidebar.addEventListener('click', function(e) {
                    if (e.target === this) {
                        mobileSidebar.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                });
            }

            // Cerrar móvil al redimensionar a desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024 && mobileSidebar) {
                    mobileSidebar.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });

            // Cerrar con tecla ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileSidebar && !mobileSidebar.classList.contains('hidden')) {
                    mobileSidebar.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>

    @stack('js')
</body>
</html>
