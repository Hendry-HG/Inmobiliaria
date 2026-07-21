@extends('layouts.dashboard')

@section('title', 'Mi Panel')
@section('header', 'Resumen')

@section('content')
    <!-- SECCIÓN DASHBOARD (RESUMEN) -->
    <div id="dashboard" class="dashboard-section space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-mso-blue flex items-center justify-center text-2xl">
                    <i class="ph-fill ph-calendar"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Próxima Cita</p>
                    @if($nextAppointment)
                        <p class="text-slate-900 font-bold text-lg">{{ $nextAppointment->scheduled_date->format('d M, h:i A') }}</p>
                        <p class="text-xs text-slate-400">{{ $nextAppointment->property->title }}</p>
                    @else
                        <p class="text-slate-900 font-bold text-lg">No hay citas</p>
                        <p class="text-xs text-slate-400">Agenda una visita</p>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full bg-gold-50 text-mso-gold flex items-center justify-center text-2xl">
                    <i class="ph-fill ph-heart"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Favoritos</p>
                    <p class="text-slate-900 font-bold text-lg">{{ $favoritesCount }} Propiedades</p>
                    <button onclick="showSection('favoritos')" class="text-xs text-mso-gold hover:underline">Ver todos</button>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-2xl">
                    <i class="ph-fill ph-check-circle"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Estado</p>
                    <p class="text-slate-900 font-bold text-lg">Activo</p>
                    <p class="text-xs text-slate-400">Miembro desde {{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Banner Promocional -->
<div class="bg-gradient-to-r from-mso-blue to-slate-800 rounded-2xl p-8 text-white relative overflow-hidden">
    <div class="relative z-10 max-w-lg">
        <h3 class="font-serif text-2xl font-bold mb-2">¿Buscas algo específico?</h3>
        <p class="text-slate-300 text-sm mb-6">Nuestros asesores pueden realizar búsquedas personalizadas para ti.</p>
        <a href="{{ route('chat.index') }}" 
           class="inline-block bg-mso-gold text-slate-900 px-6 py-2 rounded-lg text-sm font-bold hover:bg-white transition-colors">
            <i class="ph ph-chat-circle mr-2"></i>
            Contactar Asesor
        </a>
    </div>
    <i class="ph ph-house-line absolute -bottom-4 -right-4 text-9xl text-white/5 rotate-12"></i>
</div>
        <!-- Propiedades Recientemente Vistas -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-serif text-xl font-bold text-slate-800 mb-4">Propiedades Recientes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($recentProperties as $property)
                    <div class="border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                        <img src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="w-full h-40 object-cover">
                        <div class="p-3">
                            <h4 class="font-bold text-slate-800 truncate">{{ $property->title }}</h4>
                            <p class="text-mso-gold font-bold">{{ $property->formatted_price }}</p>
                            <a href="#" class="text-xs text-mso-blue hover:underline">Ver detalles →</a>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 col-span-3 text-center py-8">No has visto propiedades recientemente. ¡Explora nuestro catálogo!</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SECCIÓN CITAS -->
    <div id="citas" class="dashboard-section hidden space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-serif text-xl font-bold text-slate-800 mb-4">Mis Citas</h3>
            <div class="space-y-3">
                @forelse($appointments as $appointment)
                    <div class="border-b border-slate-100 pb-3 last:border-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-slate-800">{{ $appointment->property->title }}</p>
                                <p class="text-sm text-slate-500">{{ $appointment->scheduled_date->format('d/m/Y h:i A') }}</p>
                                <p class="text-sm text-slate-500">Asesor: {{ $appointment->asesor->name }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($appointment->status == 'confirmed') bg-green-100 text-green-700
                                @elseif($appointment->status == 'pending') bg-yellow-100 text-yellow-700
                                @elseif($appointment->status == 'cancelled') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-8">No tienes citas programadas.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SECCIÓN FAVORITOS -->
    <div id="favoritos" class="dashboard-section hidden space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-serif text-xl font-bold text-slate-800 mb-4">Mis Favoritos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($favoriteProperties as $property)
                    <div class="border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                        <img src="{{ $property->primary_image_url }}" alt="{{ $property->title }}" class="w-full h-40 object-cover">
                        <div class="p-3">
                            <h4 class="font-bold text-slate-800 truncate">{{ $property->title }}</h4>
                            <p class="text-mso-gold font-bold">{{ $property->formatted_price }}</p>
                            <a href="#" class="text-xs text-mso-blue hover:underline">Ver detalles →</a>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 col-span-3 text-center py-8">No tienes propiedades favoritas. ¡Explora nuestro catálogo!</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SECCIÓN CATÁLOGO -->
    <div id="catalogo" class="dashboard-section hidden">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-serif text-xl font-bold text-slate-800 mb-4">Catálogo de Propiedades</h3>
            <p class="text-slate-500 mb-4">Explora todas nuestras propiedades disponibles</p>
            <a href="#" class="inline-block bg-mso-blue text-white px-6 py-2 rounded-lg hover:bg-slate-800 transition-colors">
                Ver Catálogo Completo
            </a>
        </div>
    </div>

    <!-- SECCIÓN CHAT -->
    <div id="chat" class="dashboard-section hidden space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-serif text-xl font-bold text-slate-800 mb-4">Chat con Asesor</h3>
            <div class="h-96 bg-slate-50 rounded-lg p-4 overflow-y-auto">
                <p class="text-center text-slate-500">Selecciona un asesor para iniciar una conversación</p>
            </div>
        </div>
    </div>

    <!-- SECCIÓN PERFIL -->
    <div id="perfil" class="dashboard-section hidden space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-serif text-xl font-bold text-slate-800 mb-4">Mi Perfil</h3>
            <div class="flex items-center gap-6 mb-6">
                <img src="{{ $user->profile_photo_url ?? asset('images/default-avatar.png') }}" class="w-24 h-24 rounded-full object-cover border-4 border-mso-gold">
                <div>
                    <h4 class="text-xl font-bold text-slate-800">{{ $user->name }}</h4>
                    <p class="text-slate-500">{{ $user->email }}</p>
                    <p class="text-slate-500">Miembro desde {{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                    <p class="text-slate-600">{{ $user->phone ?? 'No registrado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Cédula</label>
                    <p class="text-slate-600">{{ ($user->id_type ?? '') . ' ' . ($user->id_number ?? 'No registrada') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Ciudad</label>
                    <p class="text-slate-600">{{ $user->city ?? 'No registrada' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">País</label>
                    <p class="text-slate-600">{{ $user->country ?? 'Venezuela' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    // Pasar datos del backend a JavaScript
    window.favoritesCount = {{ $favoritesCount ?? 0 }};
    window.pendingAppointmentsCount = {{ $pendingAppointmentsCount ?? 0 }};
    window.unreadMessagesCount = {{ $unreadMessagesCount ?? 0 }};
</script>
@endpush
