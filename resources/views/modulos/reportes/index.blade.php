@extends('layouts.dashboard')

@section('title', 'Reportes Gerenciales')
@section('header', 'Panel de Reportes y Estadísticas')

@push('styles')
<style>
    /* ============================================ */
    /* BASE - ESTILOS RESPONSIVOS */
    /* ============================================ */
    .stat-card {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.08);
        border-color: rgba(197, 160, 89, 0.3);
    }
    .chart-card {
        transition: all 0.3s ease;
    }
    .chart-card:hover {
        box-shadow: 0 12px 32px -8px rgba(0,0,0,0.06);
    }
    .chart-container {
        position: relative;
        height: 260px;
        width: 100%;
    }
    .chart-container canvas {
        width: 100% !important;
        height: 100% !important;
    }
    .period-btn.active {
        background: white;
        color: #2563eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
    .tabular-nums {
        font-variant-numeric: tabular-nums;
    }
    .export-btn {
        transition: all 0.3s ease;
    }
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -8px rgba(37, 99, 235, 0.3);
    }
    .export-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
    }
    .card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .card-icon-sm {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }

    /* ============================================ */
    /* ANIMACIONES */
    /* ============================================ */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stat-card, .chart-card {
        animation: fadeSlideUp 0.5s ease-out both;
    }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.1s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.2s; }
    .chart-card:nth-child(1) { animation-delay: 0.25s; }
    .chart-card:nth-child(2) { animation-delay: 0.3s; }
    .chart-card:nth-child(3) { animation-delay: 0.35s; }

    /* ============================================ */
    /* RESPONSIVE - TABLET */
    /* ============================================ */
    @media (max-width: 1024px) {
        .chart-container {
            height: 220px;
        }
        .stat-card {
            padding: 16px !important;
        }
        .stat-card .text-3xl {
            font-size: 1.75rem !important;
        }
    }

    /* ============================================ */
    /* RESPONSIVE - MÓVIL */
    /* ============================================ */
    @media (max-width: 640px) {
        /* Ajuste de KPIs - 2 columnas */
        .grid-cols-1.sm\:grid-cols-2 {
            grid-template-columns: 1fr 1fr !important;
            gap: 10px !important;
        }

        .stat-card {
            padding: 12px !important;
            border-radius: 12px !important;
        }
        .stat-card .text-3xl {
            font-size: 1.25rem !important;
        }
        .stat-card .w-12.h-12 {
            width: 36px !important;
            height: 36px !important;
            border-radius: 10px !important;
        }
        .stat-card .w-12.h-12 i {
            font-size: 16px !important;
        }
        .stat-card .text-xs {
            font-size: 9px !important;
        }
        .stat-card .text-[11px] {
            font-size: 9px !important;
        }
        .stat-card .mt-4 {
            margin-top: 8px !important;
        }
        .stat-card .pt-3 {
            padding-top: 6px !important;
        }

        /* Gráficos - 1 columna */
        .grid-cols-1.lg\:grid-cols-3 {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
        .chart-card {
            padding: 16px !important;
        }
        .chart-container {
            height: 200px !important;
        }

        /* Top Asesores y Actividad - 1 columna */
        .grid-cols-1.lg\:grid-cols-5 {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
        .lg\:col-span-2, .lg\:col-span-3 {
            grid-column: span 1 !important;
        }

        /* Cabecera */
        .flex-col.lg\:flex-row {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }
        .flex-wrap.items-center.gap-2 {
            justify-content: center !important;
        }
        .export-btn {
            padding: 6px 12px !important;
            font-size: 10px !important;
        }
        .export-btn i {
            font-size: 14px !important;
        }
        .w-10.h-10 {
            width: 32px !important;
            height: 32px !important;
        }
        .w-10.h-10 i {
            font-size: 14px !important;
        }
        .text-sm {
            font-size: 12px !important;
        }
        .text-xs {
            font-size: 10px !important;
        }

        /* Top Asesores */
        .asesor-item {
            padding: 8px 10px !important;
        }
        .asesor-item .w-9.h-9 {
            width: 28px !important;
            height: 28px !important;
            font-size: 10px !important;
        }
        .asesor-item .text-sm {
            font-size: 11px !important;
        }
        .asesor-item .text-sm.font-extrabold {
            font-size: 11px !important;
        }
        .asesor-item .h-2 {
            height: 4px !important;
        }

        /* Actividad Reciente */
        .px-5 {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        .py-4 {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }
        .flex.items-center.gap-3\.5.px-5.py-3\.5 {
            padding: 8px 12px !important;
            gap: 8px !important;
        }
        .w-9.h-9 {
            width: 28px !important;
            height: 28px !important;
            font-size: 8px !important;
        }
        .text-sm.font-semibold {
            font-size: 11px !important;
        }
        .text-xs {
            font-size: 9px !important;
        }
        .hidden.sm\:block {
            display: none !important;
        }

        /* Badges de acción */
        .px-2.py-0\.5 {
            padding: 1px 6px !important;
            font-size: 8px !important;
        }
        .gap-1 {
            gap: 2px !important;
        }
        .gap-2 {
            gap: 4px !important;
        }
        .gap-3 {
            gap: 6px !important;
        }
        .gap-3\.5 {
            gap: 6px !important;
        }

        /* Botones de filtro de período (ocultos en móvil) */
        .period-filter-desktop {
            display: none !important;
        }

        /* Breadcrumb o título */
        .mb-2 {
            margin-bottom: 4px !important;
        }
        .mb-1 {
            margin-bottom: 2px !important;
        }
        .mt-2 {
            margin-top: 4px !important;
        }
        .mt-3 {
            margin-top: 6px !important;
        }
        .mt-4 {
            margin-top: 8px !important;
        }

        /* Badges de estado */
        .inline-flex.items-center.gap-0\.5.text-\[11px\] {
            font-size: 8px !important;
            padding: 1px 4px !important;
        }
        .inline-flex.items-center.gap-0\.5.text-\[11px\] i {
            font-size: 8px !important;
        }
    }

    /* ============================================ */
    /* RESPONSIVE - MÓVIL MUY PEQUEÑO */
    /* ============================================ */
    @media (max-width: 400px) {
        .grid-cols-1.sm\:grid-cols-2 {
            grid-template-columns: 1fr 1fr !important;
            gap: 6px !important;
        }
        .stat-card {
            padding: 8px !important;
        }
        .stat-card .text-3xl {
            font-size: 1rem !important;
        }
        .stat-card .w-12.h-12 {
            width: 28px !important;
            height: 28px !important;
        }
        .stat-card .w-12.h-12 i {
            font-size: 12px !important;
        }
        .stat-card .text-xs {
            font-size: 7px !important;
        }
        .stat-card .text-[11px] {
            font-size: 7px !important;
        }
        .stat-card .mt-4 {
            margin-top: 4px !important;
        }
        .stat-card .pt-3 {
            padding-top: 4px !important;
        }
        .export-btn {
            padding: 4px 8px !important;
            font-size: 8px !important;
        }
        .export-btn i {
            font-size: 10px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4 md:space-y-6">

    {{-- ============================================ --}}
    {{-- BARRA DE FILTROS Y EXPORTACIÓN --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 md:gap-4">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-gradient-to-br from-mso-gold to-amber-600 flex items-center justify-center shadow-sm shadow-amber-200 flex-shrink-0">
                    <i class="ph ph-chart-pie-slice text-white text-sm md:text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xs md:text-sm font-bold text-slate-800">Resumen Ejecutivo</h2>
                    <p class="text-[10px] md:text-xs text-slate-400 hidden sm:block">Métricas clave y rendimiento de la plataforma</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 md:gap-2">
                <button id="exportPdfBtn" class="inline-flex items-center gap-1 md:gap-2 px-2.5 md:px-4 py-1.5 md:py-2 text-[10px] md:text-xs font-semibold text-white bg-mso-blue rounded-lg md:rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200 export-btn">
                    <i class="ph ph-file-pdf text-sm md:text-base"></i>
                    <span class="hidden xs:inline">Exportar PDF</span>
                    <span class="xs:hidden">PDF</span>
                </button>
                <button id="exportCsvBtn" class="inline-flex items-center gap-1 md:gap-2 px-2.5 md:px-4 py-1.5 md:py-2 text-[10px] md:text-xs font-semibold text-white bg-emerald-600 rounded-lg md:rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200 export-btn">
                    <i class="ph ph-file-csv text-sm md:text-base"></i>
                    <span class="hidden xs:inline">Exportar CSV</span>
                    <span class="xs:hidden">CSV</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TARJETAS KPI - RESPONSIVAS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4">

        {{-- KPI 1: Propiedades --}}
        <div class="stat-card bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-5 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[9px] md:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Propiedades</p>
                    <p class="text-lg md:text-3xl font-extrabold text-slate-800 mt-0.5 md:mt-2 tabular-nums truncate">{{ number_format($totalProperties ?? 0) }}</p>
                    <div class="flex items-center gap-1 mt-1 md:mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[8px] md:text-[11px] font-bold text-emerald-600 bg-emerald-50 px-1 md:px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up text-[7px] md:text-[10px]"></i> 12%
                        </span>
                        <span class="text-[7px] md:text-[11px] text-slate-400 truncate">vs mes ant.</span>
                    </div>
                </div>
                <div class="w-8 h-8 md:w-12 md:h-12 rounded-lg md:rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-200/50 flex-shrink-0">
                    <i class="ph ph-buildings text-white text-sm md:text-xl"></i>
                </div>
            </div>
            <div class="mt-2 md:mt-4 pt-1.5 md:pt-3 border-t border-slate-100 flex justify-between text-[8px] md:text-[11px]">
                <span class="text-slate-400">Activas</span>
                <span class="font-semibold text-slate-600">{{ number_format($activeProperties ?? $totalProperties ?? 0) }}</span>
            </div>
        </div>

        {{-- KPI 2: Leads --}}
        <div class="stat-card bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-5 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[9px] md:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Leads</p>
                    <p class="text-lg md:text-3xl font-extrabold text-slate-800 mt-0.5 md:mt-2 tabular-nums truncate">{{ number_format($totalLeads ?? 0) }}</p>
                    <div class="flex items-center gap-1 mt-1 md:mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[8px] md:text-[11px] font-bold text-emerald-600 bg-emerald-50 px-1 md:px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up text-[7px] md:text-[10px]"></i> 8%
                        </span>
                        <span class="text-[7px] md:text-[11px] text-slate-400 truncate">vs mes ant.</span>
                    </div>
                </div>
                <div class="w-8 h-8 md:w-12 md:h-12 rounded-lg md:rounded-2xl bg-gradient-to-br from-mso-gold to-amber-600 flex items-center justify-center shadow-lg shadow-amber-200/50 flex-shrink-0">
                    <i class="ph ph-user-plus text-white text-sm md:text-xl"></i>
                </div>
            </div>
            <div class="mt-2 md:mt-4 pt-1.5 md:pt-3 border-t border-slate-100 flex justify-between text-[8px] md:text-[11px]">
                <span class="text-slate-400">Nuevos esta semana</span>
                <span class="font-semibold text-slate-600">{{ number_format($newLeadsThisWeek ?? ceil(($totalLeads ?? 0) * 0.15)) }}</span>
            </div>
        </div>

        {{-- KPI 3: Citas --}}
        <div class="stat-card bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-5 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[9px] md:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Citas</p>
                    <p class="text-lg md:text-3xl font-extrabold text-slate-800 mt-0.5 md:mt-2 tabular-nums truncate">{{ number_format($totalAppointments ?? 0) }}</p>
                    <div class="flex items-center gap-1 mt-1 md:mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[8px] md:text-[11px] font-bold text-emerald-600 bg-emerald-50 px-1 md:px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up text-[7px] md:text-[10px]"></i> 5%
                        </span>
                        <span class="text-[7px] md:text-[11px] text-slate-400 truncate">vs mes ant.</span>
                    </div>
                </div>
                <div class="w-8 h-8 md:w-12 md:h-12 rounded-lg md:rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-200/50 flex-shrink-0">
                    <i class="ph ph-calendar-check text-white text-sm md:text-xl"></i>
                </div>
            </div>
            <div class="mt-2 md:mt-4 pt-1.5 md:pt-3 border-t border-slate-100 flex justify-between text-[8px] md:text-[11px]">
                <span class="text-slate-400">Completadas</span>
                <span class="font-semibold text-slate-600">{{ number_format($completedAppointments ?? ceil(($totalAppointments ?? 0) * 0.6)) }}</span>
            </div>
        </div>

        {{-- KPI 4: Tasa de Conversión --}}
        <div class="stat-card bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-5 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[9px] md:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">Conversión</p>
                    <p class="text-lg md:text-3xl font-extrabold text-slate-800 mt-0.5 md:mt-2 tabular-nums truncate">
                        @php
                            $conversionRate = ($totalLeads ?? 0) > 0
                                ? round((($closedDeals ?? ceil(($totalLeads ?? 0) * 0.2)) / ($totalLeads ?? 1)) * 100, 1)
                                : 0;
                        @endphp
                        {{ $conversionRate }}%
                    </p>
                    <div class="flex items-center gap-1 mt-1 md:mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[8px] md:text-[11px] font-bold text-emerald-600 bg-emerald-50 px-1 md:px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up text-[7px] md:text-[10px]"></i> 2.1%
                        </span>
                        <span class="text-[7px] md:text-[11px] text-slate-400 truncate">vs mes ant.</span>
                    </div>
                </div>
                <div class="w-8 h-8 md:w-12 md:h-12 rounded-lg md:rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-200/50 flex-shrink-0">
                    <i class="ph ph-target text-white text-sm md:text-xl"></i>
                </div>
            </div>
            <div class="mt-2 md:mt-4 pt-1.5 md:pt-3 border-t border-slate-100 flex justify-between text-[8px] md:text-[11px]">
                <span class="text-slate-400">Objetivo mensual</span>
                <span class="font-semibold {{ $conversionRate >= 25 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $conversionRate >= 25 ? '✓ Alcanzado' : '→ 25%' }}</span>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- GRÁFICOS --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-5">
        <div class="chart-card bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-1.5 md:gap-2.5">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="ph ph-pie-chart text-blue-500 text-xs md:text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-[10px] md:text-sm font-bold text-slate-800">Propiedades por Categoría</h3>
                        <p class="text-[8px] md:text-[11px] text-slate-400 hidden xs:block">Distribución del inventario</p>
                    </div>
                </div>
                <span class="text-[8px] md:text-[11px] bg-slate-100 px-1.5 md:px-2.5 py-0.5 md:py-1 rounded-lg text-slate-500 font-bold tabular-nums">
                    {{ number_format($totalProperties ?? 0) }}
                </span>
            </div>
            <div class="chart-container mt-1 md:mt-3" style="height: 180px; height: 220px; height: 260px; min-height: 180px;">
                <canvas id="propertiesChart"></canvas>
            </div>
        </div>

        <div class="chart-card bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-1.5 md:gap-2.5">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                        <i class="ph ph-calendar text-violet-500 text-xs md:text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-[10px] md:text-sm font-bold text-slate-800">Estado de Citas</h3>
                        <p class="text-[8px] md:text-[11px] text-slate-400 hidden xs:block">Seguimiento de citas</p>
                    </div>
                </div>
                <span class="text-[8px] md:text-[11px] bg-slate-100 px-1.5 md:px-2.5 py-0.5 md:py-1 rounded-lg text-slate-500 font-bold tabular-nums">
                    {{ number_format($totalAppointments ?? 0) }}
                </span>
            </div>
            <div class="chart-container mt-1 md:mt-3" style="height: 180px; height: 220px; height: 260px; min-height: 180px;">
                <canvas id="appointmentsChart"></canvas>
            </div>
        </div>

        <div class="chart-card bg-white rounded-xl md:rounded-2xl border border-slate-200/80 p-3 md:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-1.5 md:gap-2.5">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="ph ph-funnel text-amber-500 text-xs md:text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-[10px] md:text-sm font-bold text-slate-800">Pipeline de Leads</h3>
                        <p class="text-[8px] md:text-[11px] text-slate-400 hidden xs:block">Embudo de conversión</p>
                    </div>
                </div>
                <span class="text-[8px] md:text-[11px] bg-slate-100 px-1.5 md:px-2.5 py-0.5 md:py-1 rounded-lg text-slate-500 font-bold tabular-nums">
                    {{ number_format($totalLeads ?? 0) }}
                </span>
            </div>
            <div class="chart-container mt-1 md:mt-3" style="height: 180px; height: 220px; height: 260px; min-height: 180px;">
                <canvas id="leadsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TOP ASESORES Y ACTIVIDAD RECIENTE --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-3 md:gap-5">

        <div class="lg:col-span-2 bg-white rounded-xl md:rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-3 md:px-5 py-3 md:py-4 border-b border-slate-100">
                <div class="flex items-center gap-2 md:gap-2.5">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-gradient-to-br from-mso-gold to-amber-600 flex items-center justify-center shadow-sm shadow-amber-200/50">
                        <i class="ph ph-trophy text-white text-xs md:text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-[10px] md:text-sm font-bold text-slate-800">Top Asesores</h3>
                        <p class="text-[8px] md:text-[11px] text-slate-400 hidden xs:block">Mayor captación de leads</p>
                    </div>
                </div>
            </div>
            <div class="p-2 md:p-4 space-y-1.5 md:space-y-2.5 max-h-[320px] md:max-h-[380px] overflow-y-auto custom-scrollbar">
                @forelse($topAsesores ?? [] as $index => $asesor)
                @php
                    $maxLeads = $topAsesores->max('leads') ?? 1;
                    $percentage = $maxLeads > 0 ? ($asesor['leads'] / $maxLeads) * 100 : 0;
                    $medalColors = ['from-amber-400 to-yellow-500', 'from-slate-300 to-slate-400', 'from-orange-300 to-amber-500'];
                    $medalClass = $index < 3 ? $medalColors[$index] : 'from-slate-100 to-slate-200';
                @endphp
                <div class="flex items-center gap-2 md:gap-3 p-2 md:p-3 rounded-lg md:rounded-xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-200/60">
                    <div class="flex-shrink-0 w-7 h-7 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-gradient-to-br {{ $medalClass }} flex items-center justify-center text-[8px] md:text-xs font-extrabold text-white shadow-sm">
                        {{ $index < 3 ? ['🥇','🥈','🥉'][$index] : $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-1 md:mb-1.5">
                            <p class="text-[10px] md:text-sm font-semibold text-slate-800 truncate">{{ $asesor['name_display'] ?? $asesor['name'] ?? 'Sin nombre' }}</p>
                            <span class="flex-shrink-0 text-[10px] md:text-sm font-extrabold text-mso-blue tabular-nums">{{ $asesor['leads'] ?? 0 }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 md:h-2 overflow-hidden">
                            <div class="h-1.5 md:h-2 rounded-full transition-all duration-700 ease-out bg-gradient-to-r from-mso-gold to-amber-500" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 md:py-12 text-slate-400">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 md:mb-3">
                        <i class="ph ph-users text-xl md:text-2xl text-slate-300"></i>
                    </div>
                    <p class="text-xs md:text-sm font-medium">Sin datos de asesores</p>
                    <p class="text-[10px] md:text-xs text-slate-300 mt-1">Los datos aparecerán cuando se registren leads</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-3 bg-white rounded-xl md:rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-3 md:px-5 py-3 md:py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-1.5 md:gap-2.5">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="ph ph-clock-countdown text-emerald-500 text-xs md:text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-[10px] md:text-sm font-bold text-slate-800">Actividad Reciente</h3>
                        <p class="text-[8px] md:text-[11px] text-slate-400 hidden xs:block">Últimas acciones en la plataforma</p>
                    </div>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center gap-1 text-[9px] md:text-xs font-semibold text-mso-blue hover:text-blue-700 transition-colors">
                    <span class="hidden xs:inline">Ver historial</span>
                    <span class="xs:hidden">Ver</span>
                    <i class="ph ph-arrow-right text-[8px] md:text-[10px]"></i>
                </a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentLogs ?? [] as $log)
                @php
                    $localDate = $log->created_at ? $log->created_at->setTimezone('America/Caracas') : null;
                    $actionStr = $log->action ?? $log->event ?? 'desconocido';
                    $isCreate = str_contains($actionStr, 'create');
                    $isUpdate = str_contains($actionStr, 'update');
                    $isDelete = str_contains($actionStr, 'delete');
                    $isLogin = str_contains($actionStr, 'login');

                    $actionConfig = match(true) {
                        $isCreate  => ['bg-emerald-50 text-emerald-600', 'ph-plus-circle'],
                        $isUpdate  => ['bg-blue-50 text-blue-600', 'ph-pencil-simple'],
                        $isDelete  => ['bg-red-50 text-red-600', 'ph-trash'],
                        $isLogin   => ['bg-violet-50 text-violet-600', 'ph-sign-in'],
                        default    => ['bg-slate-50 text-slate-500', 'ph-dots-three']
                    };

                    $initial = $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY';
                    $avatarGradients = ['from-blue-500 to-indigo-600', 'from-emerald-500 to-teal-600', 'from-violet-500 to-purple-600', 'from-rose-500 to-pink-600', 'from-amber-500 to-orange-600', 'from-cyan-500 to-sky-600'];
                    $gradientIndex = $log->user ? ($log->user->id % count($avatarGradients)) : 0;
                @endphp
                <div class="flex items-center gap-2 md:gap-3.5 px-3 md:px-5 py-2 md:py-3.5 hover:bg-slate-50/80 transition-colors">
                    <div class="flex-shrink-0 w-7 h-7 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-gradient-to-br {{ $avatarGradients[$gradientIndex] }} flex items-center justify-center text-[8px] md:text-[10px] font-bold text-white shadow-sm">
                        {{ $initial }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1 md:gap-2 flex-wrap">
                            <span class="text-[10px] md:text-sm font-semibold text-slate-800 truncate max-w-[80px] md:max-w-none">{{ $log->user ? $log->user->name : 'Sistema' }}</span>
                            <span class="inline-flex items-center gap-0.5 md:gap-1 px-1 md:px-2 py-0.5 rounded-md text-[7px] md:text-[10px] font-bold uppercase {{ $actionConfig[0] }}">
                                <i class="ph {{ $actionConfig[1] }} text-[6px] md:text-[9px]"></i>
                                <span class="hidden xs:inline">{{ ucfirst(str_replace('_', ' ', $actionStr)) }}</span>
                                <span class="xs:hidden">{{ substr(ucfirst(str_replace('_', ' ', $actionStr)), 0, 8) }}</span>
                            </span>
                        </div>
                        <p class="text-[8px] md:text-xs text-slate-400 truncate mt-0.5">{{ $log->description ?? 'Sin descripción' }}</p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <span class="text-[8px] md:text-[11px] text-slate-400 font-mono tabular-nums">{{ $localDate ? $localDate->format('H:i') : '-' }}</span>
                        <span class="block text-[7px] md:text-[10px] text-slate-300 tabular-nums">{{ $localDate ? $localDate->format('d/m/y') : '-' }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 md:py-14 text-slate-400">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 md:mb-3">
                        <i class="ph ph-clock text-xl md:text-2xl text-slate-300"></i>
                    </div>
                    <p class="text-xs md:text-sm font-medium">Sin actividad registrada</p>
                    <p class="text-[10px] md:text-xs text-slate-300 mt-1">Las acciones aparecerán aquí automáticamente</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // EXPORTAR PDF
    // ============================================
    document.getElementById('exportPdfBtn')?.addEventListener('click', function() {
        const btn = this;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Generando...';
        btn.disabled = true;

        window.location.href = '{{ route("reports.export") }}?format=pdf';

        setTimeout(() => {
            btn.innerHTML = '<i class="ph ph-check-circle"></i> ¡Listo!';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }, 2000);
        }, 3000);
    });

    // ============================================
    // EXPORTAR CSV
    // ============================================
    document.getElementById('exportCsvBtn')?.addEventListener('click', function() {
        const btn = this;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Generando...';
        btn.disabled = true;

        window.location.href = '{{ route("reports.export") }}?format=csv';

        setTimeout(() => {
            btn.innerHTML = '<i class="ph ph-check-circle"></i> ¡Listo!';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }, 2000);
        }, 3000);
    });

    // ============================================
    // CONFIGURACIÓN GLOBAL DE CHART.JS
    // ============================================
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#94a3b8';

    const palette = {
        gold: '#c5a059',
        blue: '#2563eb',
        emerald: '#10b981',
        violet: '#8b5cf6',
        rose: '#f43f5e',
        amber: '#f59e0b',
        cyan: '#06b6d4',
        orange: '#f97316',
        slate: '#64748b',
    };

    const chartColors = [palette.blue, palette.gold, palette.violet, palette.emerald, palette.amber, palette.rose, palette.cyan, palette.orange, palette.slate];

    // ============================================
    // GRÁFICO 1: Propiedades por Categoría
    // ============================================
    const propertiesData = @json($propertiesByCategory ?? []);
    const ctx1 = document.getElementById('propertiesChart');

    if (ctx1 && propertiesData.length > 0) {
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: propertiesData.map(i => i.label),
                datasets: [{
                    data: propertiesData.map(i => i.value),
                    backgroundColor: chartColors.slice(0, propertiesData.length),
                    borderWidth: 0,
                    hoverOffset: 8,
                    spacing: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11, weight: '500' },
                            boxWidth: 8,
                            color: '#64748b'
                        }
                    }
                },
                animation: { animateRotate: true, duration: 1000, easing: 'easeOutQuart' }
            }
        });
    }

    // ============================================
    // GRÁFICO 2: Estado de Citas
    // ============================================
    const appointmentsData = @json($appointmentsByStatus ?? []);
    const ctx2 = document.getElementById('appointmentsChart');

    if (ctx2 && appointmentsData.length > 0) {
        const statusColors = {
            'Pendientes': palette.amber,
            'Confirmadas': palette.blue,
            'Completadas': palette.emerald,
            'Canceladas': palette.rose,
            'Reprogramadas': palette.violet
        };

        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: appointmentsData.map(i => i.label),
                datasets: [{
                    data: appointmentsData.map(i => i.value),
                    backgroundColor: appointmentsData.map(i => statusColors[i.label] || palette.slate),
                    borderRadius: { topLeft: 8, topRight: 8 },
                    borderSkipped: false,
                    maxBarThickness: 40,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        border: { display: false },
                        ticks: { font: { size: 10 }, padding: 8, callback: v => v % 1 === 0 ? v : '' }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { font: { size: 10, weight: '500' }, padding: 4 }
                    }
                },
                animation: { duration: 800, easing: 'easeOutQuart' }
            }
        });
    }

    // ============================================
    // GRÁFICO 3: Pipeline de Leads
    // ============================================
    const leadsData = @json($leadsByStatus ?? []);
    const ctx3 = document.getElementById('leadsChart');

    if (ctx3 && leadsData.length > 0) {
        const leadColors = {
            'Nuevos': palette.blue,
            'Contactados': 'rgba(37, 99, 235, 0.6)',
            'En Negociación': palette.gold,
            'Cerrados': palette.emerald,
            'Perdidos': palette.rose,
            'Inactivos': palette.slate
        };

        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: leadsData.map(i => i.label),
                datasets: [{
                    data: leadsData.map(i => i.value),
                    backgroundColor: leadsData.map(i => leadColors[i.label] || palette.slate),
                    borderRadius: { topRight: 8, bottomRight: 8 },
                    borderSkipped: false,
                    maxBarThickness: 22,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        border: { display: false },
                        ticks: { font: { size: 10 }, padding: 8, callback: v => v % 1 === 0 ? v : '' }
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { font: { size: 10, weight: '500' }, padding: 4 }
                    }
                },
                animation: { duration: 800, easing: 'easeOutQuart' }
            }
        });
    }
});
</script>
@endpush
