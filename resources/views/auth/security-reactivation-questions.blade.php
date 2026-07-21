@extends('layouts.landing')

@section('title', 'Reactivar Cuenta - Preguntas de Seguridad')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 pt-24">
    <div class="max-w-2xl w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100">
                <i class="ph ph-question text-3xl text-yellow-600"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Reactivar Cuenta
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Responde las preguntas de seguridad para reactivar tu cuenta.
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

            <form action="{{ route('security.reactivation.verify.answers') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                @foreach($questions as $index => $question)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Pregunta {{ $index + 1 }}
                        </label>
                        <p class="text-gray-900 font-medium mb-2">{{ $question }}</p>
                        <input type="text" name="answer_{{ $index + 1 }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none"
                               placeholder="Tu respuesta" required>
                    </div>
                @endforeach

                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-mso-gold hover:bg-mso-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mso-gold transition-colors">
                    <i class="ph ph-check-circle mr-2"></i>
                    Reactivar Cuenta
                </button>
            </form>

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
                }
            })
            .catch(() => {});
        }

        setInterval(refreshCsrfToken, 300000);

        if (window.location.search.includes('session_expired')) {
            alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
            const url = new URL(window.location);
            url.searchParams.delete('session_expired');
            window.history.replaceState({}, document.title, url.toString());
        }

        document.querySelectorAll('form').forEach(form => {
            let isSubmitting = false;
            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    isSubmitting = true;
                    const originalText = submitBtn.textContent;
                    submitBtn.textContent = ' ENVIANDO...';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                    submitBtn.style.cursor = 'wait';
                    setTimeout(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                        isSubmitting = false;
                    }, 30000);
                }
            });
        });
    });
</script>
@endpush
