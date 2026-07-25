<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

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

        return redirect()->route('home');
    }
}
