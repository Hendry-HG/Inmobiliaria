@extends('layouts.dashboard')

@section('title', 'Detalle del Log')
@section('header', 'Detalle de Auditoría')

@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-mso-blue transition-colors">
            <i class="ph ph-arrow-left"></i> Volver
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-mso-blue px-6 py-4 text-white flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg">Detalle del Evento #{{ $log->id }}</h3>
                <p class="text-blue-200 text-sm">
                    {{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i:s') : '' }}
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                @if(str_contains($log->action ?? $log->event, 'create')) bg-green-500 text-white
                @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-500 text-white
                @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-500 text-white
                @elseif(str_contains($log->action ?? $log->event, 'login')) bg-purple-500 text-white
                @else bg-slate-500 text-white @endif">
                {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
            </span>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Usuario</label>
                    <p class="text-slate-800 font-medium">{{ $log->user ? $log->user->full_name : 'Sistema' }}</p>
                    <p class="text-sm text-slate-400">{{ $log->user ? $log->user->email : 'Proceso automático' }}</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Descripción</label>
                    <p class="text-slate-700">{{ $log->description ?? 'Sin descripción' }}</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Modelo</label>
                    <p class="text-slate-700 font-mono text-sm">{{ $log->subject_type ?? 'N/A' }}</p>
                    <p class="text-sm text-slate-400">ID: {{ $log->subject_id ?? 'N/A' }}</p>
                </div>

                @if($log->subject)
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Entidad Relacionada</label>
                    <a href="{{ route('catalogo.show', $log->subject_id) }}" class="text-mso-gold hover:underline text-sm" target="_blank">
                        Ver {{ class_basename($log->subject_type) }}
                    </a>
                </div>
                @endif
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Dirección IP</label>
                    <p class="text-slate-700 font-mono">{{ $log->ip_address ?? '-' }}</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">User Agent</label>
                    <p class="text-slate-700 text-sm break-all">{{ $log->user_agent ?? '-' }}</p>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">URL</label>
                    <p class="text-slate-700 text-sm break-all">{{ $log->url ?? '-' }}</p>
                </div>
            </div>
        </div>

        @if($log->old_values || $log->new_values)
        <div class="border-t border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-2">
                @if($log->old_values)
                <div class="p-4 border-r border-slate-200">
                    <h4 class="text-xs font-bold text-red-600 uppercase mb-2">Valores Antiguos (Old)</h4>
                    <pre class="bg-slate-50 p-4 rounded-lg text-sm font-mono text-red-700 overflow-x-auto max-h-60 overflow-y-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
                @if($log->new_values)
                <div class="p-4 {{ $log->old_values ? '' : 'col-span-2' }}">
                    <h4 class="text-xs font-bold text-green-600 uppercase mb-2">Valores Nuevos (New)</h4>
                    <pre class="bg-slate-50 p-4 rounded-lg text-sm font-mono text-green-700 overflow-x-auto max-h-60 overflow-y-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
