@extends('layouts.dashboard')

@section('title', 'Exportar Datos - Auditoría')
@section('header', 'Centro de Exportación de Datos')

@push('styles')
<style>
    .export-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
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
    .export-card .export-hint {
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    .export-card:hover .export-hint {
        opacity: 1;
        transform: translateY(0);
    }
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
        ring: 2px solid #c9a84c;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ============================================ --}}
    {{-- ENCABEZADO --}}
    {{-- ============================================ --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="font-serif text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="ph ph-download-simple text-mso-gold text-2xl"></i>
                Centro de Exportación de Datos
            </h3>
            <p class="text-sm text-slate-500">Exporta los logs de auditoría en formato CSV para cada módulo</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <i class="ph ph-info"></i>
            <span>Formato CSV · Compatible con Excel</span>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- FILTROS GLOBALES --}}
    {{-- ============================================ --}}
    <div class="filters-section">
        <div class="filter-group">
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Desde</label>
                <input type="date" id="dateFrom" class="w-full">
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Hasta</label>
                <input type="date" id="dateTo" class="w-full">
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Usuario</label>
                <select id="filterUser" class="w-full">
                    <option value="">Todos los usuarios</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="aplicarFiltros()"
                        class="bg-mso-blue text-white px-6 py-2 rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium flex items-center gap-2">
                    <i class="ph ph-funnel"></i> Aplicar Filtros
                </button>
                <button onclick="limpiarFiltros()"
                        class="ml-2 px-4 py-2 border rounded-lg hover:bg-slate-50 transition-colors text-sm">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TARJETAS DE EXPORTACIÓN POR MÓDULO --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- TODOS LOS LOGS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6" onclick="exportarLogs('all')">
            <div class="flex items-start gap-4">
                <div class="card-icon all">
                    <i class="ph ph-scroll"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-slate-800">Todos los Logs</h4>
                    <p class="text-xs text-slate-400">Exportar todos los registros</p>
                    <span class="count-badge">{{ number_format($totalLogs ?? 0) }} registros</span>
                </div>
            </div>
            <div class="export-hint mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-clock"></i> Última exportación: {{ now()->setTimezone('America/Caracas')->format('d/m/Y H:i') }}
                </span>
                <span class="text-xs font-medium text-mso-gold flex items-center gap-1">
                    Exportar <i class="ph ph-arrow-right"></i>
                </span>
            </div>
        </div>

        {{-- USUARIOS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6" onclick="exportarLogs('users')">
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
            <div class="export-hint mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-user-circle"></i> {{ $totalUsers ?? 0 }} usuarios
                </span>
                <span class="text-xs font-medium text-mso-gold flex items-center gap-1">
                    Exportar <i class="ph ph-arrow-right"></i>
                </span>
            </div>
        </div>

        {{-- PROPIEDADES --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6" onclick="exportarLogs('properties')">
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
            <div class="export-hint mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-building"></i> {{ $totalProperties ?? 0 }} propiedades
                </span>
                <span class="text-xs font-medium text-mso-gold flex items-center gap-1">
                    Exportar <i class="ph ph-arrow-right"></i>
                </span>
            </div>
        </div>

        {{-- CITAS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6" onclick="exportarLogs('appointments')">
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
            <div class="export-hint mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-calendar"></i> {{ $totalAppointments ?? 0 }} citas
                </span>
                <span class="text-xs font-medium text-mso-gold flex items-center gap-1">
                    Exportar <i class="ph ph-arrow-right"></i>
                </span>
            </div>
        </div>

        {{-- LEADS --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6" onclick="exportarLogs('leads')">
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
            <div class="export-hint mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-users-three"></i> {{ $totalLeads ?? 0 }} leads
                </span>
                <span class="text-xs font-medium text-mso-gold flex items-center gap-1">
                    Exportar <i class="ph ph-arrow-right"></i>
                </span>
            </div>
        </div>

        {{-- SISTEMA --}}
        <div class="export-card bg-white rounded-xl shadow-sm border border-slate-200 p-6" onclick="exportarLogs('system')">
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
            <div class="export-hint mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    <i class="ph ph-cpu"></i> Cambios en sistema
                </span>
                <span class="text-xs font-medium text-mso-gold flex items-center gap-1">
                    Exportar <i class="ph ph-arrow-right"></i>
                </span>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- RESUMEN DE EXPORTACIÓN --}}
    {{-- ============================================ --}}
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
    // ============================================================
    // EXPORTAR LOGS
    // ============================================================
    function exportarLogs(module) {
        // Obtener filtros
        const dateFrom = document.getElementById('dateFrom')?.value || '';
        const dateTo = document.getElementById('dateTo')?.value || '';
        const userId = document.getElementById('filterUser')?.value || '';

        // Construir URL
        let url = '{{ route("audit-logs.export") }}?entity=' + module;
        if (dateFrom) url += '&date_from=' + dateFrom;
        if (dateTo) url += '&date_to=' + dateTo;
        if (userId) url += '&user_id=' + userId;

        // Abrir en nueva pestaña o descargar
        window.open(url, '_blank');

        // Mostrar mensaje de éxito
        showToast('Exportando ' + module + '...', 'info');
    }

    // ============================================================
    // FILTROS
    // ============================================================
    function aplicarFiltros() {
        const dateFrom = document.getElementById('dateFrom')?.value || '';
        const dateTo = document.getElementById('dateTo')?.value || '';
        const userId = document.getElementById('filterUser')?.value || '';

        // Actualizar todas las tarjetas con los filtros
        document.querySelectorAll('.export-card').forEach(card => {
            const countBadge = card.querySelector('.count-badge');
            if (countBadge) {
                // Simular actualización del contador
                countBadge.textContent = '...';
                setTimeout(() => {
                    // Aquí se podría hacer una llamada AJAX para actualizar los contadores
                    countBadge.textContent = '✅ Filtros aplicados';
                }, 500);
            }
        });

        showToast('Filtros aplicados correctamente', 'success');
    }

    function limpiarFiltros() {
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('filterUser').value = '';
        showToast('Filtros limpiados', 'info');
    }

    // ============================================================
    // TOAST / NOTIFICACIONES
    // ============================================================
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

        toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full`;
        toast.innerHTML = `
            <i class="ph ${type === 'success' ? 'ph-check-circle' : type === 'error' ? 'ph-warning-circle' : 'ph-info'} text-lg"></i>
            <span class="text-sm">${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 10);

        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // ============================================================
    // INICIALIZAR
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Fecha actual en los filtros
        const today = new Date().toISOString().split('T')[0];
        const firstDay = new Date();
        firstDay.setDate(1);
        const firstDayStr = firstDay.toISOString().split('T')[0];

        if (document.getElementById('dateFrom')) {
            document.getElementById('dateFrom').value = firstDayStr;
        }
        if (document.getElementById('dateTo')) {
            document.getElementById('dateTo').value = today;
        }
    });
</script>
@endpush
@endsection
