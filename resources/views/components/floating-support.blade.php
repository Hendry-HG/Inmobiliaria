{{-- resources/views/components/floating-support.blade.php --}}
@php
    $config = App\Models\SiteConfiguration::getConfig();
    $hasContacts = $config->support_whatsapp || $config->support_instagram || $config->support_phone || $config->support_email;
@endphp

{{-- Contenedor fijo --}}
<div id="floating-support" class="fixed bottom-6 sm:bottom-8 right-6 sm:right-8 z-50">

    {{-- Menú de opciones --}}
    <div id="support-options" class="flex flex-col gap-2.5 items-end mb-3 hidden">
        @if($config->support_whatsapp)
            <a href="https://wa.me/{{ $config->support_whatsapp }}" target="_blank"
               class="flex items-center gap-3 bg-white text-slate-800 px-4 py-2.5 rounded-2xl shadow-lg hover:shadow-xl hover:bg-green-50 transition-all duration-200 group border border-slate-100/50 backdrop-blur-sm"
               style="box-shadow: 0 4px 20px rgba(0,0,0,0.08); animation: slideInRight 0.3s ease-out forwards; opacity: 0;">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-green-400 to-green-600 text-white flex items-center justify-center shadow-md group-hover:shadow-lg transition-all flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor">
                        <path d="M128,24A104,104,0,0,0,24,128a103.8,103.8,0,0,0,6.7,37.1L24,192.7A16,16,0,0,0,40.9,208l27.8-5.9A103.8,103.8,0,0,0,128,232a104,104,0,0,0,0-208Zm12,156H119.9a8,8,0,0,1,0-16H140a8,8,0,0,1,0,16Zm-8-29.3a8,8,0,0,1-11.3-11.3L140,120a8,8,0,0,1,11.3,0L164,132a8,8,0,0,1-11.3,11.3L140,130.7Z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold">WhatsApp</span>
                <span class="text-xs text-green-500 opacity-0 group-hover:opacity-100 transition-opacity">✓</span>
            </a>
        @endif

        @if($config->support_instagram)
            <a href="https://instagram.com/{{ $config->support_instagram }}" target="_blank"
               class="flex items-center gap-3 bg-white text-slate-800 px-4 py-2.5 rounded-2xl shadow-lg hover:shadow-xl hover:bg-pink-50 transition-all duration-200 group border border-slate-100/50 backdrop-blur-sm"
               style="box-shadow: 0 4px 20px rgba(0,0,0,0.08); animation: slideInRight 0.3s ease-out forwards; opacity: 0; animation-delay: 0.05s;">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 text-white flex items-center justify-center shadow-md group-hover:shadow-lg transition-all flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor">
                        <path d="M128,80a48,48,0,1,0,48,48A48,48,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160ZM176,24H80A56,56,0,0,0,24,80v96a56,56,0,0,0,56,56h96a56,56,0,0,0,56-56V80A56,56,0,0,0,176,24Zm40,152a40,40,0,0,1-40,40H80a40,40,0,0,1-40-40V80A40,40,0,0,1,80,40h96a40,40,0,0,1,40,40ZM192,76a12,12,0,1,1-12-12A12,12,0,0,1,192,76Z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold">Instagram</span>
                <span class="text-xs text-pink-500 opacity-0 group-hover:opacity-100 transition-opacity">✓</span>
            </a>
        @endif

        @if($config->support_phone)
            <a href="tel:{{ $config->support_phone }}"
               class="flex items-center gap-3 bg-white text-slate-800 px-4 py-2.5 rounded-2xl shadow-lg hover:shadow-xl hover:bg-blue-50 transition-all duration-200 group border border-slate-100/50 backdrop-blur-sm"
               style="box-shadow: 0 4px 20px rgba(0,0,0,0.08); animation: slideInRight 0.3s ease-out forwards; opacity: 0; animation-delay: 0.10s;">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center shadow-md group-hover:shadow-lg transition-all flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor">
                        <path d="M222.4,175.1l-42.9-34.5A16,16,0,0,0,157,144.4l-12.3,15.5a12.8,12.8,0,0,1-15.8,3.7,144.2,144.2,0,0,1-36.3-28.5,143.8,143.8,0,0,1-28.5-36.3,12.8,12.8,0,0,1,3.7-15.8l15.5-12.3A16,16,0,0,0,89.4,59.1L54.9,16.2A16,16,0,0,0,32.6,11.8a15.9,15.9,0,0,0-10.8,9.3A120.1,120.1,0,0,0,8,87.5a119.9,119.9,0,0,0,35.3,85.3A135.5,135.5,0,0,0,128.7,248a119.2,119.2,0,0,0,66.2-13.8,15.9,15.9,0,0,0,9.3-10.8A16,16,0,0,0,222.4,175.1Z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold">Llamar</span>
                <span class="text-xs text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity">✓</span>
            </a>
        @endif

        @if($config->support_email)
            <a href="mailto:{{ $config->support_email }}"
               class="flex items-center gap-3 bg-white text-slate-800 px-4 py-2.5 rounded-2xl shadow-lg hover:shadow-xl hover:bg-red-50 transition-all duration-200 group border border-slate-100/50 backdrop-blur-sm"
               style="box-shadow: 0 4px 20px rgba(0,0,0,0.08); animation: slideInRight 0.3s ease-out forwards; opacity: 0; animation-delay: 0.15s;">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-red-400 to-red-600 text-white flex items-center justify-center shadow-md group-hover:shadow-lg transition-all flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor">
                        <path d="M224,48H32a8,8,0,0,0-8,8V192a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A8,8,0,0,0,224,48ZM203.4,64,128,133.2,52.6,64ZM216,192H40V74.2l82.8,75.3a8,8,0,0,0,10.4,0L216,74.2Z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold">Email</span>
                <span class="text-xs text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">✓</span>
            </a>
        @endif

        @if(!$hasContacts)
            <div class="bg-white/90 backdrop-blur-sm text-slate-500 px-5 py-3 rounded-2xl shadow-xl text-sm border border-slate-100"
                 style="animation: slideInRight 0.3s ease-out forwards; opacity: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" class="inline mr-2 text-yellow-500">
                    <path fill="currentColor" d="M128,24A104,104,0,1,0,232,128,104.2,104.2,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216ZM116,128V80a12,12,0,0,1,24,0v48a12,12,0,0,1-24,0Zm12,36a16,16,0,1,0,16,16A16,16,0,0,0,128,152Z"/>
                </svg>
                Sin contactos configurados
            </div>
        @endif
    </div>

    {{-- Botón principal - Azul marino --}}
    <button onclick="toggleSupportMenu()"
            id="support-btn"
            class="w-14 h-14 bg-mso-blue hover:bg-slate-800 text-white rounded-2xl shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95 relative group"
            style="box-shadow: 0 8px 32px rgba(15, 23, 42, 0.4);">

        {{-- Efecto de brillo --}}
        <span class="absolute inset-0 rounded-2xl bg-gradient-to-tr from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></span>

        {{-- Icono de menú hamburguesa --}}
        <div class="relative z-10 flex flex-col items-center justify-center gap-1 transition-transform duration-300"
             id="menu-icon">
            <div class="w-6 h-0.5 bg-white rounded-full transition-all duration-300" id="line1"></div>
            <div class="w-5 h-0.5 bg-white rounded-full transition-all duration-300" id="line2"></div>
            <div class="w-6 h-0.5 bg-white rounded-full transition-all duration-300" id="line3"></div>
        </div>

        {{-- Badge de notificación --}}
        @if($hasContacts)
            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
        @endif
    </button>
</div>

@push('js')
<script>
    let isMenuOpen = false;

    function toggleSupportMenu() {
        const menu = document.getElementById('support-options');
        const line1 = document.getElementById('line1');
        const line2 = document.getElementById('line2');
        const line3 = document.getElementById('line3');
        const btn = document.getElementById('support-btn');

        if (!menu) return;

        isMenuOpen = !isMenuOpen;

        if (isMenuOpen) {
            // Mostrar menú
            menu.classList.remove('hidden');
            menu.style.opacity = '1';
            menu.style.pointerEvents = 'auto';
            menu.style.transform = 'translateY(0) scale(1)';

            // Transformar hamburguesa a X
            if (line1) {
                line1.style.width = '28px';
                line1.style.transform = 'rotate(45deg) translateY(6px)';
            }
            if (line2) {
                line2.style.opacity = '0';
                line2.style.transform = 'scale(0)';
            }
            if (line3) {
                line3.style.width = '28px';
                line3.style.transform = 'rotate(-45deg) translateY(-6px)';
            }

            if (btn) {
                btn.style.transform = 'scale(1.05)';
            }
        } else {
            // Ocultar menú
            menu.style.opacity = '0';
            menu.style.pointerEvents = 'none';
            menu.style.transform = 'translateY(10px) scale(0.95)';

            setTimeout(() => {
                menu.classList.add('hidden');
            }, 300);

            // Volver a hamburguesa
            if (line1) {
                line1.style.width = '24px';
                line1.style.transform = 'rotate(0deg) translateY(0)';
            }
            if (line2) {
                line2.style.opacity = '1';
                line2.style.transform = 'scale(1)';
            }
            if (line3) {
                line3.style.width = '24px';
                line3.style.transform = 'rotate(0deg) translateY(0)';
            }

            if (btn) {
                btn.style.transform = 'scale(1)';
            }
        }
    }

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(event) {
        const container = document.getElementById('floating-support');
        if (!container) return;

        const isClickInside = container.contains(event.target);
        if (!isClickInside && isMenuOpen) {
            toggleSupportMenu();
        }
    });

    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && isMenuOpen) {
            toggleSupportMenu();
        }
    });

    // Inicializar menú cerrado
    document.addEventListener('DOMContentLoaded', function() {
        const menu = document.getElementById('support-options');
        if (menu) {
            menu.classList.add('hidden');
            menu.style.opacity = '0';
            menu.style.pointerEvents = 'none';
            menu.style.transform = 'translateY(10px) scale(0.95)';
            menu.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        }
    });
</script>
@endpush

<style>
    /* Estilos base */
    #floating-support {
        user-select: none;
        -webkit-user-select: none;
    }

    #support-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    #support-btn:hover {
        transform: scale(1.05) rotate(-3deg);
    }

    #support-btn:active {
        transform: scale(0.95);
    }

    /* Animación de entrada para los items del menú */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    /* Estilos del menú */
    #support-options {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #support-options a {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    /* Responsive */
    @media (max-width: 480px) {
        #floating-support {
            bottom: 1.25rem !important;
            right: 1.25rem !important;
        }

        #support-btn {
            width: 48px;
            height: 48px;
            border-radius: 14px;
        }

        #support-btn .w-6 {
            width: 18px;
        }

        #support-btn .w-5 {
            width: 15px;
        }

        #support-btn .h-0\.5 {
            height: 2px;
        }

        #support-options a {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            border-radius: 12px;
        }

        #support-options .w-8.h-8 {
            width: 1.75rem;
            height: 1.75rem;
        }

        #support-options .w-8.h-8 svg {
            width: 14px;
            height: 14px;
        }
    }

    /* Modo oscuro */
    @media (prefers-color-scheme: dark) {
        #support-options a {
            background: rgba(30, 30, 40, 0.95);
            color: #e2e8f0;
            border-color: rgba(255,255,255,0.1);
        }

        #support-options a:hover {
            background: rgba(40, 40, 50, 0.95);
        }
    }
</style>
