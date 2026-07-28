{{-- Logs de auditoría de citas --}}
@extends('layouts.dashboard')

@section('title', 'Auditoría - Logs de Citas')
@section('header', 'Logs de Citas')

@push('styles')
<style>
    .description-cell {
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.5;
        max-width: 350px;
    }
    .audit-row:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form action="{{ route('audit-logs.appointment-logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                <select name="status" class="w-full border rounded-lg p-2.5">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmadas</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completadas</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Canceladas</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded-lg p-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded-lg p-2.5">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-mso-blue text-white px-6 py-2 rounded-lg hover:bg-slate-800 transition-colors">
                    <i class="ph ph-funnel"></i> Filtrar
                </button>
                <a href="{{ route('audit-logs.appointment-logs') }}" class="px-6 py-2 border rounded-lg hover:bg-slate-50 transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Usuario</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Acción</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Cita</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Descripción</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500 text-right">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="audit-row hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold">
                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                                </div>
                                <span class="font-medium">{{ $log->user ? $log->user->full_name : 'Sistema' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @if(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-700
                                @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-700
                                @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-700">
                            @if($log->subject)
                                <a href="{{ route('citas.index', ['search' => $log->subject_id]) }}"
                                   class="hover:text-mso-gold transition-colors">
                                    Cita #{{ $log->subject_id }}
                                </a>
                            @else
                                #{{ $log->subject_id }}
                            @endif
                        </td>
                        {{-- 🔥 DESCRIPCIÓN COMPLETA EN VARIAS LÍNEAS --}}
                        <td class="px-6 py-3 text-sm text-slate-600 description-cell" title="{{ $log->description ?? '—' }}">
                            {{ $log->description ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-xs text-slate-500 text-right whitespace-nowrap">
                            <span class="block">{{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y') : '' }}</span>
                            <span class="text-slate-400">{{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('H:i') : '' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <i class="ph ph-inbox text-3xl block mb-2"></i>
                            No hay logs de citas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $logs->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
