{{-- Dashboard genérico para roles sin dashboard específico --}}
@extends('layouts.dashboard')

@section('title', 'Panel de Control')
@section('header', 'Bienvenido a tu Panel de Control')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Mensaje de bienvenida --}}
    <div class="bg-gradient-to-r from-mso-blue to-mso-blue/80 rounded-2xl shadow-lg p-8 mb-8 text-white">
        <h2 class="text-2xl font-bold mb-2">¡Bienvenido, {{ Auth::user()->full_name }}!</h2>
        <p class="text-blue-100">
            Este es tu panel de control personalizado. Aquí encontrarás un resumen de tu actividad en la plataforma.
        </p>
        @if(!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin() && !Auth::user()->isAsesor() && !Auth::user()->isAuditor() && !Auth::user()->isCliente())
            <div class="mt-3 inline-block bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg">
                <span class="text-sm">Rol: <strong>{{ Auth::user()->main_role }}</strong></span>
            </div>
        @endif
    </div>

    {{-- Grid de estadísticas dinámicas según permisos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        @can('ver propiedades')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Propiedades</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['total_properties'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="ph ph-buildings text-2xl text-blue-500"></i>
                </div>
            </div>
            <a href="{{ route('catalogo.index') }}" class="text-sm text-mso-gold hover:text-mso-blue mt-3 inline-block">
                Ver catálogo →
            </a>
        </div>
        @endcan

        @can('ver leads')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Leads</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['total_leads'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                    <i class="ph ph-users-three text-2xl text-purple-500"></i>
                </div>
            </div>
            @can('ver leads')
            <a href="{{ route('leads.index') }}" class="text-sm text-mso-gold hover:text-mso-blue mt-3 inline-block">
                Gestionar leads →
            </a>
            @endcan
        </div>
        @endcan

        @can('ver citas')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Citas</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['total_appointments'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <i class="ph ph-calendar-check text-2xl text-green-500"></i>
                </div>
            </div>
            <a href="{{ route('citas.index') }}" class="text-sm text-mso-gold hover:text-mso-blue mt-3 inline-block">
                Ver citas →
            </a>
        </div>
        @endcan

        @can('ver usuarios')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Usuarios</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['total_users'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="ph ph-users text-2xl text-amber-500"></i>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-mso-gold hover:text-mso-blue mt-3 inline-block">
                Gestionar usuarios →
            </a>
        </div>
        @endcan

    </div>

    {{-- Acciones rápidas según permisos --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="ph ph-lightning text-mso-gold"></i>
            Acciones Rápidas
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            @can('crear propiedad')
            <a href="{{ route('admin.properties.create') }}"
               class="flex flex-col items-center p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                <i class="ph ph-plus-circle text-2xl text-mso-gold"></i>
                <span class="text-sm font-medium mt-2">Nueva Propiedad</span>
            </a>
            @endcan

            @can('crear lead')
            <a href="{{ route('leads.index') }}"
               class="flex flex-col items-center p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                <i class="ph ph-user-plus text-2xl text-blue-500"></i>
                <span class="text-sm font-medium mt-2">Nuevo Lead</span>
            </a>
            @endcan

            @can('crear cita')
            <a href="{{ route('citas.index') }}"
               class="flex flex-col items-center p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                <i class="ph ph-calendar-plus text-2xl text-green-500"></i>
                <span class="text-sm font-medium mt-2">Agendar Cita</span>
            </a>
            @endcan

            <a href="{{ route('catalogo.index') }}"
               class="flex flex-col items-center p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                <i class="ph ph-magnifying-glass text-2xl text-purple-500"></i>
                <span class="text-sm font-medium mt-2">Explorar Catálogo</span>
            </a>

        </div>
    </div>

    {{-- Actividad reciente --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        @can('ver propiedades')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ph ph-buildings text-mso-gold"></i>
                Propiedades Recientes
            </h4>
            <div class="space-y-3">
                @forelse($recentProperties as $property)
                <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg transition-colors">
                    <img src="{{ $property->primary_image_url }}"
                         class="w-12 h-12 rounded-lg object-cover"
                         alt="{{ $property->title }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $property->title }}</p>
                        <p class="text-xs text-slate-500">{{ $property->formatted_price }}</p>
                    </div>
                    <a href="{{ route('catalogo.show', $property) }}" class="text-mso-gold hover:text-mso-blue">
                        <i class="ph ph-eye"></i>
                    </a>
                </div>
                @empty
                <p class="text-slate-500 text-sm">No hay propiedades recientes.</p>
                @endforelse
            </div>
        </div>
        @endcan

        @can('ver citas')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ph ph-calendar-check text-green-500"></i>
                Próximas Citas
            </h4>
            <div class="space-y-3">
                @forelse($upcomingAppointments as $appointment)
                <div class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg transition-colors">
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="ph ph-calendar text-green-500 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">
                            {{ $appointment->property->title ?? 'Sin propiedad' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full
                        @if($appointment->status == 'pending') bg-yellow-100 text-yellow-700
                        @else bg-green-100 text-green-700 @endif">
                        {{ ucfirst($appointment->status) }}
                    </span>
                </div>
                @empty
                <p class="text-slate-500 text-sm">No hay citas próximas.</p>
                @endforelse
            </div>
        </div>
        @endcan

    </div>
</div>
@endsection
