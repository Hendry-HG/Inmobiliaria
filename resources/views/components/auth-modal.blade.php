<div id="auth-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[60] hidden flex items-center justify-center p-4 transition-opacity duration-300">
    <div class="bg-white rounded-sm shadow-2xl w-full max-w-lg overflow-hidden modal-enter relative border border-slate-100">

        <button onclick="closeModal('auth-modal')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-800 transition-colors z-10 bg-white/80 rounded-full p-1">
            <i class="ph ph-x text-xl"></i>
        </button>

        <div class="bg-slate-50 p-8 border-b border-slate-100">
            <h3 id="modal-title" class="font-serif text-3xl font-bold text-slate-900 mb-1">Bienvenido</h3>
            <p id="modal-subtitle" class="text-slate-500 text-sm">Ingresa a tu cuenta para continuar</p>
        </div>

        <div style="position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; opacity: 0; pointer-events: none;">
            <input type="text" name="fake_username" autocomplete="username" tabindex="-1">
            <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1">
            <input type="email" name="fake_email" autocomplete="email" tabindex="-1">
            <input type="text" name="fake_name" autocomplete="name" tabindex="-1">
            <input type="tel" name="fake_phone" autocomplete="tel" tabindex="-1">
        </div>

        <div class="p-8 md:p-10 max-h-[80vh] overflow-y-auto custom-scrollbar">

            <!-- ============================================ -->
            <!-- FORMULARIO LOGIN -->
            <!-- ============================================ -->
            <form id="form-login" class="space-y-6" method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="ejemplo@correo.com"
                           class="w-full border-b-2 border-slate-200 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full border-b-2 border-slate-200 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
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
            <!-- FORMULARIO RECUPERAR CONTRASEÑA (PREGUNTAS DE SEGURIDAD) -->
            <!-- ============================================ -->
            <form id="form-forgot-password" class="space-y-6 hidden" method="POST" action="{{ route('security.verify.email') }}" novalidate>
                @csrf
                <div class="text-center mb-4">
                    <h4 class="text-lg font-bold text-slate-900">Recuperar Contraseña</h4>
                    <p class="text-xs text-slate-500">Verifica tu identidad con preguntas de seguridad</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="ejemplo@correo.com"
                           class="w-full border-b-2 border-slate-200 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
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
                        <a href="{{ route('security.recovery.form') }}" target="_blank" class="text-mso-gold hover:underline font-medium">
                            Ir a la página de recuperación
                        </a>
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        <i class="ph ph-arrow-square-out mr-1"></i>
                        Se abrirá en una nueva página
                    </p>
                </div>
            </form>

            <!-- ============================================ -->
            <!-- FORMULARIO REGISTRO CON PREGUNTAS DE SEGURIDAD -->
            <!-- ============================================ -->
            <form id="form-register" class="space-y-5 hidden" method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <!-- Nombre + Apellido -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre *</label>
                        <input type="text" name="name" id="reg-name" required placeholder="Nombre"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Apellido</label>
                        <input type="text" name="last_name" id="reg-last-name" placeholder="Apellido"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                    </div>
                </div>

                <!-- Cédula -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cédula de Identidad</label>
                    <div class="flex gap-3">
                        <select name="id_type" class="w-20 border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold bg-transparent">
                            <option value="V">V-</option>
                            <option value="E">E-</option>
                            <option value="J">J-</option>
                        </select>
                        <input type="text" name="id_number" id="reg-cedula" required placeholder="12345678"
                               class="flex-1 border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Teléfono *</label>
                    <div class="flex gap-2">
                        <select id="phone-code-select" class="w-24 border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold bg-transparent text-sm">
                            <option value="+58">+58</option>
                        </select>
                        <input type="tel" name="phone" id="reg-phone" required placeholder="412 1234567"
                               class="flex-1 border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Selecciona el código de país e ingresa tu número</p>
                </div>

                <!-- Dirección -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dirección</label>
                    <textarea name="address" id="reg-address" rows="2" placeholder="Tu dirección completa (opcional)"
                              class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent resize-none"></textarea>
                </div>

                <!-- Ubicación -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">País *</label>
                        <select name="country_id" id="reg_country_id" required class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                            <option value="">Cargando países...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Estado</label>
                        <select name="state_id" id="reg_state_id" class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                            <option value="">Primero seleccione un país</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Municipio</label>
                        <select name="municipality_id" id="reg_municipality_id" class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                            <option value="">Primero seleccione un estado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Parroquia</label>
                        <select name="parish_id" id="reg_parish_id" class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                            <option value="">Primero seleccione un municipio</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ciudad</label>
                        <select name="city_id" id="reg_city_id" class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent" disabled>
                            <option value="">Primero seleccione una parroquia</option>
                        </select>
                    </div>
                </div>

                <!-- Correo -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico *</label>
                    <input type="email" name="email" id="reg-email" required placeholder="ejemplo@correo.com"
                           class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                </div>

                <!-- Contraseñas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Contraseña *</label>
                        <input type="password" name="password" id="reg-pass" required placeholder="Mínimo 8 caracteres"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirmar Contraseña *</label>
                        <input type="password" name="password_confirmation" id="reg-pass-confirm" required placeholder="Repite tu contraseña"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent">
                        <div id="pass-error" class="text-red-500 text-xs mt-1 hidden">Las contraseñas no coinciden</div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- PREGUNTAS DE SEGURIDAD -->
                <!-- ============================================ -->
                <div class="border-t border-slate-200 pt-4 mt-2">
                    <div class="flex items-center mb-2">
                        <i class="ph ph-shield-check text-mso-gold mr-2"></i>
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Preguntas de Seguridad</label>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">
                        Selecciona 3 preguntas diferentes y proporciona respuestas que puedas recordar.
                        Te ayudarán a recuperar tu cuenta si olvidas tu contraseña.
                    </p>

                    <!-- Pregunta 1 -->
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pregunta 1 *</label>
                        <select name="security_question_1" id="reg_security_question_1" required
                                class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm">
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
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="security_answer_1" id="reg_security_answer_1" required
                               placeholder="Tu respuesta"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm">
                    </div>

                    <!-- Pregunta 2 -->
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pregunta 2 *</label>
                        <select name="security_question_2" id="reg_security_question_2" required
                                class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm">
                            <option value="">Selecciona una pregunta</option>
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
                    </div>
                    <div class="mb-3">
                        <input type="text" name="security_answer_2" id="reg_security_answer_2" required
                               placeholder="Tu respuesta"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm">
                    </div>

                    <!-- Pregunta 3 -->
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pregunta 3 *</label>
                        <select name="security_question_3" id="reg_security_question_3" required
                                class="w-full border-b-2 border-slate-200 py-2 text-slate-900 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm">
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
                    </div>
                    <div class="mb-3">
                        <input type="text" name="security_answer_3" id="reg_security_answer_3" required
                               placeholder="Tu respuesta"
                               class="w-full border-b-2 border-slate-200 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-mso-gold transition-colors bg-transparent text-sm">
                    </div>
                </div>

                <!-- Términos -->
                <div class="flex items-center">
                    <input type="checkbox" name="terms" id="terms" required class="rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                    <label for="terms" class="ml-2 block text-xs text-slate-600">Acepto los <a href="#" class="text-mso-gold hover:underline">Términos y Condiciones</a></label>
                </div>

                <button type="submit" class="w-full bg-mso-gold text-slate-900 font-bold py-4 rounded-sm hover:bg-yellow-600 transition-colors shadow-lg mt-2">
                    CREAR CUENTA
                </button>
            </form>
        </div>

        <!-- Footer Tabs -->
        <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 flex justify-between items-center">
            <button onclick="switchAuthTab('login')" id="tab-login" class="text-sm font-bold text-slate-900 border-b-2 border-mso-gold pb-1 transition-colors">
                Iniciar Sesión
            </button>
            <button onclick="switchAuthTab('register')" id="tab-register" class="text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors">
                Registrarse
            </button>
            <button onclick="switchAuthTab('forgot')" id="tab-forgot" class="text-sm font-medium text-slate-500 hover:text-slate-900 pb-1 transition-colors hidden">
                Recuperar
            </button>
        </div>
    </div>
</div>

<script>
// Solo validación de contraseñas y carga de ubicaciones
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // VALIDACIÓN DE CONTRASEÑAS EN REGISTRO
    // ============================================
    const passInput = document.getElementById('reg-pass');
    const passConfirm = document.getElementById('reg-pass-confirm');
    const passError = document.getElementById('pass-error');

    if (passInput && passConfirm) {
        function validatePassword() {
            if (passInput.value && passConfirm.value && passInput.value !== passConfirm.value) {
                passError.classList.remove('hidden');
                passConfirm.style.borderColor = '#ef4444';
                return false;
            } else {
                passError.classList.add('hidden');
                passConfirm.style.borderColor = '';
                return true;
            }
        }

        passInput.addEventListener('input', validatePassword);
        passConfirm.addEventListener('input', validatePassword);
    }

    // ============================================
    // CARGAR UBICACIONES
    // ============================================
    const countrySelect = document.getElementById('reg_country_id');
    const stateSelect = document.getElementById('reg_state_id');
    const municipalitySelect = document.getElementById('reg_municipality_id');
    const parishSelect = document.getElementById('reg_parish_id');
    const citySelect = document.getElementById('reg_city_id');

    if (countrySelect) {
        // Cargar países
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

        // Cargar estados
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

        // Cargar municipios
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

        // Cargar parroquias
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

        // Cargar ciudades
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
});
</script>
