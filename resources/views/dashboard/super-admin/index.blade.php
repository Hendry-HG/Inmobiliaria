@extends('layouts.dashboard')

@section('title', 'Panel Super Admin')
@section('header', 'Panel de Control General')

@section('content')
    <!-- CAMBIO: w-full px-4 md:px-8 en lugar de max-w-7xl para ancho completo -->
    <div class="w-full px-4 md:px-8 space-y-6">

        <!-- 2. Tarjetas de Métricas (KPIs) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Propiedades -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph ph-buildings text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-green-500 bg-green-50 px-2 py-1 rounded-full">+12% este mes</span>
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
                    <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded-full">Registrados</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800">{{ $totalUsers ?? 0 }}</h3>
                <p class="text-sm text-slate-500 mt-1">Usuarios Activos</p>
            </div>

            <!-- Citas Hoy -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph ph-calendar-check text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-mso-gold bg-yellow-50 px-2 py-1 rounded-full">Hoy</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800">{{ $todayAppointments ?? 0 }}</h3>
                <p class="text-sm text-slate-500 mt-1">Citas Programadas</p>
            </div>

            <!-- Leads Activos -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph ph-chart-line-up text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Oportunidad</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800">{{ $activeLeads ?? 0 }}</h3>
                <p class="text-sm text-slate-500 mt-1">Leads Activos</p>
            </div>
        </div>

        <!-- 3. Contenido Inferior (ANCHO COMPLETO) -->
        <div class="space-y-6">

            {{-- ============================================ --}}
            {{-- USUARIOS RECIENTES (CON APELLIDO) --}}
            {{-- ============================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Usuarios Recientes</h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-mso-blue hover:underline">Ver todos</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($recentUsers ?? [] as $recentUser)
                        <div class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <img src="{{ $recentUser->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                     class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                     alt="{{ $recentUser->full_name }}">
                                <div class="min-w-0">
                                    {{--  USAR full_name EN LUGAR DE name --}}
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ $recentUser->full_name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $recentUser->email }}</p>
                                    {{-- Mostrar el rol si existe --}}
                                    @if($recentUser->main_role)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600 inline-block mt-0.5">
                                            {{ $recentUser->main_role }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs font-medium px-2 py-1 rounded bg-slate-100 text-slate-600 flex-shrink-0 whitespace-nowrap ml-2">
                                {{ $recentUser->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 col-span-3 text-center py-4">No hay usuarios recientes.</p>
                    @endforelse
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- PROPIEDADES RECIENTES --}}
            {{-- ============================================ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Propiedades Agregadas Recientemente</h3>
                    <a href="#" class="text-sm text-mso-blue hover:underline">Ver todas</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($recentProperties ?? [] as $property)
                        <div class="flex gap-3 p-2 rounded-lg border border-slate-100 hover:border-mso-gold transition-colors cursor-pointer">
                            <img src="{{ $property->primary_image_url ?? asset('images/placeholder.jpg') }}"
                                 class="w-20 h-16 rounded-lg object-cover flex-shrink-0"
                                 alt="{{ $property->title }}">
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

        </div>

    </div>
@endsection
