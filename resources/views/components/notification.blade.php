{{-- Componente de notificaciones toast del sistema --}}
@props([
    'type' => 'success',
    'message' => null,
    'title' => null,
    'dismissible' => true,
    'autoDismiss' => true,
    'dismissAfter' => 5000,
    'class' => '',
    'id' => null,
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
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-500',
            'text' => 'text-red-700',
            'icon' => 'ph-warning-circle',
            'icon_bg' => 'bg-red-100',
            'icon_color' => 'text-red-500',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-500',
            'text' => 'text-yellow-700',
            'icon' => 'ph-warning',
            'icon_bg' => 'bg-yellow-100',
            'icon_color' => 'text-yellow-500',
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-500',
            'text' => 'text-blue-700',
            'icon' => 'ph-info',
            'icon_bg' => 'bg-blue-100',
            'icon_color' => 'text-blue-500',
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
     x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-4"
     class="{{ $config['bg'] }} border-l-4 {{ $config['border'] }} {{ $config['text'] }} p-4 mb-4 rounded-r shadow-sm {{ $class }}"
     role="alert">

    <div class="flex items-start">
        {{-- Icono --}}
        <div class="flex-shrink-0">
            <div class="w-8 h-8 {{ $config['icon_bg'] }} rounded-full flex items-center justify-center">
                <i class="ph {{ $config['icon'] }} text-xl {{ $config['icon_color'] }}"></i>
            </div>
        </div>

        {{-- Contenido --}}
        <div class="ml-3 flex-1">
            @if($titleText)
                <h4 class="font-bold text-sm {{ $config['text'] }}">{{ $titleText }}</h4>
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
            <div class="ml-auto pl-3">
                <button
                    type="button"
                    @click="show = false"
                    class="inline-flex rounded-md p-1.5 {{ $config['text'] }} hover:bg-white/20 focus:outline-none transition-colors"
                    aria-label="Cerrar notificación">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
        @endif
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