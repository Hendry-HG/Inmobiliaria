<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - MSO Inmobiliaria</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Iconos Phosphor -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* ============================================ */
        /* ESTILOS BASE */
        /* ============================================ */
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .custom-scroll::-webkit-scrollbar-track { background-color: transparent; }

        /* Sidebar - estilos específicos con mayor especificidad */
        #sidebar .nav-item.active {
            background-color: rgba(197, 160, 89, 0.1);
            color: #c5a059;
            border-right: 3px solid #c5a059;
        }

        [x-cloak] { display: none !important; }

        @keyframes fade-in-down {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down { animation: fade-in-down 0.3s ease-out; }

        /* ============================================ */
        /* SIDEBAR - TAMAÑO FIJO Y CONSISTENTE */
        /* ============================================ */
        #sidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            overflow: hidden;
            position: relative;
            z-index: 30;
        }

        #sidebar .sidebar-inner {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Texto del sidebar */
        #sidebar .nav-text {
            transition: opacity 0.2s ease, width 0.2s ease;
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
        }

        #sidebar .nav-text-hidden {
            opacity: 0;
            width: 0;
            max-width: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        #sidebar .nav-text-visible {
            opacity: 1;
            width: auto;
            max-width: 200px;
        }

        /* ============================================ */
        /* HEADER - TAMAÑO FIJO */
        /* ============================================ */
        .dashboard-header {
            height: 80px;
            min-height: 80px;
            max-height: 80px;
            flex-shrink: 0;
        }

        /* ============================================ */
        /* CONTENIDO PRINCIPAL */
        /* ============================================ */
        .dashboard-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        .dashboard-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem 2rem;
        }

        /* ============================================ */
        /* RESPONSIVE - TABLET Y MÓVIL */
        /* ============================================ */
        @media (max-width: 1024px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1000;
                transform: translateX(-100%);
                width: 280px !important;
                box-shadow: 4px 0 30px rgba(0,0,0,0.2);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            #sidebar.mobile-open {
                transform: translateX(0);
            }

            #sidebar .nav-text {
                opacity: 1 !important;
                width: auto !important;
                max-width: 200px !important;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .dashboard-header {
                height: 64px;
                min-height: 64px;
                max-height: 64px;
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .dashboard-content {
                padding: 1rem;
            }
        }

        @media (max-width: 640px) {
            .dashboard-header {
                height: 56px;
                min-height: 56px;
                max-height: 56px;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .dashboard-content {
                padding: 0.75rem;
            }

            #sidebar {
                width: 280px !important;
            }
        }
    </style>
    @stack('styles')
    @stack('css')
</head>
<body class="bg-slate-50 font-sans antialiased">

    {{-- Overlay para móvil --}}
    <div id="sidebar-overlay" class="sidebar-overlay" onclick="closeMobileSidebar()"></div>

    {{-- Contenedor principal con Alpine.js para manejar el sidebar --}}
    <div class="flex h-screen overflow-hidden"
         x-data="{
            sidebarOpen: true,
            isMobile: window.innerWidth < 1024,
            init() {
                if (!this.isMobile) {
                    const saved = localStorage.getItem('sidebarOpen');
                    if (saved !== null) {
                        this.sidebarOpen = saved === 'true';
                    }
                    this.$watch('sidebarOpen', value => {
                        localStorage.setItem('sidebarOpen', value);
                    });
                }

                window.addEventListener('resize', () => {
                    this.isMobile = window.innerWidth < 1024;
                    if (!this.isMobile) {
                        const sidebar = document.getElementById('sidebar');
                        const overlay = document.getElementById('sidebar-overlay');
                        if (sidebar) sidebar.classList.remove('mobile-open');
                        if (overlay) overlay.classList.remove('active');
                    }
                });
            }
         }"
         x-init="init()">

        {{-- Sidebar --}}
        <x-sidebar :user="Auth::user()" />

        {{-- Contenido Principal --}}
        <main class="dashboard-main">

            {{-- Header --}}
            <header class="dashboard-header bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 shadow-sm flex-shrink-0 relative z-20">
                <div class="flex items-center gap-2 min-w-0">
                    <button id="mobile-menu-btn" class="lg:hidden text-slate-600 hover:text-mso-gold transition-colors p-2 rounded-lg hover:bg-slate-100 flex-shrink-0">
                        <i class="ph ph-list text-2xl"></i>
                    </button>
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="hidden lg:flex text-slate-400 hover:text-mso-gold transition-colors p-2 rounded-lg hover:bg-slate-100 flex-shrink-0"
                            title="Toggle sidebar">
                        <i class="ph text-xl" :class="sidebarOpen ? 'ph-caret-left' : 'ph-caret-right'"></i>
                    </button>
                    <h2 class="font-serif text-xl md:text-2xl font-bold text-slate-800 truncate">
                        @yield('header', 'Dashboard')
                    </h2>
                </div>

                <div class="flex items-center gap-3 md:gap-4 flex-shrink-0">

                    {{-- NOTIFICACIONES DEL SISTEMA (CAMPANA) --}}
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

                    {{-- PERFIL Y MENÚ DESPLEGABLE UNIFICADO --}}
                    <div x-data="{
                        openProfileMenu: false,
                        toggleMenu() {
                            this.openProfileMenu = !this.openProfileMenu;
                        },
                        closeMenu() {
                            this.openProfileMenu = false;
                        }
                    }" class="relative">
                        {{-- Botón del perfil (visible en todos los tamaños) --}}
                        <button @click="toggleMenu()" @click.away="closeMenu()" class="flex items-center gap-2 group focus:outline-none">
                            <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->full_name) . '&background=c5a059&color=fff&size=40' }}"
                                 class="w-9 h-9 rounded-full border-2 border-slate-200 object-cover group-hover:border-mso-gold transition-colors"
                                 alt="{{ Auth::user()->full_name }}">
                            <span class="hidden sm:inline-block text-sm font-medium text-slate-700 group-hover:text-mso-gold transition-colors">
                                {{ Auth::user()->full_name }}
                            </span>
                            <i class="ph ph-caret-down hidden sm:inline-block text-slate-400 group-hover:text-mso-gold transition-colors text-xs"></i>
                        </button>

                        {{-- Menú desplegable unificado --}}
                        <div x-show="openProfileMenu" 
                             x-transition:enter="transition ease-out duration-150" 
                             x-transition:enter-start="opacity-0 scale-95" 
                             x-transition:enter-end="opacity-100 scale-100" 
                             class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-lg shadow-2xl z-50 py-1 origin-top-right">
                            
                            {{-- Opción: Perfil --}}
                            <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-mso-gold transition-colors">
                                <i class="ph ph-user-circle text-xl text-slate-400"></i>
                                <span>Mi perfil</span>
                            </a>

                            {{-- Divisor --}}
                            <hr class="my-1 border-slate-100">

                            {{-- Opción: Cerrar sesión (unificada) --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                    <i class="ph ph-sign-out text-xl"></i>
                                    <span>Cerrar sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ============================================ --}}
            {{-- CONTENIDO PRINCIPAL CON NOTIFICACIONES --}}
            {{-- ============================================ --}}
            <div class="dashboard-content custom-scroll">
                {{-- NOTIFICACIONES DEL SISTEMA (COMPONENTE) --}}
                @if(session('success'))
                    <x-notification 
                        type="success" 
                        :message="session('success')"
                        title="¡Operación exitosa!"
                    />
                @endif

                @if(session('error'))
                    <x-notification 
                        type="error" 
                        :message="session('error')"
                        title="¡Error!"
                    />
                @endif

                @if(session('warning'))
                    <x-notification 
                        type="warning" 
                        :message="session('warning')"
                        title="¡Atención!"
                    />
                @endif

                @if(session('info'))
                    <x-notification 
                        type="info" 
                        :message="session('info')"
                        title="Información"
                    />
                @endif

                {{-- Errores de validación --}}
                @if($errors->any())
                    <x-notification type="error" title="Errores de validación">
                        <ul class="list-disc list-inside text-sm mt-1 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-notification>
                @endif

                {{-- Contenido de la vista --}}
                @yield('content')
            </div>
        </main>
    </div>

    <!-- ============================================ -->
    <!-- FUNCIONES PARA EL SIDEBAR MÓVIL -->
    <!-- ============================================ -->
    <script>
        function openMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) {
                sidebar.classList.add('mobile-open');
                document.body.style.overflow = 'hidden';
            }
            if (overlay) overlay.classList.add('active');
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) {
                sidebar.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
            if (overlay) overlay.classList.remove('active');
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const mobileBtn = document.getElementById('mobile-menu-btn');
            if (mobileBtn) {
                mobileBtn.addEventListener('click', toggleMobileSidebar);
            }

            const closeBtn = document.getElementById('close-mobile-menu');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeMobileSidebar);
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileSidebar();
                }
            });

            const overlay = document.getElementById('sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', closeMobileSidebar);
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeMobileSidebar();
                }
            });

            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // ============================================
            // REFRESCAR TOKEN CSRF EN EL DASHBOARD
            // ============================================
            function refreshCsrfToken() {
                fetch('/refresh-csrf', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.csrf_token) {
                        document.querySelectorAll('form input[name="_token"]').forEach(input => {
                            input.value = data.csrf_token;
                        });
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        if (metaTag) {
                            metaTag.content = data.csrf_token;
                        }
                    }
                })
                .catch(() => {
                    console.warn(' No se pudo refrescar el token');
                });
            }

            // Refrescar token cada 5 minutos en el dashboard
            setInterval(refreshCsrfToken, 300000);

            // Verificar si hay error de sesión expirada
            if (window.location.search.includes('session_expired')) {
                alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                const url = new URL(window.location);
                url.searchParams.delete('session_expired');
                window.history.replaceState({}, document.title, url.toString());
            }

            // ============================================
            // PREVENIR DOBLE CLIC EN FORMULARIOS
            // ============================================
            document.querySelectorAll('form').forEach(form => {
                let isSubmitting = false;
                form.addEventListener('submit', function(e) {
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        isSubmitting = true;
                        const originalText = submitBtn.textContent;
                        submitBtn.textContent = ' ENVIANDO...';
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.7';
                        submitBtn.style.cursor = 'wait';
                        setTimeout(() => {
                            submitBtn.textContent = originalText;
                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                            submitBtn.style.cursor = 'pointer';
                            isSubmitting = false;
                        }, 30000);
                    }
                });
            });

            // Exponer función globalmente
            window.refreshCsrfToken = refreshCsrfToken;
        });

        // Echo listener para notificaciones en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            if (window.Echo) {
                setTimeout(function() {
                    try {
                        window.Echo.private('user.{{ Auth::id() }}')
                            .listen('.new-notification', function(e) {
                                window.dispatchEvent(new CustomEvent('new-notification', { detail: e }));
                            });
                    } catch (err) {
                        console.warn('Error al suscribirse a notificaciones:', err);
                    }
                }, 2000);
            }
        });
    </script>

    <!-- Modal global de campos incompletos -->
    @include('components.missing-fields-modal')

    @stack('js')
    @stack('scripts')
</body>
</html>