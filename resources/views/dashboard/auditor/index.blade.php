@extends('layouts.dashboard')

@section('title', 'Panel Auditoría')
@section('header', 'Panel de Control - Auditoría')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Playfair+Display:wght@500;600;700&display=swap');

    :root {
        --navy-950:#0a0f1a;--navy-900:#0f1729;--navy-800:#162038;--navy-700:#1e2d4a;--navy-600:#2a3f66;
        --slate-50:#f8f9fb;--slate-100:#f0f1f5;--slate-200:#e2e4ea;--slate-300:#cdd0d9;--slate-400:#9ca1b0;
        --slate-500:#6b7185;--slate-600:#4a4f61;--slate-700:#353a4a;--slate-800:#1f2333;
        --gold-500:#c9a84c;--gold-400:#d4b96a;--gold-100:#f5ecd4;--gold-50:#faf6ec;
        --red-500:#dc2626;--red-100:#fee2e2;
        --green-600:#16a34a;--green-100:#dcfce7;
        --blue-600:#2563eb;--blue-100:#dbeafe;
        --amber-500:#d97706;--amber-100:#fef3c7;
        --purple-600:#7c3aed;--purple-100:#ede9fe;
    }
    .audit-panel *{font-family:'DM Sans',sans-serif}
    .audit-panel .font-display{font-family:'Playfair Display',serif}
    .kpi-card{transition:transform .2s,box-shadow .2s}
    .kpi-card:hover{transform:translateY(-1px);box-shadow:0 4px 24px rgba(15,23,41,.06)}
    .audit-row{transition:background-color .12s}
    .audit-row:hover{background-color:var(--slate-50)}
    .severity-critical{background:var(--red-100);color:#991b1b;border:1px solid #fecaca}
    .severity-high{background:var(--amber-100);color:#92400e;border:1px solid #fde68a}
    .severity-medium{background:var(--blue-100);color:#1e40af;border:1px solid #bfdbfe}
    .severity-low{background:var(--green-100);color:#166534;border:1px solid #bbf7d0}
    .severity-info{background:var(--purple-100);color:#5b21b6;border:1px solid #ddd6fe}
    .ts{font-variant-numeric:tabular-nums;letter-spacing:.015em}
</style>
@endpush

@php
    // Helper: str_contains con múltiples needles
    $matches = function($haystack, $needles) {
        foreach ((array) $needles as $n) {
            if (str_contains($haystack, $n)) return true;
        }
        return false;
    };

    // ── Distribución por tipo ──
    $dist = collect([
        'Creaciones'      => $recentLogs->filter(fn($l) => $matches($l->action ?? $l->event ?? '', ['create','store']))->count(),
        'Actualizaciones'  => $recentLogs->filter(fn($l) => $matches($l->action ?? $l->event ?? '', ['update','edit']))->count(),
        'Eliminaciones'    => $recentLogs->filter(fn($l) => $matches($l->action ?? $l->event ?? '', ['delete','destroy','remove']))->count(),
        'Accesos'          => $recentLogs->filter(fn($l) => $matches($l->action ?? $l->event ?? '', ['login','auth']))->count(),
    ]);
    $dist['Otros'] = max(0, $recentLogs->count() - $dist->sum());

    // ── Top usuarios ──
    $topUsers = $recentLogs->whereNotNull('user_id')
        ->groupBy('user_id')->map(fn($logs) => [
            'name'  => $logs->first()->user->name ?? 'Desconocido',
            'count' => $logs->count(),
        ])->sortByDesc('count')->take(5);

    // ── Actividad diaria (últimos 7 días) ──
    $dailyLabels = collect(range(6,0))->map(fn($i) => now()->subDays($i)->format('d/m'))->values();
    $dailyData   = collect(range(6,0))->map(fn($i) =>
        $recentLogs->filter(fn($l) => $l->created_at && $l->created_at->format('Y-m-d') === now()->subDays($i)->format('Y-m-d'))->count()
    )->values();
@endphp

@section('content')
<div class="audit-panel" style="background:var(--slate-50);min-height:calc(100vh - 64px)">

    <!-- ═══ Encabezado ═══ -->
    <div class="px-6 lg:px-8 pt-8 pb-2">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-8 h-0.5" style="background:var(--gold-500)"></div>
                    <span class="text-[11px] font-semibold uppercase tracking-[.18em]" style="color:var(--gold-500)">Centro de Auditoría</span>
                </div>
                <h1 class="font-display text-3xl font-bold" style="color:var(--navy-900)">Panel de Control</h1>
                <p class="text-sm mt-1" style="color:var(--slate-500)">Supervisión en tiempo real — actividad y eventos del sistema.</p>
            </div>
            <div class="flex items-center gap-3">
                <select class="appearance-none pl-4 pr-10 py-2.5 rounded-lg text-sm font-medium border focus:outline-none focus:ring-2 focus:ring-[color:var(--gold-500)]/30 cursor-pointer"
                        style="background:#fff;border-color:var(--slate-200);color:var(--slate-700)">
                    <option>Últimos 7 días</option>
                    <option>Últimos 30 días</option>
                    <option>Este mes</option>
                </select>
                <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-opacity hover:opacity-90" style="background:var(--navy-900)">
                    <i class="ph ph-file-text"></i> Bitácora Completa
                </a>
            </div>
        </div>
    </div>

    <!-- ═══ KPIs ═══ -->
    <div class="px-6 lg:px-8 py-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="kpi-card bg-white rounded-xl p-5" style="border:1px solid var(--slate-200)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--slate-400)">Propiedades</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:var(--gold-50);border:1px solid var(--gold-100)">
                        <i class="ph ph-buildings" style="color:var(--gold-500)"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight" style="color:var(--navy-900)">{{ number_format($totalProperties) }}</div>
            </div>
            <div class="kpi-card bg-white rounded-xl p-5" style="border:1px solid var(--slate-200)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--slate-400)">Usuarios Activos</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:var(--blue-100);border:1px solid #bfdbfe">
                        <i class="ph ph-user-focus" style="color:var(--blue-600)"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight" style="color:var(--navy-900)">{{ number_format($totalUsers) }}</div>
            </div>
            <div class="kpi-card bg-white rounded-xl p-5" style="border:1px solid var(--slate-200)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--slate-400)">Citas Agendadas</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:var(--green-100);border:1px solid #bbf7d0">
                        <i class="ph ph-calendar-check" style="color:var(--green-600)"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight" style="color:var(--navy-900)">{{ number_format($totalAppointments) }}</div>
            </div>
            <div class="kpi-card bg-white rounded-xl p-5" style="border:1px solid var(--slate-200)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--slate-400)">Registros Auditoría</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:var(--slate-100);border:1px solid var(--slate-200)">
                        <i class="ph ph-database" style="color:var(--slate-600)"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold tracking-tight" style="color:var(--navy-900)">{{ number_format($logsCount) }}</div>
            </div>
        </div>
    </div>

    <!-- ═══ Tabla full-width ═══ -->
    <div class="px-6 lg:px-8 pb-6">
        <div class="bg-white rounded-xl overflow-hidden" style="border:1px solid var(--slate-200)">
            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" style="border-bottom:1px solid var(--slate-100)">
                <div>
                    <h2 class="font-display text-lg font-bold" style="color:var(--navy-900)">Registro de Actividad</h2>
                    <p class="text-xs mt-0.5" style="color:var(--slate-400)">Últimos eventos registrados por el sistema</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:var(--slate-400)"></i>
                        <input id="tableSearch" type="text" placeholder="Buscar..." class="pl-9 pr-4 py-2 rounded-lg text-sm border focus:outline-none focus:ring-2 focus:ring-[color:var(--gold-500)]/30 w-44" style="border-color:var(--slate-200)">
                    </div>
                    <a href="{{ route('auditor.logs') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold" style="color:var(--navy-900);background:var(--slate-100)">
                        Ver todo <i class="ph ph-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="auditTable">
                    <thead>
                        <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200)">
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Usuario</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Evento</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Nivel</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Descripción</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-right" style="color:var(--slate-500)">Fecha / Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color:var(--slate-100)">
                        @forelse($recentLogs as $log)
                        @php
                            $a = $log->action ?? $log->event ?? 'unknown';
                            if($matches($a, ['delete','destroy','remove']))  { $s='severity-critical'; $sl='Crítico'; }
                            elseif($matches($a, ['login','auth']))           { $s='severity-info';      $sl='Info'; }
                            elseif($matches($a, ['create','store']))          { $s='severity-low';       $sl='Bajo'; }
                            elseif($matches($a, ['update','edit']))           { $s='severity-medium';    $sl='Medio'; }
                            else                                              { $s='severity-high';      $sl='Alto'; }
                        @endphp
                        <tr class="audit-row">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold text-white shrink-0"
                                         style="background:{{ $log->user ? 'var(--navy-700)' : 'var(--slate-400)' }}">
                                        {{ $log->user ? strtoupper(substr($log->user->name,0,1)) : 'S' }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm" style="color:var(--navy-900)">{{ $log->user ? $log->user->name : 'Sistema' }}</div>
                                        <div class="text-[11px]" style="color:var(--slate-400)">{{ $log->user ? $log->user->email : 'Automático' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 font-medium text-sm" style="color:var(--slate-700)">{{ ucfirst(str_replace('_',' ',$a)) }}</td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold {{ $s }}">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background:currentColor"></span>{{ $sl }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs max-w-xs truncate" style="color:var(--slate-400)">{{ $log->description ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <span class="ts text-xs font-medium block" style="color:var(--slate-600)">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '—' }}</span>
                                <span class="ts text-xs" style="color:var(--slate-400)">{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="ph ph-inbox text-3xl mb-2 block" style="color:var(--slate-300)"></i>
                                <p class="font-semibold text-sm" style="color:var(--slate-400)">Sin actividad registrada</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentLogs->count() > 0)
            <div class="px-6 py-3 flex items-center justify-between text-xs" style="background:var(--slate-50);border-top:1px solid var(--slate-100);color:var(--slate-400)">
                <span>Mostrando {{ $recentLogs->count() }} de {{ number_format($logsCount) }} registros</span>
                <a href="{{ route('auditor.logs') }}" class="hover:underline font-medium" style="color:var(--gold-500)">Ver todos →</a>
            </div>
            @endif
        </div>
    </div>

    <!-- ═══ Gráficas Corporativas ═══ -->
    <div class="px-6 lg:px-8 pb-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Dona -->
            <div class="bg-white rounded-xl p-6" style="border:1px solid var(--slate-200)">
                <h3 class="font-display text-base font-bold mb-0.5" style="color:var(--navy-900)">Distribución de Eventos</h3>
                <p class="text-[11px] mb-5" style="color:var(--slate-400)">Por tipo de acción registrada</p>
                <div class="relative mx-auto" style="max-width:220px">
                    <canvas id="chartDoughnut"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-bold" style="color:var(--navy-900)">{{ $recentLogs->count() }}</span>
                        <span class="text-[10px] uppercase tracking-wider font-semibold" style="color:var(--slate-400)">Total</span>
                    </div>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-y-2 gap-x-4">
                    @foreach($dist as $label => $count)
                    @php
                        // Paleta exclusiva dorado y azules marinos
                        $c = [
                            'Creaciones'     => '#c9a84c', // Dorado principal
                            'Actualizaciones' => '#0f1729', // Navy oscuro
                            'Eliminaciones'   => '#1e2d4a', // Navy medio
                            'Accesos'         => '#2a3f66', // Navy claro
                            'Otros'           => '#d4b96a', // Dorado claro
                        ][$label] ?? '#6b7185'
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:{{ $c }}"></span>
                        <span class="text-[11px]" style="color:var(--slate-600)">{{ $label }} <strong style="color:var(--navy-900)">{{ $count }}</strong></span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Barras verticales: actividad diaria -->
            <div class="bg-white rounded-xl p-6" style="border:1px solid var(--slate-200)">
                <div class="flex items-center justify-between mb-0.5">
                    <h3 class="font-display text-base font-bold" style="color:var(--navy-900)">Actividad por Día</h3>
                    <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded" style="background:var(--gold-50);color:var(--gold-500);border:1px solid var(--gold-100)">7 días</span>
                </div>
                <p class="text-[11px] mb-5" style="color:var(--slate-400)">Registros de auditoría diarios</p>
                <div style="height:230px"><canvas id="chartBars"></canvas></div>
            </div>

            <!-- Barras horizontales: top usuarios -->
            <div class="bg-white rounded-xl p-6" style="border:1px solid var(--slate-200)">
                <h3 class="font-display text-base font-bold mb-0.5" style="color:var(--navy-900)">Usuarios Más Activos</h3>
                <p class="text-[11px] mb-5" style="color:var(--slate-400)">Top 5 por cantidad de eventos</p>
                <div style="height:230px">
                    @if($topUsers->count() > 0)
                        <canvas id="chartHBars"></canvas>
                    @else
                        <div class="flex items-center justify-center h-full">
                            <p class="text-sm" style="color:var(--slate-400)">Sin datos de usuarios</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){

    // Paleta corporativa estricta
    const GOLD       = '#c9a84c';
    const GOLD_LIGHT = '#d4b96a';
    const NAVY_900   = '#0f1729';
    const NAVY_700   = '#1e2d4a';
    const NAVY_600   = '#2a3f66';
    const SL4        = '#9ca1b0';
    const GRID       = '#e2e4ea';
    const FONT       = { family:'DM Sans' };

    const tooltipStyle = {
        backgroundColor: NAVY_900,
        titleFont: { ...FONT, size:12, weight:'600' },
        bodyFont:  { ...FONT, size:11 },
        padding: 10,
        cornerRadius: 8,
        displayColors: true,
        boxPadding: 4,
    };

    // ── Dona (Dorado y Azules Marinos) ──
    new Chart(document.getElementById('chartDoughnut'), {
        type: 'doughnut',
        data: {
            labels: @json($dist->keys()),
            datasets: [{
                data: @json($dist->values()),
                backgroundColor: [GOLD, NAVY_900, NAVY_700, NAVY_600, GOLD_LIGHT],
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '74%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: {
                        label(ctx) {
                            const t = ctx.dataset.data.reduce((a,b)=>a+b,0);
                            const p = t ? ((ctx.raw/t)*100).toFixed(1) : 0;
                            return ' ' + ctx.label + ': ' + ctx.raw + ' (' + p + '%)';
                        }
                    }
                }
            }
        }
    });

    // ── Barras verticales (Dorado) ──
    new Chart(document.getElementById('chartBars'), {
        type: 'bar',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                data: @json($dailyData),
                backgroundColor: GOLD,
                hoverBackgroundColor: GOLD_LIGHT,
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 38,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: { label(ctx) { return ' ' + ctx.raw + ' eventos'; } }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { ...FONT, size: 11, color: SL4 },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: GRID },
                    ticks: { ...FONT, size: 11, color: SL4, stepSize: 1, precision: 0 },
                    border: { display: false },
                }
            }
        }
    });

    // ── Barras horizontales (Azul Marino) ──
    @if($topUsers->count() > 0)
    new Chart(document.getElementById('chartHBars'), {
        type: 'bar',
        data: {
            labels: @json($topUsers->pluck('name')->values()),
            datasets: [{
                data: @json($topUsers->pluck('count')->values()),
                backgroundColor: NAVY_900,
                hoverBackgroundColor: NAVY_600,
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 22,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: { label(ctx) { return ' ' + ctx.raw + ' eventos'; } }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: GRID },
                    ticks: { ...FONT, size: 11, color: SL4, stepSize: 1, precision: 0 },
                    border: { display: false },
                },
                y: {
                    grid: { display: false },
                    ticks: { ...FONT, size: 11, weight: '500', color: NAVY_700 },
                    border: { display: false },
                }
            }
        }
    });
    @endif

    // ── Búsqueda en tabla ──
    var input = document.getElementById('tableSearch');
    if (input) {
        input.addEventListener('input', function(){
            var q = this.value.toLowerCase();
            document.querySelectorAll('#auditTable tbody tr').forEach(function(r){
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
