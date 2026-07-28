{{-- Dashboard principal del Super Administrador --}}
@extends('layouts.dashboard')

@section('title', 'Panel Super Admin')
@section('header', 'Panel de Control General')

@push('styles')
<style>
    /* ============================================ */
    /* BASE - ESTILOS RESPONSIVOS */
    /* ============================================ */
    .metric-card {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.08);
        border-color: rgba(197, 160, 89, 0.3);
    }
    .metric-card .icon-wrapper {
        transition: transform 0.3s ease;
    }
    .metric-card:hover .icon-wrapper {
        transform: scale(1.1);
    }
    .user-item, .property-item {
        transition: all 0.25s ease;
    }
    .user-item:hover, .property-item:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    /* ============================================ */
    /* ANIMACIONES */
    /* ============================================ */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .metric-card {
        animation: fadeSlideUp 0.5s ease-out both;
    }
    .metric-card:nth-child(1) { animation-delay: 0.05s; }
    .metric-card:nth-child(2) { animation-delay: 0.1s; }
    .metric-card:nth-child(3) { animation-delay: 0.15s; }
    .metric-card:nth-child(4) { animation-delay: 0.2s; }

    /* ============================================ */
    /* RESPONSIVE - TABLET */
    /* ============================================ */
    @media (max-width: 1024px) {
        .metric-card {
            padding: 16px !important;
        }
        .metric-card .text-3xl {
            font-size: 1.75rem !important;
        }
        .metric-card .w-12.h-12 {
            width: 40px !important;
            height: 40px !important;
        }
        .metric-card .w-12.h-12 i {
            font-size: 1.25rem !important;
        }
    }

    /* ============================================ */
    /* RESPONSIVE - MÓVIL */
    /* ============================================ */
    @media (max-width: 640px) {
        /* KPIs - 2 columnas */
        .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-4 {
            grid-template-columns: 1fr 1fr !important;
            gap: 10px !important;
        }

        .metric-card {
            padding: 12px !important;
            border-radius: 12px !important;
        }
        .metric-card .text-3xl {
            font-size: 1.25rem !important;
        }
        .metric-card .w-12.h-12 {
            width: 32px !important;
            height: 32px !important;
            border-radius: 10px !important;
        }
        .metric-card .w-12.h-12 i {
            font-size: 1rem !important;
        }
        .metric-card .text-sm {
            font-size: 10px !important;
        }
        .metric-card .text-xs {
            font-size: 8px !important;
        }
        .metric-card .mb-4 {
            margin-bottom: 8px !important;
        }
        .metric-card .mt-1 {
            margin-top: 2px !important;
        }
        .metric-card .px-2.py-1 {
            padding: 1px 6px !important;
            font-size: 7px !important;
        }

        /* Usuarios y Propiedades - 1 columna */
        .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-3 {
            grid-template-columns: 1fr !important;
            gap: 8px !important;
        }
        .user-item, .property-item {
            padding: 8px 10px !important;
            gap: 8px !important;
        }
        .user-item .w-10.h-10 {
            width: 32px !important;
            height: 32px !important;
        }
        .user-item .text-sm.font-bold {
            font-size: 11px !important;
        }
        .user-item .text-xs {
            font-size: 9px !important;
        }
        .user-item .text-[10px] {
            font-size: 8px !important;
        }
        .user-item .px-2.py-1 {
            padding: 1px 4px !important;
            font-size: 7px !important;
        }
        .user-item .ml-2 {
            margin-left: 4px !important;
        }

        /* Propiedades */
        .property-item .w-20.h-16 {
            width: 56px !important;
            height: 44px !important;
        }
        .property-item .text-sm.font-bold {
            font-size: 11px !important;
        }
        .property-item .text-xs {
            font-size: 9px !important;
        }
        .property-item .text-[10px] {
            font-size: 8px !important;
        }

        /* Títulos de sección */
        .text-lg {
            font-size: 14px !important;
        }
        .text-sm.text-mso-blue {
            font-size: 10px !important;
        }
        .p-6 {
            padding: 12px !important;
        }
        .mb-4 {
            margin-bottom: 8px !important;
        }
        .gap-4 {
            gap: 8px !important;
        }
        .gap-6 {
            gap: 12px !important;
        }
        .space-y-6 > * + * {
            margin-top: 12px !important;
        }

        /* Badges de estado */
        .inline-block.mt-0\.5 {
            margin-top: 0 !important;
        }
        .rounded-full {
            border-radius: 9999px !important;
        }
        .rounded-lg {
            border-radius: 8px !important;
        }
        .rounded-2xl {
            border-radius: 12px !important;
        }
    }

    /* ============================================ */
    /* RESPONSIVE - MÓVIL MUY PEQUEÑO */
    /* ============================================ */
    @media (max-width: 400px) {
        .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-4 {
            grid-template-columns: 1fr 1fr !important;
            gap: 6px !important;
        }
        .metric-card {
            padding: 8px !important;
        }
        .metric-card .text-3xl {
            font-size: 1rem !important;
        }
        .metric-card .w-12.h-12 {
            width: 24px !important;
            height: 24px !important;
        }
        .metric-card .w-12.h-12 i {
            font-size: 0.75rem !important;
        }
        .metric-card .text-sm {
            font-size: 8px !important;
        }
        .user-item .text-sm.font-bold {
            font-size: 9px !important;
        }
        .user-item .text-xs {
            font-size: 7px !important;
        }
        .property-item .text-sm.font-bold {
            font-size: 9px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="w-full px-3 sm:px-4 md:px-8 space-y-4 sm:space-y-6">

    {{-- ============================================ --}}
    {{-- 1. TARJETAS DE MÉTRICAS (KPIs) --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-6">

        {{-- Total Propiedades --}}
        <div class="metric-card bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center icon-wrapper">
                    <i class="ph ph-buildings text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-green-500 bg-green-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">+12% este mes</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($totalProperties ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Total Propiedades</p>
        </div>

        {{-- Total Usuarios --}}
        <div class="metric-card bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center icon-wrapper">
                    <i class="ph ph-users-three text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-slate-500 bg-slate-100 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">Registrados</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($totalUsers ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Usuarios Activos</p>
        </div>

        {{-- Citas Hoy --}}
        <div class="metric-card bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center icon-wrapper">
                    <i class="ph ph-calendar-check text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-mso-gold bg-yellow-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">Hoy</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($todayAppointments ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Citas Programadas</p>
        </div>

        {{-- Leads Activos --}}
        <div class="metric-card bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center icon-wrapper">
                    <i class="ph ph-chart-line-up text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-emerald-600 bg-emerald-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">Oportunidad</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($activeLeads ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Leads Activos</p>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- 2. CONTENIDO INFERIOR --}}
    {{-- ============================================ --}}
    <div class="space-y-4 sm:space-y-6">

        {{-- ============================================ --}}
        {{-- USUARIOS RECIENTES --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="text-sm sm:text-base md:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-users text-mso-gold text-base sm:text-lg md:text-xl"></i>
                    Usuarios Recientes
                </h3>
                <a href="{{ route('admin.users.index') }}" class="text-[10px] sm:text-xs md:text-sm text-mso-blue hover:underline font-medium flex items-center gap-1">
                    Ver todos
                    <i class="ph ph-arrow-right text-[10px] sm:text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                @forelse($recentUsers ?? [] as $recentUser)
                    <div class="user-item flex items-center justify-between p-2 sm:p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                        <div class="flex items-center gap-2 sm:gap-3 overflow-hidden min-w-0">
                            <img src="{{ $recentUser->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                 class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover flex-shrink-0 border border-slate-200"
                                 alt="{{ $recentUser->full_name }}">
                            <div class="min-w-0">
                                <p class="text-[10px] sm:text-xs md:text-sm font-bold text-slate-800 truncate">{{ $recentUser->full_name }}</p>
                                <p class="text-[8px] sm:text-[10px] md:text-xs text-slate-500 truncate">{{ $recentUser->email }}</p>
                                @if($recentUser->main_role)
                                    <span class="text-[7px] sm:text-[8px] md:text-[10px] font-medium px-1 sm:px-2 py-0.5 rounded bg-slate-100 text-slate-600 inline-block mt-0.5">
                                        {{ $recentUser->main_role }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="text-[7px] sm:text-[8px] md:text-xs font-medium px-1 sm:px-2 py-0.5 sm:py-1 rounded bg-slate-100 text-slate-600 flex-shrink-0 whitespace-nowrap ml-1 sm:ml-2">
                            {{ $recentUser->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs sm:text-sm text-slate-500 col-span-1 sm:col-span-2 lg:col-span-3 text-center py-4">No hay usuarios recientes.</p>
                @endforelse
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PROPIEDADES RECIENTES --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="text-sm sm:text-base md:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-buildings text-mso-gold text-base sm:text-lg md:text-xl"></i>
                    Propiedades Agregadas Recientemente
                </h3>
                <a href="{{ route('admin.properties.index') }}" class="text-[10px] sm:text-xs md:text-sm text-mso-blue hover:underline font-medium flex items-center gap-1">
                    Ver todas
                    <i class="ph ph-arrow-right text-[10px] sm:text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                @forelse($recentProperties ?? [] as $property)
                    <div class="property-item flex gap-2 sm:gap-3 p-2 rounded-lg border border-slate-100 hover:border-mso-gold transition-colors cursor-pointer">
                        <img src="{{ $property->primary_image_url ?? asset('images/placeholder.jpg') }}"
                             class="w-14 h-12 sm:w-20 sm:h-16 rounded-lg object-cover flex-shrink-0"
                             alt="{{ $property->title }}">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[10px] sm:text-xs md:text-sm font-bold text-slate-800 truncate">{{ Str::limit($property->title, 30) }}</h4>
                            <p class="text-[10px] sm:text-xs text-mso-gold font-bold">{{ $property->formatted_price ?? 'Consultar' }}</p>
                            <p class="text-[8px] sm:text-[9px] md:text-[10px] text-slate-400 mt-0.5 truncate">{{ $property->city ?? 'Sin ubicación' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs sm:text-sm text-slate-500 col-span-1 sm:col-span-2 lg:col-span-3 text-center py-4">No hay propiedades recientes.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
