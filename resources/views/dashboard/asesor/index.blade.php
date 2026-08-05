{{-- Dashboard principal del Asesor Inmobiliario --}}
@extends('layouts.dashboard')

@section('title', 'Panel Asesor')
@section('header', 'Dashboard Asesor')

@section('content')
<div class="space-y-6">

    {{-- 1. MÉTRICAS PRINCIPALES (KPIs) - 3 COLUMNAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        {{-- Propiedades Activas --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20">
                    <i class="ph ph-buildings text-lg"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+{{ rand(5, 20) }}%</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800">{{ $myProperties ?? 0 }}</h3>
            <p class="text-xs text-slate-500">Propiedades Activas</p>
        </div>

        {{-- Citas Programadas --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="ph ph-calendar-check text-lg"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+{{ rand(2, 10) }}%</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800">{{ $todayAppointments ?? 0 }}</h3>
            <p class="text-xs text-slate-500">Citas de Hoy</p>
            <div class="mt-2 flex items-center gap-2 text-xs">
                <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">{{ $pendingAppointments ?? 0 }} pendientes</span>
            </div>
        </div>

        {{-- Leads Nuevos --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-purple-500 text-white flex items-center justify-center shadow-lg shadow-purple-500/20">
                    <i class="ph ph-users-three text-lg"></i>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+{{ rand(10, 30) }}%</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800">{{ $newLeads ?? 0 }}</h3>
            <p class="text-xs text-slate-500">Leads Nuevos</p>
            <div class="mt-2 flex items-center gap-2 text-xs">
                <span class="text-slate-400">Total asignados</span>
            </div>
        </div>
    </div>

    {{-- 2. SECCIÓN PRINCIPAL: AGENDA, LEADS Y ACTIVIDAD --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna Izquierda (2/3): Agenda y Leads --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Próximas Citas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph ph-calendar text-mso-gold"></i>
                            Próximas Citas
                        </h3>
                        <p class="text-xs text-slate-500">Tus reuniones programadas</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('citas.index') }}" class="text-xs text-mso-blue font-medium hover:underline flex items-center gap-1">
                            <i class="ph ph-arrow-right"></i> Ver Calendario
                        </a>
                    </div>
                </div>

                <div class="p-3">
                    @if(isset($upcomingAppointments) && $upcomingAppointments->count() > 0)
                    <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scroll">
                        @foreach($upcomingAppointments as $appointment)
                            <div class="flex flex-col sm:flex-row items-start gap-3 p-3 rounded-xl border border-slate-100 hover:border-mso-gold hover:bg-slate-50 transition-all group cursor-pointer"
                                 onclick="window.location.href='{{ route('citas.index') }}'">
                                <div class="flex flex-row sm:flex-col items-center justify-between sm:justify-center min-w-[60px] w-full sm:w-auto bg-gradient-to-br from-mso-blue to-slate-700 rounded-lg p-2 text-white shadow-md">
                                    <span class="text-[10px] font-bold uppercase">{{ $appointment->scheduled_date->format('M') }}</span>
                                    <span class="text-xl font-bold">{{ $appointment->scheduled_date->format('d') }}</span>
                                    <span class="text-[10px] opacity-80">{{ $appointment->scheduled_date->format('h:i A') }}</span>
                                </div>
                                <div class="flex-1 w-full sm:w-auto">
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1">
                                        <div>
                                            <h4 class="font-semibold text-sm text-slate-800">{{ $appointment->contact_name ?? $appointment->user->full_name ?? 'Cliente' }}</h4>
                                            <p class="text-[10px] text-slate-500 flex items-center gap-1">
                                                <i class="ph ph-phone"></i> {{ $appointment->contact_phone ?? 'Sin teléfono' }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full whitespace-nowrap
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
                                    <div class="mt-2 flex items-center gap-2 bg-slate-50 p-1.5 rounded-lg">
                                        <img src="{{ $appointment->property->primary_image_url ?? 'https://via.placeholder.com/40' }}"
                                             class="w-8 h-8 rounded-lg object-cover border border-slate-200"
                                             onerror="this.src='https://via.placeholder.com/40'">
                                        <div>
                                            <p class="text-xs font-medium text-slate-800 truncate max-w-[150px]">{{ $appointment->property->title }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $appointment->property->full_location ?? 'Sin ubicación' }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @else
                        <div class="text-center py-8 bg-gradient-to-br from-slate-50 to-white rounded-xl border-2 border-dashed border-slate-200">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="ph ph-calendar-slash text-2xl text-slate-400"></i>
                            </div>
                            <p class="text-slate-600 font-medium text-sm">No tienes citas programadas</p>
                            <p class="text-xs text-slate-400 mt-1">Agenda una nueva cita para comenzar</p>
                            <a href="{{ route('citas.index') }}" class="mt-3 inline-flex items-center gap-2 bg-mso-blue text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-slate-800 transition-colors">
                                <i class="ph ph-plus"></i> Ver Calendario
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Leads Recientes --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-slate-50/50">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-users text-mso-gold"></i>
                        Leads Recientes
                    </h3>
                    <a href="{{ route('leads.index') }}" class="text-xs text-mso-blue font-medium hover:underline flex items-center gap-1">
                        Ver todos <i class="ph ph-arrow-right"></i>
                    </a>
                </div>

                <div class="overflow-x-auto p-1">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">Cliente</th>
                                <th class="px-3 py-2 text-left font-medium hidden sm:table-cell">Propiedad</th>
                                <th class="px-3 py-2 text-left font-medium hidden md:table-cell">Origen</th>
                                <th class="px-3 py-2 text-left font-medium">Estado</th>
                                <th class="px-3 py-2 text-right font-medium">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentLeads ?? [] as $lead)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-slate-600 font-bold text-[10px] flex-shrink-0">
                                                {{ substr($lead->name ?? 'C', 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-800 truncate max-w-[80px] sm:max-w-[120px]">{{ $lead->name ?? 'Cliente' }}</p>
                                                <p class="text-[10px] text-slate-500 truncate max-w-[80px] sm:max-w-[120px]">{{ $lead->email ?? 'Sin email' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600 max-w-[120px] truncate hidden sm:table-cell">
                                        {{ $lead->property->title ?? 'General' }}
                                    </td>
                                    <td class="px-3 py-2.5 hidden md:table-cell">
                                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-600 whitespace-nowrap">
                                            {{ $lead->source ?? 'Web' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full whitespace-nowrap
                                            @if($lead->status == 'nuevo') bg-blue-100 text-blue-700 border border-blue-200
                                            @elseif($lead->status == 'contactado') bg-yellow-100 text-yellow-700 border border-yellow-200
                                            @elseif($lead->status == 'calificado') bg-green-100 text-green-700 border border-green-200
                                            @elseif($lead->status == 'cerrado_ganado') bg-emerald-100 text-emerald-700 border border-emerald-200
                                            @elseif($lead->status == 'cerrado_perdido') bg-red-100 text-red-700 border border-red-200
                                            @else bg-gray-100 text-gray-600 border border-gray-200 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $lead->status ?? 'nuevo')) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <a href="{{ route('leads.show', $lead->id) }}"
                                           class="text-mso-blue hover:text-mso-gold transition-colors inline-flex items-center gap-1">
                                            <i class="ph ph-chat-circle-text text-base"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-slate-500">
                                        <i class="ph ph-users text-2xl text-slate-300 mb-1 block"></i>
                                        <p class="text-xs">No hay leads recientes.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna Derecha (1/3): Acciones Rápidas y Recordatorios --}}
        <div class="space-y-6">
            {{-- Acciones Rápidas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                <h4 class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="ph ph-lightning text-mso-gold"></i>
                    Acciones Rápidas
                </h4>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('asesor.properties.create') }}"
                       class="flex flex-col items-center gap-1.5 p-2.5 bg-gradient-to-br from-mso-blue to-slate-700 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-plus-circle text-xl"></i>
                        <span class="text-[10px] font-medium text-center">Nueva Propiedad</span>
                    </a>
                    <a href="{{ route('citas.index') }}"
                       class="flex flex-col items-center gap-1.5 p-2.5 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-calendar-plus text-xl"></i>
                        <span class="text-[10px] font-medium text-center">Agendar Cita</span>
                    </a>
                    <a href="{{ route('leads.index') }}"
                       class="flex flex-col items-center gap-1.5 p-2.5 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-user-plus text-xl"></i>
                        <span class="text-[10px] font-medium text-center">Ver Leads</span>
                    </a>
                    <a href="{{ route('citas.configuracion') }}"
                       class="flex flex-col items-center gap-1.5 p-2.5 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="ph ph-gear text-xl"></i>
                        <span class="text-[10px] font-medium text-center">Configurar</span>
                    </a>
                </div>
            </div>

            {{-- Recordatorios - Próximas Citas del Día --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-100 p-4">
                <h4 class="font-bold text-sm text-slate-800 mb-2 flex items-center gap-2">
                    <i class="ph ph-bell-ringing text-amber-600"></i>
                    Recordatorios
                </h4>
                <div class="space-y-2">
                    @php
                        $todayAppointmentsList = $appointments ?? collect();
                        $todayAppointmentsList = $todayAppointmentsList->filter(function($app) {
                            return $app->scheduled_date->isToday() && in_array($app->status, ['pending', 'confirmed']);
                        });
                    @endphp

                    @if($todayAppointmentsList->count() > 0)
                        @foreach($todayAppointmentsList->take(3) as $appointment)
                            <div class="flex items-start gap-2 p-2 bg-white/70 rounded-lg border border-amber-200 shadow-sm">
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                                    <i class="ph ph-clock text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-800 truncate">
                                        {{ $appointment->scheduled_date->format('h:i A') }}
                                    </p>
                                    <p class="text-[10px] text-slate-600 truncate">
                                        {{ $appointment->contact_name ?? $appointment->user->full_name ?? 'Cliente' }}
                                    </p>
                                    @if($appointment->property)
                                        <p class="text-[10px] text-slate-500 truncate">
                                            {{ $appointment->property->title }}
                                        </p>
                                    @endif
                                </div>
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full whitespace-nowrap
                                    @if($appointment->status == 'confirmed') bg-green-100 text-green-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>
                        @endforeach
                        @if($todayAppointmentsList->count() > 3)
                            <p class="text-[10px] text-amber-600 text-center">
                                +{{ $todayAppointmentsList->count() - 3 }} citas más hoy
                            </p>
                        @endif
                    @else
                        <div class="text-center py-3 text-slate-500 text-xs">
                            <i class="ph ph-check-circle text-green-500 block text-xl mb-1"></i>
                            No hay citas pendientes para hoy
                        </div>
                    @endif
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
            'client' => $app->contact_name ?? $app->user->full_name,
            'client_email' => $app->user->email ?? 'No disponible',
            'client_phone' => $app->user->phone ?? 'No disponible',
            'asesor' => $app->asesor->full_name ?? 'Sin Asesor',
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
    })->toArray(), JSON_HEX_TAG | JSON_HEX_AMP) : '[]' !!};

    document.addEventListener('DOMContentLoaded', function() {
    });
</script>

<style>
    .custom-scroll::-webkit-scrollbar {
        width: 4px;
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

    /* Mejoras para dispositivos móviles */
    @media (max-width: 640px) {
        .grid-cols-1.sm\:grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush
