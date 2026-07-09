@extends('layouts.dashboard')

@section('title', 'Panel Administrador')
@section('header', 'Panel de Administrador')

@section('content')
    <!-- Contenedor Principal con Ancho Completo -->
    <div class="w-full px-4 md:px-8 space-y-6">

        <!-- 1. Tarjetas de Métricas (KPIs) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Total Propiedades -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph ph-buildings text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded-full">Inventario</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800">{{ $totalProperties ?? 0 }}</h3>
                <p class="text-sm text-slate-500 mt-1">Total Propiedades</p>
            </div>

            <!-- Total Usuarios -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph ph-users-three text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-blue-500 bg-blue-50 px-2 py-1 rounded-full">Registrados</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800">{{ $totalUsers ?? 0 }}</h3>
                <p class="text-sm text-slate-500 mt-1">Usuarios Activos</p>
            </div>

            <!-- Citas Pendientes (Prioridad para Admin) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph ph-clock text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-red-500 bg-red-50 px-2 py-1 rounded-full">Atención</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800">{{ $pendingAppointments ?? 0 }}</h3>
                <p class="text-sm text-slate-500 mt-1">Citas Pendientes</p>
            </div>

            <!-- Propiedades Publicadas -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph ph-house-line text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Activas</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800">{{ $publishedProperties ?? 0 }}</h3>
                <p class="text-sm text-slate-500 mt-1">Publicadas</p>
            </div>

        </div>

        <!-- 2. Contenido Inferior (Listas Recientes) -->
        <div class="space-y-6">

            <!-- Citas Recientes (Para Admin es más útil que Usuarios recientes) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Citas Recientes</h3>
                    <a href="#" class="text-sm text-mso-blue hover:underline">Ver calendario</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($recentAppointments ?? [] as $appointment)
                        <div class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0">
                                    <i class="ph ph-calendar text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ $appointment->property->title ?? 'Sin Propiedad' }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $appointment->user->name ?? 'Cliente' }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium px-2 py-1 rounded bg-slate-100 text-slate-600 flex-shrink-0 whitespace-nowrap">
                                {{ $appointment->scheduled_date->format('d/m') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 col-span-3 text-center py-4">No hay citas recientes.</p>
                    @endforelse
                </div>
            </div>

            <!-- Propiedades Recientes -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Propiedades Recientes</h3>
                    <a href="{{ route('admin.properties.index') }}" class="text-sm text-mso-blue hover:underline">Ver todas</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($recentProperties ?? [] as $property)
                        <div class="flex gap-3 p-2 rounded-lg border border-slate-100 hover:border-mso-gold transition-colors cursor-pointer">
                            <img src="{{ $property->primary_image_url ?? asset('images/placeholder.jpg') }}" class="w-20 h-16 rounded-lg object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 truncate">{{ $property->title }}</h4>
                                <p class="text-xs text-mso-gold font-bold">{{ $property->formatted_price ?? 'Consultar' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">{{ $property->city ?? 'Sin ubicación' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 col-span-3 text-center py-4">No hay propiedades recientes.</p>
                    @endforelse
                </div>
            </div>

            <!-- Sección Incrustada de Usuarios (Oculta por defecto o al final) -->
            <!-- Nota: Mantengo tu include por si lo usas en la pestaña, pero visualmente ahora el dashboard se ve mejor arriba -->
            <div id="usuarios" class="dashboard-section hidden">
                 @include('dashboard.admin.users.index', [
                    'users' => $users,
                    'roles' => $roles,
                    'countries' => $countries,
                    'embedded' => true
                ])
            </div>

        </div>

    </div>
@endsection
