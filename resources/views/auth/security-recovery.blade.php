@extends('layouts.landing')

@section('title', 'Recuperar Contraseña - Preguntas de Seguridad')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 pt-24">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                <i class="ph ph-shield-check text-3xl text-blue-600"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Recuperar Contraseña
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Ingresa tu correo para verificar tu identidad con las preguntas de seguridad.
            </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            @if(session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    <i class="ph ph-check-circle inline-block mr-1"></i>
                    {{ session('status') }}
                </div>
            @endif

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

            <form action="{{ route('security.verify.email') }}" method="POST" class="space-y-6">
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

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-mso-gold hover:bg-mso-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mso-gold transition-colors">
                        <i class="ph ph-shield-check mr-2"></i>
                        Verificar Identidad
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿No recuerdas tus preguntas de seguridad?
                    <a href="{{ route('password.request') }}" class="text-mso-gold hover:underline">
                        Recuperar por correo
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
