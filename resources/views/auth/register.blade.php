@extends('layouts.landing')

@section('title', 'Registro')

@push('css')
<style>
    .auth-card {
        background: #ffffff;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid #f1f5f9;
        max-width: 580px;
        width: 100%;
        border-radius: 0;
    }
    .auth-header {
        background-color: #f8fafc;
        padding: 1rem 1.5rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        text-align: center;
    }
    .auth-header h2 {
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.1rem;
    }
    .auth-header p {
        color: #64748b;
        font-size: 0.8rem;
    }
    .auth-body {
        padding: 1rem 1.25rem 1rem;
    }
    .auth-footer {
        background-color: #f8fafc;
        padding: 0.5rem 1.5rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: center;
        gap: 2rem;
    }
    .form-group {
        margin-bottom: 0.7rem;
    }
    .form-group label {
        display: block;
        font-size: 0.6rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .form-input {
        width: 100%;
        border: none;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.4rem 0.25rem 0.4rem 0.25rem;
        font-size: 0.8rem;
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
        font-size: 0.75rem;
    }
    .form-input-error {
        border-bottom-color: #ef4444;
    }
    .form-input-error:focus {
        border-bottom-color: #ef4444;
    }
    .form-select {
        width: 100%;
        border: none;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.4rem 0.25rem 0.4rem 0.25rem;
        font-size: 0.8rem;
        color: #0f172a;
        background: transparent;
        transition: border-color 0.2s ease;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.25rem center;
        padding-right: 1.2rem;
        cursor: pointer;
    }
    .form-select:focus {
        border-bottom-color: #c5a059;
    }
    .form-select:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-image: none;
    }
    .form-select option {
        color: #0f172a;
        background: #ffffff;
    }
    .checkbox-custom {
        width: 0.9rem;
        height: 0.9rem;
        accent-color: #c5a059;
        border-radius: 0.25rem;
        border: 2px solid #cbd5e1;
        cursor: pointer;
        flex-shrink: 0;
    }
    .btn-register {
        width: 100%;
        background-color: #c5a059;
        color: #0f172a;
        font-weight: 700;
        padding: 0.7rem 1.5rem;
        border: none;
        border-radius: 0;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px rgba(197, 160, 89, 0.3);
        letter-spacing: 0.05em;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
    .btn-register:hover {
        background-color: #b8923f;
        box-shadow: 0 8px 25px rgba(197, 160, 89, 0.4);
    }
    .btn-register:active {
        transform: scale(0.98);
    }
    .btn-register:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .link-tab {
        font-size: 0.8rem;
        font-weight: 500;
        color: #94a3b8;
        padding-bottom: 0.2rem;
        transition: all 0.2s ease;
        background: none;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }
    .link-tab:hover {
        color: #0f172a;
    }
    .link-tab-active {
        font-size: 0.8rem;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 2px solid #c5a059;
        padding-bottom: 0.2rem;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        cursor: pointer;
        text-decoration: none;
    }
    .link-login {
        color: #c5a059;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s ease;
        font-size: 0.8rem;
    }
    .link-login:hover {
        color: #1a2a3a;
    }
    .error-message {
        color: #ef4444;
        font-size: 0.65rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.2rem;
    }
    .error-message i {
        font-size: 0.8rem;
    }
    .error-box {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        padding: 0.5rem 0.75rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: #dc2626;
        font-size: 0.75rem;
        margin-bottom: 1rem;
    }
    .error-box i {
        font-size: 1rem;
        flex-shrink: 0;
        margin-top: 0.05rem;
    }
    .error-box ul {
        margin: 0;
        padding-left: 1rem;
    }
    .error-box ul li {
        list-style: disc;
        font-size: 0.7rem;
    }
    /* Utilidades escopadas SOLO al formulario para no pisar las clases
       globales de Tailwind (navbar, footer y botones de redes sociales) */
    .auth-card .text-xs {
        font-size: 0.7rem;
    }
    .auth-card .text-slate-500 {
        color: #64748b;
    }
    .auth-card .text-slate-600 {
        color: #475569;
    }
    .auth-card .text-green-600 {
        color: #16a34a;
    }
    .auth-card .text-red-500 {
        color: #ef4444;
    }
    .auth-card .text-center {
        text-align: center;
    }
    .auth-card .mt-1 {
        margin-top: 0.2rem;
    }
    .auth-card .mt-2 {
        margin-top: 0.4rem;
    }
    .auth-card .flex {
        display: flex;
    }
    .auth-card .flex-1 {
        flex: 1 1 0%;
        min-width: 0;
    }
    .auth-card .phone-code-select {
        flex: 0 0 auto;
        width: auto;
        min-width: 7.5rem;
    }
    .auth-card .items-center {
        align-items: center;
    }
    .auth-card .gap-2 {
        gap: 0.4rem;
    }
    .auth-card .gap-3 {
        gap: 0.6rem;
    }
    .auth-card .grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }
    .auth-card .grid-cols-1 {
        grid-template-columns: 1fr;
    }
    .auth-card .grid {
        display: grid;
    }
    .auth-card .col-span-1 {
        grid-column: span 1;
    }
    .auth-card .col-span-2 {
        grid-column: span 2;
    }
    .auth-card .mb-1 {
        margin-bottom: 0.2rem;
    }
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin: 1rem 0;
    }
    .auth-divider span {
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }
    .auth-divider p {
        font-size: 0.65rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        margin: 0;
        flex-shrink: 0;
    }

    /* SOLUCIÓN PARA EL AUTORELLENADO */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px #ffffff inset !important;
        -webkit-text-fill-color: #0f172a !important;
        border-bottom-color: #c5a059 !important;
        background-color: #ffffff !important;
    }

    @media (min-width: 768px) {
        .auth-card .md\:grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        .auth-card .md\:col-span-2 {
            grid-column: span 2;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-8 px-4 sm:px-6 lg:px-8 pt-28">

    <div class="auth-card">

        {{-- Header --}}
        <div class="auth-header">
            <h2>Crear Cuenta</h2>
            <p>Únete a MSO Grupo Inmobiliario</p>
        </div>

        {{-- Body --}}
        <div class="auth-body">

            {{-- Errores del servidor --}}
            @if ($errors->any())
            <div class="error-box">
                <i class="ph ph-warning-circle"></i>
                <div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- FORMULARIO CORREGIDO --}}
            <form method="POST" action="{{ route('register') }}" novalidate id="register-form">
                @csrf

                {{-- ============================================ --}}
                {{-- PASO 1: DATOS PERSONALES Y CONTACTO          --}}
                {{-- ============================================ --}}
                <div id="step-1" class="space-y-3">

                    {{-- Nombre y Apellido --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group">
                            <label for="name">Nombre Completo *</label>
                            <input id="name" name="name" type="text" required
                                   class="form-input @error('name') form-input-error @enderror"
                                   placeholder="Nombre"
                                   autocomplete="given-name"
                                   value="{{ old('name') }}">
                            @error('name')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="last_name">Apellido *</label>
                            <input id="last_name" name="last_name" type="text" required
                                   class="form-input"
                                   placeholder="Apellido"
                                   autocomplete="family-name"
                                   value="{{ old('last_name') }}">
                            @error('last_name')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Tipo ID y Número --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group">
                            <label for="id_type">Tipo ID *</label>
                            <select id="id_type" name="id_type" class="form-select" autocomplete="off" required>
                                <option value="">Tipo</option>
                                <option value="V" {{ old('id_type') == 'V' ? 'selected' : '' }}>Venezolano (V)</option>
                                <option value="E" {{ old('id_type') == 'E' ? 'selected' : '' }}>Extranjero (E)</option>
                                <option value="J" {{ old('id_type') == 'J' ? 'selected' : '' }}>Jurídico (J)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_number">Número de ID *</label>
                            <input id="id_number" name="id_number" type="text" required
                                   class="form-input"
                                   placeholder="12345678"
                                   autocomplete="off"
                                   inputmode="numeric"
                                   value="{{ old('id_number') }}">
                            <div id="id_number-error" class="text-red-500 text-xs mt-1 hidden">
                                Este número de identificación ya está en uso por otro usuario.
                            </div>
                        </div>
                    </div>

                    {{-- Teléfono --}}
                    <div class="form-group">
                        <label for="phone">Teléfono *</label>
                        <div class="flex gap-2">
                                <select id="phone_code" name="phone_code" autocomplete="off"
                                        class="form-select phone-code-select" required>
                                    @forelse($phoneCountries as $country)
                                        <option value="{{ $country->phone_code }}"
                                                data-country-id="{{ $country->id }}"
                                                data-format="{{ $country->phone_format ?? '' }}"
                                                data-min="{{ $country->phone_min_length ?? 7 }}"
                                                data-max="{{ $country->phone_max_length ?? 15 }}"
                                                {{ old('phone_code', $phoneCountries->first()?->phone_code ?? '+58') == $country->phone_code ? 'selected' : '' }}>
                                            {{ $country->name }} {{ $country->phone_code }}
                                        </option>
                                    @empty
                                        <option value="+58" selected>Venezuela +58</option>
                                    @endforelse
                                </select>
                                <input id="phone" name="phone" type="tel" required
                                       class="flex-1 form-input @error('phone') form-input-error @enderror"
                                       placeholder="412-1234567"
                                       autocomplete="tel-national"
                                       inputmode="numeric"
                                       value="{{ old('phone') }}">
                            </div>
                            <div id="phone-format-error" class="text-red-500 text-xs mt-1 hidden">
                                El teléfono no tiene la longitud válida para el país.
                            </div>
                            <div id="phone-error" class="text-red-500 text-xs mt-1 hidden">
                                Este teléfono ya está en uso por otro usuario.
                            </div>
                            @error('phone')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Correo Electrónico *</label>
                            <input id="email" name="email" type="email" required
                                   class="form-input @error('email') form-input-error @enderror"
                                   placeholder="ejemplo@correo.com"
                                   autocomplete="email"
                                   value="{{ old('email') }}">
                            <div id="email-error" class="text-red-500 text-xs mt-1 hidden">
                                Este correo ya está en uso por otro usuario.
                            </div>
                            @error('email')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                    {{-- Botón Siguiente Paso 1 --}}
                    <div class="flex justify-end pt-2">
                        <button type="button" onclick="goToStep(2)"
                                class="px-6 py-2 bg-mso-gold text-slate-900 font-medium rounded-sm hover:bg-yellow-600 transition-colors text-sm">
                            Siguiente <i class="ph ph-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- PASO 2: UBICACIÓN                            --}}
                {{-- ============================================ --}}
                <div id="step-2" class="space-y-3 hidden">

                    {{-- Ubicaciones --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group">
                            <label for="reg_country_id">País *</label>
                            <select name="country_id" id="reg_country_id" required autocomplete="off" class="form-select @error('country_id') form-input-error @enderror">
                                <option value="">Seleccione un país</option>
                            </select>
                            @error('country_id')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reg_state_id">Estado *</label>
                            <select name="state_id" id="reg_state_id" required autocomplete="off" class="form-select @error('state_id') form-input-error @enderror" disabled>
                                <option value="">Primero seleccione un país</option>
                            </select>
                            @error('state_id')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reg_municipality_id">Municipio *</label>
                            <select name="municipality_id" id="reg_municipality_id" required autocomplete="off" class="form-select @error('municipality_id') form-input-error @enderror" disabled>
                                <option value="">Primero seleccione un estado</option>
                            </select>
                            @error('municipality_id')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reg_parish_id">Parroquia *</label>
                            <select name="parish_id" id="reg_parish_id" required autocomplete="off" class="form-select @error('parish_id') form-input-error @enderror" disabled>
                                <option value="">Primero seleccione un municipio</option>
                            </select>
                            @error('parish_id')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group md:col-span-2">
                            <label for="reg_city_id">Ciudad *</label>
                            <select name="city_id" id="reg_city_id" required autocomplete="off" class="form-select @error('city_id') form-input-error @enderror" disabled>
                                <option value="">Primero seleccione una parroquia</option>
                            </select>
                            @error('city_id')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Dirección --}}
                    <div class="form-group">
                        <label for="address">Dirección *</label>
                        <textarea id="address" name="address" rows="2" required
                                  class="form-input resize-none" autocomplete="street-address"
                                  placeholder="Tu dirección completa">{{ old('address') }}</textarea>
                    </div>

                    {{-- Botones Paso 2 --}}
                    <div class="flex justify-between pt-2">
                        <button type="button" onclick="goToStep(1)"
                                class="px-6 py-2 bg-slate-200 text-slate-700 font-medium rounded-sm hover:bg-slate-300 transition-colors text-sm">
                            <i class="ph ph-arrow-left mr-1"></i> Anterior
                        </button>
                        <button type="button" onclick="goToStep(3)"
                                class="px-6 py-2 bg-mso-gold text-slate-900 font-medium rounded-sm hover:bg-yellow-600 transition-colors text-sm">
                            Siguiente <i class="ph ph-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- PASO 3: CONTRASEÑA Y SEGURIDAD              --}}
                {{-- ============================================ --}}
                <div id="step-3" class="space-y-3 hidden">

                    {{-- Contraseñas --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group">
                            <label for="password">Contraseña *</label>
                            <input id="password" name="password" type="password" required
                                   class="form-input @error('password') form-input-error @enderror"
                                   placeholder="Mínimo 10 caracteres"
                                   autocomplete="new-password"
                                   minlength="10"
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$"
                                   oninput="validatePassword(this)">
                            <div id="password-strength" class="text-xs mt-1 text-slate-500">
                                Debe tener: Mayúscula, minúscula, número y caracter especial
                            </div>
                            @error('password')
                                <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Contraseña *</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                   class="form-input"
                                   placeholder="Repite tu contraseña"
                                   autocomplete="new-password">
                            <div id="pass-error" class="text-red-500 text-xs mt-1 hidden">Las contraseñas no coinciden</div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- PREGUNTAS DE SEGURIDAD (OBLIGATORIAS)     --}}
                    {{-- ========================================== --}}
                    <div class="border-t border-slate-200 pt-3 mt-1">
                        <div class="flex items-center mb-2">
                            <i class="ph ph-shield-check text-mso-gold mr-2"></i>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Preguntas de Seguridad *</span>
                            <span class="ml-1 text-red-500 text-xs">(Obligatorias)</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-2">
                            <i class="ph ph-info mr-1 text-slate-400"></i>
                            Selecciona 3 preguntas <strong>diferentes</strong> y proporciona respuestas que puedas recordar.
                        </p>

                        {{-- Pregunta 1 --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div class="form-group">
                                <label for="security_question_1">Pregunta 1 *</label>
                                <select name="security_question_1" id="security_question_1" required autocomplete="off"
                                        class="form-select security-question @error('security_question_1') form-input-error @enderror">
                                    <option value="">Selecciona una pregunta</option>
                                    <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                                    <option value="¿Cuál es el apellido de soltera de tu madre?">¿Cuál es el apellido de soltera de tu madre?</option>
                                    <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
                                    <option value="¿Cuál es tu comida favorita?">¿Cuál es tu comida favorita?</option>
                                    <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?">¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                                    <option value="¿Cuál es el título de tu libro favorito?">¿Cuál es el título de tu libro favorito?</option>
                                    <option value="¿Cuál es el nombre de tu profesor favorito?">¿Cuál es el nombre de tu profesor favorito?</option>
                                    <option value="¿En qué año te graduaste de la escuela?">¿En qué año te graduaste de la escuela?</option>
                                    <option value="¿Cuál es el nombre de tu primer amor?">¿Cuál es el nombre de tu primer amor?</option>
                                    <option value="¿Cuál es el nombre de tu abuelo favorito?">¿Cuál es el nombre de tu abuelo favorito?</option>
                                    <option value="¿Cuál es el nombre de tu hijo/a?">¿Cuál es el nombre de tu hijo/a?</option>
                                    <option value="¿Cuál es el nombre de tu padre?">¿Cuál es el nombre de tu padre?</option>
                                    <option value="¿Cuál es el modelo de tu primer auto?">¿Cuál es el modelo de tu primer auto?</option>
                                    <option value="¿Cuál es el nombre de tu mejor amigo?">¿Cuál es el nombre de tu mejor amigo?</option>
                                    <option value="¿Cuál es tu color favorito?">¿Cuál es tu color favorito?</option>
                                    <option value="¿Cuál es tu deporte favorito?">¿Cuál es tu deporte favorito?</option>
                                    <option value="¿Cuál es el nombre de tu primera escuela?">¿Cuál es el nombre de tu primera escuela?</option>
                                    <option value="¿Cuál es el nombre de tu primer jefe?">¿Cuál es el nombre de tu primer jefe?</option>
                                    <option value="¿Cuál es tu lugar favorito para vacacionar?">¿Cuál es tu lugar favorito para vacacionar?</option>
                                    <option value="¿Cuál es el nombre de tu tío favorito?">¿Cuál es el nombre de tu tío favorito?</option>
                                </select>
                                @error('security_question_1')
                                    <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="security_answer_1">Respuesta 1 *</label>
                                <input type="text" name="security_answer_1" id="security_answer_1" required
                                       class="form-input @error('security_answer_1') form-input-error @enderror"
                                       placeholder="Tu respuesta" maxlength="255" autocomplete="off">
                                @error('security_answer_1')
                                    <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Pregunta 2 --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div class="form-group">
                                <label for="security_question_2">Pregunta 2 *</label>
                                <select name="security_question_2" id="security_question_2" required autocomplete="off"
                                        class="form-select security-question @error('security_question_2') form-input-error @enderror">
                                    <option value="">Selecciona una pregunta</option>
                                    <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                                    <option value="¿Cuál es el apellido de soltera de tu madre?">¿Cuál es el apellido de soltera de tu madre?</option>
                                    <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
                                    <option value="¿Cuál es tu comida favorita?">¿Cuál es tu comida favorita?</option>
                                    <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?">¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                                    <option value="¿Cuál es el título de tu libro favorito?">¿Cuál es el título de tu libro favorito?</option>
                                    <option value="¿Cuál es el nombre de tu profesor favorito?">¿Cuál es el nombre de tu profesor favorito?</option>
                                    <option value="¿En qué año te graduaste de la escuela?">¿En qué año te graduaste de la escuela?</option>
                                    <option value="¿Cuál es el nombre de tu primer amor?">¿Cuál es el nombre de tu primer amor?</option>
                                    <option value="¿Cuál es el nombre de tu abuelo favorito?">¿Cuál es el nombre de tu abuelo favorito?</option>
                                    <option value="¿Cuál es el nombre de tu hijo/a?">¿Cuál es el nombre de tu hijo/a?</option>
                                    <option value="¿Cuál es el nombre de tu padre?">¿Cuál es el nombre de tu padre?</option>
                                    <option value="¿Cuál es el modelo de tu primer auto?">¿Cuál es el modelo de tu primer auto?</option>
                                    <option value="¿Cuál es el nombre de tu mejor amigo?">¿Cuál es el nombre de tu mejor amigo?</option>
                                    <option value="¿Cuál es tu color favorito?">¿Cuál es tu color favorito?</option>
                                    <option value="¿Cuál es tu deporte favorito?">¿Cuál es tu deporte favorito?</option>
                                    <option value="¿Cuál es el nombre de tu primera escuela?">¿Cuál es el nombre de tu primera escuela?</option>
                                    <option value="¿Cuál es el nombre de tu primer jefe?">¿Cuál es el nombre de tu primer jefe?</option>
                                    <option value="¿Cuál es tu lugar favorito para vacacionar?">¿Cuál es tu lugar favorito para vacacionar?</option>
                                    <option value="¿Cuál es el nombre de tu tío favorito?">¿Cuál es el nombre de tu tío favorito?</option>
                                </select>
                                @error('security_question_2')
                                    <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="security_answer_2">Respuesta 2 *</label>
                                <input type="text" name="security_answer_2" id="security_answer_2" required
                                       class="form-input @error('security_answer_2') form-input-error @enderror"
                                       placeholder="Tu respuesta" maxlength="255" autocomplete="off">
                                @error('security_answer_2')
                                    <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Pregunta 3 --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div class="form-group">
                                <label for="security_question_3">Pregunta 3 *</label>
                                <select name="security_question_3" id="security_question_3" required autocomplete="off"
                                        class="form-select security-question @error('security_question_3') form-input-error @enderror">
                                    <option value="">Selecciona una pregunta</option>
                                    <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                                    <option value="¿Cuál es el apellido de soltera de tu madre?">¿Cuál es el apellido de soltera de tu madre?</option>
                                    <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
                                    <option value="¿Cuál es tu comida favorita?">¿Cuál es tu comida favorita?</option>
                                    <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?">¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                                    <option value="¿Cuál es el título de tu libro favorito?">¿Cuál es el título de tu libro favorito?</option>
                                    <option value="¿Cuál es el nombre de tu profesor favorito?">¿Cuál es el nombre de tu profesor favorito?</option>
                                    <option value="¿En qué año te graduaste de la escuela?">¿En qué año te graduaste de la escuela?</option>
                                    <option value="¿Cuál es el nombre de tu primer amor?">¿Cuál es el nombre de tu primer amor?</option>
                                    <option value="¿Cuál es el nombre de tu abuelo favorito?">¿Cuál es el nombre de tu abuelo favorito?</option>
                                    <option value="¿Cuál es el nombre de tu hijo/a?">¿Cuál es el nombre de tu hijo/a?</option>
                                    <option value="¿Cuál es el nombre de tu padre?">¿Cuál es el nombre de tu padre?</option>
                                    <option value="¿Cuál es el modelo de tu primer auto?">¿Cuál es el modelo de tu primer auto?</option>
                                    <option value="¿Cuál es el nombre de tu mejor amigo?">¿Cuál es el nombre de tu mejor amigo?</option>
                                    <option value="¿Cuál es tu color favorito?">¿Cuál es tu color favorito?</option>
                                    <option value="¿Cuál es tu deporte favorito?">¿Cuál es tu deporte favorito?</option>
                                    <option value="¿Cuál es el nombre de tu primera escuela?">¿Cuál es el nombre de tu primera escuela?</option>
                                    <option value="¿Cuál es el nombre de tu primer jefe?">¿Cuál es el nombre de tu primer jefe?</option>
                                    <option value="¿Cuál es tu lugar favorito para vacacionar?">¿Cuál es tu lugar favorito para vacacionar?</option>
                                    <option value="¿Cuál es el nombre de tu tío favorito?">¿Cuál es el nombre de tu tío favorito?</option>
                                </select>
                                @error('security_question_3')
                                    <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="security_answer_3">Respuesta 3 *</label>
                                <input type="text" name="security_answer_3" id="security_answer_3" required
                                       class="form-input @error('security_answer_3') form-input-error @enderror"
                                       placeholder="Tu respuesta" maxlength="255" autocomplete="off">
                                @error('security_answer_3')
                                    <p class="error-message"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Términos --}}
                    <div class="flex items-center gap-2 mt-1">
                        <input id="terms" name="terms" type="checkbox" required autocomplete="off" class="checkbox-custom" {{ old('terms') ? 'checked' : '' }}>
                        <label for="terms" class="text-xs text-slate-600 select-none">
                            Acepto los <a href="#" class="text-mso-gold hover:text-slate-900 transition-colors font-medium">Términos y Condiciones</a>
                        </label>
                    </div>

                    {{-- Botones Paso 3 --}}
                    <div class="flex justify-between pt-2">
                        <button type="button" onclick="goToStep(2)"
                                class="px-6 py-2 bg-slate-200 text-slate-700 font-medium rounded-sm hover:bg-slate-300 transition-colors text-sm">
                            <i class="ph ph-arrow-left mr-1"></i> Anterior
                        </button>
                        <button type="submit" id="register-submit"
                                class="px-8 py-2 bg-mso-gold text-slate-900 font-bold rounded-sm hover:bg-yellow-600 transition-colors shadow-lg text-sm">
                            <i class="ph ph-check-circle mr-1"></i> CREAR CUENTA
                        </button>
                    </div>
                </div>

                {{-- Separador --}}
                <div class="auth-divider">
                    <span></span>
                    <p>o</p>
                    <span></span>
                </div>

                {{-- Login --}}
                <div class="text-center">
                    <p class="text-xs text-slate-500">
                        ¿Ya tienes una cuenta?
                        <a href="{{ route('login') }}" class="link-login">
                            Iniciar Sesión
                        </a>
                    </p>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="auth-footer">
            <a href="{{ route('login') }}" class="link-tab">
                Iniciar Sesión
            </a>
            <button class="link-tab-active">
                Registrarse
            </button>
        </div>
    </div>
</div>

@push('js')
<script>
    // ============================================
    //  NAVEGACIÓN ENTRE PASOS
    // ============================================
    let currentStep = 1;
    const totalSteps = 3;
    let isSubmitting = false;
    let fieldChecks = {};

    // ============================================
    //  VERIFICACIÓN DE UNICIDAD EN VIVO (AJAX)
    // ============================================
    const registerCheckUrl = '{{ route('api.register.check-field') }}';

    function runUniqueChecks() {
        const fields = ['email', 'id_number', 'phone'];
        fields.forEach(field => {
            const input = document.getElementById(field);
            if (!input) return;
            if (field === 'phone') {
                const code = document.getElementById('phone_code');
                const codeDigits = code ? code.value.replace(/[^0-9]/g, '') : '58';
                const numDigits = input.value.replace(/[^0-9]/g, '');
                checkUniqueField('phone', numDigits ? '+' + codeDigits + numDigits : '');
            } else {
                checkUniqueField(field, input.value.trim());
            }
        });
    }

    function checkUniqueField(field, value) {
        if (!value) {
            fieldChecks[field] = true;
            const errorDiv = document.getElementById(field + '-error');
            if (errorDiv) errorDiv.classList.add('hidden');
            const input = document.getElementById(field);
            if (input) input.style.borderBottomColor = '';
            return;
        }

        fetch(registerCheckUrl + '?field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(value), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            fieldChecks[field] = data.available;
            const errorDiv = document.getElementById(field + '-error');
            const input = document.getElementById(field);
            if (!data.available) {
                if (errorDiv) errorDiv.classList.remove('hidden');
                if (input) input.style.borderBottomColor = '#ef4444';
            } else {
                if (errorDiv) errorDiv.classList.add('hidden');
                if (input) input.style.borderBottomColor = '';
            }
        })
        .catch(() => {
            fieldChecks[field] = true;
        });
    }

    function attachUniqueCheck(input, field, transform) {
        if (!input) return;

        let timeout = null;

        const doCheck = function() {
            let value = input.value.trim();
            if (typeof transform === 'function') value = transform();
            checkUniqueField(field, value);
        };

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(doCheck, 600);
        });
        input.addEventListener('blur', doCheck);
    }

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        if (isSubmitting) return;

        // Validar paso actual antes de avanzar
        if (step > currentStep) {
            if (!validateStep(currentStep)) {
                return;
            }
        }

        // Ocultar todos los pasos
        for (let i = 1; i <= totalSteps; i++) {
            const stepEl = document.getElementById(`step-${i}`);
            if (stepEl) stepEl.classList.add('hidden');
        }

        // Mostrar el paso seleccionado
        const targetStep = document.getElementById(`step-${step}`);
        if (targetStep) targetStep.classList.remove('hidden');

        currentStep = step;
    }

    function getFieldLabel(el) {
        if (el.dataset && el.dataset.label) return el.dataset.label;
        if (el.id) {
            try {
                const label = document.querySelector('label[for="' + CSS.escape(el.id) + '"]');
                if (label) return (label.textContent || '').replace(/\s*\*\s*$/, '').trim();
            } catch (e) {}
        }
        if (el.placeholder && el.placeholder.trim()) return el.placeholder.trim();
        if (el.name) {
            return el.name
                .replace(/[\[\]]/g, ' ')
                .replace(/[_.]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim()
                .replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        }
        return 'Campo requerido';
    }

    function collectStepMissing(step) {
        const missing = [];
        const stepEl = document.getElementById('step-' + step);
        if (!stepEl) return missing;

        stepEl.querySelectorAll('input, select, textarea').forEach(function(el) {
            if (el.disabled) return;
            const type = (el.type || '').toLowerCase();
            if (type === 'submit' || type === 'button' || type === 'hidden') return;

            if (type === 'checkbox' || type === 'radio') {
                if (el.required && !el.checked) missing.push(getFieldLabel(el));
                return;
            }

            if (!el.required) return;
            const value = (el.value || '').trim();

            if (type === 'email' || /email/i.test(el.name || '')) {
                if (!value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) missing.push(getFieldLabel(el));
                return;
            }

            if (!value) missing.push(getFieldLabel(el));
        });

        return missing;
    }

    function showMissing(labels) {
        const unique = labels.filter(function(v, i, a) { return a.indexOf(v) === i; });
        if (window.showMissingFieldsModal) {
            window.showMissingFieldsModal(unique);
        } else {
            alert('Faltan campos por completar: ' + unique.join(', '));
        }
    }

    function getPhoneConfig() {
        const select = document.getElementById('phone_code');
        const option = select && select.selectedOptions && select.selectedOptions[0];
        return {
            min: parseInt(option && option.dataset.min, 10) || 7,
            max: parseInt(option && option.dataset.max, 10) || 15,
            format: (option && option.dataset.format) || ''
        };
    }

    function applyPhoneConfig() {
        const cfg = getPhoneConfig();
        const phone = document.getElementById('phone');
        if (!phone) return;
        phone.maxLength = cfg.max;
        if (cfg.format) {
            phone.placeholder = cfg.format.replace(/0/g, 'x');
        }
        const fmtErr = document.getElementById('phone-format-error');
        if (fmtErr) fmtErr.classList.add('hidden');
        phone.style.borderBottomColor = '';
    }

    function validateStep(step) {
        let isValid = true;
        const missing = [];

        if (step === 1) {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const phoneFormatError = document.getElementById('phone-format-error');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneDigits = phone.value.replace(/[^0-9]/g, '');
            const pc = getPhoneConfig();

            if (!name.value || name.value.trim().length < 2) {
                name.style.borderBottomColor = '#ef4444';
                missing.push('Nombre Completo');
                isValid = false;
            } else {
                name.style.borderBottomColor = '';
            }

            if (!email.value || !emailPattern.test(email.value)) {
                email.style.borderBottomColor = '#ef4444';
                missing.push('Correo Electrónico');
                isValid = false;
            } else {
                email.style.borderBottomColor = '';
            }

            if (!phoneDigits || phoneDigits.length < pc.min || phoneDigits.length > pc.max) {
                phone.style.borderBottomColor = '#ef4444';
                if (phoneFormatError) phoneFormatError.classList.remove('hidden');
                missing.push('Teléfono');
                isValid = false;
            } else {
                phone.style.borderBottomColor = '#22c55e';
                if (phoneFormatError) phoneFormatError.classList.add('hidden');
            }

            const needsCheck = ['email', 'phone', 'id_number'].some(function(f) {
                const input = document.getElementById(f);
                if (fieldChecks[f] !== undefined) return false;
                return input && input.value.trim() !== '';
            });

            if (needsCheck) {
                isValid = false;
                runUniqueChecks();
                missing.push('Verificación de disponibilidad de correo, cédula y teléfono');
            } else {
                if (fieldChecks.email === false) {
                    email.style.borderBottomColor = '#ef4444';
                    missing.push('Correo Electrónico (ya está en uso)');
                    isValid = false;
                }
                if (fieldChecks.id_number === false) {
                    document.getElementById('id_number').style.borderBottomColor = '#ef4444';
                    missing.push('Número de ID (ya está en uso)');
                    isValid = false;
                }
                if (fieldChecks.phone === false) {
                    phone.style.borderBottomColor = '#ef4444';
                    missing.push('Teléfono (ya está en uso)');
                    isValid = false;
                }
            }

            missing.push.apply(missing, collectStepMissing(1));
        }

        if (step === 2) {
            const country = document.getElementById('reg_country_id');
            if (!country.value) {
                country.style.borderBottomColor = '#ef4444';
                missing.push('País');
                isValid = false;
            } else {
                country.style.borderBottomColor = '';
            }

            missing.push.apply(missing, collectStepMissing(2));
        }

        if (step === 3) {
            const pass = document.getElementById('password');
            const passConfirm = document.getElementById('password_confirmation');
            const passError = document.getElementById('pass-error');
            if (pass && passConfirm && passConfirm.value !== pass.value) {
                passConfirm.style.borderBottomColor = '#ef4444';
                if (passError) passError.classList.remove('hidden');
                missing.push('Confirmación de Contraseña');
                isValid = false;
            } else if (passConfirm) {
                passConfirm.style.borderBottomColor = '';
                if (passError) passError.classList.add('hidden');
            }

            missing.push.apply(missing, collectStepMissing(3));
        }

        if (missing.length) {
            showMissing(missing);
            return false;
        }

        return isValid;
    }

    // ============================================
    //  VALIDACIÓN DE CONTRASEÑA EN TIEMPO REAL
    // ============================================
    function validatePassword(input) {
        const password = input.value;
        const strengthDiv = document.getElementById('password-strength');

        if (password.length === 0) {
            strengthDiv.innerHTML = 'Debe tener: Mayúscula, minúscula, número y caracter especial';
            strengthDiv.className = 'text-xs mt-1 text-slate-500';
            return;
        }

        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[@$!%*?&]/.test(password);
        const isValidLength = password.length >= 10;

        const requirements = [];
        if (!isValidLength) requirements.push('mínimo 10 caracteres');
        if (!hasUpper) requirements.push('mayúscula');
        if (!hasLower) requirements.push('minúscula');
        if (!hasNumber) requirements.push('número');
        if (!hasSpecial) requirements.push('caracter especial (@$!%*?&)');

        if (requirements.length === 0) {
            strengthDiv.innerHTML = ' Contraseña segura';
            strengthDiv.className = 'text-xs mt-1 text-green-600';
        } else {
            strengthDiv.innerHTML = ' Falta: ' + requirements.join(', ');
            strengthDiv.className = 'text-xs mt-1 text-red-500';
        }
    }

    // ============================================
    //  REFRESCAR TOKEN CSRF
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
                const tokenInput = document.querySelector('#register-form input[name="_token"]');
                if (tokenInput) {
                    tokenInput.value = data.csrf_token;
                }
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.content = data.csrf_token;
                }
            }
        })
        .catch(() => {
            console.warn(' No se pudo refrescar el token en registro');
        });
    }

    // ============================================
    //  INICIALIZAR
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const pass = document.getElementById('password');
        const passConfirm = document.getElementById('password_confirmation');
        const passError = document.getElementById('pass-error');

        if (pass && passConfirm) {
            passConfirm.addEventListener('input', function() {
                if (this.value && this.value !== pass.value) {
                    this.style.borderBottomColor = '#ef4444';
                    passError.classList.remove('hidden');
                } else {
                    this.style.borderBottomColor = '';
                    passError.classList.add('hidden');
                }
            });
        }

        // Inicializar ubicaciones
        initLocationSelects();

        // ============================================
        //  VERIFICAR UNICIDAD (CORREO, CÉDULA, TELÉFONO)
        // ============================================
        const emailInput = document.getElementById('email');
        const idNumberInput = document.getElementById('id_number');
        const phoneInput = document.getElementById('phone');
        const phoneCodeSelect = document.getElementById('phone_code');

        // Teléfono: solo dígitos + limpiar errores al escribir
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                const fmtErr = document.getElementById('phone-format-error');
                if (fmtErr) fmtErr.classList.add('hidden');
                const pErr = document.getElementById('phone-error');
                if (pErr) pErr.classList.add('hidden');
                this.style.borderBottomColor = '';
            });
        }

        // Teléfono completo = código + número
        const phoneTransform = function() {
            const code = phoneCodeSelect ? phoneCodeSelect.value.replace(/[^0-9]/g, '') : '58';
            const num = phoneInput ? phoneInput.value.replace(/[^0-9]/g, '') : '';
            return '+' + code + num;
        };

        attachUniqueCheck(emailInput, 'email', null);
        attachUniqueCheck(idNumberInput, 'id_number', null);
        attachUniqueCheck(phoneInput, 'phone', phoneTransform);

        applyPhoneConfig();
        if (phoneCodeSelect) {
            phoneCodeSelect.addEventListener('change', function() {
                applyPhoneConfig();
                if (phoneInput && phoneInput.value.trim()) {
                    checkUniqueField('phone', phoneTransform());
                }
            });
        }

        // ============================================
        //  REFRESCAR TOKEN CADA 5 MINUTOS
        // ============================================
        setInterval(refreshCsrfToken, 300000);

        // ============================================
        //  VERIFICAR SESIÓN EXPIRADA
        // ============================================
        if (window.location.search.includes('session_expired')) {
            alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
            const url = new URL(window.location);
            url.searchParams.delete('session_expired');
            window.history.replaceState({}, document.title, url.toString());
        }

        // ============================================
        //  ENVÍO DEL FORMULARIO CON PREVENCIÓN DE DOBLE CLIC
        // ============================================
        const registerForm = document.getElementById('register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                // Asegurar que las verificaciones de unicidad estén completas
                const pendingChecks = ['email', 'id_number', 'phone'].some(function(f) {
                    const input = document.getElementById(f);
                    const hasValue = input && input.value.trim() !== '';
                    return hasValue && fieldChecks[f] === undefined;
                });

                if (pendingChecks) {
                    e.preventDefault();
                    runUniqueChecks();
                    showMissing(['Verificación de disponibilidad de correo, cédula y teléfono']);
                    return false;
                }

                if (!validateStep(3)) {
                    e.preventDefault();
                    return false;
                }

                isSubmitting = true;
                const submitBtn = document.getElementById('register-submit');
                const originalText = submitBtn.textContent;

                submitBtn.textContent = ' ENVIANDO...';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'wait';

                return true;
            });
        }
    });

    // ============================================
    //  UBICACIONES
    // ============================================
    function initLocationSelects() {
        const countrySelect = document.getElementById('reg_country_id');
        const stateSelect = document.getElementById('reg_state_id');
        const municipalitySelect = document.getElementById('reg_municipality_id');
        const parishSelect = document.getElementById('reg_parish_id');
        const citySelect = document.getElementById('reg_city_id');

        if (!countrySelect) return;

        function addOptions(select, items) {
            items.forEach(function(item) {
                var option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });
        }

        // Cargar países
        fetch('/api/locations/countries')
            .then(response => response.json())
            .then(countries => {
                countrySelect.innerHTML = '<option value="">Seleccione un país</option>';
                addOptions(countrySelect, countries);
            })
            .catch(error => {
                console.error('Error cargando países:', error);
                countrySelect.innerHTML = '<option value="">Error cargando países</option>';
            });

        // Cargar estados al seleccionar país
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/locations/states/${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        stateSelect.disabled = false;
                        stateSelect.innerHTML = '<option value="">Seleccione un estado</option>';
                        addOptions(stateSelect, states);
                        municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
                        municipalitySelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        parishSelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                        citySelect.disabled = true;
                    });
            } else {
                stateSelect.disabled = true;
                stateSelect.innerHTML = '<option value="">Primero seleccione un país</option>';
            }
        });

        // Cargar municipios al seleccionar estado
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            if (stateId) {
                fetch(`/api/locations/municipalities/${stateId}`)
                    .then(response => response.json())
                    .then(municipalities => {
                        municipalitySelect.disabled = false;
                        municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                        addOptions(municipalitySelect, municipalities);
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        parishSelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                        citySelect.disabled = true;
                    });
            } else {
                municipalitySelect.disabled = true;
                municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
            }
        });

        // Cargar parroquias al seleccionar municipio
        municipalitySelect.addEventListener('change', function() {
            const municipalityId = this.value;
            if (municipalityId) {
                fetch(`/api/locations/parishes/${municipalityId}`)
                    .then(response => response.json())
                    .then(parishes => {
                        parishSelect.disabled = false;
                        parishSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                        addOptions(parishSelect, parishes);
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                        citySelect.disabled = true;
                    });
            } else {
                parishSelect.disabled = true;
                parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
            }
        });

        // Cargar ciudades al seleccionar parroquia
        parishSelect.addEventListener('change', function() {
            const parishId = this.value;
            if (parishId) {
                fetch(`/api/locations/cities/${parishId}`)
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.disabled = false;
                        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                        addOptions(citySelect, cities);
                    });
            } else {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
            }
        });
    }

    // Exponer funciones globales
    window.goToStep = goToStep;
    window.validatePassword = validatePassword;
    window.refreshCsrfToken = refreshCsrfToken;
</script>
@endpush
@endsection
