{{-- Componente de notificaciones modales del sistema --}}
@props([
    'type' => 'success',
    'message' => null,
    'title' => null,
    'dismissible' => true,
    'autoDismiss' => true,
    'dismissAfter' => 5000,
    'class' => '',
    'id' => null,
    'buttonText' => 'Entendido',
])

@php
    $config = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-500',
            'text' => 'text-green-700',
            'icon' => 'ph-check-circle',
            'icon_bg' => 'bg-green-100',
            'icon_color' => 'text-green-500',
            'accent' => 'bg-green-500',
            'button_bg' => 'bg-green-600',
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-500',
            'text' => 'text-red-700',
            'icon' => 'ph-warning-circle',
            'icon_bg' => 'bg-red-100',
            'icon_color' => 'text-red-500',
            'accent' => 'bg-red-500',
            'button_bg' => 'bg-red-600',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-500',
            'text' => 'text-yellow-700',
            'icon' => 'ph-warning',
            'icon_bg' => 'bg-yellow-100',
            'icon_color' => 'text-yellow-500',
            'accent' => 'bg-yellow-500',
            'button_bg' => 'bg-yellow-600',
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-500',
            'text' => 'text-blue-700',
            'icon' => 'ph-info',
            'icon_bg' => 'bg-blue-100',
            'icon_color' => 'text-blue-500',
            'accent' => 'bg-blue-500',
            'button_bg' => 'bg-blue-600',
        ],
    ][$type];

    $notificationId = $id ?? 'notification-' . uniqid();
    $titleText = $title ?? match($type) {
        'success' => '¡Éxito!',
        'error' => '¡Error!',
        'warning' => '¡Advertencia!',
        'info' => 'Información',
        default => 'Notificación'
    };
@endphp

<div id="{{ $notificationId }}"
     x-data="{ show: true, open: false }"
     x-init="setTimeout(() => open = true, 50)"
     x-show="show"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="show = false"
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true">

    {{-- Fondo oscurecido --}}
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
         @click="show = false"></div>

    {{-- Tarjeta del modal --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-auto overflow-hidden {{ $class }}"
         role="alert">

        {{-- Barra superior según el tipo --}}
        <div class="h-1.5 {{ $config['accent'] }}"></div>

        <div class="p-6">
            <div class="flex items-start">
                {{-- Icono --}}
                <div class="flex-shrink-0">
                    <div class="w-14 h-14 {{ $config['icon_bg'] }} rounded-full flex items-center justify-center">
                        <i class="ph {{ $config['icon'] }} text-3xl {{ $config['icon_color'] }}"></i>
                    </div>
                </div>

                {{-- Contenido --}}
                <div class="ml-4 flex-1">
                    @if($titleText)
                        <h3 class="font-bold text-lg text-slate-800">{{ $titleText }}</h3>
                    @endif

                    @if($message)
                        <p class="text-sm {{ $config['text'] }} {{ $titleText ? 'mt-1' : '' }}">
                            {{ $message }}
                        </p>
                    @endif

                    {{-- Slot para contenido personalizado --}}
                    {{ $slot }}
                </div>

                {{-- Botón de cierre --}}
                @if($dismissible)
                    <button
                        type="button"
                        @click="show = false"
                        class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors"
                        aria-label="Cerrar notificación">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                @endif
            </div>

            {{-- Botón de confirmación --}}
            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    @click="show = false"
                    class="px-6 py-2.5 rounded-lg font-semibold text-white {{ $config['button_bg'] }} hover:opacity-90 transition-opacity shadow">
                    {{ $buttonText }}
                </button>
            </div>
        </div>
    </div>
</div>

@if($autoDismiss)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const notification = document.getElementById('{{ $notificationId }}');
            if (notification && notification.__x) {
                notification.__x.$data.show = false;
            } else if (notification) {
                notification.style.transition = 'opacity 0.5s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }
        }, {{ $dismissAfter }});
    });
</script>
@endif
