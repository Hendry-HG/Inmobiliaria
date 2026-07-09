@extends('layouts.dashboard')

@section('title', 'Detalle del Servicio')
@section('header', 'Ver Servicio')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-handshake text-mso-gold"></i>
                    {{ $service->title }}
                </h3>
                <p class="text-sm text-slate-500 mt-1">Detalles del servicio</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.servicios.edit', $service->id) }}"
                   class="bg-mso-gold text-mso-blue px-4 py-2 rounded-lg font-bold hover:bg-mso-blue hover:text-white transition-colors shadow-sm flex items-center gap-2">
                    <i class="ph ph-pencil"></i> Editar
                </a>
                <a href="{{ route('admin.servicios.index') }}"
                   class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg font-bold hover:bg-slate-50 transition-colors">
                    Volver
                </a>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Imagen Principal --}}
            @if($service->image)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Imagen Principal</label>
                    <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->title }}"
                         class="w-48 h-48 object-cover rounded-lg border border-slate-200">
                </div>
            @endif

            {{-- Información --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-500">Título</label>
                    <p class="text-slate-800 font-semibold">{{ $service->title }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">Estado</label>
                    <p class="text-slate-800 font-semibold">
                        @if($service->is_active)
                            <span class="text-green-600">Activo</span>
                        @else
                            <span class="text-red-600">Inactivo</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">Destacado</label>
                    <p class="text-slate-800 font-semibold">
                        @if($service->is_featured)
                            <span class="text-amber-600"><i class="ph ph-star"></i> Sí</span>
                        @else
                            <span class="text-slate-400">No</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">Orden</label>
                    <p class="text-slate-800 font-semibold">{{ $service->order }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">Badge</label>
                    <p class="text-slate-800 font-semibold">
                        @if($service->badge)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-mso-gold/20 text-mso-gold">
                                {{ $service->badge }}
                            </span>
                        @else
                            <span class="text-slate-400">Sin badge</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">Icono</label>
                    <p class="text-slate-800 font-semibold flex items-center gap-2">
                        @if($service->icon)
                            <i class="{{ $service->icon }}"></i> {{ $service->icon }}
                        @else
                            <span class="text-slate-400">Sin icono</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Descripción --}}
            <div>
                <label class="block text-sm font-medium text-slate-500">Descripción</label>
                <p class="text-slate-800 mt-1">{{ $service->description ?? 'Sin descripción' }}</p>
            </div>

            {{-- Características --}}
            @if($service->features && is_array($service->features) && count($service->features) > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-500">Características</label>
                    <ul class="mt-2 space-y-1">
                        @foreach($service->features as $feature)
                            <li class="flex items-center gap-2 text-slate-700">
                                <i class="ph ph-check-circle text-mso-gold"></i> {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Galería --}}
            @if($service->gallery->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Galería de Imágenes</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($service->gallery as $image)
                            <div class="rounded-lg overflow-hidden border border-slate-200 aspect-square">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     class="w-full h-full object-cover"
                                     alt="Imagen del servicio">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- URL Externa --}}
            @if($service->external_url)
                <div>
                    <label class="block text-sm font-medium text-slate-500">URL Externa</label>
                    <a href="{{ $service->external_url }}" target="_blank"
                       class="text-mso-gold hover:underline font-semibold">
                        {{ $service->external_url }}
                    </a>
                </div>
            @endif

            {{-- Fechas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t">
                <div>
                    <label class="block text-sm font-medium text-slate-500">Creado</label>
                    <p class="text-slate-600 text-sm">{{ $service->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500">Actualizado</label>
                    <p class="text-slate-600 text-sm">{{ $service->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
