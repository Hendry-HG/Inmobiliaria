{{-- Formulario de creación de nuevo usuario --}}
@extends('layouts.dashboard')

@section('title', 'Crear Usuario')
@section('header', 'Crear Nuevo Usuario')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate id="create-user-form" data-no-missing-modal>
        @csrf

        <!-- Campos falsos para engañar al autocompletado -->
        <div style="position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; opacity: 0;">
            <input type="text" name="fake_username" autocomplete="username" tabindex="-1">
            <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1">
            <input type="email" name="fake_email" autocomplete="email" tabindex="-1">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Columna izquierda -->
            <div class="space-y-4">
                {{-- NOMBRE --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- APELLIDO --}}
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text"
                           name="last_name"
                           id="last_name"
                           value="{{ old('last_name') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('last_name') border-red-500 @enderror">
                    @error('last_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- EMAIL --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('email') border-red-500 @enderror"
                           required>
                    <div id="email-error" class="text-red-500 text-xs mt-1 hidden">Este correo ya está en uso por otro usuario.</div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CONTRASEÑA --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
                    <input type="password"
                           name="password"
                           id="password"
                           autocomplete="new-password"
                           data-lpignore="true"
                           minlength="10"
                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$"
                           oninput="validatePassword(this)"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('password') border-red-500 @enderror"
                           required>
                    <div id="password-strength" class="text-xs mt-1 text-slate-500">Mínimo 10 caracteres, con mayúscula, minúscula, número y carácter especial</div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CONFIRMAR CONTRASEÑA --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña *</label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           autocomplete="new-password"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold"
                           required>
                    <div id="pass-error" class="text-red-500 text-xs mt-1 hidden">Las contraseñas no coinciden</div>
                </div>

                {{-- PREGUNTAS DE SEGURIDAD --}}
                <div class="border-t pt-4 mt-4">
                    <h4 class="text-sm font-bold text-gray-700 mb-1 flex items-center gap-2">
                        <i class="ph ph-shield-check text-mso-gold"></i>
                        Preguntas de Seguridad
                    </h4>
                    <p class="text-xs text-gray-500 mb-3">El usuario podrá recuperar su contraseña respondiendo estas preguntas.</p>
                    @error('security_question')
                        <p class="text-red-500 text-xs mb-2">{{ $message }}</p>
                    @enderror

                    {{-- Pregunta 1 --}}
                    <div class="mb-3">
                        <label for="security_question_1" class="block text-xs font-medium text-gray-600 mb-1">Pregunta 1 *</label>
                        <select name="security_question_1" id="security_question_1" required autocomplete="off"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold text-sm @error('security_question_1') border-red-500 @enderror">
                            <option value="">Selecciona una pregunta</option>
                            <option value="¿Cuál es el nombre de tu primera mascota?" {{ old('security_question_1') == '¿Cuál es el nombre de tu primera mascota?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primera mascota?</option>
                            <option value="¿Cuál es el apellido de soltera de tu madre?" {{ old('security_question_1') == '¿Cuál es el apellido de soltera de tu madre?' ? 'selected' : '' }}>¿Cuál es el apellido de soltera de tu madre?</option>
                            <option value="¿En qué ciudad naciste?" {{ old('security_question_1') == '¿En qué ciudad naciste?' ? 'selected' : '' }}>¿En qué ciudad naciste?</option>
                            <option value="¿Cuál es tu comida favorita?" {{ old('security_question_1') == '¿Cuál es tu comida favorita?' ? 'selected' : '' }}>¿Cuál es tu comida favorita?</option>
                            <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?" {{ old('security_question_1') == '¿Cuál es el nombre de tu mejor amigo de la infancia?' ? 'selected' : '' }}>¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                            <option value="¿Cuál es el título de tu libro favorito?" {{ old('security_question_1') == '¿Cuál es el título de tu libro favorito?' ? 'selected' : '' }}>¿Cuál es el título de tu libro favorito?</option>
                            <option value="¿Cuál es el nombre de tu profesor favorito?" {{ old('security_question_1') == '¿Cuál es el nombre de tu profesor favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu profesor favorito?</option>
                            <option value="¿En qué año te graduaste de la escuela?" {{ old('security_question_1') == '¿En qué año te graduaste de la escuela?' ? 'selected' : '' }}>¿En qué año te graduaste de la escuela?</option>
                            <option value="¿Cuál es el nombre de tu primer amor?" {{ old('security_question_1') == '¿Cuál es el nombre de tu primer amor?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primer amor?</option>
                            <option value="¿Cuál es el nombre de tu abuelo favorito?" {{ old('security_question_1') == '¿Cuál es el nombre de tu abuelo favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu abuelo favorito?</option>
                            <option value="¿Cuál es el nombre de tu hijo/a?" {{ old('security_question_1') == '¿Cuál es el nombre de tu hijo/a?' ? 'selected' : '' }}>¿Cuál es el nombre de tu hijo/a?</option>
                            <option value="¿Cuál es el nombre de tu padre?" {{ old('security_question_1') == '¿Cuál es el nombre de tu padre?' ? 'selected' : '' }}>¿Cuál es el nombre de tu padre?</option>
                            <option value="¿Cuál es el modelo de tu primer auto?" {{ old('security_question_1') == '¿Cuál es el modelo de tu primer auto?' ? 'selected' : '' }}>¿Cuál es el modelo de tu primer auto?</option>
                            <option value="¿Cuál es el nombre de tu mejor amigo?" {{ old('security_question_1') == '¿Cuál es el nombre de tu mejor amigo?' ? 'selected' : '' }}>¿Cuál es el nombre de tu mejor amigo?</option>
                            <option value="¿Cuál es tu color favorito?" {{ old('security_question_1') == '¿Cuál es tu color favorito?' ? 'selected' : '' }}>¿Cuál es tu color favorito?</option>
                            <option value="¿Cuál es tu deporte favorito?" {{ old('security_question_1') == '¿Cuál es tu deporte favorito?' ? 'selected' : '' }}>¿Cuál es tu deporte favorito?</option>
                            <option value="¿Cuál es el nombre de tu primera escuela?" {{ old('security_question_1') == '¿Cuál es el nombre de tu primera escuela?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primera escuela?</option>
                            <option value="¿Cuál es el nombre de tu primer jefe?" {{ old('security_question_1') == '¿Cuál es el nombre de tu primer jefe?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primer jefe?</option>
                            <option value="¿Cuál es tu lugar favorito para vacacionar?" {{ old('security_question_1') == '¿Cuál es tu lugar favorito para vacacionar?' ? 'selected' : '' }}>¿Cuál es tu lugar favorito para vacacionar?</option>
                            <option value="¿Cuál es el nombre de tu tío favorito?" {{ old('security_question_1') == '¿Cuál es el nombre de tu tío favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu tío favorito?</option>
                        </select>
                        <input type="text" name="security_answer_1" id="security_answer_1" placeholder="Respuesta" required
                               aria-label="Respuesta a la pregunta 1"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold text-sm mt-1 @error('security_answer_1') border-red-500 @enderror"
                               value="{{ old('security_answer_1') }}" maxlength="255" autocomplete="off">
                        @error('security_question_1')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('security_answer_1')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pregunta 2 --}}
                    <div class="mb-3">
                        <label for="security_question_2" class="block text-xs font-medium text-gray-600 mb-1">Pregunta 2 *</label>
                        <select name="security_question_2" id="security_question_2" required autocomplete="off"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold text-sm @error('security_question_2') border-red-500 @enderror">
                            <option value="">Selecciona una pregunta</option>
                            <option value="¿Cuál es el nombre de tu primera mascota?" {{ old('security_question_2') == '¿Cuál es el nombre de tu primera mascota?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primera mascota?</option>
                            <option value="¿Cuál es el apellido de soltera de tu madre?" {{ old('security_question_2') == '¿Cuál es el apellido de soltera de tu madre?' ? 'selected' : '' }}>¿Cuál es el apellido de soltera de tu madre?</option>
                            <option value="¿En qué ciudad naciste?" {{ old('security_question_2') == '¿En qué ciudad naciste?' ? 'selected' : '' }}>¿En qué ciudad naciste?</option>
                            <option value="¿Cuál es tu comida favorita?" {{ old('security_question_2') == '¿Cuál es tu comida favorita?' ? 'selected' : '' }}>¿Cuál es tu comida favorita?</option>
                            <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?" {{ old('security_question_2') == '¿Cuál es el nombre de tu mejor amigo de la infancia?' ? 'selected' : '' }}>¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                            <option value="¿Cuál es el título de tu libro favorito?" {{ old('security_question_2') == '¿Cuál es el título de tu libro favorito?' ? 'selected' : '' }}>¿Cuál es el título de tu libro favorito?</option>
                            <option value="¿Cuál es el nombre de tu profesor favorito?" {{ old('security_question_2') == '¿Cuál es el nombre de tu profesor favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu profesor favorito?</option>
                            <option value="¿En qué año te graduaste de la escuela?" {{ old('security_question_2') == '¿En qué año te graduaste de la escuela?' ? 'selected' : '' }}>¿En qué año te graduaste de la escuela?</option>
                            <option value="¿Cuál es el nombre de tu primer amor?" {{ old('security_question_2') == '¿Cuál es el nombre de tu primer amor?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primer amor?</option>
                            <option value="¿Cuál es el nombre de tu abuelo favorito?" {{ old('security_question_2') == '¿Cuál es el nombre de tu abuelo favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu abuelo favorito?</option>
                            <option value="¿Cuál es el nombre de tu hijo/a?" {{ old('security_question_2') == '¿Cuál es el nombre de tu hijo/a?' ? 'selected' : '' }}>¿Cuál es el nombre de tu hijo/a?</option>
                            <option value="¿Cuál es el nombre de tu padre?" {{ old('security_question_2') == '¿Cuál es el nombre de tu padre?' ? 'selected' : '' }}>¿Cuál es el nombre de tu padre?</option>
                            <option value="¿Cuál es el modelo de tu primer auto?" {{ old('security_question_2') == '¿Cuál es el modelo de tu primer auto?' ? 'selected' : '' }}>¿Cuál es el modelo de tu primer auto?</option>
                            <option value="¿Cuál es el nombre de tu mejor amigo?" {{ old('security_question_2') == '¿Cuál es el nombre de tu mejor amigo?' ? 'selected' : '' }}>¿Cuál es el nombre de tu mejor amigo?</option>
                            <option value="¿Cuál es tu color favorito?" {{ old('security_question_2') == '¿Cuál es tu color favorito?' ? 'selected' : '' }}>¿Cuál es tu color favorito?</option>
                            <option value="¿Cuál es tu deporte favorito?" {{ old('security_question_2') == '¿Cuál es tu deporte favorito?' ? 'selected' : '' }}>¿Cuál es tu deporte favorito?</option>
                            <option value="¿Cuál es el nombre de tu primera escuela?" {{ old('security_question_2') == '¿Cuál es el nombre de tu primera escuela?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primera escuela?</option>
                            <option value="¿Cuál es el nombre de tu primer jefe?" {{ old('security_question_2') == '¿Cuál es el nombre de tu primer jefe?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primer jefe?</option>
                            <option value="¿Cuál es tu lugar favorito para vacacionar?" {{ old('security_question_2') == '¿Cuál es tu lugar favorito para vacacionar?' ? 'selected' : '' }}>¿Cuál es tu lugar favorito para vacacionar?</option>
                            <option value="¿Cuál es el nombre de tu tío favorito?" {{ old('security_question_2') == '¿Cuál es el nombre de tu tío favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu tío favorito?</option>
                        </select>
                        <input type="text" name="security_answer_2" id="security_answer_2" placeholder="Respuesta" required
                               aria-label="Respuesta a la pregunta 2"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold text-sm mt-1 @error('security_answer_2') border-red-500 @enderror"
                               value="{{ old('security_answer_2') }}" maxlength="255" autocomplete="off">
                        @error('security_question_2')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('security_answer_2')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pregunta 3 --}}
                    <div class="mb-3">
                        <label for="security_question_3" class="block text-xs font-medium text-gray-600 mb-1">Pregunta 3 *</label>
                        <select name="security_question_3" id="security_question_3" required autocomplete="off"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold text-sm @error('security_question_3') border-red-500 @enderror">
                            <option value="">Selecciona una pregunta</option>
                            <option value="¿Cuál es el nombre de tu primera mascota?" {{ old('security_question_3') == '¿Cuál es el nombre de tu primera mascota?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primera mascota?</option>
                            <option value="¿Cuál es el apellido de soltera de tu madre?" {{ old('security_question_3') == '¿Cuál es el apellido de soltera de tu madre?' ? 'selected' : '' }}>¿Cuál es el apellido de soltera de tu madre?</option>
                            <option value="¿En qué ciudad naciste?" {{ old('security_question_3') == '¿En qué ciudad naciste?' ? 'selected' : '' }}>¿En qué ciudad naciste?</option>
                            <option value="¿Cuál es tu comida favorita?" {{ old('security_question_3') == '¿Cuál es tu comida favorita?' ? 'selected' : '' }}>¿Cuál es tu comida favorita?</option>
                            <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?" {{ old('security_question_3') == '¿Cuál es el nombre de tu mejor amigo de la infancia?' ? 'selected' : '' }}>¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                            <option value="¿Cuál es el título de tu libro favorito?" {{ old('security_question_3') == '¿Cuál es el título de tu libro favorito?' ? 'selected' : '' }}>¿Cuál es el título de tu libro favorito?</option>
                            <option value="¿Cuál es el nombre de tu profesor favorito?" {{ old('security_question_3') == '¿Cuál es el nombre de tu profesor favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu profesor favorito?</option>
                            <option value="¿En qué año te graduaste de la escuela?" {{ old('security_question_3') == '¿En qué año te graduaste de la escuela?' ? 'selected' : '' }}>¿En qué año te graduaste de la escuela?</option>
                            <option value="¿Cuál es el nombre de tu primer amor?" {{ old('security_question_3') == '¿Cuál es el nombre de tu primer amor?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primer amor?</option>
                            <option value="¿Cuál es el nombre de tu abuelo favorito?" {{ old('security_question_3') == '¿Cuál es el nombre de tu abuelo favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu abuelo favorito?</option>
                            <option value="¿Cuál es el nombre de tu hijo/a?" {{ old('security_question_3') == '¿Cuál es el nombre de tu hijo/a?' ? 'selected' : '' }}>¿Cuál es el nombre de tu hijo/a?</option>
                            <option value="¿Cuál es el nombre de tu padre?" {{ old('security_question_3') == '¿Cuál es el nombre de tu padre?' ? 'selected' : '' }}>¿Cuál es el nombre de tu padre?</option>
                            <option value="¿Cuál es el modelo de tu primer auto?" {{ old('security_question_3') == '¿Cuál es el modelo de tu primer auto?' ? 'selected' : '' }}>¿Cuál es el modelo de tu primer auto?</option>
                            <option value="¿Cuál es el nombre de tu mejor amigo?" {{ old('security_question_3') == '¿Cuál es el nombre de tu mejor amigo?' ? 'selected' : '' }}>¿Cuál es el nombre de tu mejor amigo?</option>
                            <option value="¿Cuál es tu color favorito?" {{ old('security_question_3') == '¿Cuál es tu color favorito?' ? 'selected' : '' }}>¿Cuál es tu color favorito?</option>
                            <option value="¿Cuál es tu deporte favorito?" {{ old('security_question_3') == '¿Cuál es tu deporte favorito?' ? 'selected' : '' }}>¿Cuál es tu deporte favorito?</option>
                            <option value="¿Cuál es el nombre de tu primera escuela?" {{ old('security_question_3') == '¿Cuál es el nombre de tu primera escuela?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primera escuela?</option>
                            <option value="¿Cuál es el nombre de tu primer jefe?" {{ old('security_question_3') == '¿Cuál es el nombre de tu primer jefe?' ? 'selected' : '' }}>¿Cuál es el nombre de tu primer jefe?</option>
                            <option value="¿Cuál es tu lugar favorito para vacacionar?" {{ old('security_question_3') == '¿Cuál es tu lugar favorito para vacacionar?' ? 'selected' : '' }}>¿Cuál es tu lugar favorito para vacacionar?</option>
                            <option value="¿Cuál es el nombre de tu tío favorito?" {{ old('security_question_3') == '¿Cuál es el nombre de tu tío favorito?' ? 'selected' : '' }}>¿Cuál es el nombre de tu tío favorito?</option>
                        </select>
                        <input type="text" name="security_answer_3" id="security_answer_3" placeholder="Respuesta" required
                               aria-label="Respuesta a la pregunta 3"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold text-sm mt-1 @error('security_answer_3') border-red-500 @enderror"
                               value="{{ old('security_answer_3') }}" maxlength="255" autocomplete="off">
                        @error('security_question_3')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('security_answer_3')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- TELÉFONO --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           value="{{ old('phone') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('phone') border-red-500 @enderror">
                    <div id="phone-error" class="text-red-500 text-xs mt-1 hidden">Este teléfono ya está en uso por otro usuario.</div>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="space-y-4">
                {{-- TIPO DE IDENTIFICACIÓN --}}
                <div>
                    <label for="id_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Identificación</label>
                    <select name="id_type" id="id_type" autocomplete="off" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                        <option value="">Seleccionar</option>
                        <option value="V" {{ old('id_type') == 'V' ? 'selected' : '' }}>Venezolano (V)</option>
                        <option value="E" {{ old('id_type') == 'E' ? 'selected' : '' }}>Extranjero (E)</option>
                        <option value="J" {{ old('id_type') == 'J' ? 'selected' : '' }}>Jurídico (J)</option>
                        <option value="P" {{ old('id_type') == 'P' ? 'selected' : '' }}>Pasaporte (P)</option>
                    </select>
                </div>

                {{-- NÚMERO DE IDENTIFICACIÓN --}}
                <div>
                    <label for="id_number" class="block text-sm font-medium text-gray-700 mb-1">Número de Identificación</label>
                    <input type="text"
                           name="id_number"
                           id="id_number"
                           value="{{ old('id_number') }}"
                           autocomplete="off"
                           spellcheck="false"
                           data-lpignore="true"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold @error('id_number') border-red-500 @enderror">
                    <div id="id_number-error" class="text-red-500 text-xs mt-1 hidden">Este número de identificación ya está en uso por otro usuario.</div>
                    @error('id_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ROL --}}
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                    <select name="role" id="role" autocomplete="off" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" required>
                        <option value="">Seleccionar rol</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PAÍS --}}
                <div>
                    <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">País</label>
                    <select name="country_id" id="country_id" autocomplete="off" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                        <option value="">Seleccionar país</option>
                        @foreach($countries ?? [] as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ESTADO --}}
                <div>
                    <label for="state_id" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="state_id" id="state_id" autocomplete="off" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione un país</option>
                    </select>
                </div>

                {{-- MUNICIPIO --}}
                <div>
                    <label for="municipality_id" class="block text-sm font-medium text-gray-700 mb-1">Municipio</label>
                    <select name="municipality_id" id="municipality_id" autocomplete="off" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione un estado</option>
                    </select>
                </div>

                {{-- PARROQUIA --}}
                <div>
                    <label for="parish_id" class="block text-sm font-medium text-gray-700 mb-1">Parroquia</label>
                    <select name="parish_id" id="parish_id" autocomplete="off" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione un municipio</option>
                    </select>
                </div>

                {{-- CIUDAD --}}
                <div>
                    <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <select name="city_id" id="city_id" autocomplete="off" class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold" disabled>
                        <option value="">Primero seleccione una parroquia</option>
                    </select>
                </div>

                {{-- DIRECCIÓN --}}
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <textarea name="address" id="address" autocomplete="street-address"
                              rows="2"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">{{ old('address') }}</textarea>
                </div>

                {{-- FOTO DE PERFIL --}}
                <div>
                    <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-1">Foto de Perfil</label>
                    <input type="file"
                           name="profile_photo"
                           id="profile_photo"
                           accept="image/*"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-mso-gold focus:border-mso-gold">
                    <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG. Máximo 2MB</p>
                </div>

                {{-- ESTADO ACTIVO --}}
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-mso-gold focus:ring-mso-gold">
                        <span class="ml-2 text-sm text-gray-700">Usuario activo</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-mso-gold text-mso-blue rounded-lg hover:bg-mso-blue hover:text-white transition-colors">
                Crear Usuario
            </button>
        </div>
    </form>
</div>

@push('js')
<script>
let fieldChecks = {};

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#create-user-form');
    const pass = document.getElementById('password');
    const passConfirm = document.getElementById('password_confirmation');
    const passError = document.getElementById('pass-error');
    const checkUrl = '{{ route('admin.users.check-field') }}';

    function attachUniqueCheck(input, errorDiv, field) {
        let timeout = null;
        const pattern = field === 'email' ? /^[^\s@]+@[^\s@]+\.[^\s@]+$/ : /^[0-9]{4,20}$/;

        const check = function() {
            const value = input.value.trim();

            if (!value) {
                errorDiv.classList.add('hidden');
                input.style.borderColor = '';
                fieldChecks[field] = true;
                return;
            }

            if (!pattern.test(value)) {
                errorDiv.classList.add('hidden');
                input.style.borderColor = '';
                fieldChecks[field] = true;
                return;
            }

            fetch(checkUrl + '?field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(value), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                fieldChecks[field] = data.available;
                if (!data.available) {
                    errorDiv.classList.remove('hidden');
                    input.style.borderColor = '#ef4444';
                } else {
                    errorDiv.classList.add('hidden');
                    input.style.borderColor = '';
                }
            })
            .catch(() => {
                fieldChecks[field] = true;
            });
        };

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(check, 500);
        });
        input.addEventListener('blur', check);
    }

    attachUniqueCheck(document.getElementById('email'), document.getElementById('email-error'), 'email');
    attachUniqueCheck(document.getElementById('id_number'), document.getElementById('id_number-error'), 'id_number');
    attachUniqueCheck(document.getElementById('phone'), document.getElementById('phone-error'), 'phone');

    if (passConfirm) {
        passConfirm.addEventListener('input', function() {
            if (this.value && this.value !== pass.value) {
                this.style.borderColor = '#ef4444';
                passError.classList.remove('hidden');
            } else {
                this.style.borderColor = '';
                passError.classList.add('hidden');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateUserForm()) {
                e.preventDefault();
                return false;
            }
        });
    }

    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const municipalitySelect = document.getElementById('municipality_id');
    const parishSelect = document.getElementById('parish_id');
    const citySelect = document.getElementById('city_id');

    // Cargar estados al seleccionar país
    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/locations/states/${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        stateSelect.disabled = false;
                        stateSelect.innerHTML = '<option value="">Seleccione un estado</option>';
                        states.forEach(state => {
                            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                        municipalitySelect.disabled = true;
                        municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                stateSelect.disabled = true;
                stateSelect.innerHTML = '<option value="">Primero seleccione un país</option>';
            }
        });
    }

    if (stateSelect) {
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            if (stateId) {
                fetch(`/api/locations/municipalities/${stateId}`)
                    .then(response => response.json())
                    .then(municipalities => {
                        municipalitySelect.disabled = false;
                        municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                        municipalities.forEach(municipality => {
                            municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
                        });
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                municipalitySelect.disabled = true;
                municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
            }
        });
    }

    if (municipalitySelect) {
        municipalitySelect.addEventListener('change', function() {
            const municipalityId = this.value;
            if (municipalityId) {
                fetch(`/api/locations/parishes/${municipalityId}`)
                    .then(response => response.json())
                    .then(parishes => {
                        parishSelect.disabled = false;
                        parishSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                        parishes.forEach(parish => {
                            parishSelect.innerHTML += `<option value="${parish.id}">${parish.name}</option>`;
                        });
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            } else {
                parishSelect.disabled = true;
                parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
            }
        });
    }

    if (parishSelect) {
        parishSelect.addEventListener('change', function() {
            const parishId = this.value;
            if (parishId) {
                fetch(`/api/locations/cities/${parishId}`)
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.disabled = false;
                        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                        cities.forEach(city => {
                            citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                    });
            } else {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
            }
        });
    }

    // Limpiar campos al cargar la página
    setTimeout(function() {
        document.querySelector('input[name="name"]').value = '';
        document.querySelector('input[name="last_name"]').value = '';
        document.querySelector('input[name="email"]').value = '';
        document.querySelector('input[name="password"]').value = '';
        document.querySelector('input[name="password_confirmation"]').value = '';
        document.querySelector('input[name="phone"]').value = '';
        document.querySelector('input[name="id_number"]').value = '';
    }, 100);
});

// ============================================
//  VALIDACIÓN DE CONTRASEÑA EN TIEMPO REAL
// ============================================
function validatePassword(input) {
    const password = input.value;
    const strengthDiv = document.getElementById('password-strength');

    if (password.length === 0) {
        strengthDiv.textContent = 'Mínimo 10 caracteres, con mayúscula, minúscula, número y carácter especial';
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
    if (!hasSpecial) requirements.push('carácter especial (@$!%*?&)');

    if (requirements.length === 0) {
        strengthDiv.textContent = ' Contraseña segura';
        strengthDiv.className = 'text-xs mt-1 text-green-600 font-medium';
    } else {
        strengthDiv.textContent = ' Falta: ' + requirements.join(', ');
        strengthDiv.className = 'text-xs mt-1 text-red-500';
    }
}

// ============================================
//  VALIDACIÓN DEL FORMULARIO AL ENVIAR
// ============================================
function validateUserForm() {
    const name = document.querySelector('input[name="name"]');
    const email = document.querySelector('input[name="email"]');
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    const role = document.querySelector('select[name="role"]');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const missing = [];

    if (!name.value || name.value.trim().length < 2) missing.push('el nombre completo');
    if (!email.value || !emailPattern.test(email.value)) missing.push('un correo electrónico válido');
    if (fieldChecks.email === false) missing.push('un correo que no esté en uso');
    if (fieldChecks.id_number === false) missing.push('un número de identificación que no esté en uso');
    if (fieldChecks.phone === false) missing.push('un teléfono que no esté en uso');

    const strength = getPasswordStrength(password.value);
    if (strength.length > 0) missing.push('una contraseña segura (' + strength.join(', ') + ')');
    if (password.value !== passwordConfirmation.value) missing.push('que las contraseñas coincidan');

    if (!role.value) missing.push('seleccionar un rol');

    const idType = document.querySelector('select[name="id_type"]');
    const idNumber = document.querySelector('input[name="id_number"]');
    const country = document.querySelector('select[name="country_id"]');
    const state = document.querySelector('select[name="state_id"]');
    const municipality = document.querySelector('select[name="municipality_id"]');
    const parish = document.querySelector('select[name="parish_id"]');
    const city = document.querySelector('select[name="city_id"]');
    const address = document.querySelector('textarea[name="address"]');

    if (!idType.value) missing.push('el tipo de identificación');
    if (!idNumber.value || !idNumber.value.trim()) missing.push('el número de identificación (cédula)');
    if (!country.value) missing.push('el país');
    if (!state.value) missing.push('el estado');
    if (!municipality.value) missing.push('el municipio');
    if (!parish.value) missing.push('la parroquia');
    if (!city.value) missing.push('la ciudad');
    if (!address.value || !address.value.trim()) missing.push('la dirección');

    const securityQuestions = ['security_question_1', 'security_question_2', 'security_question_3'];
    const selectedQuestions = [];
    securityQuestions.forEach(function(selName) {
        const select = document.querySelector('select[name="' + selName + '"]');
        const answer = document.querySelector('input[name="' + selName.replace('question', 'answer') + '"]');
        if (!select.value || (answer && answer.value.trim().length < 2)) missing.push('las preguntas de seguridad y sus respuestas');
        if (select.value) selectedQuestions.push(select.value);
    });

    if (new Set(selectedQuestions).size < 3) missing.push('3 preguntas de seguridad diferentes');

    if (missing.length > 0) {
        showMissingFieldsModal(missing.filter((v, i, a) => a.indexOf(v) === i));
        return false;
    }
    return true;
}

function getPasswordStrength(password) {
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[@$!%*?&]/.test(password);

    const requirements = [];
    if (password.length < 10) requirements.push('mínimo 10 caracteres');
    if (!hasUpper) requirements.push('mayúscula');
    if (!hasLower) requirements.push('minúscula');
    if (!hasNumber) requirements.push('número');
    if (!hasSpecial) requirements.push('carácter especial');
    return requirements;
}
</script>
@endpush
@endsection
