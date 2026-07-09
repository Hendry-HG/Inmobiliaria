@extends('layouts.dashboard')

@section('title', 'Panel Asesor')
@section('header', 'Dashboard Asesor')

@section('content')
<div class="w-full px-4 md:px-8 space-y-6">

    {{-- 1. MÉTRICAS PRINCIPALES (KPIs) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Propiedades Activas --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20">
                    <i class="ph ph-buildings text-xl"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">+12%</span>
            </div>
            <h3 class="text-2xl font-bold text-slate-800">{{ $myProperties ?? 0 }}</h3>
            <p class="text-sm text-slate-500">Propiedades Activas</p>
        </div>

        {{-- Citas Programadas --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="ph ph-calendar-check text-xl"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">+5%</span>
            </div>
            <h3 class="text-2xl font-bold text-slate-800">{{ $todayAppointments ?? 0 }}</h3>
            <p class="text-sm text-slate-500">Citas de Hoy</p>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-amber-600 bg-amber-50 px-2 py-1 rounded-full">{{ $pendingAppointments ?? 0 }} pendientes</span>
            </div>
        </div>

        {{-- Leads Nuevos --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-purple-500 text-white flex items-center justify-center shadow-lg shadow-purple-500/20">
                    <i class="ph ph-users-three text-xl"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">+23%</span>
            </div>
            <h3 class="text-2xl font-bold text-slate-800">{{ $newLeads ?? 0 }}</h3>
            <p class="text-sm text-slate-500">Leads Nuevos</p>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-slate-400">Total asignados</span>
            </div>
        </div>

        {{-- Tasa de Conversión --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-green-500 text-white flex items-center justify-center shadow-lg shadow-green-500/20">
                    <i class="ph ph-trend-up text-xl"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">+8%</span>
            </div>
            <h3 class="text-2xl font-bold text-slate-800">{{ $conversionRate ?? 0 }}%</h3>
            <p class="text-sm text-slate-500">Tasa de Conversión</p>
            <div class="mt-3 w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $conversionRate ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    {{-- 3. SECCIÓN PRINCIPAL: AGENDA, LEADS Y ACTIVIDAD --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna Izquierda (2/3): Agenda y Leads --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Próximas Citas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph ph-calendar text-mso-gold"></i>
                            Próximas Citas
                        </h3>
                        <p class="text-sm text-slate-500">Tus reuniones programadas</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('citas.index') }}" class="text-sm text-mso-blue font-medium hover:underline flex items-center gap-1">
                            <i class="ph ph-plus"></i> Ver Calendario
                        </a>
                    </div>
                </div>

                <div class="p-4">
                    <div class="space-y-3 max-h-[400px] overflow-y-auto custom-scroll">
                        @forelse($upcomingAppointments ?? [] as $appointment)
                            <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 hover:border-mso-gold hover:bg-slate-50 transition-all group cursor-pointer" onclick="window.location.href='{{ route('citas.index') }}'">
                                <div class="flex flex-col items-center justify-center min-w-[70px] bg-gradient-to-br from-mso-blue to-slate-700 rounded-xl p-3 text-white shadow-md">
                                    <span class="text-xs font-bold uppercase">{{ $appointment->scheduled_date->format('M') }}</span>
                                    <span class="text-2xl font-bold">{{ $appointment->scheduled_date->format('d') }}</span>
                                    <span class="text-xs opacity-80">{{ $appointment->scheduled_date->format('h:i A') }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-slate-800">{{ $appointment->contact_name ?? $appointment->user->name ?? 'Cliente' }}</h4>
                                            <p class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                                <i class="ph ph-phone"></i> {{ $appointment->contact_phone ?? 'Sin teléfono' }}
                                            </p>
                                        </div>
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                            @if($appointment->status == 'confirmed') bg-green-100 text-green-700 border border-green-200
                                            @elseif($appointment->status == 'pending') bg-yellow-100 text-yellow-700 border border-yellow-200
                                            @else bg-gray-100 text-gray-600 border border-gray-200 @endif">
                                            <i class="ph
                                                @if($appointment->status == 'confirmed') ph-check-circle
                                                @elseif($appointment->status == 'pending') ph-clock
                                                @else ph-info @endif mr-1"></i>
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </div>
                                    @if($appointment->property)
                                    <div class="mt-3 flex items-center gap-3 bg-slate-50 p-2 rounded-lg">
                                        <img src="{{ $appointment->property->primary_image_url ?? 'https://via.placeholder.com/40' }}"
                                             class="w-10 h-10 rounded-lg object-cover border border-slate-200"
                                             onerror="this.src='https://via.placeholder.com/40'">
                                        <div>
                                            <p class="text-sm font-medium text-slate-800">{{ $appointment->property->title }}</p>
                                            <p class="text-xs text-slate-500">{{ $appointment->property->city ?? 'Sin ubicación' }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 bg-gradient-to-br from-slate-50 to-white rounded-xl border-2 border-dashed border-slate-200">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="ph ph-calendar-slash text-3xl text-slate-400"></i>
                                </div>
                                <p class="text-slate-600 font-medium">No tienes citas programadas</p>
                                <p class="text-sm text-slate-400 mt-1">Agenda una nueva cita para comenzar</p>
                                <a href="{{ route('citas.index') }}" class="mt-4 inline-flex items-center gap-2 bg-mso-blue text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors">
                                    <i class="ph ph-plus"></i> Ver Calendario
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Leads Recientes --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-users text-mso-gold"></i>
                        Leads Recientes
                    </h3>
                    <a href="#" class="text-sm text-mso-blue font-medium hover:underline">Ver todos →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Cliente</th>
                                <th class="px-6 py-3 text-left font-medium">Propiedad</th>
                                <th class="px-6 py-3 text-left font-medium">Origen</th>
                                <th class="px-6 py-3 text-left font-medium">Estado</th>
                                <th class="px-6 py-3 text-right font-medium">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentLeads ?? [] as $lead)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-slate-600 font-bold text-xs">
                                                {{ substr($lead->name ?? 'C', 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-800">{{ $lead->name ?? 'Cliente' }}</p>
                                                <p class="text-xs text-slate-500">{{ $lead->email ?? 'Sin email' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 max-w-[200px] truncate">
                                        {{ $lead->property->title ?? 'General' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs bg-slate-100 px-2 py-1 rounded-full text-slate-600">
                                            {{ $lead->source ?? 'Web' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                            @if($lead->status == 'nuevo') bg-blue-100 text-blue-700 border border-blue-200
                                            @elseif($lead->status == 'contactado') bg-yellow-100 text-yellow-700 border border-yellow-200
                                            @elseif($lead->status == 'calificado') bg-green-100 text-green-700 border border-green-200
                                            @elseif($lead->status == 'cerrado_ganado') bg-emerald-100 text-emerald-700 border border-emerald-200
                                            @elseif($lead->status == 'cerrado_perdido') bg-red-100 text-red-700 border border-red-200
                                            @else bg-gray-100 text-gray-600 border border-gray-200 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $lead->status ?? 'nuevo')) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="text-mso-blue hover:text-mso-gold transition-colors">
                                            <i class="ph ph-chat-circle-text text-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                        <i class="ph ph-users text-3xl text-slate-300 mb-2"></i>
                                        <p>No hay leads recientes.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna Derecha (1): Acciones Rápidas --}}
        <div class="space-y-6">
            {{-- Acciones Rápidas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph ph-lightning text-mso-gold"></i>
                    Acciones Rápidas
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('asesor.properties.create') }}"
                       class="flex flex-col items-center gap-2 p-3 bg-gradient-to-br from-mso-blue to-slate-700 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-plus-circle text-2xl"></i>
                        <span class="text-xs font-medium">Nueva Propiedad</span>
                    </a>
                    <a href="{{ route('citas.index') }}"
                       class="flex flex-col items-center gap-2 p-3 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-calendar-plus text-2xl"></i>
                        <span class="text-xs font-medium">Agendar Cita</span>
                    </a>
                    <a href="#"
                       class="flex flex-col items-center gap-2 p-3 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-user-plus text-2xl"></i>
                        <span class="text-xs font-medium">Nuevo Lead</span>
                    </a>
                    <a href="#"
                       class="flex flex-col items-center gap-2 p-3 bg-gradient-to-br from-orange-400 to-orange-500 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-chart-bar text-2xl"></i>
                        <span class="text-xs font-medium">Reportes</span>
                    </a>
                </div>
            </div>

            {{-- Recordatorios --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-100 p-5">
                <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="ph ph-bell-ringing text-amber-600"></i>
                    Recordatorios
                </h4>
                <div class="space-y-3">
                    <div class="text-center py-4 text-slate-500 text-sm">
                        <i class="ph ph-check-circle text-green-500"></i> No hay recordatorios pendientes
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Datos de citas para el dashboard
    var dashboardAppointments = {!! isset($appointments) && $appointments->count() > 0 ? json_encode($appointments->map(function($app) {
        $user = Auth::user();
        $canEdit = ($user->id == $app->asesor_id || $user->hasRole(['Super Admin', 'Admin']));

        return [
            'id' => $app->id,
            'title' => $app->property->title ?? 'Sin Propiedad',
            'client' => $app->contact_name ?? $app->user->name,
            'client_email' => $app->user->email ?? 'No disponible',
            'client_phone' => $app->user->phone ?? 'No disponible',
            'asesor' => $app->asesor->name ?? 'Sin Asesor',
            'asesor_id' => $app->asesor_id,
            'date' => $app->scheduled_date ? $app->scheduled_date->format('Y-m-d') : '',
            'time' => $app->scheduled_date ? $app->scheduled_date->format('H:i') : '--:--',
            'status' => $app->status,
            'address' => $app->property->address ?? 'Dirección no disponible',
            'property_type' => $app->property->type ?? 'No especificado',
            'property_price' => $app->property->price ?? 'No disponible',
            'property_area' => $app->property->area ?? 'No disponible',
            'notes' => $app->notes ?? 'Sin notas adicionales',
            'created_at' => $app->created_at ? $app->created_at->format('d/m/Y H:i') : 'No disponible',
            'canEdit' => $canEdit
        ];
    })->toArray()) : '[]' !!};

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dashboard del asesor cargado correctamente');
        console.log('Citas disponibles:', dashboardAppointments.length);
    });
</script>

<style>
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush
