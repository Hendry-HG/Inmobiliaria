<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Controlador de Restablecimiento de Contrasena
 *
 * Gestiona el proceso de restablecimiento de contrasena para usuarios
 * que olvidaron su clave de acceso. Muestra el formulario con el token
 * de recuperacion y procesa el cambio efectivo de la contrasena.
 * Regenera tokens CSRF en cada operacion por seguridad.
 *
 * @package App\Http\Controllers\Auth
 */
class ResetPasswordController extends Controller
{
    /**
     * Muestra el formulario de restablecimiento de contrasena.
     *
     * Flujo de datos:
     * 1. Regenera el token CSRF de la sesion para prevenir ataques
     * 2. Recibe el token de recuperacion y el email como parametros de ruta
     * 3. Retorna la vista 'auth.reset-password' con token y email precargados
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null $token Token de restablecimiento enviado por correo
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Procesa el restablecimiento efectivo de la contrasena.
     *
     * Flujo de datos:
     * 1. Valida que el token, email y nueva contrasena sean validos
     * 2. Utiliza Password::reset de Laravel para validar el token y actualizar
     * 3. Hashifica la nueva contrasena y genera un nuevo remember_token
     * 4. Regenera el token CSRF de la sesion por seguridad
     * 5. Redirige al login con mensaje de exito o retorna errores
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        //  REGENERAR TOKEN DESPUÉS DE RESTABLECER LA CONTRASEÑA
        session()->regenerateToken();

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}