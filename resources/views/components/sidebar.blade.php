@props(['user'])

@php
    if ($user) {
        $permission = new \App\Services\PermissionService($user);
    } else {
        $permission = app(\App\Services\PermissionService::class);
    }

    $perms = $permission->getSidebarPermissions();
    $dashboardRoute = $permission->getDashboardRoute();
    $mainRole = $permission->getMainRole();
    $roleDisplayName = $permission->getRoleDisplayName();

    $isSuperAdmin = $permission->hasRole('Super Admin');
    $isAdmin = $permission->hasRole('Administrador');
    $isAsesor = $permission->hasRole('Asesor Inmobiliario');
    $isAuditor = $permission->hasRole('Auditor');
    $isCliente = $permission->hasRole('Cliente');
    $isAdminOrSuper = $isAdmin || $isSuperAdmin;

    // ==========================================
    // Verificar visibilidad del sidebar
    // ==========================================
    $canSee = function($permissionName) use ($permission) {
        return $permission->canSeeSidebarItem($permissionName);
    };
@endphp

{{-- ============================================= --}}
{{-- SIDEBAR MÓVIL - BOTÓN FLOTANTE --}}
{{-- ============================================= --}}
<button id="mobileMenuToggle"
        class="lg:hidden fixed bottom-4 right-4 z-50 bg-mso-blue text-white p-4 rounded-full shadow-2xl hover:bg-mso-gold hover:text-mso-blue transition-all duration-300 hover:scale-110"
        onclick="toggleMobileSidebar()"
        aria-label="Abrir menú">
    <i class="ph ph-list text-2xl"></i>
</button>

{{-- ============================================= --}}
{{-- SIDEBAR DESKTOP --}}
{{-- ============================================= --}}
<aside id="sidebar"
       class="hidden lg:block bg-mso-blue text-white h-full shadow-2xl transition-all duration-300 ease-in-out"
       :class="sidebarOpen ? 'w-64' : 'w-20'"
       x-data>
    <div class="sidebar-inner"
         :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-hidden'">

        <!-- Logo -->
        <div class="h-20 flex items-center px-3 border-b border-slate-700/50 flex-shrink-0"
             :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <a href="{{ $dashboardRoute }}" class="flex items-center gap-2 group overflow-hidden"
               :class="sidebarOpen ? 'w-auto' : 'w-8'">
                <div class="w-8 h-8 bg-mso-gold flex items-center justify-center rounded flex-shrink-0 overflow-hidden">
                    <img src="{{ asset('favicon-96x96.png') }}" alt="MSO" class="w-7 h-7 object-contain">
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

                {{-- ========================================== --}}
                {{-- DASHBOARD --}}
                {{-- ========================================== --}}
                @if($canSee('sidebar.dashboard'))
                <li class="relative group">
                    <a href="{{ $dashboardRoute }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('*dashboard') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-squares-four text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Dashboard</span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Dashboard
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ========================================== --}}
                {{-- CATÁLOGO --}}
                {{-- ========================================== --}}
                @if($canSee('sidebar.catalogo'))
                <li class="relative group">
                    <a href="{{ route('catalogo.index') }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('catalogo*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-buildings text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Catálogo</span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Catálogo
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ========================================== --}}
                {{-- ADMINISTRACIÓN --}}
                {{-- ========================================== --}}
                @if($isSuperAdmin || $isAdmin)
                @if($canSee('sidebar.users') || $canSee('sidebar.roles'))
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Administración</p>
                </li>

                {{-- USUARIOS --}}
                @if($canSee('sidebar.users'))
                <li class="relative group">
                    <button type="button"
                            onclick="toggleSidebarSubmenu('users-submenu')"
                            class="w-full flex items-center gap-3 px-2 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-users text-xl flex-shrink-0"></i>
                        <span class="nav-text flex-1 text-left" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Usuarios</span>
                        <i class="ph ph-caret-down text-xs transition-transform duration-200"
                           id="users-submenu-icon"
                           x-show="sidebarOpen"
                           x-transition:enter.duration.300ms></i>
                    </button>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Usuarios
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                    <div id="users-submenu"
                         class="hidden pl-7 mt-1 space-y-1"
                         x-show="sidebarOpen"
                         x-transition:enter.duration.300ms">

                        @if($canSee('sidebar.users'))
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.users*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-user-list text-sm"></i>
                            Gestión de Usuarios
                        </a>
                        @endif

                        @if($canSee('sidebar.roles'))
                        <a href="{{ route('super-admin.roles.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('super-admin.roles*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-lock-key text-sm"></i>
                            Roles y Permisos
                        </a>
                        @endif

                        @if($isSuperAdmin)
                        <a href="{{ route('super-admin.sidebar-permissions.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('super-admin.sidebar-permissions*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-layout text-sm"></i>
                            Sidebar Permissions
                        </a>
                        @endif

                    </div>
                </li>
                @endif
                @endif
                @endif

                {{-- ========================================== --}}
                {{-- PROPIEDADES --}}
                {{-- ========================================== --}}
                @if($canSee('sidebar.properties'))
                <li class="relative group">
                    <a href="{{ $isAdminOrSuper ? route('admin.properties.index') : route('asesor.properties.index') }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('*properties*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-building text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                            {{ $isAdminOrSuper ? 'Propiedades' : 'Mis Propiedades' }}
                        </span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        {{ $isAdminOrSuper ? 'Propiedades' : 'Mis Propiedades' }}
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ========================================== --}}
                {{-- GESTIÓN --}}
                {{-- ========================================== --}}
                @if($isAsesor || $isAdminOrSuper)
                @if($canSee('sidebar.leads') || $canSee('sidebar.appointments'))
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gestión</p>
                </li>

                {{-- LEADS --}}
                @if($canSee('sidebar.leads'))
                <li class="relative group">
                    <a href="{{ route('leads.index') }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('leads*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-users-three text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Leads</span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Leads
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- CITAS --}}
                @if($canSee('sidebar.appointments'))
                <li class="relative group">
                    <a href="{{ route('citas.index') }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('citas*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-calendar-check text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Citas</span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Citas
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif
                @endif
                @endif

                {{-- ========================================== --}}
                {{-- CHAT --}}
                {{-- ========================================== --}}
                @if($canSee('sidebar.chat'))
                <li class="relative group">
                    <a href="{{ route('chat.index') }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('chat*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-chat-circle-text text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Chat</span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Chat
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ========================================== --}}
                {{-- AUDITORÍA --}}
                {{-- ========================================== --}}
                @if($canSee('sidebar.audit'))
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Auditoría</p>
                </li>

                <li class="relative group">
                    <button type="button"
                            onclick="toggleSidebarSubmenu('audit-submenu')"
                            class="w-full flex items-center gap-3 px-2 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-scroll text-xl flex-shrink-0"></i>
                        <span class="nav-text flex-1 text-left" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Auditoría</span>
                        <i class="ph ph-caret-down text-xs transition-transform duration-200"
                           id="audit-submenu-icon"
                           x-show="sidebarOpen"
                           x-transition:enter.duration.300ms></i>
                    </button>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Auditoría
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                    <div id="audit-submenu"
                         class="hidden pl-7 mt-1 space-y-1"
                         x-show="sidebarOpen"
                         x-transition:enter.duration.300ms">
                        <a href="{{ route('audit-logs.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.index') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-speedometer text-sm"></i> Dashboard
                        </a>
                        <a href="{{ route('audit-logs.user-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.user-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-users text-sm"></i> Logs de Usuarios
                        </a>
                        <a href="{{ route('audit-logs.property-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.property-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-buildings text-sm"></i> Logs de Propiedades
                        </a>
                        <a href="{{ route('audit-logs.appointment-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.appointment-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-calendar-check text-sm"></i> Logs de Citas
                        </a>
                        <a href="{{ route('audit-logs.lead-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.lead-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-target text-sm"></i> Logs de Leads
                        </a>
                        <a href="{{ route('audit-logs.system-logs') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.system-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-gear text-sm"></i> Logs del Sistema
                        </a>
                        <a href="{{ route('audit-logs.reports') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.reports*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-chart-pie text-sm"></i> Reportes
                        </a>
                    </div>
                </li>
                @endif

                {{-- ========================================== --}}
                {{-- REPORTES GERENCIALES --}}
                {{-- ========================================== --}}
                @if($canSee('sidebar.reports'))
                <li class="relative group">
                    <a href="{{ route('reports.index') }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('reports*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-chart-bar text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Reportes Gerenciales</span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Reportes Gerenciales
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

                {{-- ========================================== --}}
                {{-- CONFIGURACIÓN --}}
                {{-- ========================================== --}}
                @if($isSuperAdmin || $isAdmin)
                @if($canSee('sidebar.config') || $canSee('sidebar.servicios') || $canSee('sidebar.categorias') || $canSee('sidebar.ubicaciones') || $canSee('sidebar.telefonos'))
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Configuración</p>
                </li>

                <li class="relative group">
                    <button type="button"
                            onclick="toggleSidebarSubmenu('config-submenu')"
                            class="w-full flex items-center gap-3 px-2 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-gear text-xl flex-shrink-0"></i>
                        <span class="nav-text flex-1 text-left" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Configuración</span>
                        <i class="ph ph-caret-down text-xs transition-transform duration-200"
                           id="config-submenu-icon"
                           x-show="sidebarOpen"
                           x-transition:enter.duration.300ms></i>
                    </button>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Configuración
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                    <div id="config-submenu"
                         class="hidden pl-7 mt-1 space-y-1"
                         x-show="sidebarOpen"
                         x-transition:enter.duration.300ms">
                        @if($canSee('sidebar.config'))
                        <a href="{{ route('admin.config.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.config*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-sliders text-sm"></i>
                            Configuración Plataforma
                        </a>
                        @endif
                        @if($canSee('sidebar.categorias'))
                        <a href="{{ route('admin.categories.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.categories*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-folder text-sm"></i>
                            Categorías
                        </a>
                        @endif
                        @if($canSee('sidebar.servicios'))
                        <a href="{{ route('admin.servicios.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.servicios*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-handshake text-sm"></i>
                            Servicios Inmobiliarios
                        </a>
                        @endif
                        @if($canSee('sidebar.ubicaciones'))
                        <a href="{{ route('admin.locations.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.locations*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-globe-hemisphere-west text-sm"></i>
                            Ubicaciones
                        </a>
                        @endif
                        @if($canSee('sidebar.telefonos'))
                        <a href="{{ route('admin.phones.index') }}"
                           class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.phones*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                            <i class="ph ph-phone text-sm"></i>
                            Formato Telefónico
                        </a>
                        @endif
                    </div>
                </li>
                @endif
                @endif

                {{-- ========================================== --}}
                {{-- FAVORITOS --}}
                {{-- ========================================== --}}
                @if($canSee('sidebar.favorites'))
                <li class="pt-3" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Favoritos</p>
                </li>

                <li class="relative group">
                    <a href="{{ route('favorites.index') }}"
                       class="nav-item flex items-center gap-3 px-2 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('favorites*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}"
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <i class="ph ph-heart text-xl flex-shrink-0"></i>
                        <span class="nav-text" :class="sidebarOpen ? 'nav-text-visible' : 'nav-text-hidden'" x-show="sidebarOpen" x-transition:enter.duration.300ms>Mis Favoritos</span>
                    </a>
                    <div x-show="!sidebarOpen" x-cloak
                         class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50 shadow-lg border border-slate-700">
                        Mis Favoritos
                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-800 rotate-45 border-l border-b border-slate-700"></div>
                    </div>
                </li>
                @endif

            </ul>
        </nav>

        <!-- Footer del Sidebar Desktop -->
        <footer class="p-2 border-t border-slate-700/50 flex-shrink-0">
            @if($canSee('sidebar.profile'))
            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-700/50 transition-colors"
                 :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'Usuario') . '&background=c5a059&color=fff&size=128&rounded=true&bold=true' }}"
                     class="w-9 h-9 rounded-full object-cover border border-slate-600 flex-shrink-0"
                     alt="{{ $user->full_name ?? 'Usuario' }}">
                <div class="flex-1 min-w-0" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="text-sm font-bold text-white truncate">{{ $user->full_name ?? 'Usuario' }}</p>
                    <p class="text-[10px] text-slate-400 truncate">
                        {{ $roleDisplayName }}
                    </p>
                </div>
                <a href="{{ route('profile.index') }}" class="text-slate-400 hover:text-mso-gold transition-colors flex-shrink-0" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <i class="ph ph-gear text-sm"></i>
                </a>
            </div>
            @else
            <div class="flex items-center justify-center p-2" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'Usuario') . '&background=c5a059&color=fff&size=128&rounded=true&bold=true' }}"
                     class="w-9 h-9 rounded-full object-cover border border-slate-600 flex-shrink-0"
                     alt="{{ $user->full_name ?? 'Usuario' }}">
                <div class="flex-1 min-w-0" x-show="sidebarOpen" x-transition:enter.duration.300ms>
                    <p class="text-sm font-bold text-white truncate">{{ $user->full_name ?? 'Usuario' }}</p>
                    <p class="text-[10px] text-slate-400 truncate">
                        {{ $roleDisplayName }}
                    </p>
                </div>
            </div>
            @endif
        </footer>
    </div>
</aside>

{{-- ============================================= --}}
{{-- SIDEBAR MÓVIL - OVERLAY Y PANEL --}}
{{-- ============================================= --}}

{{-- Overlay oscuro --}}
<div id="mobileSidebarOverlay"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden"
     onclick="closeMobileSidebar()">
</div>

{{-- Panel del sidebar móvil --}}
<aside id="mobileSidebar"
       class="fixed top-0 left-0 h-full w-72 bg-mso-blue text-white z-50 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto lg:hidden">

    {{-- Logo --}}
    <div class="h-20 flex items-center px-4 border-b border-slate-700/50">
        <a href="{{ $dashboardRoute }}" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-mso-gold flex items-center justify-center rounded overflow-hidden">
                <img src="{{ asset('favicon-96x96.png') }}" alt="MSO" class="w-7 h-7 object-contain">
            </div>
            <div>
                <span class="font-serif font-bold text-xl text-white leading-none">MSO</span>
                <span class="block text-[10px] text-slate-400 uppercase tracking-[0.2em]">Inmobiliaria</span>
            </div>
        </a>
        <button onclick="closeMobileSidebar()" class="ml-auto text-slate-400 hover:text-white p-2">
            <i class="ph ph-x text-2xl"></i>
        </button>
    </div>

    {{-- Menú Móvil --}}
    <nav class="p-3 space-y-1">

        {{-- DASHBOARD --}}
        @if($canSee('sidebar.dashboard'))
        <a href="{{ $dashboardRoute }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('*dashboard') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-squares-four text-xl"></i> Dashboard
        </a>
        @endif

        {{-- CATÁLOGO --}}
        @if($canSee('sidebar.catalogo'))
        <a href="{{ route('catalogo.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('catalogo*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-buildings text-xl"></i> Catálogo
        </a>
        @endif

        {{-- ADMINISTRACIÓN --}}
        @if($isSuperAdmin || $isAdmin)
        @if($canSee('sidebar.users') || $canSee('sidebar.roles'))
        <div class="pt-3">
            <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Administración</p>
        </div>

        {{-- USUARIOS --}}
        @if($canSee('sidebar.users'))
        <div class="space-y-1">
            <button type="button"
                    onclick="toggleMobileSubmenu('users-mobile')"
                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">
                <div class="flex items-center gap-3">
                    <i class="ph ph-users text-xl"></i> Usuarios
                </div>
                <i class="ph ph-caret-down text-xs transition-transform duration-200" id="users-mobile-icon"></i>
            </button>
            <div id="users-mobile" class="hidden pl-7 space-y-1">

                @if($canSee('sidebar.users'))
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.users*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-user-list text-sm"></i> Gestión de Usuarios
                </a>
                @endif

                @if($canSee('sidebar.roles'))
                <a href="{{ route('super-admin.roles.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('super-admin.roles*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-lock-key text-sm"></i> Roles y Permisos
                </a>
                @endif

                @if($isSuperAdmin)
                <a href="{{ route('super-admin.sidebar-permissions.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('super-admin.sidebar-permissions*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-layout text-sm"></i> Sidebar Permissions
                </a>
                @endif

            </div>
        </div>
        @endif
        @endif
        @endif

        {{-- PROPIEDADES --}}
        @if($canSee('sidebar.properties'))
        <a href="{{ $isAdminOrSuper ? route('admin.properties.index') : route('asesor.properties.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('*properties*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-building text-xl"></i> {{ $isAdminOrSuper ? 'Propiedades' : 'Mis Propiedades' }}
        </a>
        @endif

        {{-- GESTIÓN --}}
        @if($isAsesor || $isAdminOrSuper)
        @if($canSee('sidebar.leads') || $canSee('sidebar.appointments'))
        <div class="pt-3">
            <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gestión</p>
        </div>

        {{-- LEADS --}}
        @if($canSee('sidebar.leads'))
        <a href="{{ route('leads.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('leads*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-users-three text-xl"></i> Leads
        </a>
        @endif

        {{-- CITAS --}}
        @if($canSee('sidebar.appointments'))
        <a href="{{ route('citas.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('citas*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-calendar-check text-xl"></i> Citas
        </a>
        @endif
        @endif
        @endif

        {{-- CHAT --}}
        @if($canSee('sidebar.chat'))
        <a href="{{ route('chat.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('chat*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-chat-circle-text text-xl"></i> Chat
        </a>
        @endif

        {{-- AUDITORÍA --}}
        @if($canSee('sidebar.audit'))
        <div class="pt-3">
            <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Auditoría</p>
        </div>

        <div class="space-y-1">
            <button type="button"
                    onclick="toggleMobileSubmenu('audit-mobile')"
                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">
                <div class="flex items-center gap-3">
                    <i class="ph ph-scroll text-xl"></i> Auditoría
                </div>
                <i class="ph ph-caret-down text-xs transition-transform duration-200" id="audit-mobile-icon"></i>
            </button>
            <div id="audit-mobile" class="hidden pl-7 space-y-1">
                <a href="{{ route('audit-logs.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.index') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-speedometer text-sm"></i> Dashboard
                </a>
                <a href="{{ route('audit-logs.user-logs') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.user-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-users text-sm"></i> Logs de Usuarios
                </a>
                <a href="{{ route('audit-logs.property-logs') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.property-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-buildings text-sm"></i> Logs de Propiedades
                </a>
                <a href="{{ route('audit-logs.appointment-logs') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.appointment-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-calendar-check text-sm"></i> Logs de Citas
                </a>
                <a href="{{ route('audit-logs.lead-logs') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.lead-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-target text-sm"></i> Logs de Leads
                </a>
                <a href="{{ route('audit-logs.system-logs') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.system-logs') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-gear text-sm"></i> Logs del Sistema
                </a>
                <a href="{{ route('audit-logs.reports') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('audit-logs.reports*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-chart-pie text-sm"></i> Reportes
                </a>
            </div>
        </div>
        @endif

        {{-- REPORTES GERENCIALES --}}
        @if($canSee('sidebar.reports'))
        <a href="{{ route('reports.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('reports*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-chart-bar text-xl"></i> Reportes Gerenciales
        </a>
        @endif

        {{-- CONFIGURACIÓN --}}
        @if($isSuperAdmin || $isAdmin)
        @if($canSee('sidebar.config') || $canSee('sidebar.servicios') || $canSee('sidebar.categorias') || $canSee('sidebar.ubicaciones') || $canSee('sidebar.telefonos'))
        <div class="pt-3">
            <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Configuración</p>
        </div>

        <div class="space-y-1">
            <button type="button"
                    onclick="toggleMobileSubmenu('config-mobile')"
                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium text-slate-300 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors">
                <div class="flex items-center gap-3">
                    <i class="ph ph-gear text-xl"></i> Configuración
                </div>
                <i class="ph ph-caret-down text-xs transition-transform duration-200" id="config-mobile-icon"></i>
            </button>
            <div id="config-mobile" class="hidden pl-7 space-y-1">
                @if($canSee('sidebar.config'))
                <a href="{{ route('admin.config.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.config*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-sliders text-sm"></i> Configuración Plataforma
                </a>
                @endif
                @if($canSee('sidebar.categorias'))
                <a href="{{ route('admin.categories.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.categories*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-folder text-sm"></i> Categorías
                </a>
                @endif
                @if($canSee('sidebar.servicios'))
                <a href="{{ route('admin.servicios.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.servicios*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-handshake text-sm"></i> Servicios Inmobiliarios
                </a>
                @endif
                @if($canSee('sidebar.ubicaciones'))
                <a href="{{ route('admin.locations.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.locations*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-globe-hemisphere-west text-sm"></i> Ubicaciones
                </a>
                @endif
                @if($canSee('sidebar.telefonos'))
                <a href="{{ route('admin.phones.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('admin.phones*') ? 'text-mso-gold bg-slate-700/50' : '' }}">
                    <i class="ph ph-phone text-sm"></i> Formato Telefónico
                </a>
                @endif
            </div>
        </div>
        @endif
        @endif

        {{-- FAVORITOS --}}
        @if($canSee('sidebar.favorites'))
        <div class="pt-3">
            <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Favoritos</p>
        </div>

        <a href="{{ route('favorites.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 transition-colors {{ request()->routeIs('favorites*') ? 'bg-slate-700/50 text-mso-gold' : 'text-slate-300 hover:text-white' }}">
            <i class="ph ph-heart text-xl"></i> Mis Favoritos
        </a>
        @endif

        {{-- PERFIL - Móvil --}}
        <div class="pt-3 border-t border-slate-700/50 mt-4">
            <div class="flex items-center gap-3 px-3 py-2">
                <img src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'Usuario') . '&background=c5a059&color=fff&size=128&rounded=true&bold=true' }}"
                     class="w-9 h-9 rounded-full object-cover border border-slate-600"
                     alt="{{ $user->full_name ?? 'Usuario' }}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ $user->full_name ?? 'Usuario' }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ $roleDisplayName }}</p>
                </div>
                @if($canSee('sidebar.profile'))
                <a href="{{ route('profile.index') }}" class="text-slate-400 hover:text-mso-gold transition-colors">
                    <i class="ph ph-gear text-sm"></i>
                </a>
                @endif
            </div>
        </div>

    </nav>
</aside>

@push('js')
<script>
    // ============================================
    // FUNCIONES DEL SIDEBAR MÓVIL
    // ============================================
    function openMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');

        if (sidebar) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        }
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('block');
        }
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');

        if (sidebar) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
        }
        if (overlay) {
            overlay.classList.remove('block');
            overlay.classList.add('hidden');
        }
        document.body.style.overflow = '';
    }

    function toggleMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        if (sidebar) {
            if (sidebar.classList.contains('-translate-x-full')) {
                openMobileSidebar();
            } else {
                closeMobileSidebar();
            }
        } else {
            openMobileSidebar();
        }
    }

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
    // CERRAR SIDEBAR MÓVIL CON ESC
    // ============================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileSidebar();
        }
    });

    // ============================================
    // MANTENER SUBMENÚS ABIERTOS SEGÚN RUTA
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;

        // Usuarios
        if (currentPath.includes('/admin/users') ||
            currentPath.includes('/super-admin/roles') ||
            currentPath.includes('/super-admin/sidebar-permissions')) {
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
    });

    // ============================================
    // PREVENIR QUE EL SIDEBAR MÓVIL SE CIERRE AL HACER CLICK DENTRO
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const mobileSidebar = document.getElementById('mobileSidebar');
        if (mobileSidebar) {
            mobileSidebar.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Cerrar sidebar móvil al hacer clic en enlaces
        const mobileLinks = document.querySelectorAll('#mobileSidebar a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(closeMobileSidebar, 300);
            });
        });
    });

    // ============================================
    // CERRAR SIDEBAR AL REDIMENSIONAR A DESKTOP
    // ============================================
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            closeMobileSidebar();
        }
    });
</script>

<style>
    .custom-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
    .nav-text-hidden {
        opacity: 0;
        width: 0;
        overflow: hidden;
        display: none;
    }
    .nav-text-visible {
        opacity: 1;
        width: auto;
        display: inline;
    }

    /* ========================================== */
    /* ANIMACIONES DEL SIDEBAR MÓVIL */
    /* ========================================== */
    #mobileSidebar {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #mobileSidebar.-translate-x-full {
        transform: translateX(-100%);
    }

    #mobileSidebar.translate-x-0 {
        transform: translateX(0);
    }

    #mobileSidebarOverlay {
        transition: opacity 0.3s ease;
    }

    #mobileSidebarOverlay.hidden {
        opacity: 0;
        pointer-events: none;
    }

    #mobileSidebarOverlay.block {
        opacity: 1;
        pointer-events: auto;
    }
</style>
@endpush
