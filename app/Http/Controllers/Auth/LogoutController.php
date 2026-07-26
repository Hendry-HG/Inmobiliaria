<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador dedicado al cierre de sesion con registro de auditoria.
 *
 * Flujo de cierre de sesion:
 * 1. Antes de cerrar la sesion, se obtiene el usuario autenticado y se registra
 *    un evento de auditoria en AuditLog con accion 'logout'. Esto garantiza que
 *    el registro se realice con los datos del usuario antes de que la sesion
 *    sea invalidada.
 * 2. Se ejecuta Auth::logout() para cerrar la sesion de Laravel.
 * 3. Se invalida completamente la sesion para eliminar todos los datos.
 * 4. Se regenera el token CSRF para proteger futuras solicitudes.
 * 5. Se regenera el ID de sesion como medida adicional de seguridad.
 * 6. Se eliminan las cookies de sesion del navegador del cliente.
 *
 * Registro de auditoria:
 * - Cada cierre de sesion genera un registro en la tabla de auditoria que incluye:
 *   user_id, tipo de usuario, accion ('logout'), evento, tipo e ID del sujeto,
 *   descripcion legible, direccion IP, user agent del navegador y URL solicitada.
 * - El registro se realiza ANTES de invalidar la sesion para asegurar que el
 *   usuario este autenticado y sus datos esten disponibles.
 *
 * Seguridad:
 * - El middleware 'auth' en el constructor garantiza que solo usuarios
 *   autenticados puedan acceder a este controlador.
 * - La invalidacion completa de sesion previene el uso de sesiones capturadas.
 */
class LogoutController extends Controller
{
    /**
     * Crea una nueva instancia del controlador.
     *
     * Aplica el middleware 'auth' a todas las rutas del controlador, lo que
     * garantiza que solo los usuarios autenticados puedan ejecutar el cierre
     * de sesion. Los usuarios no autenticados seran redirigidos al login.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Cierra la sesion del usuario y registra el evento de auditoria.
     *
     * Flujo completo:
     * 1. Obtiene el usuario actualmente autenticado via Auth::user().
     * 2. Si existe un usuario, crea un registro en AuditLog con:
     *    - user_id y user_type: identificacion del usuario.
     *    - action y event: ambos establecidos en 'logout'.
     *    - subject_type y subject_id: referencia al modelo del usuario.
     *    - description: texto legible del evento (nombre completo del usuario).
     *    - ip_address: direccion IP del cliente.
     *    - user_agent: cadena del navegador del cliente.
     *    - url: URL completa que fue solicitada.
     * 3. Ejecuta Auth::logout() para cerrar la sesion de Laravel.
     * 4. Invalida la sesion completa con session()->invalidate().
     * 5. Regenera el token CSRF con session()->regenerateToken().
     * 6. Regenera el ID de sesion con session()->regenerate() como medida extra.
     * 7. Elimina manualmente las cookies de sesion ('session' y 'laravel') del
     *    navegador estableciendo su fecha de expiracion en el pasado.
     * 8. Redirige al usuario a la ruta 'home' (pagina principal del sitio).
     *
     * @param  \Illuminate\Http\Request $request Solicitud HTTP actual.
     * @return \Illuminate\Http\RedirectResponse Redireccion a la ruta home.
     */
    public function logout(Request $request)
    {
        // AUDITORÍA - LOGOUT (antes de cerrar sesión para obtener el usuario)
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'action' => 'logout',
                'event' => 'logout',
                'subject_type' => get_class($user),
                'subject_id' => $user->id,
                'description' => $user->full_name . ' cerró sesión',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
        }

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

        return redirect()->route('home');
    }
}
