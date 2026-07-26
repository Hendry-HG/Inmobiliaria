@extends('layouts.dashboard')

@section('title', 'Gestión de Citas')
@section('header', 'Mis Citas')

@section('content')
@php
    $primaryRole = $userRoles[0] ?? 'Cliente';
    $isAdmin = in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles) || in_array('Auditor', $userRoles);
    $isAsesor = in_array('Asesor Inmobiliario', $userRoles);
    $isCliente = $primaryRole === 'Cliente';
    $currentUserId = Auth::id();

    // Preparar datos para JavaScript
    $appointmentsData = [];
    foreach($appointments as $app) {
        $propTitle = $app->property ? $app->property->title : 'Propiedad eliminada';
        $propAddress = $app->property ? $app->property->address : 'N/A';
        $asesorName = $app->asesor ? $app->asesor->name : 'Sin Asesor';
        $userName = $app->user ? $app->user->name : ($app->contact_name ?? 'Cliente');
        $userEmail = $app->user ? $app->user->email : ($app->contact_email ?? 'N/A');

        // Obtener imagen principal de la propiedad
        $propertyImage = null;
        if ($app->property && $app->property->images && $app->property->images->count() > 0) {
            $primaryImage = $app->property->images->where('is_primary', true)->first();
            if ($primaryImage) {
                $propertyImage = asset('storage/' . $primaryImage->image_path);
            } else {
                $propertyImage = asset('storage/' . $app->property->images->first()->image_path);
            }
        } else {
            $propertyImage = $app->property?->primary_image_url ?? 'https://placehold.co/400x300/1e293b/64748b?text=Sin+Imagen';
        }

        $appointmentsData[] = [
            'id' => $app->id,
            'title' => $propTitle,
            'client' => $app->contact_name ?? $userName,
            'client_email' => $userEmail,
            'client_phone' => $app->contact_phone ?? ($app->user->phone ?? 'No disponible'),
            'asesor' => $asesorName,
            'asesor_id' => $app->asesor_id,
            'date' => $app->scheduled_date ? $app->scheduled_date->format('Y-m-d') : '',
            'date_display' => $app->scheduled_date ? $app->scheduled_date->format('d/m/Y') : '',
            'time' => $app->scheduled_date ? $app->scheduled_date->format('H:i') : '--:--',
            'status' => $app->status,
            'address' => $propAddress,
            'property_type' => $app->property ? $app->property->type : 'N/A',
            'property_price' => $app->property ? ($app->property->type == 'alquiler' ? '$' . number_format($app->property->price, 0, ',', '.') . '/mes' : '$' . number_format($app->property->price, 0, ',', '.')) : '0',
            'property_area' => $app->property ? $app->property->area : '0',
            'property_image' => $propertyImage,
            'property_id' => $app->property_id,
            'notes' => $app->notes ?? '',
            'created_at' => $app->created_at ? $app->created_at->format('d/m/Y H:i') : 'No disponible',
            'canEdit' => ($currentUserId == $app->asesor_id || in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles)),
            'has_property' => $app->property ? true : false
        ];
    }
@endphp

{{-- ==================================================== --}}
{{-- BOTÓN DE CONFIGURACIÓN - Solo con permiso específico --}}
{{-- ==================================================== --}}
@can('configurar agenda')
<div class="mb-4 flex justify-end">
    <a href="{{ route('citas.configuracion') }}"
       class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition-all duration-200 text-slate-600 hover:text-mso-blue group">
        <i class="ph ph-gear-six text-xl group-hover:rotate-90 transition-transform duration-300"></i>
        <span class="text-sm font-medium">Configurar Agenda</span>
    </a>
</div>
@endcan

{{-- MODAL DE VISTA PREVIA DE PROPIEDAD - GLOBAL PARA TODOS LOS ROLES --}}
<div id="propertyPreviewModal" class="fixed inset-0 bg-black/70 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-slate-200 p-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800" id="previewPropertyTitle">Propiedad</h3>
            <button onclick="window.closeModal('propertyPreviewModal')" class="text-slate-400 hover:text-slate-600">
                <i class="ph ph-x text-2xl"></i>
            </button>
        </div>
        <div id="previewContent" class="p-6">
            <div class="text-center py-8 text-slate-400">Cargando...</div>
        </div>
    </div>
</div>

{{-- ============================================= --}}
{{-- CONTENIDO PRINCIPAL - Solo si tiene permisos  --}}
{{-- ============================================= --}}
@canany(['ver citas', 'gestionar citas'])

    {{-- ============================================= --}}
    {{-- VISTA PARA ASESOR --}}
    {{-- ============================================= --}}
    @if($isAsesor)
    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-5 bg-white rounded-xl shadow-sm border border-slate-200 p-3">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-slate-700 text-sm">Calendario</h3>
                    <div class="flex gap-1">
                        <button onclick="window.changeMonth(-1)" class="p-1 hover:bg-slate-100 rounded"><i class="ph-bold ph-caret-left"></i></button>
                        <button onclick="window.resetToToday()" class="px-2 text-xs font-medium text-mso-blue">Hoy</button>
                        <button onclick="window.changeMonth(1)" class="p-1 hover:bg-slate-100 rounded"><i class="ph-bold ph-caret-right"></i></button>
                    </div>
                </div>
                <div id="asesorCalendar"></div>
            </div>
            <div class="lg:col-span-7 bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="font-bold text-slate-700 text-sm mb-3">Filtros de Búsqueda</h3>
                <form method="GET" action="{{ route('citas.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="asesor_id" value="{{ Auth::id() }}">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
                        <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="todos">Todos</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmadas</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completadas</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Canceladas</option>
                            <option value="reprogrammed" {{ request('status') == 'reprogrammed' ? 'selected' : '' }}>Reprogramadas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Fecha Desde</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Fecha Hasta</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
                        <input type="text" name="search" placeholder="Nombre del cliente..." value="{{ request('search') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2 flex justify-center gap-3">
                        <button type="submit" class="bg-mso-blue text-white px-8 py-2 rounded-lg">Buscar</button>
                        <a href="{{ route('citas.index') }}?asesor_id={{ Auth::id() }}" class="bg-slate-100 text-slate-600 border px-8 py-2 rounded-lg">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b bg-slate-50/50">
                <div class="flex flex-wrap gap-2">
                    <button onclick="window.filterByStatus('all')" class="chip-filter px-4 py-1.5 rounded-full text-xs border" data-status="all">Todas <span id="count-all">0</span></button>
                    <button onclick="window.filterByStatus('pending')" class="chip-filter px-4 py-1.5 rounded-full text-xs border" data-status="pending">Pendientes <span id="count-pending">0</span></button>
                    <button onclick="window.filterByStatus('confirmed')" class="chip-filter px-4 py-1.5 rounded-full text-xs border" data-status="confirmed">Confirmadas <span id="count-confirmed">0</span></button>
                    <button onclick="window.filterByStatus('completed')" class="chip-filter px-4 py-1.5 rounded-full text-xs border" data-status="completed">Completadas <span id="count-completed">0</span></button>
                    <button onclick="window.filterByStatus('cancelled')" class="chip-filter px-4 py-1.5 rounded-full text-xs border" data-status="cancelled">Canceladas <span id="count-cancelled">0</span></button>
                    <button onclick="window.filterByStatus('reprogrammed')" class="chip-filter px-4 py-1.5 rounded-full text-xs border" data-status="reprogrammed">Reprogramadas <span id="count-reprogrammed">0</span></button>
                </div>
            </div>

            <div class="divide-y divide-slate-100" id="appointmentsListContainer">
                @forelse($appointments as $appointment)
                <div class="appointment-item p-4 hover:bg-slate-50" data-status="{{ $appointment->status }}" data-id="{{ $appointment->id }}">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 cursor-pointer" onclick="window.showPropertyPreview({{ $appointment->id }})">
                            @php
                                $imgUrl = 'https://placehold.co/400x300/1e293b/64748b?text=Sin+Imagen';
                                if ($appointment->property && $appointment->property->images && $appointment->property->images->count() > 0) {
                                    $primaryImg = $appointment->property->images->where('is_primary', true)->first();
                                    if ($primaryImg) {
                                        $imgUrl = asset('storage/' . $primaryImg->image_path);
                                    } else {
                                        $imgUrl = asset('storage/' . $appointment->property->images->first()->image_path);
                                    }
                                } elseif ($appointment->property && $appointment->property->primary_image_url) {
                                    $imgUrl = $appointment->property->primary_image_url;
                                }
                            @endphp
                            <img src="{{ $imgUrl }}" class="w-16 h-16 rounded-lg object-cover border">
                        </div>

                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-slate-800 cursor-pointer hover:text-mso-blue" onclick="window.showPropertyPreview({{ $appointment->id }})">
                                        {{ $appointment->property->title ?? 'Propiedad eliminada' }}
                                    </h4>
                                    <p class="text-sm text-slate-600">{{ $appointment->contact_name ?? ($appointment->user->name ?? 'Cliente') }}</p>
                                    <p class="text-xs text-slate-400">{{ $appointment->property->address ?? '' }}</p>
                                    @if($appointment->property)
                                    <div class="flex gap-3 mt-1 text-xs text-slate-500">
                                        <span>{{ number_format($appointment->property->area, 0) }}m²</span>
                                        <span>{{ $appointment->property->type == 'alquiler' ? '$' . number_format($appointment->property->price, 0, ',', '.') . '/mes' : '$' . number_format($appointment->property->price, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="bg-slate-100 rounded-lg px-3 py-1 text-center min-w-[90px]">
                                        <div class="text-xs text-slate-400">{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('M') : '-' }}</div>
                                        <div class="text-xl font-bold">{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('d') : '-' }}</div>
                                        <div class="text-xs">{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('H:i') : '--:--' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @php
                                $badgeClass = match($appointment->status) {
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'confirmed' => 'bg-green-100 text-green-700',
                                    'completed' => 'bg-blue-100 text-blue-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'reprogrammed' => 'bg-purple-100 text-purple-700',
                                    default => 'bg-slate-100'
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ ucfirst($appointment->status) }}
                            </span>

                            @canany(['editar cita', 'gestionar citas'])
                                <button onclick="window.openActionModal({{ $appointment->id }})" class="px-3 py-2 bg-mso-blue text-white rounded-lg text-sm">
                                    <i class="ph-bold ph-gear"></i> Gestionar
                                </button>
                            @else
                                <span class="px-3 py-2 text-slate-400 text-sm">Sin permisos</span>
                            @endcanany
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-slate-400">
                    <i class="ph-bold ph-calendar-x text-4xl mb-3 block"></i>
                    <p>No hay citas registradas</p>
                </div>
                @endforelse
            </div>

            <div class="p-4 border-t">
                {{ $appointments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL GESTIONAR CITA ASESOR --}}
    <div id="actionModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 m-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Gestionar Cita</h3>
                <button onclick="window.closeModal('actionModal')" class="text-slate-400"><i class="ph ph-x text-xl"></i></button>
            </div>
            <div class="bg-slate-50 p-4 rounded-lg mb-4">
                <p class="text-sm text-slate-500">Propiedad:</p>
                <p class="text-sm font-semibold" id="modalPropertyTitle">...</p>
                <p class="text-sm mt-2" id="modalClientInfo">Cliente: ...</p>
                <p class="text-sm" id="modalDateTime">Fecha: ...</p>
            </div>
            <form id="actionForm">
                @csrf
                <input type="hidden" id="actionAppointmentId">
                <div>
                    <label class="block text-xs font-medium mb-1">Cambiar Estado</label>
                    <select id="statusSelect" class="w-full border rounded-lg px-4 py-2.5 text-sm">
                        <option value="pending">Pendiente</option>
                        <option value="confirmed">Confirmada</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                        <option value="reprogrammed">Reprogramada</option>
                    </select>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-medium mb-1">Reprogramar</label>
                    <input type="datetime-local" id="rescheduleDateInput" class="w-full border rounded-lg px-4 py-2.5 text-sm">
                    <p class="text-xs text-slate-400 mt-1">Dejar vacío para mantener la fecha actual</p>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-medium mb-1">Nota</label>
                    <textarea id="actionMessage" rows="3" class="w-full border rounded-lg px-4 py-2.5 text-sm resize-none"></textarea>
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="window.closeModal('actionModal')" class="flex-1 px-4 py-2 border rounded-lg">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-mso-blue text-white rounded-lg">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- VISTA PARA ADMIN                              --}}
    {{-- ============================================= --}}
    @if($isAdmin)
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border">
                <p class="text-sm text-slate-500">Total</p>
                <p class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border">
                <p class="text-sm text-slate-500">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border">
                <p class="text-sm text-slate-500">Confirmadas</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['confirmed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border">
                <p class="text-sm text-slate-500">Completadas</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['completed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border">
                <p class="text-sm text-slate-500">Canceladas</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4">
            <form method="GET" action="{{ route('citas.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div>
                    <label class="text-xs text-slate-600">Asesor</label>
                    <select name="asesor_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="todos">Todos</option>
                        @if(isset($asesores))
                            @foreach($asesores as $asesor)
                            <option value="{{ $asesor->id }}" {{ request('asesor_id') == $asesor->id ? 'selected' : '' }}>{{ $asesor->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-600">Estado</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="todos">Todos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completada</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        <option value="reprogrammed" {{ request('status') == 'reprogrammed' ? 'selected' : '' }}>Reprogramada</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-600">Fecha Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-600">Fecha Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-600">Buscar</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" placeholder="Cliente, email, propiedad..." value="{{ request('search') }}" class="flex-1 border rounded-lg px-3 py-2 text-sm">
                        <button type="submit" class="bg-mso-blue text-white px-4 rounded-lg"><i class="ph-bold ph-magnifying-glass"></i></button>
                        <a href="{{ route('citas.index') }}" class="bg-slate-200 text-slate-600 px-4 rounded-lg flex items-center"><i class="ph-bold ph-x"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="text-left px-4 py-3">Propiedad</th>
                            <th class="text-left px-4 py-3 hidden md:table-cell">Cliente</th>
                            <th class="text-left px-4 py-3">Fecha/Hora</th>
                            <th class="text-left px-4 py-3 hidden lg:table-cell">Asesor</th>
                            <th class="text-left px-4 py-3">Estado</th>
                            <th class="text-center px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                        <tr class="border-b hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @php
                                        $adminImgUrl = 'https://placehold.co/400x300/1e293b/64748b?text=Sin+Imagen';
                                        if ($appointment->property && $appointment->property->images && $appointment->property->images->count() > 0) {
                                            $primaryImg = $appointment->property->images->where('is_primary', true)->first();
                                            if ($primaryImg) {
                                                $adminImgUrl = asset('storage/' . $primaryImg->image_path);
                                            } else {
                                                $adminImgUrl = asset('storage/' . $appointment->property->images->first()->image_path);
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $adminImgUrl }}" class="w-10 h-10 rounded object-cover cursor-pointer" onclick="window.showPropertyPreview({{ $appointment->id }})">
                                    <div>
                                        <div class="font-medium cursor-pointer text-mso-blue" onclick="window.showPropertyPreview({{ $appointment->id }})">{{ $appointment->property->title ?? 'N/A' }}</div>
                                        <div class="text-xs text-slate-400 truncate max-w-[200px]">{{ $appointment->property->address ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div class="font-medium">{{ $appointment->contact_name ?? ($appointment->user->name ?? 'N/A') }}</div>
                                <div class="text-xs text-slate-400">{{ $appointment->contact_email ?? ($appointment->user->email ?? 'N/A') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('d/m/Y') : 'N/A' }}</div>
                                <div class="text-xs text-slate-400">{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('h:i A') : '' }}</div>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">{{ $appointment->asesor->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $adminBadgeClass = match($appointment->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'confirmed' => 'bg-green-100 text-green-700',
                                        'completed' => 'bg-blue-100 text-blue-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'reprogrammed' => 'bg-purple-100 text-purple-700',
                                        default => 'bg-slate-100'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs {{ $adminBadgeClass }}">{{ ucfirst($appointment->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    @can('ver propiedades')
                                        <button onclick="window.showPropertyPreview({{ $appointment->id }})" class="text-mso-blue" title="Ver propiedad">
                                            <i class="ph-bold ph-eye text-lg"></i>
                                        </button>
                                    @endcan

                                    @canany(['editar cita', 'gestionar citas'])
                                        <button onclick="window.openAdminActionModal({{ $appointment->id }})" class="text-mso-blue" title="Gestionar">
                                            <i class="ph-bold ph-gear text-lg"></i>
                                        </button>
                                    @endcanany
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-slate-400">No hay citas registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">
                {{ $appointments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL ADMIN --}}
    <div id="adminActionModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 m-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Gestionar Cita</h3>
                <button onclick="window.closeModal('adminActionModal')" class="text-slate-400"><i class="ph ph-x text-xl"></i></button>
            </div>
            <input type="hidden" id="adminAppointmentId">
            <form id="adminActionForm">
                @csrf
                <div class="bg-slate-50 p-4 rounded-lg mb-4">
                    <p class="text-sm font-semibold" id="adminModalPropertyTitle">...</p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Cambiar Estado</label>
                    <select id="adminStatusSelect" class="w-full border rounded-lg px-4 py-2.5 text-sm">
                        <option value="pending">Pendiente</option>
                        <option value="confirmed">Confirmada</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                        <option value="reprogrammed">Reprogramada</option>
                    </select>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-medium mb-1">Reprogramar</label>
                    <input type="datetime-local" id="adminRescheduleDate" class="w-full border rounded-lg px-4 py-2.5 text-sm">
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-medium mb-1">Nota</label>
                    <textarea id="adminNotes" rows="3" class="w-full border rounded-lg px-4 py-2.5 text-sm resize-none"></textarea>
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="window.closeModal('adminActionModal')" class="flex-1 px-4 py-2 border rounded-lg">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-mso-blue text-white rounded-lg">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- VISTA PARA CLIENTE - Solo para rol Cliente    --}}
    {{-- ============================================= --}}
    @hasrole('Cliente')
    <div class="bg-white rounded-2xl shadow-sm border">
        <div class="p-5 border-b">
            <h3 class="font-bold text-lg"><i class="ph-bold ph-calendar-check text-mso-gold"></i> Mis Citas</h3>
            <p class="text-sm text-slate-500">Historial de todas tus citas agendadas</p>
        </div>
        <div class="divide-y">
            @forelse($appointments as $appointment)
            <div class="p-4 hover:bg-slate-50">
                <div class="flex items-start gap-4">
                    @php
                        $clienteImgUrl = 'https://placehold.co/400x300/1e293b/64748b?text=Sin+Imagen';
                        if ($appointment->property && $appointment->property->images && $appointment->property->images->count() > 0) {
                            $primaryImg = $appointment->property->images->where('is_primary', true)->first();
                            if ($primaryImg) {
                                $clienteImgUrl = asset('storage/' . $primaryImg->image_path);
                            } else {
                                $clienteImgUrl = asset('storage/' . $appointment->property->images->first()->image_path);
                            }
                        }
                    @endphp
                    <img src="{{ $clienteImgUrl }}" class="w-12 h-12 rounded-lg object-cover cursor-pointer" onclick="window.showPropertyPreview({{ $appointment->id }})">
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-semibold cursor-pointer text-mso-blue hover:underline" onclick="window.showPropertyPreview({{ $appointment->id }})">
                                    {{ $appointment->property->title ?? 'Propiedad' }}
                                </p>
                                <p class="text-xs text-slate-500">{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('h:i A') : '--:--' }} • Asesor: {{ $appointment->asesor->name ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <div class="bg-slate-100 rounded-lg px-2 py-1 text-center min-w-[65px]">
                                    <div class="text-xs text-slate-400">{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('M') : '-' }}</div>
                                    <div class="text-lg font-bold">{{ $appointment->scheduled_date ? $appointment->scheduled_date->format('d') : '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mt-2">
                            @php
                                $clienteBadgeClass = match($appointment->status) {
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'confirmed' => 'bg-green-100 text-green-700',
                                    'completed' => 'bg-blue-100 text-blue-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'reprogrammed' => 'bg-purple-100 text-purple-700',
                                    default => 'bg-slate-100'
                                };
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $clienteBadgeClass }}">{{ ucfirst($appointment->status) }}</span>
                            <button onclick="window.showClientDetailModal({{ $appointment->id }})" class="text-xs text-mso-blue hover:text-mso-gold">
                                Ver detalles <i class="ph-bold ph-arrow-right"></i>
                            </button>
                            <button onclick="window.showPropertyPreview({{ $appointment->id }})" class="text-xs text-mso-blue hover:text-mso-gold">
                                Ver propiedad <i class="ph-bold ph-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-slate-400">
                <i class="ph-bold ph-calendar-x text-4xl mb-3 block"></i>
                <p>No tienes citas agendadas</p>
                <a href="{{ route('catalogo.index') }}" class="inline-block mt-4 bg-mso-blue text-white px-4 py-2 rounded-lg text-sm">Buscar Propiedades</a>
            </div>
            @endforelse
        </div>
        <div class="p-4 border-t">
            {{ $appointments->links() }}
        </div>
    </div>
    @endhasrole

@else
    {{-- ============================================= --}}
    {{-- MENSAJE DE ACCESO DENEGADO - Solo si NO tiene permisos --}}
    {{-- ============================================= --}}
    <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
        <i class="ph ph-lock-simple text-3xl mb-2 block"></i>
        <p class="font-bold">Acceso Denegado</p>
        <p class="text-sm">No tienes permisos para ver las citas.</p>
    </div>
@endcanany

@endsection

@push('js')
<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
var allAppointments = @json($appointmentsData);
var currentUserRole = '{{ $primaryRole }}';
var isAsesor = {{ $isAsesor ? 'true' : 'false' }};
var isAdmin = {{ $isAdmin ? 'true' : 'false' }};
var isCliente = {{ $isCliente ? 'true' : 'false' }};

// ============================================
// FUNCIONES UTILITARIAS
// ============================================
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-mso-blue';
    toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full`;
    toast.innerHTML = `<i class="ph ${type === 'success' ? 'ph-check-circle' : type === 'error' ? 'ph-warning-circle' : 'ph-info'} text-lg"></i><span class="text-sm">${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function closeModal(modalId) {
    const el = document.getElementById(modalId);
    if(el) el.classList.add('hidden');
}

function reloadPageWithDelay(delay = 1500) {
    setTimeout(() => location.reload(), delay);
}

// ============================================
// VISTA PREVIA DE PROPIEDAD
// ============================================
window.showPropertyPreview = function(appointmentId) {
    const appointment = allAppointments.find(a => a.id === appointmentId);
    if (!appointment) {
        showToast('No se encontró información de la cita', 'error');
        return;
    }

    if (!appointment.has_property || !appointment.property_id) {
        showToast('La propiedad asociada a esta cita ya no está disponible', 'error');
        document.getElementById('previewContent').innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="ph-bold ph-warning-circle text-4xl mb-3 block"></i>
                <p>La propiedad asociada a esta cita ya no está disponible o fue eliminada.</p>
                <button onclick="window.closeModal('propertyPreviewModal')" class="mt-4 px-4 py-2 bg-mso-blue text-white rounded-lg">Cerrar</button>
            </div>
        `;
        document.getElementById('previewPropertyTitle').innerText = 'Propiedad no disponible';
        document.getElementById('propertyPreviewModal').classList.remove('hidden');
        return;
    }

    const statusLabels = {
        'pending': 'Pendiente',
        'confirmed': 'Confirmada',
        'completed': 'Completada',
        'cancelled': 'Cancelada',
        'reprogrammed': 'Reprogramada'
    };
    const statusLabel = statusLabels[appointment.status] || appointment.status;

    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-green-100 text-green-800',
        'completed': 'bg-blue-100 text-blue-800',
        'cancelled': 'bg-red-100 text-red-800',
        'reprogrammed': 'bg-purple-100 text-purple-800'
    };
    const statusColor = statusColors[appointment.status] || 'bg-slate-100 text-slate-800';

    const propertyUrl = `/catalogo/${appointment.property_id}`;

    const modalContent = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <img src="${appointment.property_image}"
                     alt="${escapeHtml(appointment.title)}"
                     class="w-full rounded-xl shadow-lg object-cover aspect-video"
                     onerror="this.src='https://placehold.co/400x300/1e293b/64748b?text=Sin+Imagen'">
                <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="text-xs text-slate-500">Tipo</p>
                        <p class="font-medium">${appointment.property_type === 'venta' ? ' Venta' : (appointment.property_type === 'alquiler' ? ' Alquiler' : ' Venta/Alquiler')}</p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="text-xs text-slate-500">Área</p>
                        <p class="font-medium">${appointment.property_area || 'N/A'} m²</p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="text-xs text-slate-500">Precio</p>
                        <p class="font-medium text-mso-gold">${appointment.property_price}</p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="text-xs text-slate-500">Ubicación</p>
                        <p class="font-medium text-sm">${escapeHtml(appointment.address)}</p>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-xl font-bold text-slate-800 mb-2">${escapeHtml(appointment.title)}</h4>
                <div class="flex items-center gap-2 text-slate-500 mb-4">
                    <i class="ph ph-map-pin"></i>
                    <span class="text-sm">${escapeHtml(appointment.address)}</span>
                </div>
                <div class="border-t border-slate-200 pt-4 mb-4">
                    <h5 class="font-semibold text-slate-700 mb-2">Información de la Cita</h5>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-slate-500">Cliente:</span> ${escapeHtml(appointment.client)}</p>
                        <p><span class="text-slate-500">Email:</span> ${escapeHtml(appointment.client_email)}</p>
                        <p><span class="text-slate-500">Teléfono:</span> ${escapeHtml(appointment.client_phone)}</p>
                        <p><span class="text-slate-500">Fecha de cita:</span> ${appointment.date_display} - ${appointment.time} hrs</p>
                        <p><span class="text-slate-500">Asesor:</span> ${escapeHtml(appointment.asesor)}</p>
                        <p><span class="text-slate-500">Estado:</span> <span class="px-2 py-0.5 rounded-full text-xs ${statusColor}">${statusLabel}</span></p>
                        ${appointment.notes ? `<p><span class="text-slate-500">Notas:</span> ${escapeHtml(appointment.notes)}</p>` : ''}
                    </div>
                </div>
                <div class="flex gap-3 mt-4">
                    <a href="${propertyUrl}" target="_blank"
                       class="flex-1 bg-mso-blue text-white text-center py-2 rounded-lg hover:bg-slate-800 transition-colors">
                        <i class="ph-bold ph-arrow-square-out"></i> Ver propiedad completa
                    </a>
                    <button onclick="window.closeModal('propertyPreviewModal')"
                            class="flex-1 px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('previewPropertyTitle').innerText = appointment.title;
    document.getElementById('previewContent').innerHTML = modalContent;
    document.getElementById('propertyPreviewModal').classList.remove('hidden');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// ============================================
// LÓGICA PARA ASESOR
// ============================================
if (isAsesor) {
    window.currentCalendarDate = new Date();

    window.renderAsesorCalendar = function() {
        const calendarDiv = document.getElementById('asesorCalendar');
        if (!calendarDiv) return;

        const year = window.currentCalendarDate.getFullYear();
        const month = window.currentCalendarDate.getMonth();
        const monthNames = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

        let html = `<div class="flex items-center justify-between mb-2 text-xs font-bold text-slate-700">`;
        html += `<span>${monthNames[month]} ${year}</span></div>`;
        html += `<div class="grid grid-cols-7 gap-1 text-center text-[10px] font-medium text-slate-400 mb-1">`;
        html += `<span>D</span><span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span>`;
        html += `</div><div class="grid grid-cols-7 gap-1">`;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();

        for (let i = 0; i < firstDay; i++) {
            html += '<div class="h-8"></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const hasEvent = allAppointments.some(a => a.date === dateStr);
            const isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();

            let additionalClass = 'h-8 flex flex-col items-center justify-center rounded text-xs cursor-pointer hover:bg-slate-100 transition-colors relative';
            if (isToday) additionalClass += ' bg-mso-gold text-white font-bold hover:bg-yellow-600';

            html += `<div class="${additionalClass}" onclick="window.filterByDate('${dateStr}')">`;
            html += `<span>${day}</span>`;
            if (hasEvent) {
                html += `<span class="absolute bottom-1 w-1 h-1 rounded-full ${isToday ? 'bg-white' : 'bg-mso-gold'}"></span>`;
            }
            html += '</div>';
        }
        html += '</div>';
        calendarDiv.innerHTML = html;
    }

    window.filterByDate = function(dateStr) {
        const items = document.querySelectorAll('.appointment-item');
        let visibleCount = 0;
        items.forEach(item => {
            const appointment = allAppointments.find(a => a.id == item.dataset.id);
            if (appointment && appointment.date === dateStr) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            showToast('No hay citas para esta fecha', 'info');
        } else {
            showToast(`${visibleCount} cita(s) encontrada(s)`, 'success');
        }
    }

    window.filterByStatus = function(status) {
        document.querySelectorAll('.chip-filter').forEach(btn => {
            btn.classList.remove('bg-mso-gold', 'text-white');
            btn.classList.add('bg-white', 'text-slate-600');
        });
        const activeBtn = document.querySelector(`.chip-filter[data-status="${status}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-slate-600');
            activeBtn.classList.add('bg-mso-gold', 'text-white');
        }

        const items = document.querySelectorAll('.appointment-item');
        let visibleCount = 0;
        items.forEach(item => {
            if (status === 'all' || item.dataset.status === status) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
    }

    window.openActionModal = function(appointmentId) {
        const appointment = allAppointments.find(a => a.id === appointmentId);
        if (!appointment) return;

        document.getElementById('modalPropertyTitle').innerHTML = `<a href="javascript:void(0)" onclick="window.showPropertyPreview(${appointmentId})" class="hover:text-mso-gold">${escapeHtml(appointment.title)}</a>`;
        document.getElementById('modalClientInfo').innerHTML = `Cliente: ${escapeHtml(appointment.client)}<br>Email: ${escapeHtml(appointment.client_email)}<br>Tel: ${escapeHtml(appointment.client_phone)}`;
        document.getElementById('modalDateTime').innerHTML = `Fecha: ${appointment.date_display} - ${appointment.time} hrs`;
        document.getElementById('actionAppointmentId').value = appointmentId;
        document.getElementById('statusSelect').value = appointment.status;
        document.getElementById('rescheduleDateInput').value = '';
        document.getElementById('actionMessage').value = appointment.notes || '';

        document.getElementById('actionModal').classList.remove('hidden');
    }

    window.changeMonth = function(step) {
        window.currentCalendarDate.setMonth(window.currentCalendarDate.getMonth() + step);
        window.renderAsesorCalendar();
    }

    window.resetToToday = function() {
        window.currentCalendarDate = new Date();
        window.renderAsesorCalendar();
    }

    function updateCounts() {
        const counts = { all: 0, pending: 0, confirmed: 0, completed: 0, cancelled: 0, reprogrammed: 0 };
        allAppointments.forEach(app => {
            counts.all++;
            if (counts[app.status] !== undefined) counts[app.status]++;
        });
        const elements = ['count-all', 'count-pending', 'count-confirmed', 'count-completed', 'count-cancelled', 'count-reprogrammed'];
        elements.forEach(el => {
            const elem = document.getElementById(el);
            if (elem) elem.innerText = counts[el.replace('count-', '')] || 0;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.renderAsesorCalendar();
        updateCounts();
    });
}

// ============================================
// LÓGICA PARA ADMIN
// ============================================
if (isAdmin) {
    window.openAdminActionModal = function(appointmentId) {
        const appointment = allAppointments.find(a => a.id === appointmentId);
        if (!appointment) return;

        document.getElementById('adminModalPropertyTitle').innerHTML = `<a href="javascript:void(0)" onclick="window.showPropertyPreview(${appointmentId})" class="hover:text-mso-gold">${escapeHtml(appointment.title)}</a>`;
        document.getElementById('adminAppointmentId').value = appointmentId;
        document.getElementById('adminStatusSelect').value = appointment.status;
        document.getElementById('adminRescheduleDate').value = '';
        document.getElementById('adminNotes').value = appointment.notes || '';

        document.getElementById('adminActionModal').classList.remove('hidden');
    }

    document.getElementById('adminActionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const appointmentId = document.getElementById('adminAppointmentId').value;
        const status = document.getElementById('adminStatusSelect').value;
        const newDate = document.getElementById('adminRescheduleDate').value;
        const notes = document.getElementById('adminNotes').value;
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Procesando...';

        fetch(`/citas/${appointmentId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);
            if (newDate) {
                const formattedDate = newDate.replace('T', ' ') + ':00';
                return fetch(`/citas/${appointmentId}/reschedule`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({ scheduled_date: formattedDate, notes: notes || null })
                }).then(res => res.json());
            } else if (notes && notes !== (allAppointments.find(a => a.id == appointmentId)?.notes || '')) {
                return fetch(`/citas/${appointmentId}/reschedule`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({ notes: notes })
                }).then(res => res.json());
            }
            return { success: true };
        })
        .then(() => {
            showToast('Cita actualizada correctamente', 'success');
            window.closeModal('adminActionModal');
            reloadPageWithDelay();
        })
        .catch(error => {
            showToast(error.message || 'Error al procesar', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
}

// ============================================
// MODAL CLIENTE (DETALLES)
// ============================================
window.showClientDetailModal = function(appointmentId) {
    const appointment = allAppointments.find(a => a.id === appointmentId);
    if (!appointment) return;

    const statusLabels = {
        'pending': 'Pendiente', 'confirmed': 'Confirmada', 'completed': 'Completada',
        'cancelled': 'Cancelada', 'reprogrammed': 'Reprogramada'
    };
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800', 'confirmed': 'bg-green-100 text-green-800',
        'completed': 'bg-blue-100 text-blue-800', 'cancelled': 'bg-red-100 text-red-800',
        'reprogrammed': 'bg-purple-100 text-purple-800'
    };

    const modalContent = `
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 rounded-lg ${statusColors[appointment.status] || 'bg-slate-100'}">
                <span class="font-semibold">Estado: ${statusLabels[appointment.status] || appointment.status}</span>
                <button onclick="window.showPropertyPreview(${appointmentId})" class="text-mso-blue hover:underline text-sm">Ver propiedad <i class="ph-bold ph-arrow-right"></i></button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="text-xs text-slate-500">Propiedad</label><p class="font-medium">${escapeHtml(appointment.title)}</p></div>
                <div><label class="text-xs text-slate-500">Dirección</label><p class="text-sm">${escapeHtml(appointment.address)}</p></div>
                <div><label class="text-xs text-slate-500">Fecha</label><p class="font-medium">${appointment.date_display}</p></div>
                <div><label class="text-xs text-slate-500">Hora</label><p class="font-medium">${appointment.time} hrs</p></div>
                <div><label class="text-xs text-slate-500">Asesor</label><p>${escapeHtml(appointment.asesor)}</p></div>
            </div>
            <div><label class="text-xs text-slate-500">Notas</label><p class="text-sm bg-slate-50 p-2 rounded">${escapeHtml(appointment.notes || 'Sin notas adicionales')}</p></div>
        </div>
    `;

    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 m-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-slate-800">Detalle de la Cita</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-slate-400"><i class="ph ph-x text-xl"></i></button>
            </div>
            <div class="space-y-4">${modalContent}</div>
            <div class="mt-4 pt-4 border-t flex gap-3">
                <button onclick="window.showPropertyPreview(${appointmentId});this.closest('.fixed').remove();" class="flex-1 px-4 py-2 bg-mso-blue text-white rounded-lg">Ver Propiedad</button>
                <button onclick="this.closest('.fixed').remove()" class="flex-1 px-4 py-2 bg-slate-100 text-slate-600 rounded-lg">Cerrar</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// ============================================
// MANEJO DE FORMULARIOS ASESOR
// ============================================
document.getElementById('actionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const appointmentId = document.getElementById('actionAppointmentId').value;
    const newStatus = document.getElementById('statusSelect').value;
    const newDate = document.getElementById('rescheduleDateInput').value;
    const message = document.getElementById('actionMessage').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Procesando...';

    const formattedDate = newDate ? newDate.replace('T', ' ') + ':00' : null;

    fetch(`/citas/${appointmentId}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) throw new Error(data.message);
        if (formattedDate) {
            return fetch(`/citas/${appointmentId}/reschedule`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ scheduled_date: formattedDate, notes: message || null })
            }).then(res => res.json());
        } else if (message && message.trim() !== '') {
            return fetch(`/citas/${appointmentId}/reschedule`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ notes: message })
            }).then(res => res.json());
        }
        return { success: true };
    })
    .then(() => {
        showToast('Cita actualizada correctamente', 'success');
        window.closeModal('actionModal');
        reloadPageWithDelay();
    })
    .catch(error => {
        showToast(error.message || 'Error al procesar', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});
</script>
@endpush
