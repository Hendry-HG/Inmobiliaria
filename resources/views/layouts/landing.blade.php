<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MSO Grupo Inmobiliario - @yield('title', 'Tu hogar en Venezuela')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        mso: {
                            blue: '#0f172a',
                            gold: '#c5a059',
                            light: '#f8fafc'
                        }
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>

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
            transition: opacity 1.5s ease-in-out;
            z-index: 0;
        }
        .hero-slider.active {
            opacity: 1;
        }
        .hero-overlay {
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.6) 0%, rgba(15, 23, 42, 0.85) 100%);
        }
        .glass-filters {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .modal-enter { animation: modalFadeIn 0.3s ease-out forwards; }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        main {
            padding-top: 80px;
        }

        [x-cloak] { display: none !important; }
    </style>

    @stack('css')
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-[#c5a059] selection:text-white">

    <!-- Navbar -->
    <x-navbar />

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <x-footer />

    <!-- Floating Support -->
    <x-floating-support />

    <!-- Auth Modal -->
    <x-auth-modal />

    <!-- JavaScript Personalizado -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/phone-mask.js') }}"></script>

    {{-- ALPINE.JS - ÚLTIMO EN CARGARSE --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ============================================ --}}
    {{-- FUNCIONES GLOBALES PARA MODALES --}}
    {{-- ============================================ --}}
    <script>
        // ============================================
        // FUNCIONES GLOBALES PARA MODALES
        // ============================================

        let modalJustOpened = false;

        function openModal(modalId) {
            console.log('🔵 Abriendo modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                console.log('✅ Modal abierto:', modalId);

                // Marcar que el modal fue abierto recientemente
                modalJustOpened = true;
                setTimeout(() => {
                    modalJustOpened = false;
                }, 300);

                if (modalId === 'auth-modal' && typeof switchAuthTab === 'function') {
                    switchAuthTab('login');
                }
            } else {
                console.error('❌ Modal no encontrado:', modalId);
            }
        }

        function closeModal(modalId) {
            console.log('🔵 Cerrando modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                console.log('✅ Modal cerrado:', modalId);
            }
        }

        function switchAuthTab(tab) {
            console.log('🔵 switchAuthTab llamado con:', tab);
            const loginForm = document.getElementById('form-login');
            const registerForm = document.getElementById('form-register');
            const forgotForm = document.getElementById('form-forgot-password');
            const loginTab = document.getElementById('tab-login');
            const registerTab = document.getElementById('tab-register');
            const forgotTab = document.getElementById('tab-forgot');
            const modalTitle = document.getElementById('modal-title');
            const modalSubtitle = document.getElementById('modal-subtitle');

            if (loginForm) loginForm.classList.add('hidden');
            if (registerForm) registerForm.classList.add('hidden');
            if (forgotForm) forgotForm.classList.add('hidden');
            if (forgotTab) forgotTab.classList.add('hidden');

            if (tab === 'login') {
                if (loginForm) loginForm.classList.remove('hidden');
                if (loginTab) loginTab.className = 'text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors';
                if (registerTab) registerTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
                if (forgotTab) forgotTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
                if (modalTitle) modalTitle.textContent = 'Bienvenido';
                if (modalSubtitle) modalSubtitle.textContent = 'Ingresa a tu cuenta para continuar';
            } else if (tab === 'register') {
                if (registerForm) registerForm.classList.remove('hidden');
                if (registerTab) registerTab.className = 'text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors';
                if (loginTab) loginTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
                if (forgotTab) forgotTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
                if (modalTitle) modalTitle.textContent = 'Crear Cuenta';
                if (modalSubtitle) modalSubtitle.textContent = 'Únete a MSO Grupo Inmobiliario';
            } else if (tab === 'forgot') {
                if (forgotForm) forgotForm.classList.remove('hidden');
                if (forgotTab) {
                    forgotTab.classList.remove('hidden');
                    forgotTab.className = 'text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors';
                }
                if (loginTab) loginTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
                if (registerTab) registerTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
                if (modalTitle) modalTitle.textContent = 'Recuperar Contraseña';
                if (modalSubtitle) modalSubtitle.textContent = 'Verifica tu identidad con preguntas de seguridad';
            }
        }

        // Exponer funciones globalmente
        window.openModal = openModal;
        window.closeModal = closeModal;
        window.switchAuthTab = switchAuthTab;

        // Función de prueba para diagnóstico
        function testModal() {
            console.log('🔵 TEST: Intentando abrir modal');
            const modal = document.getElementById('auth-modal');
            console.log('🔵 Modal encontrado:', modal);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                console.log('✅ Modal abierto desde TEST');
                if (typeof switchAuthTab === 'function') {
                    switchAuthTab('login');
                }
            } else {
                console.error('❌ Modal NO encontrado');
                alert('Modal no encontrado en el DOM');
            }
        }
        window.testModal = testModal;

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('[id$="-modal"]:not(.hidden)');
                openModals.forEach(modal => {
                    closeModal(modal.id);
                });
            }
        });

        // Cerrar modal al hacer clic fuera - CORREGIDO
        document.addEventListener('click', function(e) {
            // Si el modal se abrió recientemente, no cerrar
            if (modalJustOpened) {
                return;
            }

            const modal = document.getElementById('auth-modal');
            if (modal && !modal.classList.contains('hidden')) {
                const modalContent = modal.querySelector('.bg-white');
                if (modalContent && !modalContent.contains(e.target)) {
                    closeModal('auth-modal');
                }
            }
        });

        console.log('✅ Funciones de modal cargadas globalmente');
    </script>

    {{-- VERIFICAR QUE PHOSPHOR ICONS CARGÓ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof PhosphorIcons !== 'undefined') {
                console.log('✅ Phosphor Icons cargado correctamente');
            } else {
                console.warn('⚠️ Phosphor Icons no se cargó, intentando recargar...');
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/@phosphor-icons/web';
                document.head.appendChild(script);
            }
        });
    </script>

    @stack('js')
</body>
</html>
