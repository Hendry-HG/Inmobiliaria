<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware que verifica si la cuenta del usuario autenticado esta activa.
 *
 * Este middleware se ejecuta en cada peticion HTTP para usuarios autenticados.
 * Si detecta que la cuenta tiene el campo is_active en false, cierra la sesion
 * del usuario, almacena su correo electronico en la sesion para el flujo de
 * reactivacion, y lo redirige a la ruta 'account.inactive' con un mensaje
 * de advertencia. Esto garantiza que usuarios con cuentas desactivadas no
 * puedan acceder a ninguna ruta protegida por este middleware.
 *
 * Si el usuario esta autenticado y su cuenta esta activa (o no hay sesion),
 * la peticion continúa normalmente hacia el siguiente handler.
 */
class CheckAccountActive
{
    /**
     * Intercepta la peticion y verifica el estado de la cuenta del usuario.
     *
     * Flujo:
     * 1. Verifica si hay un usuario autenticado (Auth::check()).
     * 2. Si existe usuario y su propiedad is_active es falsa:
     *    a. Obtiene su correo electronico antes de cerrar sesion.
     *    b. Cierra la sesion con Auth::logout().
     *    c. Almacena el correo en la sesion bajo la clave 'reactivation_email'
     *       para que el flujo de reactivacion pueda identificar al usuario.
     *    d. Redirige a la ruta 'account.inactive' con un mensaje de advertencia.
     * 3. Si el usuario no esta autenticado o su cuenta esta activa, la peticion
     *    continua normalmente hacia el siguiente middleware o controller.
     *
     * @param Request $request La peticion HTTP entrante.
     * @param Closure $next Callback que ejecuta el siguiente middleware o controller.
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *         Redireccion a la pagina de cuenta inactiva si la cuenta esta desactivada,
     *         o la respuesta normal del siguiente handler en caso contrario.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->is_active) {
            $email = Auth::user()->email;
            Auth::logout();

            session(['reactivation_email' => $email]);

            return redirect()->route('account.inactive')
                ->with('warning', 'Tu cuenta ha sido desactivada.');
        }

        return $next($request);
    }
}
