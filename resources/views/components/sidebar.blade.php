@props(['user'])

@php
    $isSuperAdmin = $user->hasRole('Super Admin');
    $isAdmin = $user->hasRole('Administrador');
    $isAsesor = $user->hasRole('Asesor Inmobiliario');
    $isAuditor = $user->hasRole('Auditor');
    $isCliente = $user->hasRole('Cliente');

    // Variables de utilidad
    $isAdminOrSuper = $isAdmin || $isSuperAdmin;
    $isAdminSuperOrAuditor = $isAdmin || $isSuperAdmin || $isAuditor;
    $showSupervision = ($isSuperAdmin || $isAdmin || $isAuditor) && !$isAsesor;
    $showAudit = $isSuperAdmin || $isAdmin || $isAuditor;
    $showChat = $isAsesor || $isCliente;
    $showLeads = $isAsesor || $isAuditor || $isAdmin || $isSuperAdmin;
    $showProperties = !$isCliente;
    $showAdminProperties = $isAdminOrSuper;
@endphp

{{-- Sidebar Desktop --}}
<aside id="sidebar"
       class="hidden lg:block bg-mso-blue text-white h-full flex-shrink-0 shadow-2xl sidebar-transition overflow-hidden"
       :class="sidebarOpen ? 'w-64' : 'w-20'"
       x-data>

    <div class="flex flex-col h-full overflow-y-auto custom-scroll"
         :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-hidden'">

        <!-- Logo -->
        <div class="h-20 flex items-center px-3 border-b border-slate-700/50 flex-shrink-0"
             :class="sidebarOpen ? 'justify-start' : 'justify-center'">

            <a href="{{ route('home') }}" class="flex items-center gap-2 group overflow-hidden"
               :class="sidebarOpen ? 'w-auto' : 'w-8'">
                <div class="w-8 h-8 bg-mso-gold flex items-center justify-center rounded flex-shrink-0 overflow-hidden">
                    <img src="{{ asset('favicon-96x96.png') }}"
                         alt="MSO"
                         class="w-7 h-7 object-contain">
                </div>

                <div class="flex flex-col" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <span class="font-serif font-bold text-xl text-white leading-none tracking-wide group-hover:text-mso-gold transition-colors">MSO</span>
                    <span class="text-[10px] text-slate-400 uppercase tracking-[0.2em]">Inmobiliaria</span>
                </div>
            </a>
        </div>

        <!-- Menú -->
        <nav class="flex-1 py-4 px-2 overflow-y-auto custom-scroll"
             :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-hidden'">
            <ul class="space-y-1">

                {{-- ============================================ --}}
                {{-- 1. DASHBOARD (TODOS) --}}
                {{-- ============================================ --}}
                <li class="relative group">
                    <a href="{{ $isSuperAdmin ? route('super-admin.dashboard') : ($isAdmin ? route('admin.dashboard') : ($isAsesor ? route('asesor.dashboard') : ($isAuditor ? route('auditor.dashboard') : route('cliente.dashboard')))) }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('*dashboard') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-squares-four text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Dashboard</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Dashboard
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>

                {{-- ============================================ --}}
                {{-- 2. CATÁLOGO DE PROPIEDADES (TODOS) --}}
                {{-- ============================================ --}}
                <li class="relative group">
                    <a href="{{ route('catalogo.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('catalogo*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-buildings text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Catálogo</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Catálogo
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>

                {{-- ============================================ --}}
                {{-- 3. SECCIÓN ADMINISTRACIÓN (Super Admin y Admin) --}}
                {{-- ============================================ --}}
                @if($isAdminOrSuper)
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Administración</p>
                </li>

                {{-- USUARIOS con submenú --}}
                <li class="relative group">
                    <button type="button"
                            onclick="toggleSidebarSubmenu('users-submenu')"
                            class="w-full flex items-center gap-3 px-2 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-users text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms class="flex-1 text-left">Usuarios</span>
                        <i class="ph ph-caret-down text-xs transition-transform duration-200"
                           id="users-submenu-icon"
                           x-show="sidebarOpen"
                           x-transition:enter.duration.300ms></i>
                    </button>

                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Usuarios
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>

                    <div id="users-submenu"
                         class="hidden pl-7 mt-1 space-y-1"
                         x-show="sidebarOpen"
                         x-transition:enter.duration.300ms>
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.users*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-user-list text-sm"></i>
                            Gestión de Usuarios
                        </a>
                        @if($isSuperAdmin)
                        <a href="{{ route('super-admin.roles.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('super-admin.roles*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-lock-key text-sm"></i>
                            Roles y Permisos
                        </a>
                        @endif
                    </div>
                </li>

                {{-- PROPIEDADES (Solo en Administración para Super Admin y Admin) --}}
                <li class="relative group">
                    <a href="{{ route('admin.properties.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.properties*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-building text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Propiedades</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Propiedades
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ============================================ --}}
                {{-- 4. SECCIÓN GESTIÓN (ASESOR) --}}
                {{-- ============================================ --}}
                @if($isAsesor)
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gestión</p>
                </li>

                <li class="relative group">
                    <a href="{{ route('leads.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('leads*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-users-three text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Leads</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Leads
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>

                <li class="relative group">
                    <a href="{{ route('asesor.properties.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('asesor.properties*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-building text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Mis Propiedades</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Mis Propiedades
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ============================================ --}}
                {{-- 5. SECCIÓN SUPERVISIÓN (Solo Auditor) --}}
                {{-- ============================================ --}}
                @if($isAuditor)
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Supervisión</p>
                </li>

                <li class="relative group">
                    <a href="{{ route('leads.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('leads*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-users-three text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Leads</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Leads
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ============================================ --}}
                {{-- 6. CITAS (TODOS) --}}
                {{-- ============================================ --}}
                <li class="relative group">
                    <a href="{{ route('citas.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('citas*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-calendar-check text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Citas</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Citas
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>

                {{-- ============================================ --}}
                {{-- 7. CHAT (SOLO ASESOR Y CLIENTE) --}}
                {{-- ============================================ --}}
                @if($showChat)
                <li class="relative group">
                    <a href="{{ route('chat.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('chat*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-chat-circle-text text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Chat</span>
                        <span id="unread-badge-sidebar" class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full hidden"
                              x-show="sidebarOpen"
                              x-transition:enter.duration.300ms>0</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Chat
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ============================================ --}}
                {{-- 8. SECCIÓN AUDITORÍA (Super Admin, Admin, Auditor) --}}
                {{-- ============================================ --}}
                @if($showAudit)
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Auditoría</p>
                </li>

                <li class="relative group">
                    <button type="button"
                            onclick="toggleSidebarSubmenu('audit-submenu')"
                            class="w-full flex items-center gap-3 px-2 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-scroll text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms class="flex-1 text-left">Auditoría</span>
                        <i class="ph ph-caret-down text-xs transition-transform duration-200"
                           id="audit-submenu-icon"
                           x-show="sidebarOpen"
                           x-transition:enter.duration.300ms></i>
                    </button>

                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Auditoría
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>

                    <div id="audit-submenu"
                         class="hidden pl-7 mt-1 space-y-1"
                         x-show="sidebarOpen"
                         x-transition:enter.duration.300ms>
                        <a href="{{ route('audit-logs.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.index') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-speedometer text-sm"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('audit-logs.user-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.user-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-users text-sm"></i>
                            Logs de Usuarios
                        </a>
                        <a href="{{ route('audit-logs.property-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.property-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-buildings text-sm"></i>
                            Logs de Propiedades
                        </a>
                        <a href="{{ route('audit-logs.appointment-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.appointment-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-calendar-check text-sm"></i>
                            Logs de Citas
                        </a>
                        <a href="{{ route('audit-logs.lead-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.lead-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-target text-sm"></i>
                            Logs de Leads
                        </a>
                        <a href="{{ route('audit-logs.system-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.system-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-gear text-sm"></i>
                            Logs del Sistema
                        </a>
                        <a href="{{ route('audit-logs.reports') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.reports*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-chart-pie text-sm"></i>
                            Reportes
                        </a>
                    </div>
                </li>

                {{-- REPORTES GERENCIALES (Super Admin, Admin, Auditor) --}}
                <li class="relative group">
                    <a href="{{ route('reports.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('reports*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-chart-bar text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Reportes Gerenciales</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Reportes Gerenciales
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ============================================ --}}
                {{-- 9. CONFIGURACIÓN (Solo Super Admin y Admin) --}}
                {{-- ============================================ --}}
                @if($isAdminOrSuper)
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Configuración</p>
                </li>

                <li class="relative group">
                    <button type="button"
                            onclick="toggleSidebarSubmenu('config-submenu')"
                            class="w-full flex items-center gap-3 px-2 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-gear text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms class="flex-1 text-left">Configuración</span>
                        <i class="ph ph-caret-down text-xs transition-transform duration-200"
                           id="config-submenu-icon"
                           x-show="sidebarOpen"
                           x-transition:enter.duration.300ms></i>
                    </button>

                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Configuración
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>

                    <div id="config-submenu"
                         class="hidden pl-7 mt-1 space-y-1"
                         x-show="sidebarOpen"
                         x-transition:enter.duration.300ms>
                        <a href="{{ route('admin.config.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.config*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-sliders text-sm"></i>
                            Configuración Plataforma
                        </a>
                        <a href="{{ route('admin.categories.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.categories*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-folder text-sm"></i>
                            Categorías
                        </a>
                        <a href="{{ route('admin.servicios.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.servicios*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-handshake text-sm"></i>
                            Servicios Inmobiliarios
                        </a>
                        <a href="{{ route('admin.locations.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.locations*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-globe-hemisphere-west text-sm"></i>
                            Ubicaciones
                        </a>
                        <a href="{{ route('admin.phones.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.phones*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-phone text-sm"></i>
                            Formato Telefónico
                        </a>
                    </div>
                </li>
                @endif

                {{-- ============================================ --}}
                {{-- 10. FAVORITOS (TODOS) --}}
                {{-- ============================================ --}}
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Favoritos</p>
                </li>

                <li class="relative group">
                    <a href="{{ route('favorites.index') }}"
                       class="flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('favorites*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-heart text-xl flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition:enter.duration.300ms>Mis Favoritos</span>
                        <span id="favorites-count-sidebar" class="ml-auto bg-mso-gold text-mso-blue text-[10px] px-2 py-0.5 rounded-full hidden"
                              x-show="sidebarOpen"
                              x-transition:enter.duration.300ms>0</span>
                    </a>
                    <div x-show="!sidebarOpen"
                         x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Mis Favoritos
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>

            </ul>
        </nav>

        <!-- Footer del Sidebar Desktop -->
        <footer class="p-2 border-t border-slate-700/50 flex-shrink-0">
            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-700/50 transition-colors"
                 :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'Usuario') . '&background=c5a059&color=fff&size=128&rounded=true&bold=true' }}"
                     class="w-9 h-9 rounded-full object-cover border border-slate-600 flex-shrink-0"
                     alt="{{ $user->full_name ?? 'Usuario' }}">
                <div class="flex-1 min-w-0" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="text-sm font-bold text-white truncate">{{ $user->full_name ?? 'Usuario' }}</p>
                    <p class="text-[10px] text-slate-400 truncate">
                        @if($isSuperAdmin) Super Admin
                        @elseif($isAdmin) Administrador
                        @elseif($isAsesor) Asesor
                        @elseif($isAuditor) Auditor
                        @else Cliente @endif
                    </p>
                </div>
                <a href="{{ route('profile.index') }}" class="text-slate-400 hover:text-mso-gold transition-colors flex-shrink-0" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <i class="ph ph-gear text-sm"></i>
                </a>
            </div>
        </footer>
    </div>
</aside>

{{-- Mobile Sidebar (Overlay) --}}
<div id="mobile-sidebar" class="fixed inset-0 bg-black/50 z-50 hidden lg:hidden">
    <div class="w-64 bg-mso-blue text-white h-full shadow-2xl overflow-y-auto custom-scroll">
        <div class="flex flex-col h-full">

            <!-- Header Mobile -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-700/50">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-mso-gold flex items-center justify-center rounded flex-shrink-0 overflow-hidden">
                        <img src="{{ asset('favicon-96x96.png') }}" alt="MSO" class="w-7 h-7 object-contain">
                    </div>
                    <span class="font-serif font-bold text-xl tracking-wide">MSO</span>
                </a>
                <button id="close-mobile-menu" class="text-slate-400 hover:text-white transition-colors">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <!-- Menú Mobile -->
            <nav class="flex-1 py-4 px-3 overflow-y-auto custom-scroll">
                <ul class="space-y-1">
                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ $isSuperAdmin ? route('super-admin.dashboard') : ($isAdmin ? route('admin.dashboard') : ($isAsesor ? route('asesor.dashboard') : ($isAuditor ? route('auditor.dashboard') : route('cliente.dashboard')))) }}"
                           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('*dashboard') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
                            <i class="ph ph-squares-four text-lg"></i>
                            Dashboard
                        </a>
                    </li>

                    {{-- Catálogo --}}
                    <li>
                        <a href="{{ route('catalogo.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('catalogo*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
                            <i class="ph ph-buildings text-lg"></i>
                            Catálogo
                        </a>
                    </li>

                    {{-- Administración (Super Admin y Admin) --}}
                    @if($isAdminOrSuper)
                    <li class="pt-3"><p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Administración</p></li>

                    <li>
                        <button type="button" onclick="toggleMobileSubmenu('users-mobile')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">
                            <div class="flex items-center gap-3"><i class="ph ph-users text-lg"></i> Usuarios</div>
                            <i class="ph ph-caret-down text-xs transition-transform duration-200" id="users-mobile-icon"></i>
                        </button>
                        <div id="users-mobile" class="hidden pl-7 mt-1 space-y-1">
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Gestión de Usuarios</a>
                            @if($isSuperAdmin)
                            <a href="{{ route('super-admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Roles y Permisos</a>
                            @endif
                        </div>
                    </li>

                    <li><a href="{{ route('admin.properties.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-building text-lg"></i> Propiedades</a></li>
                    @endif

                    {{-- Gestión (Asesor) --}}
                    @if($isAsesor)
                    <li class="pt-3"><p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gestión</p></li>
                    <li><a href="{{ route('leads.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-users-three text-lg"></i> Leads</a></li>
                    <li><a href="{{ route('asesor.properties.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-building text-lg"></i> Mis Propiedades</a></li>
                    @endif

                    {{-- Supervisión (Solo Auditor) --}}
                    @if($isAuditor)
                    <li class="pt-3"><p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Supervisión</p></li>
                    <li><a href="{{ route('leads.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-users-three text-lg"></i> Leads</a></li>
                    @endif

                    {{-- Citas (todos) --}}
                    <li><a href="{{ route('citas.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-calendar-check text-lg"></i> Citas</a></li>

                    {{-- Chat (SOLO ASESOR Y CLIENTE) --}}
                    @if($showChat)
                    <li><a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-chat-circle-text text-lg"></i> Chat</a></li>
                    @endif

                    {{-- Auditoría (Super Admin, Admin, Auditor) --}}
                    @if($showAudit)
                    <li class="pt-3"><p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Auditoría</p></li>
                    <li>
                        <button type="button" onclick="toggleMobileSubmenu('audit-mobile')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">
                            <div class="flex items-center gap-3"><i class="ph ph-scroll text-lg"></i> Auditoría</div>
                            <i class="ph ph-caret-down text-xs transition-transform duration-200" id="audit-mobile-icon"></i>
                        </button>
                        <div id="audit-mobile" class="hidden pl-7 mt-1 space-y-1">
                            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Dashboard</a>
                            <a href="{{ route('audit-logs.user-logs') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Logs de Usuarios</a>
                            <a href="{{ route('audit-logs.property-logs') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Logs de Propiedades</a>
                            <a href="{{ route('audit-logs.appointment-logs') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Logs de Citas</a>
                            <a href="{{ route('audit-logs.lead-logs') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Logs de Leads</a>
                            <a href="{{ route('audit-logs.system-logs') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Logs del Sistema</a>
                            <a href="{{ route('audit-logs.reports') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Reportes</a>
                        </div>
                    </li>
                    <li><a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-chart-bar text-lg"></i> Reportes Gerenciales</a></li>
                    @endif

                    {{-- Configuración (Super Admin y Admin) --}}
                    @if($isAdminOrSuper)
                    <li class="pt-3"><p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Configuración</p></li>
                    <li>
                        <button type="button" onclick="toggleMobileSubmenu('config-mobile')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">
                            <div class="flex items-center gap-3"><i class="ph ph-gear text-lg"></i> Configuración</div>
                            <i class="ph ph-caret-down text-xs transition-transform duration-200" id="config-mobile-icon"></i>
                        </button>
                        <div id="config-mobile" class="hidden pl-7 mt-1 space-y-1">
                            <a href="{{ route('admin.config.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Configuración Plataforma</a>
                            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Categorías</a>
                            <a href="{{ route('admin.servicios.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Servicios Inmobiliarios</a>
                            <a href="{{ route('admin.locations.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Ubicaciones</a>
                            <a href="{{ route('admin.phones.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">Formato Telefónico</a>
                        </div>
                    </li>
                    @endif

                    {{-- Favoritos --}}
                    <li class="pt-3"><p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Favoritos</p></li>
                    <li><a href="{{ route('favorites.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors text-slate-300 hover:text-white"><i class="ph ph-heart text-lg"></i> Mis Favoritos</a></li>

                    {{-- FOOTER DEL USUARIO EN MÓVIL --}}
                    <li class="pt-3"><p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mi Cuenta</p></li>

                    <li class="px-3 py-2">
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-700/30">
                            <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'Usuario') . '&background=c5a059&color=fff&size=128&rounded=true&bold=true' }}"
                                 class="w-9 h-9 rounded-full object-cover border border-slate-600 flex-shrink-0"
                                 alt="{{ $user->full_name ?? 'Usuario' }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-white truncate">{{ $user->full_name ?? 'Usuario' }}</p>
                                <p class="text-[10px] text-slate-400 truncate">
                                    @if($isSuperAdmin) Super Admin
                                    @elseif($isAdmin) Administrador
                                    @elseif($isAsesor) Asesor
                                    @elseif($isAuditor) Auditor
                                    @else Cliente @endif
                                </p>
                            </div>
                            <a href="{{ route('profile.index') }}" class="text-slate-400 hover:text-mso-gold transition-colors flex-shrink-0">
                                <i class="ph ph-gear text-sm"></i>
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

@push('js')
<script>
    // ============================================
    // TOGGLE SUBMENÚ EN SIDEBAR DESKTOP
    // ============================================
    function toggleSidebarSubmenu(id) {
        const content = document.getElementById(id);
        const icon = document.getElementById(id + '-icon');

        if (content) {
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                if (icon) icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }
    }

    // ============================================
    // TOGGLE SUBMENÚ EN SIDEBAR MÓVIL
    // ============================================
    function toggleMobileSubmenu(id) {
        const content = document.getElementById(id);
        const icon = document.getElementById(id + '-icon');

        if (content) {
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                if (icon) icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }
    }

    // ============================================
    // MANTENER SUBMENÚS ABIERTOS SEGÚN RUTA
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;

        // Usuarios
        if (currentPath.includes('/admin/users') || currentPath.includes('/super-admin/roles')) {
            ['users-submenu', 'users-mobile'].forEach(id => {
                const content = document.getElementById(id);
                const icon = document.getElementById(id + '-icon');
                if (content) {
                    content.classList.remove('hidden');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            });
        }

        // Auditoría
        if (currentPath.includes('/audit-logs')) {
            ['audit-submenu', 'audit-mobile'].forEach(id => {
                const content = document.getElementById(id);
                const icon = document.getElementById(id + '-icon');
                if (content) {
                    content.classList.remove('hidden');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            });
        }

        // Configuración
        if (currentPath.includes('/admin/config') ||
            currentPath.includes('/admin/categories') ||
            currentPath.includes('/admin/locations') ||
            currentPath.includes('/admin/phones') ||
            currentPath.includes('/admin/servicios')) {
            ['config-submenu', 'config-mobile'].forEach(id => {
                const content = document.getElementById(id);
                const icon = document.getElementById(id + '-icon');
                if (content) {
                    content.classList.remove('hidden');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            });
        }

        // ============================================
        // ACTUALIZAR BADGES
        // ============================================
        function updateUnreadBadge() {
            fetch('{{ route("chat.unread-count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('unread-badge-sidebar');
                    if (badge && data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.classList.remove('hidden');
                    } else if (badge) {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function updateFavoritesCount() {
            fetch('{{ route("favorites.count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('favorites-count-sidebar');
                    if (badge && data.total > 0) {
                        badge.textContent = data.total > 99 ? '99+' : data.total;
                        badge.classList.remove('hidden');
                    } else if (badge) {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        updateUnreadBadge();
        updateFavoritesCount();
        setInterval(updateUnreadBadge, 30000);
        setInterval(updateFavoritesCount, 30000);
    });
</script>
@endpush
