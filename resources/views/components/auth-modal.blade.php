<div id="auth-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[60] hidden flex items-center justify-center p-4 transition-opacity duration-300">
    <div class="bg-white rounded-sm shadow-2xl w-full max-w-lg overflow-hidden modal-enter relative border border-slate-100">

        <!-- ============================================ -->
        <!-- BOTÓN CERRAR -->
        <!-- ============================================ -->
        <button onclick="closeModal('auth-modal')"
                class="absolute top-5 right-5 text-slate-400 hover:text-slate-800 transition-colors z-10 bg-white/80 rounded-full p-1"
                aria-label="Cerrar modal">
            <i class="ph ph-x text-xl"></i>
        </button>

        <!-- ============================================ -->
        <!-- HEADER -->
        <!-- ============================================ -->
        <div class="bg-slate-50 p-6 border-b border-slate-100">
            <div class="flex justify-between items-center mb-2">
                <h3 id="modal-title" class="font-serif text-2xl font-bold text-slate-900">Bienvenido</h3>
                <!--  INDICADOR DE PASO - SOLO VISIBLE EN REGISTRO -->
                <span id="step-indicator" class="text-sm text-slate-400 font-medium hidden">Paso 1 de 3</span>
            </div>
            <p id="modal-subtitle" class="text-slate-500 text-sm">Ingresa a tu cuenta para continuar</p>

            <!--  BARRA DE PROGRESO - SOLO VISIBLE EN REGISTRO -->
            <div id="progress-bar-container" class="mt-4 flex gap-1 hidden">
                <div id="step-bar-1" class="h-1 flex-1 rounded-full bg-mso-gold transition-all duration-500"></div>
                <div id="step-bar-2" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-500"></div>
                <div id="step-bar-3" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-500"></div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- FAKE FIELDS PARA PREVENIR AUTOFILL -->
        <!-- ============================================ -->
        <div style="position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; opacity: 0; pointer-events: none;">
            <input type="text" name="fake_username" autocomplete="username" tabindex="-1">
            <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1">
            <input type="email" name="fake_email" autocomplete="email" tabindex="-1">
            <input type="text" name="fake_name" autocomplete="name" tabindex="-1">
            <input type="tel" name="fake_phone" autocomplete="tel" tabindex="-1">
        </div>

        <!-- ============================================ -->
        <!-- CONTENIDO DEL MODAL -->
        <!-- ============================================ -->
        <div class="p-6 md:p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">

            <!-- ============================================ -->
            <!-- FORMULARIO LOGIN (SIEMPRE VISIBLE) -->
            <!-- ============================================ -->
            <form id="form-login" class="space-y-6" method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <div>
                    <label for="login-email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico</label>
                    <input type="email" name="email" id="login-email" required placeholder="ejemplo@correo.com"
                           class="w-full border-b-2 border-slate-200 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                           autocomplete="email">
                </div>
                <div>
                    <label for="login-password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Contraseña</label>
                    <input type="password" name="password" id="login-password" required placeholder="••••••••"
                           class="w-full border-b-2 border-slate-200 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                           autocomplete="current-password">
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember"
                               class="rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                        <label for="remember" class="ml-2 text-xs text-slate-600">Recordarme</label>
                    </div>
                    <button type="button" onclick="switchAuthTab('forgot')" class="text-xs text-mso-gold hover:underline">
                        ¿Olvidaste tu contraseña?
                    </button>
                </div>
                <button type="submit" class="w-full bg-mso-blue text-white font-bold py-4 rounded-sm hover:bg-slate-800 transition-colors shadow-lg mt-4">
                    INICIAR SESIÓN
                </button>
            </form>

            <!-- ============================================ -->
            <!-- FORMULARIO RECUPERAR CONTRASEÑA -->
            <!-- ============================================ -->
            <form id="form-forgot-password" class="space-y-6 hidden" method="POST" action="{{ route('security.verify.email') }}" novalidate>
                @csrf
                <div class="text-center mb-4">
                    <h4 class="text-lg font-bold text-slate-900">Recuperar Contraseña</h4>
                    <p class="text-xs text-slate-500">Verifica tu identidad con preguntas de seguridad</p>
                </div>

                <div>
                    <label for="forgot-email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico</label>
                    <input type="email" name="email" id="forgot-email" required placeholder="ejemplo@correo.com"
                           class="w-full border-b-2 border-slate-200 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                           autocomplete="email">
                </div>

                <p class="text-xs text-slate-500 -mt-2">
                    <i class="ph ph-shield-check mr-1 text-mso-gold"></i>
                    Te enviaremos a la página de preguntas de seguridad
                </p>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-mso-gold text-slate-900 font-bold py-3 rounded-sm hover:bg-yellow-600 transition-colors shadow-lg text-sm">
                        <i class="ph ph-shield-check mr-2"></i>
                        Verificar Identidad
                    </button>
                    <button type="button" onclick="switchAuthTab('login')" class="px-4 bg-slate-200 text-slate-700 font-bold py-3 rounded-sm hover:bg-slate-300 transition-colors text-sm">
                        <i class="ph ph-arrow-left"></i>
                        Volver
                    </button>
                </div>

                <div class="text-center mt-2 border-t border-slate-100 pt-4">
                    <p class="text-xs text-slate-500">
                        <i class="ph ph-info mr-1 text-slate-400"></i>
                        ¿No tienes preguntas de seguridad configuradas?
                        <a href="{{ route('security.recovery.form') }}" target="_blank" rel="noopener noreferrer"
                           class="text-mso-gold hover:underline font-medium">
                            Ir a la página de recuperación
                        </a>
                    </p>
                </div>
            </form>

            <!-- ============================================ -->
            <!-- FORMULARIO REGISTRO - 3 PASOS -->
            <!-- ============================================ -->
            <form id="form-register" class="hidden" method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <!-- ========================================== -->
                <!-- PASO 1: DATOS PERSONALES Y CONTACTO -->
                <!-- ========================================== -->
                <div id="step-1" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="reg-name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre *</label>
                            <input type="text" name="name" id="reg-name" required placeholder="Nombre"
                                   class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                                   autocomplete="given-name" maxlength="255">
                            <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="name"></div>
                        </div>
                        <div>
                            <label for="reg-last-name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Apellido</label>
                            <input type="text" name="last_name" id="reg-last-name" placeholder="Apellido"
                                   class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                                   autocomplete="family-name" maxlength="255">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cédula de Identidad</label>
                        <div class="flex gap-3">
                            <select name="id_type" class="w-20 border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold bg-transparent">
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="J">J</option>
                            </select>
                            <input type="text" name="id_number" id="reg-cedula" placeholder="12345678 (obligatorio)"
                                   class="flex-1 border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                                   maxlength="20">
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Obligatorio - No puedes dejarlo en blanco</p>
                    </div>

                    <div>
                        <label for="reg-phone" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Teléfono *</label>
                        <div class="flex gap-2">
                            <select id="phone-code-select" class="w-24 border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold bg-transparent text-sm">
                                <option value="+58">+58</option>
                                <option value="+1">+1</option>
                                <option value="+34">+34</option>
                                <option value="+52">+52</option>
                                <option value="+57">+57</option>
                                <option value="+54">+54</option>
                            </select>
                            <input type="tel" name="phone" id="reg-phone" required placeholder="412 1234567"
                                   class="flex-1 border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                                   autocomplete="tel" maxlength="20">
                        </div>
                        <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="phone"></div>
                        <p class="text-xs text-slate-400 mt-1">Selecciona el código de país e ingresa tu número (mínimo 7 dígitos)</p>
                    </div>

                    <div>
                        <label for="reg-email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico *</label>
                        <input type="email" name="email" id="reg-email" required placeholder="ejemplo@correo.com"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                               autocomplete="email" maxlength="255">
                        <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="email"></div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" onclick="goToStep(2)"
                                class="px-6 py-2 bg-mso-gold text-slate-900 font-medium rounded-sm hover:bg-yellow-600 transition-colors text-sm">
                            Siguiente <i class="ph ph-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PASO 2: UBICACIÓN -->
                <!-- ========================================== -->
                <div id="step-2" class="space-y-4 hidden">
                    <div class="space-y-4">
                        <div>
                            <label for="reg_country_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">País *</label>
                            <select name="country_id" id="reg_country_id" required
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                                <option value="">Cargando países...</option>
                            </select>
                        </div>

                        <div>
                            <label for="reg_state_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Estado</label>
                            <select name="state_id" id="reg_state_id"
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                                <option value="">Primero seleccione un país</option>
                            </select>
                        </div>

                        <div>
                            <label for="reg_municipality_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Municipio</label>
                            <select name="municipality_id" id="reg_municipality_id"
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                                <option value="">Primero seleccione un estado</option>
                            </select>
                        </div>

                        <div>
                            <label for="reg_parish_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Parroquia</label>
                            <select name="parish_id" id="reg_parish_id"
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                                <option value="">Primero seleccione un municipio</option>
                            </select>
                        </div>

                        <div>
                            <label for="reg_city_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ciudad</label>
                            <select name="city_id" id="reg_city_id"
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                                <option value="">Primero seleccione una parroquia</option>
                            </select>
                        </div>

                        <div>
                            <label for="reg-address" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dirección</label>
                            <textarea name="address" id="reg-address" rows="2" placeholder="Tu dirección completa (opcional)"
                                      class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent resize-none"
                                      maxlength="500"></textarea>
                        </div>
                    </div>

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

                <!-- ========================================== -->
                <!-- PASO 3: CONTRASEÑA Y SEGURIDAD -->
                <!-- ========================================== -->
                <div id="step-3" class="space-y-4 hidden">
                    <!-- Contraseñas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="reg-pass" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Contraseña *</label>
                            <input type="password" name="password" id="reg-pass" required placeholder="Mínimo 8 caracteres"
                                   class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                                   autocomplete="new-password" minlength="8" maxlength="255">
                            <div id="password-strength" class="text-xs mt-1 text-slate-500">
                                Debe tener: Mayúscula, minúscula, número y caracter especial (@$!%*?&)
                            </div>
                        </div>
                        <div>
                            <label for="reg-pass-confirm" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirmar Contraseña *</label>
                            <input type="password" name="password_confirmation" id="reg-pass-confirm" required placeholder="Repite tu contraseña"
                                   class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent"
                                   autocomplete="new-password" minlength="8" maxlength="255">
                            <div id="pass-error" class="text-red-500 text-xs mt-1 hidden">Las contraseñas no coinciden</div>
                        </div>
                    </div>

                    <!-- Preguntas de Seguridad -->
                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex items-center mb-2">
                            <i class="ph ph-shield-check text-mso-gold mr-2"></i>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Preguntas de Seguridad *</span>
                            <span class="ml-1 text-red-500 text-xs">(Obligatorias)</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">
                            <i class="ph ph-info mr-1 text-slate-400"></i>
                            Selecciona 3 preguntas <strong>diferentes</strong> y proporciona respuestas que puedas recordar.
                        </p>

                        <!-- Pregunta 1 -->
                        <div class="mb-3">
                            <label for="reg_security_question_1" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pregunta 1 *</label>
                            <select name="security_question_1" id="reg_security_question_1" required
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm security-question">
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
                            <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="security_question_1"></div>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="security_answer_1" id="reg_security_answer_1" required
                                   placeholder="Tu respuesta"
                                   class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm"
                                   maxlength="255" autocomplete="off">
                            <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="security_answer_1"></div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="mb-3">
                            <label for="reg_security_question_2" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pregunta 2 *</label>
                            <select name="security_question_2" id="reg_security_question_2" required
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm security-question">
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
                            <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="security_question_2"></div>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="security_answer_2" id="reg_security_answer_2" required
                                   placeholder="Tu respuesta"
                                   class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm"
                                   maxlength="255" autocomplete="off">
                            <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="security_answer_2"></div>
                        </div>

                        <!-- Pregunta 3 -->
                        <div class="mb-3">
                            <label for="reg_security_question_3" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pregunta 3 *</label>
                            <select name="security_question_3" id="reg_security_question_3" required
                                    class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm security-question">
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
                            <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="security_question_3"></div>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="security_answer_3" id="reg_security_answer_3" required
                                   placeholder="Tu respuesta"
                                   class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm"
                                   maxlength="255" autocomplete="off">
                            <div class="error-message text-red-500 text-xs mt-1 hidden" data-for="security_answer_3"></div>
                        </div>
                    </div>

                    <!-- Términos -->
                    <div class="flex items-center">
                        <input type="checkbox" name="terms" id="terms" required
                               class="rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                        <label for="terms" class="ml-2 block text-xs text-slate-600">
                            Acepto los <a href="#" class="text-mso-gold hover:underline font-medium">Términos y Condiciones</a>
                        </label>
                        <div class="error-message text-red-500 text-xs ml-2 hidden" data-for="terms">Debes aceptar los términos</div>
                    </div>

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
            </form>
        </div>

        <!-- ============================================ -->
        <!-- FOOTER TABS -->
        <!-- ============================================ -->
        <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex justify-between items-center">
            <button onclick="switchAuthTab('login')" id="tab-login"
                    class="text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors">
                Iniciar Sesión
            </button>
            <button onclick="switchAuthTab('register')" id="tab-register"
                    class="text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors">
                Registrarse
            </button>
            <button onclick="switchAuthTab('forgot')" id="tab-forgot"
                    class="text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors hidden">
                Recuperar
            </button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL DE ÉXITO -->
<!-- ============================================ -->
<div id="success-modal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[70] hidden flex items-center justify-center p-4 transition-opacity duration-300">
    <div class="bg-white rounded-sm shadow-2xl w-full max-w-md overflow-hidden modal-enter relative border border-slate-100 text-center p-8">
        <div class="flex justify-center mb-4">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                <i class="ph ph-check-circle text-5xl text-green-500"></i>
            </div>
        </div>
        <h3 class="font-serif text-2xl font-bold text-slate-900 mb-2">¡Cuenta Creada!</h3>
        <p class="text-slate-500 text-sm mb-6">Tu cuenta ha sido creada exitosamente. Serás redirigido automáticamente.</p>
        <div class="w-full bg-slate-200 rounded-full h-1.5 mb-4">
            <div id="success-progress" class="bg-mso-gold h-1.5 rounded-full transition-all duration-1000" style="width: 0%"></div>
        </div>
        <button onclick="closeModal('auth-modal'); closeModal('success-modal')"
                class="w-full bg-mso-gold text-slate-900 font-bold py-3 rounded-sm hover:bg-yellow-600 transition-colors">
            Ir al Dashboard
        </button>
    </div>
</div>

<!-- ============================================ -->
<!-- SCRIPT COMPLETO -->
<!-- ============================================ -->
<script>
(function() {
    'use strict';

    // ============================================
    // VARIABLES DE ESTADO
    // ============================================
    let currentStep = 1;
    const totalSteps = 3;
    let isSubmitting = false;

    // ============================================
    // SOBREESCRIBIR switchAuthTab PARA CONTROLAR VISIBILIDAD DE LA BARRA
    // ============================================
    const originalSwitchAuthTab = window.switchAuthTab;

    window.switchAuthTab = function(tab) {
        console.log(' switchAuthTab llamado con:', tab);

        const loginForm = document.getElementById('form-login');
        const registerForm = document.getElementById('form-register');
        const forgotForm = document.getElementById('form-forgot-password');
        const loginTab = document.getElementById('tab-login');
        const registerTab = document.getElementById('tab-register');
        const forgotTab = document.getElementById('tab-forgot');
        const modalTitle = document.getElementById('modal-title');
        const modalSubtitle = document.getElementById('modal-subtitle');
        const stepIndicator = document.getElementById('step-indicator');
        const progressContainer = document.getElementById('progress-bar-container');

        if (loginForm) loginForm.classList.add('hidden');
        if (registerForm) registerForm.classList.add('hidden');
        if (forgotForm) forgotForm.classList.add('hidden');
        if (forgotTab) forgotTab.classList.add('hidden');

        if (stepIndicator) stepIndicator.classList.add('hidden');
        if (progressContainer) progressContainer.classList.add('hidden');

        if (tab === 'login') {
            if (loginForm) loginForm.classList.remove('hidden');
            if (loginTab) loginTab.className = 'text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors';
            if (registerTab) registerTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
            if (forgotTab) forgotTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
            if (modalTitle) modalTitle.textContent = 'Bienvenido';
            if (modalSubtitle) modalSubtitle.textContent = 'Ingresa a tu cuenta para continuar';
        } else if (tab === 'register') {
            if (registerForm) registerForm.classList.remove('hidden');
            if (registerTab) registerTab.className = 'text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors';
            if (loginTab) loginTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
            if (forgotTab) forgotTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
            if (modalTitle) modalTitle.textContent = 'Crear Cuenta';
            if (modalSubtitle) modalSubtitle.textContent = 'Únete a MSO Grupo Inmobiliario';

            if (stepIndicator) stepIndicator.classList.remove('hidden');
            if (progressContainer) progressContainer.classList.remove('hidden');

            goToStep(1);
        } else if (tab === 'forgot') {
            if (forgotForm) forgotForm.classList.remove('hidden');
            if (forgotTab) {
                forgotTab.classList.remove('hidden');
                forgotTab.className = 'text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors';
            }
            if (loginTab) loginTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
            if (registerTab) registerTab.className = 'text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors';
            if (modalTitle) modalTitle.textContent = 'Recuperar Contraseña';
            if (modalSubtitle) modalSubtitle.textContent = 'Verifica tu identidad con preguntas de seguridad';
        }
    };

    // ============================================
    // NAVEGACIÓN ENTRE PASOS
    // ============================================
    window.goToStep = function(step) {
        if (step < 1 || step > totalSteps) return;
        if (isSubmitting) return;

        if (step > currentStep) {
            if (!validateStep(currentStep)) {
                return;
            }
        }

        for (let i = 1; i <= totalSteps; i++) {
            const stepEl = document.getElementById(`step-${i}`);
            if (stepEl) stepEl.classList.add('hidden');
        }

        const targetStep = document.getElementById(`step-${step}`);
        if (targetStep) targetStep.classList.remove('hidden');

        currentStep = step;

        updateProgress(step);

        const indicator = document.getElementById('step-indicator');
        if (indicator) {
            indicator.textContent = `Paso ${step} de ${totalSteps}`;
        }

        const subtitle = document.getElementById('modal-subtitle');
        const titles = {
            1: 'Completa tus datos personales y de contacto',
            2: 'Selecciona tu ubicación y dirección',
            3: 'Configura tu contraseña y seguridad'
        };
        if (subtitle) {
            subtitle.textContent = titles[step] || 'Completa todos los campos';
        }

        const container = document.querySelector('.custom-scrollbar');
        if (container) container.scrollTop = 0;
    };

    // ============================================
    // ACTUALIZAR BARRA DE PROGRESO
    // ============================================
    function updateProgress(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const bar = document.getElementById(`step-bar-${i}`);
            if (bar) {
                if (i <= step) {
                    bar.classList.remove('bg-slate-200');
                    bar.classList.add('bg-mso-gold');
                } else {
                    bar.classList.remove('bg-mso-gold');
                    bar.classList.add('bg-slate-200');
                }
            }
        }
    }

    // ============================================
    // VALIDAR PASO ACTUAL
    // ============================================
    function validateStep(step) {
        let isValid = true;
        const errorMessages = [];

        if (step === 1) {
            const name = document.getElementById('reg-name');
            const nameError = document.querySelector('[data-for="name"]');
            if (!name.value || name.value.trim().length < 2) {
                if (nameError) {
                    nameError.textContent = 'El nombre es obligatorio';
                    nameError.classList.remove('hidden');
                }
                name.style.borderColor = '#ef4444';
                isValid = false;
                errorMessages.push('El nombre es obligatorio');
            } else {
                if (nameError) nameError.classList.add('hidden');
                name.style.borderColor = '';
            }

            const email = document.getElementById('reg-email');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value || !emailPattern.test(email.value)) {
                const emailError = document.querySelector('[data-for="email"]');
                if (emailError) {
                    emailError.textContent = 'Ingresa un correo electrónico válido';
                    emailError.classList.remove('hidden');
                }
                email.style.borderColor = '#ef4444';
                isValid = false;
                errorMessages.push('El correo electrónico no es válido');
            } else {
                const emailError = document.querySelector('[data-for="email"]');
                if (emailError) emailError.classList.add('hidden');
                email.style.borderColor = '';
            }

            const phone = document.getElementById('reg-phone');
            if (!phone.value || phone.value.length < 7) {
                const phoneError = document.querySelector('[data-for="phone"]');
                if (phoneError) {
                    phoneError.textContent = 'El teléfono es obligatorio y debe tener al menos 7 dígitos';
                    phoneError.classList.remove('hidden');
                }
                phone.style.borderColor = '#ef4444';
                isValid = false;
                errorMessages.push('El teléfono debe tener al menos 7 dígitos');
            } else {
                const phoneError = document.querySelector('[data-for="phone"]');
                if (phoneError) phoneError.classList.add('hidden');
                phone.style.borderColor = '#22c55e';
            }
        }

        if (step === 2) {
            const country = document.getElementById('reg_country_id');
            if (!country.value) {
                country.style.borderColor = '#ef4444';
                isValid = false;
                errorMessages.push('Debes seleccionar un país');
            } else {
                country.style.borderColor = '';
            }
        }

        if (step === 3) {
            const passInput = document.getElementById('reg-pass');
            const passConfirm = document.getElementById('reg-pass-confirm');
            const passError = document.getElementById('pass-error');

            if (passInput.value.length < 8) {
                isValid = false;
                errorMessages.push('La contraseña debe tener al menos 8 caracteres');
            }

            if (passInput.value !== passConfirm.value) {
                if (passError) passError.classList.remove('hidden');
                passConfirm.style.borderColor = '#ef4444';
                isValid = false;
                errorMessages.push('Las contraseñas no coinciden');
            } else {
                if (passError) passError.classList.add('hidden');
                passConfirm.style.borderColor = '';
            }

            const q1 = document.getElementById('reg_security_question_1');
            const q2 = document.getElementById('reg_security_question_2');
            const q3 = document.getElementById('reg_security_question_3');
            const questions = [q1?.value || '', q2?.value || '', q3?.value || ''];

            if (questions.some(q => q === '')) {
                isValid = false;
                errorMessages.push('Debes seleccionar 3 preguntas de seguridad');
            }

            if (questions.every(q => q !== '') && new Set(questions).size < 3) {
                isValid = false;
                errorMessages.push('Las preguntas de seguridad deben ser diferentes');
            }

            const a1 = document.getElementById('reg_security_answer_1');
            const a2 = document.getElementById('reg_security_answer_2');
            const a3 = document.getElementById('reg_security_answer_3');
            const answers = [a1?.value || '', a2?.value || '', a3?.value || ''];

            if (answers.some(a => a.trim().length < 2)) {
                isValid = false;
                errorMessages.push('Las respuestas deben tener al menos 2 caracteres');
            }

            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                const termsError = document.querySelector('[data-for="terms"]');
                if (termsError) termsError.classList.remove('hidden');
                isValid = false;
                errorMessages.push('Debes aceptar los términos y condiciones');
            } else {
                const termsError = document.querySelector('[data-for="terms"]');
                if (termsError) termsError.classList.add('hidden');
            }
        }

        if (!isValid) {
            const submitBtn = document.getElementById('register-submit');
            if (submitBtn && errorMessages.length > 0) {
                const originalText = submitBtn.textContent;
                submitBtn.textContent = '⚠️ ' + errorMessages[0];
                submitBtn.style.backgroundColor = '#ef4444';
                submitBtn.style.color = 'white';

                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.style.backgroundColor = '';
                    submitBtn.style.color = '';
                }, 3000);
            }
        }

        return isValid;
    }

    // ============================================
    // VALIDACIÓN DE CONTRASEÑAS
    // ============================================
    function initPasswordValidation() {
        const passInput = document.getElementById('reg-pass');
        const passConfirm = document.getElementById('reg-pass-confirm');
        const passError = document.getElementById('pass-error');
        const strengthDiv = document.getElementById('password-strength');

        if (!passInput || !passConfirm) return;

        function validatePasswordStrength(password) {
            if (!password || password.length === 0) {
                if (strengthDiv) {
                    strengthDiv.innerHTML = 'Debe tener: Mayúscula, minúscula, número y caracter especial (@$!%*?&)';
                    strengthDiv.className = 'text-xs mt-1 text-slate-500';
                }
                return;
            }

            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasSpecial = /[@$!%*?&]/.test(password);
            const isValidLength = password.length >= 8;

            const requirements = [];
            if (!isValidLength) requirements.push('mínimo 8 caracteres');
            if (!hasUpper) requirements.push('mayúscula');
            if (!hasLower) requirements.push('minúscula');
            if (!hasNumber) requirements.push('número');
            if (!hasSpecial) requirements.push('caracter especial (@$!%*?&)');

            if (strengthDiv) {
                if (requirements.length === 0) {
                    strengthDiv.innerHTML = ' Contraseña segura';
                    strengthDiv.className = 'text-xs mt-1 text-green-600';
                } else {
                    strengthDiv.innerHTML = ' Falta: ' + requirements.join(', ');
                    strengthDiv.className = 'text-xs mt-1 text-red-500';
                }
            }
        }

        function validatePasswordMatch() {
            if (!passError) return true;

            if (passConfirm.value && passInput.value !== passConfirm.value) {
                passError.classList.remove('hidden');
                passConfirm.style.borderColor = '#ef4444';
                return false;
            } else {
                passError.classList.add('hidden');
                passConfirm.style.borderColor = '';
                return true;
            }
        }

        passInput.addEventListener('input', function() {
            validatePasswordStrength(this.value);
            validatePasswordMatch();
        });

        passConfirm.addEventListener('input', validatePasswordMatch);
    }

    // ============================================
    // VALIDACIÓN DE PREGUNTAS DE SEGURIDAD
    // ============================================
    function initSecurityQuestionsValidation() {
        const q1 = document.getElementById('reg_security_question_1');
        const q2 = document.getElementById('reg_security_question_2');
        const q3 = document.getElementById('reg_security_question_3');
        const a1 = document.getElementById('reg_security_answer_1');
        const a2 = document.getElementById('reg_security_answer_2');
        const a3 = document.getElementById('reg_security_answer_3');

        if (!q1 || !q2 || !q3) return;

        function validateQuestions() {
            const questions = [q1.value, q2.value, q3.value];
            const unique = new Set(questions);
            let isValid = true;

            document.querySelectorAll('.security-question').forEach(el => {
                el.style.borderColor = '';
                const errorEl = document.querySelector(`[data-for="${el.name}"]`);
                if (errorEl) errorEl.classList.add('hidden');
            });

            if (questions.some(q => q === '')) {
                questions.forEach((q, index) => {
                    if (q === '') {
                        const select = document.getElementById(`reg_security_question_${index + 1}`);
                        if (select) select.style.borderColor = '#ef4444';
                        const errorEl = document.querySelector(`[data-for="security_question_${index + 1}"]`);
                        if (errorEl) {
                            errorEl.textContent = 'Debes seleccionar una pregunta';
                            errorEl.classList.remove('hidden');
                        }
                    }
                });
                isValid = false;
            }

            if (questions.every(q => q !== '') && unique.size < 3) {
                const duplicates = questions.filter((q, index) => questions.indexOf(q) !== index);
                duplicates.forEach(dup => {
                    questions.forEach((q, index) => {
                        if (q === dup) {
                            const select = document.getElementById(`reg_security_question_${index + 1}`);
                            if (select) select.style.borderColor = '#ef4444';
                            const errorEl = document.querySelector(`[data-for="security_question_${index + 1}"]`);
                            if (errorEl) {
                                errorEl.textContent = 'Las preguntas deben ser diferentes';
                                errorEl.classList.remove('hidden');
                            }
                        }
                    });
                });
                isValid = false;
            }

            return isValid;
        }

        function validateAnswers() {
            const answers = [a1?.value || '', a2?.value || '', a3?.value || ''];
            let isValid = true;

            answers.forEach((answer, index) => {
                const errorEl = document.querySelector(`[data-for="security_answer_${index + 1}"]`);
                if (!answer || answer.trim().length < 2) {
                    if (errorEl) {
                        errorEl.textContent = 'La respuesta debe tener al menos 2 caracteres';
                        errorEl.classList.remove('hidden');
                    }
                    isValid = false;
                } else {
                    if (errorEl) errorEl.classList.add('hidden');
                }
            });

            return isValid;
        }

        [q1, q2, q3].forEach(q => {
            q.addEventListener('change', function() {
                validateQuestions();
                validateAnswers();
            });
        });

        [a1, a2, a3].forEach(a => {
            if (a) {
                a.addEventListener('input', function() {
                    const errorEl = document.querySelector(`[data-for="${this.name}"]`);
                    if (this.value.trim().length >= 2) {
                        if (errorEl) errorEl.classList.add('hidden');
                        this.style.borderColor = '';
                    } else {
                        if (errorEl) {
                            errorEl.textContent = 'La respuesta debe tener al menos 2 caracteres';
                            errorEl.classList.remove('hidden');
                        }
                        this.style.borderColor = '#ef4444';
                    }
                });
            }
        });
    }

    // ============================================
    // VALIDACIÓN DE TELÉFONO
    // ============================================
    function initPhoneValidation() {
        const phoneInput = document.getElementById('reg-phone');
        if (!phoneInput) return;

        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');

            const errorEl = document.querySelector('[data-for="phone"]');
            if (this.value.length > 0 && this.value.length < 7) {
                if (errorEl) {
                    errorEl.textContent = 'El teléfono debe tener al menos 7 dígitos';
                    errorEl.classList.remove('hidden');
                }
                this.style.borderColor = '#ef4444';
            } else if (this.value.length >= 7) {
                if (errorEl) errorEl.classList.add('hidden');
                this.style.borderColor = '#22c55e';
            } else {
                if (errorEl) errorEl.classList.add('hidden');
                this.style.borderColor = '';
            }
        });

        phoneInput.addEventListener('blur', function() {
            const errorEl = document.querySelector('[data-for="phone"]');
            if (this.value.length > 0 && this.value.length < 7) {
                if (errorEl) {
                    errorEl.textContent = 'El teléfono debe tener al menos 7 dígitos';
                    errorEl.classList.remove('hidden');
                }
                this.style.borderColor = '#ef4444';
            } else if (this.value.length === 0) {
                if (errorEl) {
                    errorEl.textContent = 'El teléfono es obligatorio';
                    errorEl.classList.remove('hidden');
                }
                this.style.borderColor = '#ef4444';
            }
        });
    }

    // ============================================
    // VALIDACIÓN DE EMAIL
    // ============================================
    function initEmailValidation() {
        const emailInput = document.getElementById('reg-email');
        if (!emailInput) return;

        emailInput.addEventListener('input', function() {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const errorEl = document.querySelector('[data-for="email"]');
            if (this.value && !emailPattern.test(this.value)) {
                if (errorEl) {
                    errorEl.textContent = 'Ingresa un correo electrónico válido';
                    errorEl.classList.remove('hidden');
                }
                this.style.borderColor = '#ef4444';
            } else {
                if (errorEl) errorEl.classList.add('hidden');
                this.style.borderColor = '';
            }
        });
    }

    // ============================================
    // VALIDACIÓN DE TÉRMINOS
    // ============================================
    function initTermsValidation() {
        const termsCheckbox = document.getElementById('terms');
        if (!termsCheckbox) return;

        termsCheckbox.addEventListener('change', function() {
            const errorEl = document.querySelector('[data-for="terms"]');
            if (!this.checked) {
                if (errorEl) errorEl.classList.remove('hidden');
            } else {
                if (errorEl) errorEl.classList.add('hidden');
            }
        });
    }

    // ============================================
    // CARGA DE UBICACIONES
    // ============================================
    function initLocations() {
        const countrySelect = document.getElementById('reg_country_id');
        const stateSelect = document.getElementById('reg_state_id');
        const municipalitySelect = document.getElementById('reg_municipality_id');
        const parishSelect = document.getElementById('reg_parish_id');
        const citySelect = document.getElementById('reg_city_id');

        if (!countrySelect) return;

        fetch('/api/locations/countries')
            .then(response => response.json())
            .then(countries => {
                countrySelect.innerHTML = '<option value="">Seleccione un país</option>';
                countries.forEach(country => {
                    countrySelect.innerHTML += `<option value="${country.id}">${country.name}</option>`;
                });
            })
            .catch(error => {
                console.error('Error cargando países:', error);
                countrySelect.innerHTML = '<option value="">Error cargando países</option>';
            });

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
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                        citySelect.disabled = true;
                    });
            } else {
                parishSelect.disabled = true;
                parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
            }
        });

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

    // ============================================
    // ENVÍO DEL FORMULARIO CON PREVENCIÓN DE DOBLE CLIC
    // ============================================
    function initFormSubmission() {
        const registerForm = document.getElementById('form-register');
        if (!registerForm) return;

        registerForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
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

            console.log(' Enviando formulario...');
            return true;
        });
    }

    // ============================================
    // MODAL DE ÉXITO
    // ============================================
    function showSuccessModal() {
        const successModal = document.getElementById('success-modal');
        if (successModal) {
            successModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            const progress = document.getElementById('success-progress');
            if (progress) {
                setTimeout(() => {
                    progress.style.width = '100%';
                }, 100);
            }

            setTimeout(() => {
                window.location.href = '/cliente/dashboard';
            }, 3000);
        }
    }

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
                // Actualizar token en TODOS los formularios del modal
                document.querySelectorAll('#auth-modal form input[name="_token"]').forEach(input => {
                    input.value = data.csrf_token;
                });

                // Actualizar meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.content = data.csrf_token;
                }

                console.log(' Token CSRF actualizado automáticamente');
            }
        })
        .catch(() => {
            console.warn(' No se pudo refrescar el token');
        });
    }

    // ============================================
    // INICIALIZAR TODO
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Modal de autenticación inicializado');

        initPasswordValidation();
        initSecurityQuestionsValidation();
        initPhoneValidation();
        initEmailValidation();
        initTermsValidation();
        initLocations();
        initFormSubmission();

        // Mostrar paso inicial (oculto por defecto)
        goToStep(1);

        const successMessage = document.querySelector('[data-success]');
        if (successMessage) {
            showSuccessModal();
        }

        // ============================================
        // SOLUCIÓN PARA ERROR 419 - REFRESCAR TOKEN
        // ============================================

        // Refrescar token cada 5 minutos
        setInterval(refreshCsrfToken, 300000);

        // Verificar si hay error de sesión expirada al cargar
        if (window.location.search.includes('session_expired')) {
            alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
            window.location.href = window.location.pathname;
        }

        console.log('✅ Todas las validaciones activas');
    });

    // ============================================
    // EXPONER FUNCIONES GLOBALES
    // ============================================
    window.goToStep = goToStep;
    window.showSuccessModal = showSuccessModal;
    window.refreshCsrfToken = refreshCsrfToken;

})();
</script>
