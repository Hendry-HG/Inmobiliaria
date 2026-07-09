<nav class="bg-white/90 backdrop-blur-sm shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">M | SMO</h1>
                <span class="ml-2 text-xs text-gray-500 hidden sm:inline">Redefiniendo el estándar inmobiliario</span>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600">Inicio</a>
                <a href="#" class="text-gray-700 hover:text-blue-600">Comprar</a>
                <a href="#" class="text-gray-700 hover:text-blue-600">Alquilar</a>
                <a href="#" class="text-gray-700 hover:text-blue-600">Vender</a>
                <a href="#" class="text-gray-700 hover:text-blue-600">Nosotros</a>
                <a href="#" class="text-gray-700 hover:text-blue-600">Contacto</a>

                @auth
                    <!-- Usuario autenticado -->
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700">Cerrar Sesión</button>
                    </form>
                @else
                    <!-- Botón para abrir modal de login -->
                    <button onclick="openAuthModal('login')"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        <i class="ph ph-sign-in mr-1"></i>
                        Iniciar Sesión
                    </button>
                    <!-- Botón para abrir modal de registro -->
                    <button onclick="openAuthModal('register')"
                            class="border border-blue-600 text-blue-600 px-4 py-2 rounded-md hover:bg-blue-50 transition-colors">
                        <i class="ph ph-user-plus mr-1"></i>
                        Registrarse
                    </button>
                @endauth
            </div>
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-500 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden fixed inset-0 z-50 bg-white">
        <div class="flex justify-end p-4">
            <button id="close-menu-button" class="text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex flex-col items-center space-y-6 pt-8">
            <a href="{{ route('home') }}" class="text-gray-700 text-lg">Inicio</a>
            <a href="#" class="text-gray-700 text-lg">Comprar</a>
            <a href="#" class="text-gray-700 text-lg">Alquilar</a>
            <a href="#" class="text-gray-700 text-lg">Vender</a>
            <a href="#" class="text-gray-700 text-lg">Nosotros</a>
            <a href="#" class="text-gray-700 text-lg">Contacto</a>

            @auth
                <span class="text-gray-700 text-lg">{{ auth()->user()->name }}</span>
                <a href="{{ route('dashboard') }}" class="text-gray-700 text-lg">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" class="w-48">
                    @csrf
                    <button type="submit" class="text-red-600 text-lg w-full">Cerrar Sesión</button>
                </form>
            @else
                <button onclick="openAuthModal('login')"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md w-48 text-center hover:bg-blue-700 transition-colors">
                    <i class="ph ph-sign-in mr-1"></i>
                    Iniciar Sesión
                </button>
                <button onclick="openAuthModal('register')"
                        class="border border-blue-600 text-blue-600 px-6 py-2 rounded-md w-48 text-center hover:bg-blue-50 transition-colors">
                    <i class="ph ph-user-plus mr-1"></i>
                    Registrarse
                </button>
            @endauth
        </div>
    </div>
    <div id="mobile-menu-backdrop" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>
</nav>

<script>
// ============================================
// FUNCIONES PARA EL MENÚ MÓVIL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const closeMenuButton = document.getElementById('close-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuBackdrop = document.getElementById('mobile-menu-backdrop');

    function openMobileMenu() {
        mobileMenu.classList.remove('hidden');
        mobileMenuBackdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        mobileMenu.classList.add('hidden');
        mobileMenuBackdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', openMobileMenu);
    }

    if (closeMenuButton) {
        closeMenuButton.addEventListener('click', closeMobileMenu);
    }

    if (mobileMenuBackdrop) {
        mobileMenuBackdrop.addEventListener('click', closeMobileMenu);
    }

    // Cerrar menú móvil con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
            closeMobileMenu();
        }
    });
});

// ============================================
// FUNCIONES PARA EL MODAL DE AUTENTICACIÓN
// ============================================
function openAuthModal(tab = 'login') {
    // Cerrar menú móvil si está abierto
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuBackdrop = document.getElementById('mobile-menu-backdrop');
    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.add('hidden');
        mobileMenuBackdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Abrir modal
    const modal = document.getElementById('auth-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        switchAuthTab(tab);
    } else {
        console.error('Modal de autenticación no encontrado');
        // Fallback: redirigir a las páginas de login/register
        if (tab === 'login') {
            window.location.href = '{{ route("login") }}';
        } else if (tab === 'register') {
            window.location.href = '{{ route("register") }}';
        }
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function switchAuthTab(tab) {
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const forgotForm = document.getElementById('form-forgot-password');
    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    const forgotTab = document.getElementById('tab-forgot');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');

    // Ocultar todos
    if (loginForm) loginForm.classList.add('hidden');
    if (registerForm) registerForm.classList.add('hidden');
    if (forgotForm) forgotForm.classList.add('hidden');

    // Ocultar forgot tab por defecto
    if (forgotTab) forgotTab.classList.add('hidden');

    // Mostrar el seleccionado
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

// ============================================
// CERRAR MODAL CON ESC
// ============================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('auth-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeModal('auth-modal');
        }
    }
});

// ============================================
// CERRAR MODAL AL HACER CLICK FUERA
// ============================================
document.addEventListener('click', function(e) {
    const modal = document.getElementById('auth-modal');
    if (modal && !modal.classList.contains('hidden')) {
        const modalContent = modal.querySelector('.bg-white');
        if (modalContent && !modalContent.contains(e.target)) {
            closeModal('auth-modal');
        }
    }
});
</script>
