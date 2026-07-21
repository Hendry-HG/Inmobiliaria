@extends('layouts.landing')

@section('title', 'Reactivar Cuenta')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 pt-24">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100">
                <i class="ph ph-arrow-counter-clockwise text-3xl text-yellow-600"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Reactivar Cuenta
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Tu cuenta está inactiva. Verifica tu identidad con las preguntas de seguridad.
            </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <i class="ph ph-x-circle inline-block mr-1"></i>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('security.reactivation.verify.email') }}" method="POST" class="space-y-6" id="reactivation-form">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none @error('email') border-red-500 @enderror"
                               placeholder="tucorreo@ejemplo.com" required autofocus>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" id="submit-btn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-mso-gold hover:bg-mso-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mso-gold transition-colors">
                    <i class="ph ph-shield-check mr-2"></i>
                    Verificar Identidad
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿No recuerdas tus preguntas de seguridad?
                    <a href="#" class="text-mso-gold hover:underline">
                        Contacta al administrador
                    </a>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-mso-blue hover:text-mso-gold">
                    <i class="ph ph-arrow-left mr-1"></i>
                    Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============================================
        // REFRESCAR TOKEN CSRF AUTOMÁTICAMENTE
        // ============================================
        function refreshCsrfToken() {
            fetch('/refresh-csrf', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_token) {
                    document.querySelectorAll('form input[name="_token"]').forEach(input => {
                        input.value = data.csrf_token;
                    });
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.content = data.csrf_token;
                    }
                    console.log(' Token CSRF actualizado (reactivación)');
                }
            })
            .catch(() => {
                console.warn(' No se pudo refrescar el token');
            });
        }

        // Refrescar token cada 5 minutos
        setInterval(refreshCsrfToken, 300000);

        // Verificar si hay error de sesión expirada
        if (window.location.search.includes('session_expired')) {
            alert('Tu sesión ha expirado. Por favor, intenta nuevamente.');
            const url = new URL(window.location);
            url.searchParams.delete('session_expired');
            window.history.replaceState({}, document.title, url.toString());
        }

        // ============================================
        // PREVENCIÓN DE DOBLE CLIC
        // ============================================
        const reactivationForm = document.getElementById('reactivation-form');
        let isSubmitting = false;

        if (reactivationForm) {
            reactivationForm.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                isSubmitting = true;
                const submitBtn = document.getElementById('submit-btn');
                const originalText = submitBtn.textContent;

                submitBtn.textContent = 'VERIFICANDO...';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'wait';

                console.log('📤 Enviando formulario de reactivación...');
                return true;
            });
        }
    });
</script>
@endpush
