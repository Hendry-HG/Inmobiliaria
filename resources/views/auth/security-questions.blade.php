@extends('layouts.landing')

@section('title', 'Preguntas de Seguridad')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 pt-24">
    <div class="max-w-2xl w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                <i class="ph ph-question text-3xl text-blue-600"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Preguntas de Seguridad
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Responde las siguientes preguntas para verificar tu identidad.
            </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <i class="ph ph-x-circle inline-block mr-1"></i>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>  <!--  ESCAPADO -->
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('security.verify.answers') }}" method="POST" class="space-y-6">
                @csrf  <!--  CSRF OBLIGATORIO -->
                <input type="hidden" name="email" value="{{ session('security_recovery_email') }}">  <!-- ✅ USAR SESIÓN -->

                @foreach($questions as $index => $question)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Pregunta {{ $index + 1 }}
                        </label>
                        <p class="text-gray-900 font-medium mb-2">{{ $question }}</p>
                        <input type="text" name="answer_{{ $index + 1 }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none"
                               placeholder="Tu respuesta" 
                               autocomplete="off"
                               required>
                    </div>
                @endforeach

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-mso-gold hover:bg-mso-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mso-gold transition-colors">
                        <i class="ph ph-check-circle mr-2"></i>
                        Verificar Respuestas
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('security.recovery.form') }}" class="text-sm text-mso-blue hover:text-mso-gold">
                    <i class="ph ph-arrow-left mr-1"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>
@endsection