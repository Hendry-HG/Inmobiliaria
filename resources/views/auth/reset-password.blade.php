@extends('layouts.landing')

@section('title', 'Restablecer Contraseña')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                <i class="ph ph-key text-3xl text-green-600"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Restablecer Contraseña
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Ingresa tu nueva contraseña para continuar.
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

            <form action="{{ route('password.update') }}" method="POST" class="space-y-6" id="reset-form">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="email_display" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email_display" id="email_display" value="{{ $email }}"
                               class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                               disabled>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password"
                               class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none @error('password') border-red-500 @enderror"
                               placeholder="••••••••" required minlength="8">
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none"
                               placeholder="••••••••" required minlength="8">
                    </div>
                    <div id="pass-error" class="text-red-500 text-xs mt-1 hidden">Las contraseñas no coinciden</div>
                </div>

                <div>
                    <button type="submit" id="submit-btn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-mso-gold hover:bg-mso-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mso-gold transition-colors">
                        <i class="ph ph-check-circle mr-2"></i>
                        Restablecer Contraseña
                    </button>
                </div>
            </form>
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
                    console.log('Token CSRF actualizado (reset password)');
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
            alert('Tu sesión ha expirado. Por favor, solicita un nuevo enlace de recuperación.');
            window.location.href = '{{ route("password.request") }}';
        }

        // ============================================
        // VALIDACIÓN DE CONTRASEÑAS EN TIEMPO REAL
        // ============================================
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');
        const passError = document.getElementById('pass-error');

        if (password && passwordConfirm) {
            passwordConfirm.addEventListener('input', function() {
                if (this.value && this.value !== password.value) {
                    this.style.borderColor = '#ef4444';
                    passError.classList.remove('hidden');
                } else {
                    this.style.borderColor = '';
                    passError.classList.add('hidden');
                }
            });

            password.addEventListener('input', function() {
                if (passwordConfirm.value && this.value !== passwordConfirm.value) {
                    passwordConfirm.style.borderColor = '#ef4444';
                    passError.classList.remove('hidden');
                } else {
                    passwordConfirm.style.borderColor = '';
                    passError.classList.add('hidden');
                }
            });
        }

        // ============================================
        // PREVENCIÓN DE DOBLE CLIC
        // ============================================
        const resetForm = document.getElementById('reset-form');
        let isSubmitting = false;

        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                // Validar que las contraseñas coincidan
                if (password && passwordConfirm && password.value !== passwordConfirm.value) {
                    e.preventDefault();
                    passError.classList.remove('hidden');
                    passwordConfirm.style.borderColor = '#ef4444';
                    alert('Las contraseñas no coinciden. Por favor, verifica.');
                    return false;
                }

                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                isSubmitting = true;
                const submitBtn = document.getElementById('submit-btn');
                const originalText = submitBtn.textContent;

                submitBtn.textContent = '⏳ RESTABLECIENDO...';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'wait';

                console.log('📤 Enviando formulario de restablecimiento...');
                return true;
            });
        }
    });
</script>
@endpush
