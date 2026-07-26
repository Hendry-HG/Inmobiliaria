<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Controlador de autenticacion encargado del inicio de sesion.
 *
 * Flujo de autenticacion:
 * - El usuario envia credenciales (email + password) desde el formulario de login.
 * - Se validan las credenciales y se verifica el estado de la cuenta (activa/inactiva).
 * - En caso exitoso, se regenera la sesion y el token CSRF para prevenir session fixation.
 * - Se registra un evento de auditoria (AuditLog) con la IP, user agent y datos del usuario.
 *
 * Gestion de sesiones:
 * - Antes del login se regenera el ID de sesion y el token CSRF.
 * - Desues del login se regenera nuevamente la sesion para asegurar un identificador unico.
 * - En cada flujo fallido se regenera el token CSRF para evitar ataques de tipo CSRF.
 *
 * Cuentas inactivas:
 * - Si la cuenta esta desactivada, se redirige a una vista de cuenta inactiva.
 * - El usuario puede solicitar reactivacion mediante un token enviado por correo.
 * - El token de reactivacion expira en 24 horas.
 *
 * Cierre de sesion:
 * - Se invalida completamente la sesion, se regenera el token CSRF y el ID de sesion.
 * - Se eliminan las cookies de sesion y Laravel del navegador del cliente.
 *
 * Registro de auditoria:
 * - Cada login exitoso genera un registro en AuditLog con accion 'login'.
 * - Los intentos fallidos se registran en el log de Laravel con nivel warning.
 * - La reactivacion de cuentas tambien genera eventos de auditoria.
 */
class LoginController extends Controller
{
    /**
     * Constructor del controlador.
     *
     * Aplica el middleware 'guest' a todas las rutas excepto las de logout,
     * gestion de cuentas inactivas y reactivacion. Esto asegura que un usuario
     * autenticado no pueda acceder a las vistas de login o registro.
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'showInactiveAccount', 'requestReactivation', 'reactivateAccount']);
    }

    /**
     * Muestra el formulario de inicio de sesion.
     *
     * Antes de renderizar la vista, regenera el token CSRF para asegurar
     * que el formulario utilice un token valido y nuevo. Esto es una medida
     * de seguridad para prevenir ataques de tipo CSRF al momento de enviar
     * las credenciales de autenticacion.
     *
     * @return \Illuminate\View\View Vista del formulario de login.
     */
    public function showLoginForm()
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO DE LOGIN
        session()->regenerateToken();
        return view('auth.login');
    }

    /**
     * Determina la ruta de redireccion despues del login basandose en los permisos del usuario.
     *
     * Utiliza el PermissionService para evaluar los roles asignados al usuario
     * y determinar el dashboard correspondiente. Cada tipo de usuario (admin,
     * gerente, agente, cliente, etc.) es redirigido a su panel especifico.
     *
     * Si el usuario no esta autenticado, redirige a la ruta de login.
     *
     * @return string Ruta a la que se redirigira al usuario.
     */
    protected function redirectTo()
    {
        $user = Auth::user();

        if (!$user) {
            return route('login');
        }

        $permissionService = new PermissionService($user);
        $dashboardRoute = $permissionService->getDashboardRoute();

        Log::info('LoginController - redirectTo', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $permissionService->getRoles(),
            'main_role' => $permissionService->getMainRole(),
            'dashboard' => $dashboardRoute
        ]);

        return $dashboardRoute;
    }

    /**
     * Metodo que Laravel utiliza internamente para determinar la ruta de redireccion.
     *
     * Actua como delegado de redirectTo(), siguiendo la convencion de Laravel
     * para controladores de autenticacion. Es llamado automaticamente por el
     * framework despues de un login exitoso.
     *
     * @return string Ruta a la que se redirigira al usuario.
     */
    protected function redirectPath()
    {
        return $this->redirectTo();
    }

    /**
     * Procesa el intento de inicio de sesion.
     *
     * Flujo completo:
     * 1. Valida que el email y password esten presentes y en formato correcto.
     * 2. Busca el usuario por email y verifica la contrasena con Hash::check.
     * 3. Si las credenciales son invalidas, registra el intento fallido en el log
     *    de Laravel con nivel warning (incluyendo email e IP del solicitante),
     *    regenera el token CSRF y lanza una ValidationException.
     * 4. Si la cuenta esta inactiva, redirige a la vista de cuenta inactiva y
     *    almacena el email en la sesion para el flujo de reactivacion.
     * 5. Si todo es correcto:
     *    a. Regenera el ID de sesion y token CSRF (prevencion de session fixation).
     *    b. Ejecuta Auth::attempt con las credenciales y la preferencia "remember me".
     *    c. Regenera la sesion nuevamente despues del login exitoso.
     *    d. Registra un AuditLog con accion 'login' que incluye: user_id, IP,
     *       user_agent, URL, y descripcion legible del evento.
     *    e. Redirige al dashboard correspondiente al rol del usuario.
     *
     * Gestion de sesiones:
     * - Se regenera el token CSRF en cada camino posible del metodo (exito, fallo,
     *   cuenta inactiva) para mantener la integridad de la proteccion CSRF.
     * - Se regenera el ID de sesion antes y despues del login exitoso para
     *   eliminar cualquier sesion previa y garantizar un ID unico y nuevo.
     *
     * @param  \Illuminate\Http\Request $request Solicitud HTTP con credenciales de login.
     * @return \Illuminate\Http\RedirectResponse Redireccion al dashboard o a la vista de error.
     * @throws \Illuminate\Validation\ValidationException Si las credenciales son invalidas.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            Log::warning('Intento de login fallido', [
                'email' => $credentials['email'],
                'ip' => $request->ip()
            ]);

            //  REGENERAR TOKEN DESPUÉS DE ERROR
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        if (!$user->is_active) {
            //  REGENERAR TOKEN ANTES DE REDIRIGIR
            session()->regenerateToken();
            session(['reactivation_email' => $user->email]);
            return redirect()->route('account.inactive')
                ->with('warning', 'Tu cuenta se encuentra inactiva.');
        }

        //  REGENERAR SESIÓN Y TOKEN ANTES DE LOGIN
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $permissionService = new PermissionService($user);
            $dashboardRoute = $permissionService->getDashboardRoute();

            Log::info('LoginController - login exitoso', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'roles' => $permissionService->getRoles(),
                'main_role' => $permissionService->getMainRole(),
                'dashboard' => $dashboardRoute
            ]);

            //  REGENERAR SESIÓN DESPUÉS DE LOGIN
            $request->session()->regenerate();

            // AUDITORÍA - LOGIN EXITOSO
            AuditLog::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'action' => 'login',
                'event' => 'login',
                'subject_type' => get_class($user),
                'subject_id' => $user->id,
                'description' => $user->full_name . ' inició sesión correctamente',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);

            return redirect()->intended($dashboardRoute);
        }

        //  REGENERAR TOKEN DESPUÉS DE ERROR
        session()->regenerateToken();

        throw ValidationException::withMessages([
            'email' => ['Hubo un problema al iniciar sesión.'],
        ]);
    }

    /**
     * Muestra la vista de cuenta inactiva.
     *
     * Recupera el email de la sesion (almacenado durante el intento de login
     * fallido por cuenta inactiva) y muestra la vista correspondiente. Si no
     * existe un email en la sesion, redirige al formulario de login.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Vista de cuenta inactiva o redireccion a login.
     */
    public function showInactiveAccount()
    {
        $email = session('reactivation_email');
        if (!$email) {
            return redirect()->route('login');
        }
        return view('auth.inactive-account', compact('email'));
    }

    /**
     * Procesa la solicitud de reactivacion de cuenta inactiva.
     *
     * Flujo:
     * 1. Valida que el email exista en la base de datos.
     * 2. Si la cuenta ya esta activa, redirige al login con un mensaje de exito.
     * 3. Genera un token aleatorio de 64 caracteres y lo almacena en la tabla
     *    'account_reactivation_tokens' usando updateOrInsert (upsert).
     * 4. Envia un correo electronico al usuario con un enlace de reactivacion
     *    que contiene el token y el email como parametros de URL.
     * 5. El token tiene una validez de 24 horas desde su creacion.
     * 6. En caso de error al enviar el correo, se registra en el log de Laravel
     *    con nivel error y se muestra un mensaje generico al usuario.
     *
     * Seguridad:
     * - Se regenera el token CSRF despues de cada flujo (exito, error, redireccion).
     * - El token de reactivacion es unico por email (updateOrInsert lo reemplaza
     *   si ya existe uno previo, invalidando tokens anteriores).
     *
     * @param  \Illuminate\Http\Request $request Solicitud con el campo 'email'.
     * @return \Illuminate\Http\RedirectResponse Mensaje de exito o error.
     */
    public function requestReactivation(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'No se encontró una cuenta con ese correo electrónico.');
        }

        if ($user->is_active) {
            //  REGENERAR TOKEN ANTES DE REDIRIGIR
            session()->regenerateToken();
            return redirect()->route('login')->with('success', 'Tu cuenta ya está activa. Puedes iniciar sesión.');
        }

        $token = \Illuminate\Support\Str::random(64);

        \Illuminate\Support\Facades\DB::table('account_reactivation_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $token,
                'created_at' => \Carbon\Carbon::now()
            ]
        );

        try {
            \Illuminate\Support\Facades\Mail::send('emails.account-reactivation', [
                'user' => $user,
                'token' => $token,
                'reactivationLink' => route('account.reactivate', ['token' => $token, 'email' => $user->email])
            ], function($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Solicitud de Reactivación de Cuenta - ' . config('app.name'));
            });

            //  REGENERAR TOKEN DESPUÉS DE ENVIAR CORREO
            session()->regenerateToken();

            return back()->with('success', 'Hemos enviado un correo con las instrucciones para reactivar tu cuenta.');
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de reactivación: ' . $e->getMessage());

            //  REGENERAR TOKEN DESPUÉS DE ERROR
            session()->regenerateToken();

            return back()->with('error', 'Hubo un problema al enviar el correo.');
        }
    }

    /**
     * Activa la cuenta del usuario utilizando el token de reactivacion.
     *
     * Flujo:
     * 1. Valida que el email y el token esten presentes y sean validos.
     * 2. Busca el token en la tabla 'account_reactivation_tokens' verificando
     *    que coincida tanto el email como el token.
     * 3. Si el token no existe, redirige al login con error de enlace invalido.
     * 4. Si el token tiene mas de 24 horas de antiguedad, lo elimina y redirige
     *    al login indicando que el enlace ha expirado.
     * 5. Si el token es valido y no ha expirado:
     *    a. Actualiza el campo 'is_active' del usuario a true.
     *    b. Elimina el token utilizado de la tabla 'account_reactivation_tokens'.
     *    c. Limpia la variable de sesion 'reactivation_email'.
     *    d. Regenera el token CSRF y redirige al login con mensaje de exito.
     *
     * @param  \Illuminate\Http\Request $request Solicitud con campos 'email' y 'token'.
     * @return \Illuminate\Http\RedirectResponse Redireccion al login con mensaje de resultado.
     */
    public function reactivateAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        $tokenData = \Illuminate\Support\Facades\DB::table('account_reactivation_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            //  REGENERAR TOKEN ANTES DE REDIRIGIR
            session()->regenerateToken();
            return redirect()->route('login')->with('error', 'El enlace de reactivación es inválido.');
        }

        if (\Carbon\Carbon::parse($tokenData->created_at)->addHours(24)->isPast()) {
            \Illuminate\Support\Facades\DB::table('account_reactivation_tokens')->where('email', $request->email)->delete();

            //  REGENERAR TOKEN ANTES DE REDIRIGIR
            session()->regenerateToken();

            return redirect()->route('login')->with('error', 'El enlace de reactivación ha expirado.');
        }

        $user = User::where('email', $request->email)->first();
        $user->is_active = true;
        $user->save();

        \Illuminate\Support\Facades\DB::table('account_reactivation_tokens')->where('email', $request->email)->delete();

        session()->forget('reactivation_email');

        //  REGENERAR TOKEN ANTES DE REDIRIGIR
        session()->regenerateToken();

        return redirect()->route('login')->with('success', '¡Tu cuenta ha sido reactivada exitosamente!');
    }

    /**
     * Cierra la sesion del usuario autenticado.
     *
     * Este metodo es una ruta de logout alternativa (la principal esta en
     * LogoutController). Ejecuta un proceso completo de cierre de sesion:
     *
     * 1. Cierra la sesion del usuario con Auth::logout().
     * 2. Invalida completamente la sesion Laravel con session()->invalidate().
     * 3. Regenera el token CSRF para proteger futuras solicitudes.
     * 4. Regenera el ID de sesion como medida adicional de seguridad.
     * 5. Elimina manualmente las cookies de sesion ('session' y 'laravel')
     *    estableciendo su fecha de expiracion en el pasado.
     *
     * Nota: Este metodo no registra evento de auditoria en AuditLog. Para el
     * logout con registro de auditoria, utilice el LogoutController.
     *
     * @param  \Illuminate\Http\Request $request Solicitud HTTP actual.
     * @return \Illuminate\Http\RedirectResponse Redireccion a la ruta raiz del sitio.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        //  INVALIDAR SESIÓN COMPLETAMENTE
        $request->session()->invalidate();

        //  REGENERAR TOKEN CSRF
        $request->session()->regenerateToken();

        //  REGENERAR ID DE SESIÓN (SEGURIDAD EXTRA)
        $request->session()->regenerate();

        //  LIMPIAR COOKIES DE SESIÓN
        foreach ($request->cookies->all() as $name => $value) {
            if (str_contains($name, 'session') || str_contains($name, 'laravel')) {
                setcookie($name, '', time() - 3600, '/');
            }
        }

        return redirect('/');
    }
}