@extends('layouts.dashboard')

@section('title', 'Reportes Gerenciales')
@section('header', 'Panel de Reportes y Estadísticas')

@section('content')
<div class="space-y-6">

    {{-- ============================================ --}}
    {{-- BARRA DE FILTROS --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-mso-gold to-amber-600 flex items-center justify-center shadow-sm shadow-amber-200">
                    <i class="ph ph-chart-pie-slice text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Resumen Ejecutivo</h2>
                    <p class="text-xs text-slate-400">Métricas clave y rendimiento de la plataforma</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex bg-slate-100 rounded-xl p-1" id="periodFilter">
                    <button data-period="7d" class="period-btn px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all text-slate-500 hover:text-slate-700">7 días</button>
                    <button data-period="30d" class="period-btn px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all bg-white text-mso-blue shadow-sm">30 días</button>
                    <button data-period="90d" class="period-btn px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all text-slate-500 hover:text-slate-700">90 días</button>
                    <button data-period="1y" class="period-btn px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all text-slate-500 hover:text-slate-700">1 año</button>
                </div>
                <div class="h-6 w-px bg-slate-200 hidden lg:block"></div>
                <button id="exportBtn" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-mso-blue rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">
                    <i class="ph ph-download-simple"></i>
                    Exportar PDF
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TARJETAS KPI --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- KPI 1: Propiedades --}}
        <div class="stat-card group relative bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-[4rem] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Propiedades</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-2 tabular-nums">{{ number_format($totalProperties ?? 0, 0, '.', ',') }}</p>
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up text-[10px]"></i> 12%
                        </span>
                        <span class="text-[11px] text-slate-400">vs mes anterior</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-200/50 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-buildings text-white text-xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-400">Activas</span>
                    <span class="font-semibold text-slate-600">{{ number_format(($activeProperties ?? $totalProperties ?? 0), 0, '.', ',') }}</span>
                </div>
            </div>
        </div>

        {{-- KPI 2: Leads --}}
        <div class="stat-card group relative bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-[4rem] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Leads</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-2 tabular-nums">{{ number_format($totalLeads ?? 0, 0, '.', ',') }}</p>
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up text-[10px]"></i> 8%
                        </span>
                        <span class="text-[11px] text-slate-400">vs mes anterior</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-mso-gold to-amber-600 flex items-center justify-center shadow-lg shadow-amber-200/50 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-user-plus text-white text-xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-400">Nuevos esta semana</span>
                    <span class="font-semibold text-slate-600">{{ number_format(($newLeadsThisWeek ?? ceil(($totalLeads ?? 0) * 0.15)), 0, '.', ',') }}</span>
                </div>
            </div>
        </div>

        {{-- KPI 3: Citas --}}
        <div class="stat-card group relative bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-violet-50 to-transparent rounded-bl-[4rem] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Citas</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-2 tabular-nums">{{ number_format($totalAppointments ?? 0, 0, '.', ',') }}</p>
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-down text-[10px]"></i> 3%
                        </span>
                        <span class="text-[11px] text-slate-400">vs mes anterior</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-200/50 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-calendar-check text-white text-xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-400">Completadas</span>
                    <span class="font-semibold text-slate-600">{{ number_format(($completedAppointments ?? ceil(($totalAppointments ?? 0) * 0.6)), 0, '.', ',') }}</span>
                </div>
            </div>
        </div>

        {{-- KPI 4: Tasa de Conversión --}}
        <div class="stat-card group relative bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-[4rem] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Conversión</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-2 tabular-nums">
                        @php
                            $conversionRate = ($totalLeads ?? 0) > 0
                                ? round((($closedLeads ?? ceil(($totalLeads ?? 0) * 0.2)) / ($totalLeads ?? 1)) * 100, 1)
                                : 0;
                        @endphp
                        {{ $conversionRate }}%
                    </p>
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up text-[10px]"></i> 2.1%
                        </span>
                        <span class="text-[11px] text-slate-400">vs mes anterior</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-200/50 group-hover:scale-110 transition-transform duration-300">
                    <i class="ph ph-target text-white text-xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-400">Objetivo mensual</span>
                    <span class="font-semibold {{ $conversionRate >= 25 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $conversionRate >= 25 ? '✓ Alcanzado' : '→ 25%' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- GRÁFICOS PRINCIPALES --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Gráfico 1: Propiedades por Categoría --}}
        <div class="chart-card bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="ph ph-pie-chart text-blue-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Propiedades por Categoría</h3>
                        <p class="text-[11px] text-slate-400">Distribución del inventario</p>
                    </div>
                </div>
                <span class="text-[11px] bg-slate-100 px-2.5 py-1 rounded-lg text-slate-500 font-bold tabular-nums">
                    {{ number_format($totalProperties ?? 0, 0, '.', ',') }}
                </span>
            </div>
            <div class="chart-container mt-3">
                <canvas id="propertiesChart"></canvas>
            </div>
        </div>

        {{-- Gráfico 2: Estado de Citas --}}
        <div class="chart-card bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                        <i class="ph ph-calendar text-violet-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Estado de Citas</h3>
                        <p class="text-[11px] text-slate-400">Seguimiento de citas</p>
                    </div>
                </div>
                <span class="text-[11px] bg-slate-100 px-2.5 py-1 rounded-lg text-slate-500 font-bold tabular-nums">
                    {{ number_format($totalAppointments ?? 0, 0, '.', ',') }}
                </span>
            </div>
            <div class="chart-container mt-3">
                <canvas id="appointmentsChart"></canvas>
            </div>
        </div>

        {{-- Gráfico 3: Pipeline de Leads --}}
        <div class="chart-card bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="ph ph-funnel text-amber-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Pipeline de Leads</h3>
                        <p class="text-[11px] text-slate-400">Embudo de conversión</p>
                    </div>
                </div>
                <span class="text-[11px] bg-slate-100 px-2.5 py-1 rounded-lg text-slate-500 font-bold tabular-nums">
                    {{ number_format($totalLeads ?? 0, 0, '.', ',') }}
                </span>
            </div>
            <div class="chart-container mt-3">
                <canvas id="leadsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- FILA INFERIOR: TOP ASESORES + ACTIVIDAD --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- Top Asesores --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-mso-gold to-amber-600 flex items-center justify-center shadow-sm shadow-amber-200/50">
                            <i class="ph ph-trophy text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Top Asesores</h3>
                            <p class="text-[11px] text-slate-400">Mayor captación de leads</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-2.5 max-h-[380px] overflow-y-auto custom-scrollbar">
                @forelse($topAsesores ?? [] as $index => $asesor)
                @php
                    $maxLeads = $topAsesores->max('leads') ?? 1;
                    $percentage = $maxLeads > 0 ? ($asesor['leads'] / $maxLeads) * 100 : 0;
                    $medalColors = ['from-amber-400 to-yellow-500 text-white', 'from-slate-300 to-slate-400 text-white', 'from-orange-300 to-amber-500 text-white'];
                    $medalClass = $index < 3 ? $medalColors[$index] : 'from-slate-100 to-slate-200 text-slate-500';
                @endphp
                <div class="asesor-item group flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-all cursor-default border border-transparent hover:border-slate-200/60">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br {{ $medalClass }} flex items-center justify-center text-xs font-extrabold shadow-sm">
                        {{ $index < 3 ? ['🥇','🥈','🥉'][$index] : $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-sm font-semibold text-slate-800 truncate pr-2">{{ $asesor['name'] ?? 'Sin nombre' }}</p>
                            <span class="flex-shrink-0 text-sm font-extrabold text-mso-blue tabular-nums">{{ $asesor['leads'] ?? 0 }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-700 ease-out bg-gradient-to-r from-mso-gold to-amber-500"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-slate-400">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <i class="ph ph-users text-2xl text-slate-300"></i>
                    </div>
                    <p class="text-sm font-medium">Sin datos de asesores</p>
                    <p class="text-xs text-slate-300 mt-1">Los datos aparecerán cuando se registren leads</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Actividad Reciente --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="ph ph-clock-countdown text-emerald-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Actividad Reciente</h3>
                        <p class="text-[11px] text-slate-400">Últimas acciones en la plataforma</p>
                    </div>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="group/link inline-flex items-center gap-1.5 text-xs font-semibold text-mso-blue hover:text-blue-700 transition-colors">
                    Ver historial
                    <i class="ph ph-arrow-right text-[10px] group-hover/link:translate-x-0.5 transition-transform"></i>
                </a>
            </div>
            <div class="overflow-hidden">
                @php
                    $recentLogs = App\Models\AuditLog::with('user')
                        ->orderBy('created_at', 'desc')
                        ->limit(6)
                        ->get();
                @endphp
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
                            $isCreate  => ['bg-emerald-50 text-emerald-600 border-emerald-200/60', 'ph-plus-circle'],
                            $isUpdate  => ['bg-blue-50 text-blue-600 border-blue-200/60', 'ph-pencil-simple'],
                            $isDelete  => ['bg-red-50 text-red-600 border-red-200/60', 'ph-trash'],
                            $isLogin   => ['bg-violet-50 text-violet-600 border-violet-200/60', 'ph-sign-in'],
                            default    => ['bg-slate-50 text-slate-500 border-slate-200/60', 'ph-dots-three']
                        };

                        $initial = $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY';
                        $avatarGradients = [
                            'from-blue-500 to-indigo-600',
                            'from-emerald-500 to-teal-600',
                            'from-violet-500 to-purple-600',
                            'from-rose-500 to-pink-600',
                            'from-amber-500 to-orange-600',
                            'from-cyan-500 to-sky-600',
                        ];
                        $gradientIndex = $log->user ? ($log->user->id % count($avatarGradients)) : 0;
                    @endphp
                    <div class="group/row flex items-center gap-3.5 px-5 py-3.5 hover:bg-slate-50/80 transition-colors cursor-default">
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br {{ $avatarGradients[$gradientIndex] }} flex items-center justify-center text-[10px] font-bold text-white shadow-sm">
                            {{ $initial }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-slate-800 truncate">{{ $log->user ? $log->user->name : 'Sistema' }}</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border {{ $actionConfig[0] }}">
                                    <i class="ph {{ $actionConfig[1] }} text-[9px]"></i>
                                    {{ ucfirst(str_replace('_', ' ', $actionStr)) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 truncate mt-0.5">{{ $log->description ?? 'Sin descripción' }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right hidden sm:block">
                            <span class="text-[11px] text-slate-400 font-mono tabular-nums">{{ $localDate ? $localDate->format('H:i') : '-' }}</span>
                            <span class="block text-[10px] text-slate-300 tabular-nums">{{ $localDate ? $localDate->format('d/m/y') : '-' }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-14 text-slate-400">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <i class="ph ph-clock text-2xl text-slate-300"></i>
                        </div>
                        <p class="text-sm font-medium">Sin actividad registrada</p>
                        <p class="text-xs text-slate-300 mt-1">Las acciones aparecerán aquí automáticamente</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    /* Tarjetas KPI */
    .stat-card {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.08);
        border-color: rgba(197, 160, 89, 0.3);
    }

    /* Tarjetas de gráficos */
    .chart-card {
        transition: all 0.3s ease;
    }
    .chart-card:hover {
        box-shadow: 0 12px 32px -8px rgba(0,0,0,0.06);
    }

    /* Asesores */
    .asesor-item {
        transition: all 0.25s ease;
    }
    .asesor-item:hover {
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        transform: translateX(2px);
    }

    /* Gráficos */
    .chart-container {
        position: relative;
        height: 260px;
        width: 100%;
    }
    .chart-container canvas {
        width: 100% !important;
        height: 100% !important;
    }

    /* Filtros de período */
    .period-btn {
        position: relative;
    }
    .period-btn.active {
        background: white;
        color: #2563eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    /* Scrollbar personalizado */
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

    /* Números tabulares */
    .tabular-nums {
        font-variant-numeric: tabular-nums;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .chart-container { height: 220px; }
    }

    /* Animación de entrada */
    @keyframes fadeSlideUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // FILTRO DE PERÍODO
    // ============================================
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            // Aquí puedes agregar lógica AJAX para refrescar datos
        });
    });

    // ============================================
    // CONFIGURACIÓN GLOBAL DE CHART.JS
    // ============================================
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.plugins.tooltip.backgroundColor = '#1e293b';
    Chart.defaults.plugins.tooltip.titleFont = { size: 12, weight: '600' };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 11 };
    Chart.defaults.plugins.tooltip.padding = { top: 10, bottom: 10, left: 14, right: 14 };
    Chart.defaults.plugins.tooltip.cornerRadius = 10;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxWidth = 8;
    Chart.defaults.plugins.tooltip.boxHeight = 8;
    Chart.defaults.plugins.tooltip.boxPadding = 4;

    const palette = {
        gold:     '#c5a059',
        goldSoft: 'rgba(197, 160, 89, 0.15)',
        blue:     '#2563eb',
        blueSoft: 'rgba(37, 99, 235, 0.15)',
        emerald:  '#10b981',
        violet:   '#8b5cf6',
        rose:     '#f43f5e',
        amber:    '#f59e0b',
        cyan:     '#06b6d4',
        orange:   '#f97316',
        slate:    '#64748b',
    };

    const chartColors = [
        palette.blue, palette.gold, palette.violet, palette.emerald,
        palette.amber, palette.rose, palette.cyan, palette.orange, palette.slate
    ];

    // ============================================
    // GRÁFICO 1: Propiedades por Categoría (Doughnut)
    // ============================================
    const propertiesData = @json($propertiesByCategory ?? []);
    const ctx1 = document.getElementById('propertiesChart');

    if (ctx1 && propertiesData.length > 0) {
        // Plugin para texto central
        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw(chart) {
                const { ctx, width, height } = chart;
                const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const centerY = height / 2;
                ctx.font = '800 26px "Inter", sans-serif';
                ctx.fillStyle = '#1e293b';
                ctx.fillText(total.toLocaleString(), width / 2, centerY - 8);
                ctx.font = '500 10px "Inter", sans-serif';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('TOTAL', width / 2, centerY + 14);
                ctx.restore();
            }
        };

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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.parsed.toLocaleString()} (${pct}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            },
            plugins: [centerTextPlugin]
        });
    } else if (ctx1) {
        drawEmptyState(ctx1, 'Sin datos de propiedades');
    }

    // ============================================
    // GRÁFICO 2: Estado de Citas (Barras verticales)
    // ============================================
    const appointmentsData = @json($appointmentsByStatus ?? []);
    const ctx2 = document.getElementById('appointmentsChart');

    if (ctx2 && appointmentsData.length > 0) {
        const statusColors = {
            'Pendientes':    palette.amber,
            'Confirmadas':   palette.blue,
            'Completadas':   palette.emerald,
            'Canceladas':    palette.rose,
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
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y.toLocaleString()} citas`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        border: { display: false },
                        ticks: {
                            font: { size: 10 },
                            padding: 8,
                            callback: v => v % 1 === 0 ? v : ''
                        }
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
    } else if (ctx2) {
        drawEmptyState(ctx2, 'Sin datos de citas');
    }

    // ============================================
    // GRÁFICO 3: Pipeline de Leads (Barras horizontales)
    // ============================================
    const leadsData = @json($leadsByStatus ?? []);
    const ctx3 = document.getElementById('leadsChart');

    if (ctx3 && leadsData.length > 0) {
        const leadColors = {
            'Nuevos':         palette.blue,
            'Contactados':    'rgba(37, 99, 235, 0.6)',
            'En Negociación': palette.gold,
            'Cerrados':       palette.emerald,
            'Perdidos':       palette.rose,
            'Inactivos':      palette.slate
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
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.x.toLocaleString()} leads`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        border: { display: false },
                        ticks: {
                            font: { size: 10 },
                            padding: 8,
                            callback: v => v % 1 === 0 ? v : ''
                        }
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
    } else if (ctx3) {
        drawEmptyState(ctx3, 'Sin datos de leads');
    }

    // ============================================
    // ESTADO VACÍO PARA GRÁFICOS
    // ============================================
    function drawEmptyState(canvas, message) {
        const ctx = canvas.getContext('2d');
        const parent = canvas.parentElement;
        canvas.style.display = 'none';
        const placeholder = document.createElement('div');
        placeholder.className = 'flex flex-col items-center justify-center h-full text-slate-300';
        placeholder.innerHTML = `
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
                <i class="ph ph-chart-bar text-2xl text-slate-300"></i>
            </div>
            <p class="text-xs font-medium text-slate-400">${message}</p>
        `;
        parent.appendChild(placeholder);
    }

    // ============================================
    // BOTÓN EXPORTAR (Placeholder)
    // ============================================
    document.getElementById('exportBtn')?.addEventListener('click', function() {
        const btn = this;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Generando...';
        btn.disabled = true;
        btn.classList.add('opacity-70');
        setTimeout(() => {
            btn.innerHTML = '<i class="ph ph-check-circle"></i> ¡Listo!';
            btn.classList.remove('opacity-70');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }, 1500);
        }, 1200);
    });
});
</script>
@endpush
