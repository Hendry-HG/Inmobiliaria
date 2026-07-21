<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'showInactiveAccount', 'requestReactivation', 'reactivateAccount']);
    }

    public function showLoginForm()
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO DE LOGIN
        session()->regenerateToken();
        return view('auth.login');
    }

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

    protected function redirectPath()
    {
        return $this->redirectTo();
    }

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

            return redirect()->intended($dashboardRoute);
        }

        //  REGENERAR TOKEN DESPUÉS DE ERROR
        session()->regenerateToken();

        throw ValidationException::withMessages([
            'email' => ['Hubo un problema al iniciar sesión.'],
        ]);
    }

    public function showInactiveAccount()
    {
        $email = session('reactivation_email');
        if (!$email) {
            return redirect()->route('login');
        }
        return view('auth.inactive-account', compact('email'));
    }

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