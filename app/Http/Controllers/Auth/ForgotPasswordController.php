<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

/**
 * Controlador de Solicitud de Restablecimiento de Contrasena
 *
 * Gestiona la primera fase del proceso de recuperacion de contrasena.
 * Muestra el formulario para solicitar un enlace de restablecimiento
 * y envia el correo electronico con el token de recuperacion.
 * Regenera tokens CSRF en cada operacion por seguridad.
 *
 * @package App\Http\Controllers\Auth
 */
class ForgotPasswordController extends Controller
{
    /**
     * Muestra el formulario para solicitar el enlace de restablecimiento.
     *
     * Flujo de datos:
     * 1. Regenera el token CSRF de la sesion para prevenir ataques
     * 2. Retorna la vista 'auth.forgot-password' con el formulario de email
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();
        return view('auth.forgot-password');
    }

    /**
     * Envia el enlace de restablecimiento de contrasena por correo electronico.
     *
     * Flujo de datos:
     * 1. Valida que el campo email sea obligatorio y tenga formato valido
     * 2. Utiliza Password::sendResetLink de Laravel para generar y enviar el token
     * 3. Regenera el token CSRF de la sesion por seguridad
     * 4. Retorna a la vista con mensaje de exito si se envio, o errores si fallo
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        //  REGENERAR TOKEN DESPUÉS DE ENVIAR EL CORREO
        session()->regenerateToken();

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
}
