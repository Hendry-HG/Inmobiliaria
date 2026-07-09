@extends('layouts.dashboard')

@section('title', 'Auditoría - Logs del Sistema')
@section('header', 'Logs del Sistema')

@section('content')
<div class="space-y-6">

    {{-- ============================================ --}}
    {{-- ESTADÍSTICAS DE CONFIGURACIÓN --}}
    {{-- ============================================ --}}
    @if(isset($configStats))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Total Cambios en Configuración</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $configStats['total_config_changes'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="ph ph-gear text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        @if($configStats['last_config_change'] ?? null)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 md:col-span-2">
            <p class="text-sm text-slate-500">Último Cambio en Configuración</p>
            <div class="flex items-center gap-3 mt-1">
                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                    {{ $configStats['last_config_change']->user ? strtoupper(substr($configStats['last_config_change']->user->name, 0, 1)) : 'S' }}
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800">
                        {{ $configStats['last_config_change']->user ? $configStats['last_config_change']->user->name : 'Sistema' }}
                    </p>
                    <p class="text-xs text-slate-400">
                        <i class="ph ph-clock"></i>
                        {{ $configStats['last_config_change']->created_at ? $configStats['last_config_change']->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i:s') : '' }}
                    </p>
                </div>
                <span class="ml-auto px-2 py-1 rounded text-xs font-medium
                    @if(str_contains($configStats['last_config_change']->action ?? $configStats['last_config_change']->event, 'update')) bg-blue-100 text-blue-700
                    @elseif(str_contains($configStats['last_config_change']->action ?? $configStats['last_config_change']->event, 'create')) bg-green-100 text-green-700
                    @else bg-slate-100 text-slate-700 @endif">
                    {{ ucfirst(str_replace('_', ' ', $configStats['last_config_change']->action ?? $configStats['last_config_change']->event ?? 'Desconocido')) }}
                </span>
            </div>
            @if($configStats['last_config_change']->description)
            <p class="text-xs text-slate-500 mt-1 truncate">
                {{ $configStats['last_config_change']->description }}
            </p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- FILTROS --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form action="{{ route('audit-logs.system-logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                <select name="type" class="w-full border rounded-lg p-2.5">
                    <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>Todos</option>
                    <option value="config" {{ request('type') == 'config' ? 'selected' : '' }}>Configuraciones del Sitio</option>
                    <option value="auth" {{ request('type') == 'auth' ? 'selected' : '' }}>Autenticación (Login/Logout)</option>
                    <option value="role" {{ request('type') == 'role' ? 'selected' : '' }}>Roles</option>
                    <option value="permission" {{ request('type') == 'permission' ? 'selected' : '' }}>Permisos</option>
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
                <a href="{{ route('audit-logs.system-logs') }}" class="px-6 py-2 border rounded-lg hover:bg-slate-50 transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- ============================================ --}}
    {{-- TABLA DE LOGS --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Usuario</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Acción</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Tipo</th>
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
                                <span class="font-medium">{{ $log->user ? $log->user->name : 'Sistema' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @if(str_contains($log->action ?? $log->event, 'login')) bg-purple-100 text-purple-700
                                @elseif(str_contains($log->action ?? $log->event, 'logout')) bg-gray-100 text-gray-700
                                @elseif(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-700
                                @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-700
                                @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-700">
                            @if($log->subject_type)
                                @php
                                    $type = class_basename($log->subject_type);
                                    $icon = match($type) {
                                        'SiteConfiguration' => 'ph-gear',
                                        'Role' => 'ph-shield-check',
                                        'Permission' => 'ph-lock-key',
                                        default => 'ph-cube'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="ph {{ $icon }} text-mso-gold"></i>
                                    {{ $type }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="ph ph-cpu text-slate-400"></i>
                                    Sistema
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-600 max-w-xs truncate">
                            {{ $log->description ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-xs text-slate-500 text-right whitespace-nowrap">
                            {{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i') : '' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <i class="ph ph-inbox text-3xl block mb-2"></i>
                            <p class="font-medium">No hay logs del sistema</p>
                            <p class="text-xs">Los logs de configuración, autenticación, roles y permisos aparecerán aquí</p>
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
