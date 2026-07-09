@extends('layouts.landing')

@section('title', 'Iniciar Sesión')

@push('css')
<style>
    .auth-card {
        background: #ffffff;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid #f1f5f9;
        max-width: 480px;
        width: 100%;
        border-radius: 0;
    }
    .auth-header {
        background-color: #f8fafc;
        padding: 2rem 2rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .auth-header h2 {
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .auth-header p {
        color: #64748b;
        font-size: 0.875rem;
    }
    .auth-body {
        padding: 2rem 2.5rem 2rem;
    }
    .auth-footer {
        background-color: #f8fafc;
        padding: 0.75rem 2rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: center;
        gap: 2rem;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    .form-input {
        width: 100%;
        border: none;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.75rem 0.25rem 0.75rem 0.25rem;
        font-size: 0.875rem;
        color: #0f172a;
        background: transparent;
        transition: border-color 0.2s ease;
        outline: none;
    }
    .form-input:focus {
        border-bottom-color: #c5a059;
    }
    .form-input::placeholder {
        color: #94a3b8;
    }
    .form-input-error {
        border-bottom-color: #ef4444;
    }
    .form-input-error:focus {
        border-bottom-color: #ef4444;
    }
    .checkbox-custom {
        width: 1rem;
        height: 1rem;
        accent-color: #c5a059;
        border-radius: 0.25rem;
        border: 2px solid #cbd5e1;
        cursor: pointer;
    }
    .btn-login {
        width: 100%;
        background-color: #1a2a3a;
        color: #ffffff;
        font-weight: 700;
        padding: 1rem 1.5rem;
        border: none;
        border-radius: 0;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px rgba(26, 42, 58, 0.25);
        letter-spacing: 0.05em;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    .btn-login:hover {
        background-color: #0f172a;
        box-shadow: 0 8px 25px rgba(26, 42, 58, 0.35);
    }
    .btn-login:active {
        transform: scale(0.98);
    }
    .link-tab {
        font-size: 0.875rem;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 2px solid #c5a059;
        padding-bottom: 0.25rem;
        transition: all 0.2s ease;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        cursor: pointer;
    }
    .link-tab-inactive {
        font-size: 0.875rem;
        font-weight: 500;
        color: #94a3b8;
        padding-bottom: 0.25rem;
        transition: all 0.2s ease;
        background: none;
        border: none;
        cursor: pointer;
    }
    .link-tab-inactive:hover {
        color: #0f172a;
    }
    .link-forgot {
        color: #c5a059;
        font-size: 0.75rem;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .link-forgot:hover {
        color: #1a2a3a;
        text-decoration: underline;
    }
    .link-register {
        color: #c5a059;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .link-register:hover {
        color: #1a2a3a;
    }
    .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .error-message i {
        font-size: 1rem;
    }
    .error-box {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #dc2626;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }
    .error-box i {
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.5rem 0;
    }
    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }
    .auth-divider span {
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }
    .auth-body .flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .auth-body .flex .flex {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .text-xs {
        font-size: 0.75rem;
    }
    .text-slate-600 {
        color: #475569;
    }
    .text-slate-500 {
        color: #64748b;
    }
    .text-center {
        text-align: center;
    }
    .mt-2 {
        margin-top: 0.5rem;
    }
    .mt-4 {
        margin-top: 1rem;
    }
    .gap-3 {
        gap: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 pt-32">

    {{-- Card --}}
    <div class="auth-card">

        {{-- Header --}}
        <div class="auth-header">
            <h2>Bienvenido</h2>
            <p>Ingresa a tu cuenta para continuar</p>
        </div>

        {{-- Body --}}
        <div class="auth-body">

            {{-- Errores generales --}}
            @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
            <div class="error-box">
                <i class="ph ph-warning-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                {{-- Campo: Email --}}
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="form-input @error('email') form-input-error @enderror"
                           placeholder="ejemplo@correo.com"
                           value="{{ old('email') }}">
                    @error('email')
                        <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Campo: Contraseña --}}
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="form-input @error('password') form-input-error @enderror"
                           placeholder="••••••••">
                    @error('password')
                        <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Recordarme y Olvidé contraseña --}}
                <div class="flex">
                    <div class="flex">
                        <input id="remember" name="remember" type="checkbox" class="checkbox-custom">
                        <label for="remember" class="text-xs text-slate-600 select-none" style="margin-left: 0.5rem;">Recordarme</label>
                    </div>
                    <a href="#" class="link-forgot">¿Olvidaste tu contraseña?</a>
                </div>

                {{-- Botón --}}
                <button type="submit" class="btn-login">
                    INICIAR SESIÓN
                </button>

                {{-- Separador --}}
                <div class="auth-divider">
                    <span>o</span>
                </div>

                {{-- Registro --}}
                <div class="text-center">
                    <p class="text-xs text-slate-500">
                        ¿No tienes una cuenta?
                        <a href="{{ route('register') }}" class="link-register">
                            Regístrate aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>

        {{-- Footer con tabs (igual que el modal) --}}
        <div class="auth-footer">
            <button class="link-tab">
                Iniciar Sesión
            </button>
            <a href="{{ route('register') }}" class="link-tab-inactive">
                Registrarse
            </a>
        </div>
    </div>
</div>
@endsection
