{{-- Dashboard de auditoría con métricas generales --}}
@extends('layouts.dashboard')

@section('title', 'Auditoría - Dashboard')
@section('header', 'Centro de Auditoría')

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .audit-row {
        transition: background-color 0.15s ease;
    }
    .audit-row:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Tarjetas de estadísticas --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="stat-card bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Total Logs</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_logs'] ?? 0) }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Hoy</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['today_logs'] ?? 0) }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Usuarios</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_users'] ?? 0) }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Propiedades</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_properties'] ?? 0) }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Citas</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['total_appointments'] ?? 0) }}</p>
        </div>
        <div class="stat-card bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Leads</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['total_leads'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Actividad reciente --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-serif text-lg font-bold text-slate-800">Actividad Reciente</h3>
            <a href="{{ route('audit-logs.index') }}" class="text-sm text-mso-gold hover:underline">Ver todos</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($stats['recent_activity'] ?? [] as $log)
            <div class="audit-row px-6 py-3 flex items-center gap-4">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background: {{ $log->user ? '#1e2d4a' : '#94a3b8' }}">
                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-slate-700 truncate">
                        <span class="font-medium">{{ $log->user ? $log->user->name : 'Sistema' }}</span>
                        <span class="text-slate-500">{{ $log->description ?? 'Realizó una acción' }}</span>
                    </p>
                    <p class="text-xs text-slate-400">
                        <i class="ph ph-clock"></i> {{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i:s') : '' }}
                        @if($log->subject_type)
                            · {{ class_basename($log->subject_type) }}
                            @if($log->subject_id) #{{ $log->subject_id }} @endif
                        @endif
                    </p>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium whitespace-nowrap
                    @if(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-700
                    @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-700
                    @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-700
                    @elseif(str_contains($log->action ?? $log->event, 'login')) bg-purple-100 text-purple-700
                    @else bg-slate-100 text-slate-700 @endif">
                    {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
                </span>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-slate-400">
                <i class="ph ph-inbox text-3xl block mb-2"></i>
                <p>No hay actividad registrada</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
