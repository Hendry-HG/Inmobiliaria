{{-- Listado general de logs de auditoría --}}
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
        --gold-500:#c9a84c;--gold-400:#d4b96a;--gold-300:#e0cb8a;--gold-100:#f5ecd4;--gold-50:#faf6ec;
    }
    .audit-panel *{font-family:'DM Sans',sans-serif}
    .audit-panel .font-display{font-family:'Playfair Display',serif}
    .kpi-card{transition:transform .2s,box-shadow .2s}
    .kpi-card:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(15,23,41,.07)}
    .audit-row{transition:background-color .12s}
    .audit-row:hover{background-color:var(--slate-50)}
    .ts{font-variant-numeric:tabular-nums;letter-spacing:.015em}

    /* Badges de entidad */
    .ent-prop{background:var(--gold-50);color:var(--gold-500);border:1px solid var(--gold-100)}
    .ent-cita{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}
    .ent-user{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe}
    .ent-lead{background:#fffbeb;color:#d97706;border:1px solid #fde68a}
    .ent-sys{background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe}
    .ent-rol{background:#fdf2f8;color:#db2777;border:1px solid #fbcfe8}

    /* Badges de severidad por acción */
    .sev-destruct{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
    .sev-create{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
    .sev-update{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe}
    .sev-cancel{background:#fef3c7;color:#b45309;border:1px solid #fde68a}
    .sev-auth{background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe}
    .sev-complete{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}
    .sev-other{background:var(--slate-100);color:var(--slate-600);border:1px solid var(--slate-200)}
</style>
@endpush

@php
    // ═══════════════════════════════════════════
    // SISTEMA DE INTERPRETACIÓN DE EVENTOS
    // ═══════════════════════════════════════════

    // Extraer nombre del modelo desde subject_type
    $getEntity = function($log) {
        if (!$log->subject_type) return 'Sistema';
        $parts = explode('\\', $log->subject_type);
        return end($parts);
    };

    // Mapeo de entidades -> datos visuales
    $entityConfig = [
        'Property'    => ['label' => 'Propiedad',  'icon' => 'ph-buildings',      'class' => 'ent-prop'],
        'Appointment' => ['label' => 'Cita',       'icon' => 'ph-calendar-check', 'class' => 'ent-cita'],
        'User'        => ['label' => 'Usuario',    'icon' => 'ph-user-plus',      'class' => 'ent-user'],
        'Lead'        => ['label' => 'Lead',       'icon' => 'ph-target',         'class' => 'ent-lead'],
        'Role'        => ['label' => 'Rol',        'icon' => 'ph-shield-check',   'class' => 'ent-rol'],
        'Sistema'     => ['label' => 'Sistema',    'icon' => 'ph-gear',           'class' => 'ent-sys'],
    ];

    // Mapeo de acciones -> etiqueta en español + clase de severidad
    $actionConfig = [
        'created'     => ['label' => 'Creó',            'sev' => 'sev-create'],
        'updated'     => ['label' => 'Actualizó',       'sev' => 'sev-update'],
        'deleted'     => ['label' => 'Eliminó',         'sev' => 'sev-destruct'],
        'login'       => ['label' => 'Inició sesión',   'sev' => 'sev-auth'],
        'logout'      => ['label' => 'Cerró sesión',    'sev' => 'sev-auth'],
        'confirmed'   => ['label' => 'Confirmó',        'sev' => 'sev-complete'],
        'cancelled'   => ['label' => 'Canceló',         'sev' => 'sev-cancel'],
        'rescheduled' => ['label' => 'Reprogramó',      'sev' => 'sev-cancel'],
        'completed'   => ['label' => 'Completó',        'sev' => 'sev-complete'],
        'published'   => ['label' => 'Publicó',         'sev' => 'sev-complete'],
        'unpublished' => ['label' => 'Despublicó',      'sev' => 'sev-update'],
        'sold'        => ['label' => 'Vendida',         'sev' => 'sev-complete'],
        'restored'    => ['label' => 'Restauró',        'sev' => 'sev-update'],
        'draft'       => ['label' => 'Guardó borrador', 'sev' => 'sev-update'],
        'attached'    => ['label' => 'Asignó',          'sev' => 'sev-update'],
        'detached'    => ['label' => 'Desasignó',       'sev' => 'sev-update'],
    ];

    // Helper para resolver un evento completo
    $resolveEvent = function($log) use ($getEntity, $entityConfig, $actionConfig) {
        $rawAction = strtolower($log->action ?? $log->event ?? '');
        $entityKey = $getEntity($log);

        // Resolver acción
        $actionData = $actionConfig[$rawAction] ?? null;
        if (!$actionData) {
            // Fallback: intentar deducir del texto crudo
            if (str_contains($rawAction, 'delete') || str_contains($rawAction, 'destroy') || str_contains($rawAction, 'remove')) {
                $actionData = ['label' => 'Eliminó', 'sev' => 'sev-destruct'];
            } elseif (str_contains($rawAction, 'create') || str_contains($rawAction, 'store')) {
                $actionData = ['label' => 'Creó', 'sev' => 'sev-create'];
            } elseif (str_contains($rawAction, 'update') || str_contains($rawAction, 'edit')) {
                $actionData = ['label' => 'Actualizó', 'sev' => 'sev-update'];
            } elseif (str_contains($rawAction, 'login') || str_contains($rawAction, 'auth')) {
                $actionData = ['label' => 'Inició sesión', 'sev' => 'sev-auth'];
            } else {
                $actionData = ['label' => ucfirst(str_replace('_', ' ', $rawAction ?: 'Desconocido')), 'sev' => 'sev-other'];
            }
        }

        // Resolver entidad
        $entData = $entityConfig[$entityKey] ?? $entityConfig['Sistema'];

        return [
            'action_label' => $actionData['label'],
            'severity'     => $actionData['sev'],
            'entity_label' => $entData['label'],
            'entity_icon'  => $entData['icon'],
            'entity_class' => $entData['class'],
        ];
    };

    // ═══════════════════════════════════════════
    // CÁLCULOS PARA GRÁFICAS
    // ═══════════════════════════════════════════
    $logsCollection = collect($recentLogs ?? []);

    // Distribución por ENTIDAD (no por acción genérica)
    $entityDist = collect([
        'Propiedades' => $logsCollection->filter(fn($l) => str_contains($l->subject_type ?? '', 'Property'))->count(),
        'Citas'       => $logsCollection->filter(fn($l) => str_contains($l->subject_type ?? '', 'Appointment'))->count(),
        'Usuarios'     => $logsCollection->filter(fn($l) => str_contains($l->subject_type ?? '', 'User'))->count(),
        'Leads'       => $logsCollection->filter(fn($l) => str_contains($l->subject_type ?? '', 'Lead'))->count(),
        'Sistema'     => $logsCollection->filter(fn($l) => empty($l->subject_type) || str_contains($l->action ?? '', ['login','logout']))->count(),
    ]);

    // Top usuarios
    $topUsers = $logsCollection->whereNotNull('user_id')
        ->groupBy('user_id')->map(fn($l) => [
            'name'  => $l->first()->user->full_name ?? $l->first()->user->name ?? 'Desconocido',
            'count' => $l->count(),
        ])->sortByDesc('count')->take(5);

    // Actividad diaria
    $dailyLabels = collect(range(6,0))->map(fn($i) => now()->subDays($i)->format('d/m'))->values();
    $dailyData   = collect(range(6,0))->map(fn($i) =>
        $logsCollection->filter(fn($l) => $l->created_at && $l->created_at->setTimezone('America/Caracas')->format('Y-m-d') === now()->subDays($i)->format('Y-m-d'))->count()
    )->values();

    $totalRecent = count($recentLogs ?? []);
@endphp

@section('content')
<div class="audit-panel" style="background:var(--slate-50);min-height:calc(100vh - 64px)">

    <!-- ═══ Encabezado ═══ -->
    <div class="px-6 lg:px-8 pt-8 pb-2">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-8 h-0.5" style="background:var(--gold-500)"></div>
                    <span class="text-[11px] font-semibold uppercase tracking-[.18em]" style="color:var(--gold-500)">Centro de Auditoría</span>
                </div>
                <h1 class="font-display text-3xl font-bold" style="color:var(--navy-900)">Panel de Control</h1>
                <p class="text-sm mt-1" style="color:var(--slate-500)">Toda actividad de la plataforma registrada: citas, propiedades, usuarios y cambios del sistema.</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-opacity hover:opacity-90" style="background:var(--navy-900);color:#fff">
                    <i class="ph ph-file-text"></i> Bitácora Completa
                </a>
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold" style="background:var(--gold-50);color:var(--gold-500);border:1px solid var(--gold-100)">
                    <i class="ph ph-chart-pie"></i> Reportes
                </a>
            </div>
        </div>
    </div>

    <!-- ═══ Tabla de Actividad Full-Width ═══ -->
    <div class="px-6 lg:px-8 pb-6">
        <div class="bg-white rounded-xl overflow-hidden" style="border:1px solid var(--slate-200)">
            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" style="border-bottom:1px solid var(--slate-100)">
                <div>
                    <h2 class="font-display text-lg font-bold" style="color:var(--navy-900)">Registro de Actividad de la Plataforma</h2>
                    <p class="text-xs mt-0.5" style="color:var(--slate-400)">Citas, propiedades, usuarios y cambios del sistema</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:var(--slate-400)"></i>
                        <input id="tableSearch" type="text" placeholder="Buscar evento..." class="pl-9 pr-4 py-2 rounded-lg text-sm border focus:outline-none focus:ring-2 w-48" style="border-color:var(--slate-200)">
                    </div>
                    <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap" style="color:var(--gold-500);background:var(--gold-50);border:1px solid var(--gold-100)">
                        Ver todo <i class="ph ph-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="auditTable">
                    <thead>
                        <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200)">
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Usuario</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Acción Realizada</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Entidad</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider" style="color:var(--slate-500)">Detalle</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-right" style="color:var(--slate-500)">Fecha / Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color:var(--slate-100)">
                        @forelse($recentLogs ?? [] as $log)
                        @php
                            $ev = $resolveEvent($log);
                            $localDate = $log->created_at ? $log->created_at->setTimezone('America/Caracas') : null;
                        @endphp
                        <tr class="audit-row">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold text-white shrink-0"
                                         style="background:{{ $log->user ? 'var(--navy-700)' : 'var(--slate-400)' }}">
                                        {{ $log->user ? strtoupper(substr($log->user->name,0,1)) : 'S' }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm" style="color:var(--navy-900)">{{ $log->user ? $log->user->full_name : 'Sistema' }}</div>
                                        <div class="text-[11px]" style="color:var(--slate-400)">{{ $log->user ? $log->user->email : 'Proceso automático' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold {{ $ev['severity'] }}">
                                    {{ $ev['action_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold {{ $ev['entity_class'] }}">
                                    <i class="ph {{ $ev['entity_icon'] }} text-xs"></i>
                                    {{ $ev['entity_label'] }}
                                    @if($log->subject_id)
                                        <span style="color:var(--slate-400);font-weight:400">#{{ $log->subject_id }}</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs max-w-[220px] truncate" style="color:var(--slate-500)">{{ $log->description ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <span class="ts text-xs font-medium block" style="color:var(--slate-600)">{{ $localDate ? $localDate->format('d/m/Y') : '—' }}</span>
                                <span class="ts text-xs" style="color:var(--slate-400)">{{ $localDate ? $localDate->format('H:i:s') : '' }}</span>
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
            @if($totalRecent > 0)
            <div class="px-6 py-3 flex items-center justify-between text-xs" style="background:var(--slate-50);border-top:1px solid var(--slate-100);color:var(--slate-400)">
                <span>Mostrando {{ $totalRecent }} de {{ number_format($logsCount ?? 0) }} registros</span>
                <a href="{{ route('audit-logs.index') }}" class="hover:underline font-medium" style="color:var(--gold-500)">Ver todos →</a>
            </div>
            @endif
        </div>
    </div>

    <!-- ═══ Gráficas Corporativas ═══ -->
    <div class="px-6 lg:px-8 pb-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Dona: Por Entidad -->
            <div class="bg-white rounded-xl p-6" style="border:1px solid var(--slate-200)">
                <h3 class="font-display text-base font-bold mb-0.5" style="color:var(--navy-900)">Actividad por Módulo</h3>
                <p class="text-[11px] mb-5" style="color:var(--slate-400)">Distribución por entidad de la plataforma</p>
                <div class="relative mx-auto" style="max-width:220px">
                    <canvas id="chartDoughnut"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-bold" style="color:var(--navy-900)">{{ $totalRecent }}</span>
                        <span class="text-[10px] uppercase tracking-wider font-semibold" style="color:var(--slate-400)">Eventos</span>
                    </div>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-y-2.5 gap-x-4">
                    @php
                        $chartColors = [
                            'Propiedades' => '#c9a84c',
                            'Citas'       => '#059669',
                            'Usuarios'     => '#2563eb',
                            'Leads'       => '#d97706',
                            'Sistema'     => '#7c3aed',
                        ];
                    @endphp
                    @foreach($entityDist as $label => $count)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:{{ $chartColors[$label] ?? '#6b7185' }}"></span>
                        <span class="text-[11px]" style="color:var(--slate-600)">{{ $label }} <strong style="color:var(--navy-900)">{{ $count }}</strong></span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Barras verticales -->
            <div class="bg-white rounded-xl p-6" style="border:1px solid var(--slate-200)">
                <div class="flex items-center justify-between mb-0.5">
                    <h3 class="font-display text-base font-bold" style="color:var(--navy-900)">Actividad por Día</h3>
                    <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded" style="background:var(--gold-50);color:var(--gold-500);border:1px solid var(--gold-100)">7 días</span>
                </div>
                <p class="text-[11px] mb-5" style="color:var(--slate-400)">Eventos registrados diariamente</p>
                <div style="height:240px"><canvas id="chartBars"></canvas></div>
            </div>

            <!-- Top usuarios -->
            <div class="bg-white rounded-xl p-6" style="border:1px solid var(--slate-200)">
                <h3 class="font-display text-base font-bold mb-0.5" style="color:var(--navy-900)">Usuarios Más Activos</h3>
                <p class="text-[11px] mb-5" style="color:var(--slate-400)">Top 5 por cantidad de eventos</p>
                <div style="height:240px">
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

    var GOLD       = '#c9a84c';
    var GOLD_LIGHT = '#d4b96a';
    var NAVY_900   = '#0f1729';
    var NAVY_700   = '#1e2d4a';
    var NAVY_600   = '#2a3f66';
    var SL4        = '#9ca1b0';
    var GRID       = '#e2e4ea';

    var tooltipStyle = {
        backgroundColor: NAVY_900,
        titleFont: { family:'DM Sans', size:12, weight:'600' },
        bodyFont:  { family:'DM Sans', size:11 },
        padding: 10, cornerRadius: 8, displayColors: true, boxPadding: 4,
    };

    // ── Dona: Por módulo/entidad ──
    new Chart(document.getElementById('chartDoughnut'), {
        type: 'doughnut',
        data: {
            labels: @json($entityDist->keys()),
            datasets: [{
                data: @json($entityDist->values()),
                backgroundColor: [GOLD, '#059669', '#2563eb', '#d97706', '#7c3aed'],
                borderWidth: 0, hoverOffset: 8,
            }]
        },
        options: {
            responsive: true, cutout: '74%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: NAVY_900,
                    titleFont: { family:'DM Sans', size:12, weight:'600' },
                    bodyFont:  { family:'DM Sans', size:11 },
                    padding: 10, cornerRadius: 8, displayColors: true, boxPadding: 4,
                    callbacks: {
                        label: function(ctx) {
                            var t = ctx.dataset.data.reduce(function(a,b){return a+b},0);
                            var p = t ? ((ctx.raw/t)*100).toFixed(1) : 0;
                            return ' ' + ctx.label + ': ' + ctx.raw + ' (' + p + '%)';
                        }
                    }
                }
            }
        }
    });

    // ── Barras verticales ──
    new Chart(document.getElementById('chartBars'), {
        type: 'bar',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                data: @json($dailyData),
                backgroundColor: GOLD,
                hoverBackgroundColor: GOLD_LIGHT,
                borderRadius: 6, borderSkipped: false, maxBarThickness: 38
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: NAVY_900,
                    titleFont: { family:'DM Sans', size:12, weight:'600' },
                    bodyFont:  { family:'DM Sans', size:11 },
                    padding: 10, cornerRadius: 8,
                    callbacks: { label: function(ctx) { return ' ' + ctx.raw + ' eventos'; } }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { family:'DM Sans', size: 11, color: SL4 }, border: { display: false } },
                y: { beginAtZero: true, grid: { color: GRID }, ticks: { family:'DM Sans', size: 11, color: SL4, stepSize: 1, precision: 0 }, border: { display: false } }
            }
        }
    });

    // ── Barras horizontales ──
    @if($topUsers->count() > 0)
    new Chart(document.getElementById('chartHBars'), {
        type: 'bar',
        data: {
            labels: @json($topUsers->pluck('name')->values()),
            datasets: [{
                data: @json($topUsers->pluck('count')->values()),
                backgroundColor: NAVY_900,
                hoverBackgroundColor: NAVY_600,
                borderRadius: 6, borderSkipped: false, maxBarThickness: 22
            }]
        },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: NAVY_900,
                    titleFont: { family:'DM Sans', size:12, weight:'600' },
                    bodyFont:  { family:'DM Sans', size:11 },
                    padding: 10, cornerRadius: 8,
                    callbacks: { label: function(ctx) { return ' ' + ctx.raw + ' eventos'; } }
                }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: GRID }, ticks: { family:'DM Sans', size: 11, color: SL4, stepSize: 1, precision: 0 }, border: { display: false } },
                y: { grid: { display: false }, ticks: { family:'DM Sans', size: 11, weight: '500', color: NAVY_700 }, border: { display: false } }
            }
        }
    });
    @endif

    // ── Búsqueda ──
    var input = document.getElementById('tableSearch');
    if (input) {
        input.addEventListener('input', function(){
            var q = this.value.toLowerCase();
            document.querySelectorAll('#auditTable tbody tr').forEach(function(r){
                r.style.display = r.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
