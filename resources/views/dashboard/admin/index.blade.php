{{-- Dashboard principal del Administrador --}}
@extends('layouts.dashboard')

@section('title', 'Panel Administrador')
@section('header', 'Panel de Administrador')

@section('content')
<div class="space-y-4 sm:space-y-6">

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-6">

        {{-- Total Propiedades --}}
        <div class="bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph ph-buildings text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-green-500 bg-green-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">+12% este mes</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($totalProperties ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Total Propiedades</p>
        </div>

        {{-- Total Usuarios --}}
        <div class="bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph ph-users-three text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-slate-500 bg-slate-100 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">Registrados</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($totalUsers ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Usuarios Activos</p>
        </div>

        {{-- Citas Pendientes --}}
        <div class="bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph ph-clock text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-red-500 bg-red-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">Atención</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($pendingAppointments ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Citas Pendientes</p>
        </div>

        {{-- Propiedades Publicadas --}}
        <div class="bg-white p-3 sm:p-4 md:p-6 rounded-xl md:rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-lg md:rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph ph-house-line text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <span class="text-[8px] sm:text-[10px] md:text-xs font-semibold text-emerald-600 bg-emerald-50 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full whitespace-nowrap">Activas</span>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-800">{{ number_format($publishedProperties ?? 0) }}</h3>
            <p class="text-[10px] sm:text-xs md:text-sm text-slate-500 mt-0.5 sm:mt-1">Publicadas</p>
        </div>

    </div>


    <div class="space-y-4 sm:space-y-6">

        {{-- ============================================ --}}
        {{-- CITAS RECIENTES --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="text-sm sm:text-base md:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-calendar-check text-mso-gold text-base sm:text-lg md:text-xl"></i>
                    Citas Recientes
                </h3>
                <a href="{{ route('citas.index') }}" class="text-[10px] sm:text-xs md:text-sm text-mso-blue hover:underline font-medium flex items-center gap-1">
                    Ver todas
                    <i class="ph ph-arrow-right text-[10px] sm:text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                @forelse($recentAppointments ?? [] as $appointment)
                    <div class="flex items-center justify-between p-2 sm:p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                        <div class="flex items-center gap-2 sm:gap-3 overflow-hidden min-w-0">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-calendar text-lg sm:text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] sm:text-xs md:text-sm font-bold text-slate-800 truncate">{{ $appointment->property->title ?? 'Sin Propiedad' }}</p>
                                <p class="text-[8px] sm:text-[10px] md:text-xs text-slate-500 truncate">{{ $appointment->user->full_name ?? 'Cliente' }}</p>
                            </div>
                        </div>
                        <span class="text-[7px] sm:text-[8px] md:text-xs font-medium px-1 sm:px-2 py-0.5 sm:py-1 rounded bg-slate-100 text-slate-600 flex-shrink-0 whitespace-nowrap ml-1 sm:ml-2">
                            {{ $appointment->scheduled_date->format('d/m') }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs sm:text-sm text-slate-500 col-span-1 sm:col-span-2 lg:col-span-3 text-center py-4">No hay citas recientes.</p>
                @endforelse
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PROPIEDADES RECIENTES --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 sm:mb-4">
                <h3 class="text-sm sm:text-base md:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-buildings text-mso-gold text-base sm:text-lg md:text-xl"></i>
                    Propiedades Recientes
                </h3>
                <a href="{{ route('admin.properties.index') }}" class="text-[10px] sm:text-xs md:text-sm text-mso-blue hover:underline font-medium flex items-center gap-1">
                    Ver todas
                    <i class="ph ph-arrow-right text-[10px] sm:text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                @forelse($recentProperties ?? [] as $property)
                    <div class="property-item flex gap-2 sm:gap-3 p-2 rounded-lg border border-slate-100 hover:border-mso-gold transition-colors cursor-pointer">
                        <img src="{{ $property->primary_image_url ?? asset('images/placeholder.jpg') }}"
                             class="w-14 h-12 sm:w-20 sm:h-16 rounded-lg object-cover flex-shrink-0"
                             alt="{{ $property->title }}">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[10px] sm:text-xs md:text-sm font-bold text-slate-800 truncate">{{ Str::limit($property->title, 30) }}</h4>
                            <p class="text-[10px] sm:text-xs text-mso-gold font-bold">{{ $property->formatted_price ?? 'Consultar' }}</p>
                            <p class="text-[8px] sm:text-[9px] md:text-[10px] text-slate-400 mt-0.5 truncate">{{ $property->city ?? 'Sin ubicación' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs sm:text-sm text-slate-500 col-span-1 sm:col-span-2 lg:col-span-3 text-center py-4">No hay propiedades recientes.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
