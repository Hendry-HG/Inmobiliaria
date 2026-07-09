@extends('layouts.landing')

@section('title', 'Cuenta Inactiva')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100">
                <i class="ph ph-warning-circle text-3xl text-yellow-600"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Cuenta Inactiva
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Tu cuenta ha sido desactivada temporalmente.
            </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    <i class="ph ph-check-circle inline-block mr-1"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <i class="ph ph-x-circle inline-block mr-1"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded">
                    <i class="ph ph-warning inline-block mr-1"></i>
                    {{ session('warning') }}
                </div>
            @endif

            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <i class="ph ph-info inline-block mr-1"></i>
                        <strong>¿Por qué está inactiva mi cuenta?</strong>
                    </p>
                    <p class="text-xs text-blue-700 mt-1">
                        Las cuentas pueden ser desactivadas por los administradores por diversas razones:
                        inactividad prolongada, violación de términos de servicio, o por solicitud del usuario.
                    </p>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <p class="text-sm text-gray-700 mb-4">
                        Para reactivar tu cuenta, te enviaremos un correo electrónico con las instrucciones necesarias.
                    </p>

                    <form action="{{ route('account.reactivation.request') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-mso-gold hover:bg-mso-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mso-gold transition-colors">
                            <i class="ph ph-envelope mr-2"></i>
                            Solicitar Reactivación
                        </button>
                    </form>

                    <p class="text-xs text-gray-500 mt-4 text-center">
                        Te enviaremos un correo a: <strong>{{ $email }}</strong>
                    </p>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-mso-blue hover:text-mso-gold">
                        <i class="ph ph-arrow-left mr-1"></i>
                        Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-xs text-gray-500">
                ¿Necesitas ayuda? <a href="{{ route('home') }}#contacto" class="text-mso-gold hover:underline">Contáctanos</a>
            </p>
        </div>
    </div>
</div>
@endsection
