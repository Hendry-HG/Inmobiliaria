<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Controlador que gestiona la autenticacion mediante preguntas de seguridad.
 *
 * Este controlador implementa dos flujos principales:
 *
 * 1. RECUPERACION DE CONTRASENA:
 *    Permite a usuarios con preguntas de seguridad configuradas recuperar el
 *    acceso a su cuenta cuando han olvidado su contrasena. El flujo consiste en:
 *    - Verificar el correo electronico del usuario.
 *    - Mostrar las preguntas de seguridad asociadas a la cuenta.
 *    - Validar las respuestas usando Hash::check para comparar con los hashes
 *      almacenados en la base de datos.
 *    - Generar un token de restablecimiento de contrasena via Password::getRepository().
 *    - Redirigir al formulario de restablecimiento de contrasena con el token.
 *
 * 2. REACTIVACION DE CUENTA:
 *    Permite a usuarios con cuentas desactivadas (is_active = false) reactivar
 *    su cuenta respondiendo las preguntas de seguridad. El flujo consiste en:
 *    - Verificar el correo electronico y confirmar que la cuenta esta inactiva.
 *    - Mostrar las preguntas de seguridad asociadas a la cuenta.
 *    - Validar las respuestas usando Hash::check.
 *    - Activar la cuenta estableciendo is_active = true.
 *    - Enviar una notificacion de reactivacion al usuario.
 *
 * En ambos flujos se implementa proteccion CSRF regenerando el token de sesion
 * (session()->regenerateToken()) en cada paso, tanto en exito como en error,
 * para prevenir ataques de replay.
 */
class SecurityQuestionsController extends Controller
{
    /**
     * Muestra el formulario de inicio del flujo de recuperacion de contrasena.
     *
     * Regenera el token CSRF de la sesion para proteger el formulario
     * contra ataques de reproduccion (replay). Retorna la vista
     * 'auth.security-recovery' donde el usuario ingresa su correo electronico.
     *
     * @return \Illuminate\View\View Vista con el formulario de recuperacion.
     */
    public function showRecoveryForm()
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();
        return view('auth.security-recovery');
    }

    /**
     * Valida el correo electronico y verifica que el usuario tenga preguntas de seguridad.
     *
     * Flujo:
     * 1. Valida que el correo electronico exista en la tabla users.
     * 2. Busca el usuario por correo electronico.
     * 3. Verifica si el usuario tiene preguntas de seguridad configuradas
     *    mediante el metodo hasSecurityQuestions(). Si no las tiene, retorna
     *    a la pagina anterior con un mensaje de error indicando que use
     *    la recuperacion por correo electronico.
     * 4. Almacena el correo en la sesion bajo 'security_recovery_email'
     *    para los pasos siguientes del flujo.
     * 5. Regenera el token CSRF y redirige al formulario de preguntas.
     *
     * @param Request $request La peticion HTTP que contiene el campo 'email'.
     * @return \Illuminate\Http\RedirectResponse Redireccion al formulario de
     *         preguntas de seguridad o a la pagina anterior con errores.
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user->hasSecurityQuestions()) {
            //  REGENERAR TOKEN DESPUÉS DE ERROR
            session()->regenerateToken();
            return back()->withErrors([
                'email' => 'Este usuario no tiene configuradas preguntas de seguridad. Por favor, usa la recuperación por correo electrónico.'
            ]);
        }

        // Guardar email en sesión
        session(['security_recovery_email' => $user->email]);

        //  REGENERAR TOKEN ANTES DE REDIRIGIR
        session()->regenerateToken();

        return redirect()->route('security.questions.show', ['email' => $user->email]);
    }

    /**
     * Muestra las preguntas de seguridad del usuario para el flujo de recuperacion.
     *
     * Recupera el correo electronico del request o de la sesion. Si no hay
     * correo disponible, redirige al formulario de recuperacion con un error.
     * Busca el usuario, decodifica las preguntas de seguridad almacenadas
     * en formato JSON y muestra la vista 'auth.security-questions' con
     * las preguntas y el correo del usuario.
     *
     * @param Request $request La peticion HTTP que puede contener el campo 'email'.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *         Vista con las preguntas de seguridad o redireccion al formulario
     *         de recuperacion si no hay correo en la sesion.
     */
    public function showQuestions(Request $request)
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();

        $email = $request->email ?? session('security_recovery_email');

        if (!$email) {
            return redirect()->route('security.recovery.form')->withErrors([
                'email' => 'Por favor, ingresa tu correo electrónico primero.'
            ]);
        }

        $user = User::where('email', $email)->firstOrFail();
        $questions = json_decode($user->security_questions, true);

        return view('auth.security-questions', compact('email', 'questions'));
    }

    /**
     * Verifica las respuestas del usuario y genera un token de restablecimiento.
     *
     * Este metodo implementa la verificacion final del flujo de recuperacion
     * de contrasena. Utiliza Hash::check para comparar cada respuesta ingresada
     * con el hash almacenado en la base de datos (campos security_answer_1,
     * security_answer_2, security_answer_3). Esta comparacion es segura ya que
     * no expone los hashes ni acepta comparaciones en texto plano.
     *
     * Flujo:
     * 1. Valida que el correo y las tres respuestas esten presentes.
     * 2. Busca el usuario por correo electronico.
     * 3. Itera las tres respuestas y verifica cada una con Hash::check().
     *    Si alguna respuesta es incorrecta, regenera el token CSRF y retorna
     *    a la pagina anterior con un mensaje de error.
     * 4. Si todas las respuestas son correctas, genera un token de
     *    restablecimiento de contrasena usando Password::getRepository()->create().
     * 5. Si la cuenta estaba inactiva, la reactiva estableciendo is_active = true.
     * 6. Redirige al formulario de restablecimiento de contrasena con el token
     *    y el correo electronico del usuario.
     *
     * @param Request $request La peticion HTTP con los campos 'email', 'answer_1',
     *                         'answer_2' y 'answer_3'.
     * @return \Illuminate\Http\RedirectResponse Redireccion al formulario de
     *         restablecimiento con el token o a la pagina anterior con errores.
     */
    public function verifyAnswers(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'answer_1' => 'required|string',
            'answer_2' => 'required|string',
            'answer_3' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verificar respuestas
        $answers = [
            $request->answer_1,
            $request->answer_2,
            $request->answer_3
        ];

        for ($i = 0; $i < 3; $i++) {
            if (!Hash::check($answers[$i], $user->{'security_answer_' . ($i + 1)})) {
                //  REGENERAR TOKEN DESPUÉS DE ERROR
                session()->regenerateToken();
                return back()->withErrors([
                    'answers' => 'Una o más respuestas son incorrectas. Por favor, inténtalo de nuevo.'
                ])->withInput();
            }
        }

        // Generar token para restablecer contraseña
        $token = Password::getRepository()->create($user);

        // Si el usuario está inactivo, activarlo
        if (!$user->is_active) {
            $user->is_active = true;
            $user->save();
        }

        //  REGENERAR TOKEN ANTES DE REDIRIGIR
        session()->regenerateToken();

        // Redirigir al formulario de restablecimiento
        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email
        ])->with('status', 'Preguntas verificadas correctamente. Ahora puedes restablecer tu contraseña.');
    }

    /**
     * Muestra el formulario de inicio del flujo de reactivacion de cuenta.
     *
     * Regenera el token CSRF de la sesion para proteger el formulario.
     * Retorna la vista 'auth.security-reactivation' donde el usuario
     * ingresa su correo electronico para iniciar el proceso de reactivacion.
     *
     * @return \Illuminate\View\View Vista con el formulario de reactivacion.
     */
    public function showReactivationForm()
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();
        return view('auth.security-reactivation');
    }

    /**
     * Valida el correo electronico y verifica elegibilidad para reactivacion.
     *
     * Flujo:
     * 1. Valida que el correo electronico exista en la tabla users.
     * 2. Busca el usuario por correo electronico.
     * 3. Si la cuenta ya esta activa (is_active = true), redirige al login
     *    con un mensaje indicando que la cuenta ya esta activa.
     * 4. Si el usuario no tiene preguntas de seguridad configuradas,
     *    retorna a la pagina anterior con un error indicando que contacte
     *    al administrador.
     * 5. Almacena el correo en la sesion bajo 'security_reactivation_email'
     *    para los pasos siguientes del flujo.
     * 6. Regenera el token CSRF y redirige al formulario de preguntas
     *    de reactivacion.
     *
     * @param Request $request La peticion HTTP que contiene el campo 'email'.
     * @return \Illuminate\Http\RedirectResponse Redireccion al formulario de
     *         preguntas de reactivacion, al login o a la pagina anterior con errores.
     */
    public function reactivateAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_active) {
            //  REGENERAR TOKEN DESPUÉS DE REDIRIGIR
            session()->regenerateToken();
            return redirect()->route('login')->with('status', 'Tu cuenta ya está activa. Puedes iniciar sesión.');
        }

        if (!$user->hasSecurityQuestions()) {
            //  REGENERAR TOKEN DESPUÉS DE ERROR
            session()->regenerateToken();
            return back()->withErrors([
                'email' => 'Este usuario no tiene configuradas preguntas de seguridad. Contacta al administrador.'
            ]);
        }

        session(['security_reactivation_email' => $user->email]);

        //  REGENERAR TOKEN ANTES DE REDIRIGIR
        session()->regenerateToken();

        return redirect()->route('security.reactivation.questions', ['email' => $user->email]);
    }

    /**
     * Muestra las preguntas de seguridad del usuario para el flujo de reactivacion.
     *
     * Recupera el correo electronico del request o de la sesion. Si no hay
     * correo disponible, redirige al formulario de reactivacion con un error.
     * Busca el usuario, decodifica las preguntas de seguridad almacenadas
     * en formato JSON y muestra la vista 'auth.security-reactivation-questions'
     * con las preguntas y el correo del usuario.
     *
     * @param Request $request La peticion HTTP que puede contener el campo 'email'.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *         Vista con las preguntas de seguridad o redireccion al formulario
     *         de reactivacion si no hay correo en la sesion.
     */
    public function showReactivationQuestions(Request $request)
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();

        $email = $request->email ?? session('security_reactivation_email');

        if (!$email) {
            return redirect()->route('security.reactivation.form')->withErrors([
                'email' => 'Por favor, ingresa tu correo electrónico primero.'
            ]);
        }

        $user = User::where('email', $email)->firstOrFail();
        $questions = json_decode($user->security_questions, true);

        return view('auth.security-reactivation-questions', compact('email', 'questions'));
    }

    /**
     * Verifica las respuestas del usuario y reactiva la cuenta.
     *
     * Este metodo implementa la verificacion final del flujo de reactivacion
     * de cuenta. Utiliza Hash::check para comparar cada respuesta ingresada
     * con el hash almacenado en la base de datos (campos security_answer_1,
     * security_answer_2, security_answer_3).
     *
     * Flujo:
     * 1. Valida que el correo y las tres respuestas esten presentes.
     * 2. Busca el usuario por correo electronico.
     * 3. Si la cuenta ya esta activa, redirige al login con un mensaje
     *    informativo.
     * 4. Itera las tres respuestas y verifica cada una con Hash::check().
     *    Si alguna respuesta es incorrecta, regenera el token CSRF y retorna
     *    a la pagina anterior con un mensaje de error.
     * 5. Si todas las respuestas son correctas:
     *    a. Establece is_active = true en el usuario y guarda los cambios.
     *    b. Crea una notificacion de tipo 'success' informando que la cuenta
     *       fue reactivada exitosamente.
     *    c. Regenera el token CSRF y redirige al login con un mensaje de
     *       exito.
     *
     * @param Request $request La peticion HTTP con los campos 'email', 'answer_1',
     *                         'answer_2' y 'answer_3'.
     * @return \Illuminate\Http\RedirectResponse Redireccion al login con mensaje
     *         de exito o a la pagina anterior con errores.
     */
    public function verifyReactivationAnswers(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'answer_1' => 'required|string',
            'answer_2' => 'required|string',
            'answer_3' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_active) {
            //  REGENERAR TOKEN DESPUÉS DE REDIRIGIR
            session()->regenerateToken();
            return redirect()->route('login')->with('status', 'Tu cuenta ya está activa.');
        }

        // Verificar respuestas
        $answers = [
            $request->answer_1,
            $request->answer_2,
            $request->answer_3
        ];

        for ($i = 0; $i < 3; $i++) {
            if (!Hash::check($answers[$i], $user->{'security_answer_' . ($i + 1)})) {
                //  REGENERAR TOKEN DESPUÉS DE ERROR
                session()->regenerateToken();
                return back()->withErrors([
                    'answers' => 'Una o más respuestas son incorrectas. Por favor, inténtalo de nuevo.'
                ])->withInput();
            }
        }

        // Reactivar cuenta
        $user->is_active = true;
        $user->save();

        // Enviar notificación
        $user->createNotification(
            'Cuenta Reactivada',
            'Tu cuenta ha sido reactivada exitosamente.',
            'success'
        );

        //  REGENERAR TOKEN ANTES DE REDIRIGIR
        session()->regenerateToken();

        return redirect()->route('login')->with('status', '¡Tu cuenta ha sido reactivada exitosamente! Ahora puedes iniciar sesión.');
    }
}