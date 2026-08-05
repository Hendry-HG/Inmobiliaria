{{-- Reportes y exportación de datos de auditoría --}}
@extends('layouts.dashboard')

@section('title', 'Exportar Datos - Auditoría')
@section('header', 'Centro de Exportación de Datos')

@push('styles')
<style>
    .export-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .export-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.08);
    }
    .export-card .card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    .export-card .card-icon.users { background: #ede9fe; color: #7c3aed; }
    .export-card .card-icon.properties { background: #dbeafe; color: #2563eb; }
    .export-card .card-icon.appointments { background: #fef3c7; color: #d97706; }
    .export-card .card-icon.leads { background: #d1fae5; color: #059669; }
    .export-card .card-icon.system { background: #fce4ec; color: #dc2626; }
    .export-card .card-icon.all { background: #f1f5f9; color: #475569; }
    .export-card .count-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
    }
    .filters-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        border: 1px solid #e2e4ea;
    }
    .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }
    .filter-group select,
    .filter-group input {
        padding: 8px 12px;
        border: 1px solid #e2e4ea;
        border-radius: 8px;
        font-size: 0.875rem;
        background: white;
        min-width: 150px;
    }
    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #c9a84c;
    }
    .export-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }
    .export-btn-excel {
        background: #d1fae5;
        color: #065f46;
    }
    .export-btn-excel:hover {
        background: #a7f3d0;
    }
    .export-btn-pdf {
        background: #fee2e2;
        color: #991b1b;
    }
    .export-btn-pdf:hover {
        background: #fecaca;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="font-serif text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="ph ph-download-simple text-mso-gold text-2xl"></i>
                Centro de Exportación de Datos
            </h3>
            <p class="text-sm text-slate-500">Exporta los logs de auditoría en CSV, Excel (.xlsx) o PDF</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <i class="ph ph-info"></i>
            <span>CSV · Excel · PDF</span>
        </div>
    </div>

    {{-- FILTROS GLOBALES --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 sm:p-4">
        <form method="GET" action="{{ route('audit-logs.reports') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4 items-end" autocomplete="off">
            <div>
                <label for="dateFrom" class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 sm:mb-2">Desde</label>
                <input type="date" id="dateFrom" name="date_from" value="{{ request('date_from') }}" class="w-full min-w-0 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
            </div>
            <div>
                <label for="dateTo" class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 sm:mb-2">Hasta</label>
                <input type="date" id="dateTo" name="date_to" value="{{ request('date_to') }}" class="w-full min-w-0 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
            </div>
            <div>
                <label for="filterUser" class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 sm:mb-2">Usuario</label>
                <select id="filterUser" name="user_id" class="w-full min-w-0 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
                    <option value="">Todos los usuarios</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterAction" class="block text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 sm:mb-2">Acción</label>
                <select id="filterAction" name="action" class="w-full min-w-0 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-mso-gold p-2 sm:p-2.5 transition-all">
                    <option value="">Todas</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Creación</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Actualización</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Eliminación</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 text-white bg-mso-blue hover:bg-slate-800 font-medium rounded-lg text-sm px-4 sm:px-5 py-2 sm:py-2.5 transition-colors shadow-lg shadow-blue-900/20 flex items-center justify-center gap-1 whitespace-nowrap">
                    <i class="ph ph-magnifying-glass"></i> Buscar
                </button>
                <a href="{{ route('audit-logs.reports') }}" class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center justify-center gap-1 whitespace-nowrap">
                    <i class="ph ph-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- TARJETAS DE EXPORTACIÓN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- TODOS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start gap-4">
                <div class="card-icon all">
                    <i class="ph ph-scroll"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-slate-800">Todos los Logs</h4>
                    <p class="text-xs text-slate-400">Exportar todos los registros de auditoría</p>
                    <span class="count-badge">{{ number_format($totalLogs ?? 0) }} registros</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-clock"></i> {{ now()->setTimezone('America/Caracas')->format('d/m/Y H:i') }}
                </span>
                <div class="flex gap-2">
                    <button onclick="exportar('all','csv')" class="export-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="ph ph-file-csv"></i>
                    </button>
                    <button onclick="exportar('all','excel')" class="export-btn export-btn-excel">
                        <i class="ph ph-file-xls"></i>
                    </button>
                    <button onclick="exportar('all','pdf')" class="export-btn export-btn-pdf">
                        <i class="ph ph-file-pdf"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- USUARIOS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start gap-4">
                <div class="card-icon users">
                    <i class="ph ph-users"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-slate-800">Logs de Usuarios</h4>
                    <p class="text-xs text-slate-400">Creación, edición, eliminación, login/logout</p>
                    <span class="count-badge">{{ number_format($userLogsCount ?? 0) }} registros</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-user-circle"></i> {{ $totalUsers ?? 0 }} usuarios
                </span>
                <div class="flex gap-2">
                    <button onclick="exportar('users','csv')" class="export-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="ph ph-file-csv"></i>
                    </button>
                    <button onclick="exportar('users','excel')" class="export-btn export-btn-excel">
                        <i class="ph ph-file-xls"></i>
                    </button>
                    <button onclick="exportar('users','pdf')" class="export-btn export-btn-pdf">
                        <i class="ph ph-file-pdf"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- PROPIEDADES --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start gap-4">
                <div class="card-icon properties">
                    <i class="ph ph-buildings"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-slate-800">Logs de Propiedades</h4>
                    <p class="text-xs text-slate-400">Creación, edición, eliminación, vistas</p>
                    <span class="count-badge">{{ number_format($propertyLogsCount ?? 0) }} registros</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-building"></i> {{ $totalProperties ?? 0 }} propiedades
                </span>
                <div class="flex gap-2">
                    <button onclick="exportar('properties','csv')" class="export-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="ph ph-file-csv"></i>
                    </button>
                    <button onclick="exportar('properties','excel')" class="export-btn export-btn-excel">
                        <i class="ph ph-file-xls"></i>
                    </button>
                    <button onclick="exportar('properties','pdf')" class="export-btn export-btn-pdf">
                        <i class="ph ph-file-pdf"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- CITAS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start gap-4">
                <div class="card-icon appointments">
                    <i class="ph ph-calendar-check"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-slate-800">Logs de Citas</h4>
                    <p class="text-xs text-slate-400">Solicitud, cambio de estado, reprogramación</p>
                    <span class="count-badge">{{ number_format($appointmentLogsCount ?? 0) }} registros</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-calendar"></i> {{ $totalAppointments ?? 0 }} citas
                </span>
                <div class="flex gap-2">
                    <button onclick="exportar('appointments','csv')" class="export-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="ph ph-file-csv"></i>
                    </button>
                    <button onclick="exportar('appointments','excel')" class="export-btn export-btn-excel">
                        <i class="ph ph-file-xls"></i>
                    </button>
                    <button onclick="exportar('appointments','pdf')" class="export-btn export-btn-pdf">
                        <i class="ph ph-file-pdf"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- LEADS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start gap-4">
                <div class="card-icon leads">
                    <i class="ph ph-target"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-slate-800">Logs de Leads</h4>
                    <p class="text-xs text-slate-400">Captura, actualización, cambio de estado</p>
                    <span class="count-badge">{{ number_format($leadLogsCount ?? 0) }} registros</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-users-three"></i> {{ $totalLeads ?? 0 }} leads
                </span>
                <div class="flex gap-2">
                    <button onclick="exportar('leads','csv')" class="export-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="ph ph-file-csv"></i>
                    </button>
                    <button onclick="exportar('leads','excel')" class="export-btn export-btn-excel">
                        <i class="ph ph-file-xls"></i>
                    </button>
                    <button onclick="exportar('leads','pdf')" class="export-btn export-btn-pdf">
                        <i class="ph ph-file-pdf"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- SISTEMA --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start gap-4">
                <div class="card-icon system">
                    <i class="ph ph-gear"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-slate-800">Logs del Sistema</h4>
                    <p class="text-xs text-slate-400">Configuración, roles, permisos, autenticación</p>
                    <span class="count-badge">{{ number_format($systemLogsCount ?? 0) }} registros</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-cpu"></i> Cambios en sistema
                </span>
                <div class="flex gap-2">
                    <button onclick="exportar('system','csv')" class="export-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="ph ph-file-csv"></i>
                    </button>
                    <button onclick="exportar('system','excel')" class="export-btn export-btn-excel">
                        <i class="ph ph-file-xls"></i>
                    </button>
                    <button onclick="exportar('system','pdf')" class="export-btn export-btn-pdf">
                        <i class="ph ph-file-pdf"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- RESULTADOS DE AUDITORÍA FILTRADA --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="ph ph-list-checks text-mso-gold"></i>
                    Auditorías
                </h4>
                <p class="text-xs text-slate-400">{{ $filteredLogs->total() }} registros encontrados</p>
            </div>
            <div class="text-xs text-slate-400">
                @if($filteredLogs->total() > 0)
                    Mostrando {{ $filteredLogs->firstItem() }} - {{ $filteredLogs->lastItem() }}
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Usuario</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Acción</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Entidad</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500">Descripción</th>
                        <th class="px-6 py-3 text-xs font-bold uppercase text-slate-500 text-right">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($filteredLogs as $log)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                                </div>
                                <span>{{ $log->user ? $log->user->full_name : 'Sistema' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium whitespace-nowrap
                                @if(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-700
                                @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-700
                                @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">{{ $log->subject_type ? class_basename($log->subject_type) : 'Sistema' }}</td>
                        <td class="px-6 py-3 text-slate-500 max-w-md truncate">{{ $log->description }}</td>
                        <td class="px-6 py-3 text-right text-slate-500 whitespace-nowrap">{{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i') : '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <i class="ph ph-inbox text-3xl block mb-2"></i>
                            <p>No hay auditorías para los filtros seleccionados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($filteredLogs->hasPages())
        <div class="px-6 py-3 border-t border-slate-200 bg-slate-50">
            {{ $filteredLogs->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    {{-- RESUMEN --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="ph ph-list-checks text-mso-gold"></i>
                    Resumen de Datos Disponibles
                </h4>
                <p class="text-xs text-slate-400">Totales por módulo para exportación</p>
            </div>
            <div class="flex flex-wrap gap-6 text-sm">
                <div>
                    <span class="text-slate-500">Total Logs</span>
                    <span class="font-bold text-slate-800 ml-2">{{ number_format($totalLogs ?? 0) }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Usuarios</span>
                    <span class="font-bold text-purple-600 ml-2">{{ number_format($userLogsCount ?? 0) }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Propiedades</span>
                    <span class="font-bold text-blue-600 ml-2">{{ number_format($propertyLogsCount ?? 0) }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Citas</span>
                    <span class="font-bold text-orange-600 ml-2">{{ number_format($appointmentLogsCount ?? 0) }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Leads</span>
                    <span class="font-bold text-green-600 ml-2">{{ number_format($leadLogsCount ?? 0) }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Sistema</span>
                    <span class="font-bold text-red-600 ml-2">{{ number_format($systemLogsCount ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function exportar(module, format) {
        const dateFrom = document.getElementById('dateFrom')?.value || '';
        const dateTo = document.getElementById('dateTo')?.value || '';
        const userId = document.getElementById('filterUser')?.value || '';
        const action = document.getElementById('filterAction')?.value || '';

        let url;
        if (format === 'csv') {
            url = '{{ route("audit-logs.export") }}?entity=' + module;
        } else if (format === 'excel') {
            url = '{{ route("audit-logs.export.excel") }}?entity=' + module;
        } else {
            url = '{{ route("audit-logs.export.pdf") }}?entity=' + module;
        }

        if (dateFrom) url += '&date_from=' + dateFrom;
        if (dateTo) url += '&date_to=' + dateTo;
        if (userId) url += '&user_id=' + userId;
        if (action) url += '&action=' + action;

        window.open(url, '_blank');
        showToast('Exportando ' + module + ' en formato ' + format.toUpperCase() + '...', 'info');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        if (dateFrom && !dateFrom.value) {
            const firstDay = new Date();
            firstDay.setDate(1);
            dateFrom.value = firstDay.toISOString().split('T')[0];
        }
        if (dateTo && !dateTo.value) {
            dateTo.value = new Date().toISOString().split('T')[0];
        }
    });

    function showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' :
                        type === 'error' ? 'bg-red-500' :
                        'bg-mso-blue';

        toast.className = bgColor + ' text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full';
        const icon = type === 'success' ? 'ph-check-circle' : type === 'error' ? 'ph-warning-circle' : 'ph-info';
        toast.innerHTML = '<i class="ph ' + icon + ' text-lg"></i><span class="text-sm">' + message + '</span>';

        container.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-x-full'), 10);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endpush
@endsection
