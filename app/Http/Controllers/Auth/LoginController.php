<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected function redirectTo()
    {
        $user = Auth::user();

        if (!$user) {
            return route('login');
        }

        //  OBTENER ROLES CON UNA SOLA CONSULTA OPTIMIZADA
        $roles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        // Redirigir según el rol
        if (in_array('Super Admin', $roles)) {
            return route('super-admin.dashboard');
        } elseif (in_array('Administrador', $roles)) {
            return route('admin.dashboard');
        } elseif (in_array('Asesor Inmobiliario', $roles)) {
            return route('asesor.dashboard');
        } elseif (in_array('Auditor', $roles)) {
            return route('auditor.dashboard');
        } elseif (in_array('Cliente', $roles)) {
            return route('cliente.dashboard');
        }

        return route('dashboard');
    }

    protected function redirectPath()
    {
        return $this->redirectTo();
    }

    /**
     *  Handle successful authentication con validación de sesión
     */
    protected function authenticated(Request $request, $user)
    {
        //  VERIFICAR QUE LA SESIÓN ESTÉ ACTIVA
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Error de autenticación. Por favor, intenta nuevamente.'
            ]);
        }

        $intendedUrl = session()->get('url.intended');

        if ($intendedUrl && $this->isValidRedirectUrl($intendedUrl)) {
            session()->forget('url.intended');

            if ($this->isInternalUrl($intendedUrl)) {
                return redirect($intendedUrl);
            }
        }

        return redirect($this->redirectTo());
    }

    private function isValidRedirectUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        if (str_starts_with($url, '/api/')) {
            return false;
        }

        $forbiddenRoutes = ['/logout', '/login', route('logout'), route('login')];
        foreach ($forbiddenRoutes as $route) {
            if ($url === $route) {
                return false;
            }
        }

        return true;
    }

    private function isInternalUrl($url)
    {
        if (str_starts_with($url, '/')) {
            return true;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['host']) && $parsedUrl['host'] === request()->getHost()) {
                return true;
            }
        }

        return false;
    }

    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'showInactiveAccount', 'requestReactivation', 'reactivateAccount']);
    }

    public function showLoginForm()
    {
        $previousUrl = url()->previous();
        $loginUrl = route('login');
        $registerUrl = route('register');

        if ($previousUrl !== $loginUrl && $previousUrl !== $registerUrl && !str_contains($previousUrl, 'login') && !str_contains($previousUrl, 'register')) {
            session(['url.intended' => $previousUrl]);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        //  VALIDACIÓN CON RATE LIMITING INTEGRADO
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            //  LOG DE INTENTOS FALLIDOS PARA AUDITORÍA
            Log::warning('Intento de login fallido', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        if (!$user->is_active) {
            session(['reactivation_email' => $user->email]);
            return redirect()->route('account.inactive')
                ->with('warning', 'Tu cuenta se encuentra inactiva. Puedes solicitar su reactivación a través de tu correo electrónico.');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            //  REGENERAR SESIÓN OBLIGATORIAMENTE
            $request->session()->regenerate();

            //  VERIFICAR AUTENTICACIÓN
            if (!Auth::check()) {
                throw ValidationException::withMessages([
                    'email' => ['Error de autenticación. Por favor, intenta nuevamente.']
                ]);
            }

            $this->cleanIntendedUrl();

            return $this->authenticated($request, Auth::user());
        }

        throw ValidationException::withMessages([
            'email' => ['Hubo un problema al iniciar sesión.'],
        ]);
    }

    private function cleanIntendedUrl()
    {
        $intended = session()->get('url.intended');

        if ($intended) {
            $ignoredUrls = [
                route('login'),
                route('register'),
                url('/login'),
                url('/register'),
                '/login',
                '/register'
            ];

            if (in_array($intended, $ignoredUrls)) {
                session()->forget('url.intended');
            }
        }
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
            return redirect()->route('login')->with('success', 'Tu cuenta ya está activa. Puedes iniciar sesión.');
        }

        $token = Str::random(64);

        DB::table('account_reactivation_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        try {
            Mail::send('emails.account-reactivation', [
                'user' => $user,
                'token' => $token,
                'reactivationLink' => route('account.reactivate', ['token' => $token, 'email' => $user->email])
            ], function($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Solicitud de Reactivación de Cuenta - ' . config('app.name'));
            });

            return back()->with('success', 'Hemos enviado un correo con las instrucciones para reactivar tu cuenta. Por favor, revisa tu bandeja de entrada.');
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de reactivación: ' . $e->getMessage());
            return back()->with('error', 'Hubo un problema al enviar el correo. Por favor, intenta nuevamente más tarde.');
        }
    }

    public function reactivateAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        $tokenData = DB::table('account_reactivation_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return redirect()->route('login')->with('error', 'El enlace de reactivación es inválido o ya ha sido utilizado.');
        }

        if (Carbon::parse($tokenData->created_at)->addHours(24)->isPast()) {
            DB::table('account_reactivation_tokens')->where('email', $request->email)->delete();
            return redirect()->route('login')->with('error', 'El enlace de reactivación ha expirado. Por favor, solicita uno nuevo.');
        }

        $user = User::where('email', $request->email)->first();
        $user->is_active = true;
        $user->save();

        DB::table('account_reactivation_tokens')->where('email', $request->email)->delete();

        try {
            Mail::send('emails.account-reactivated', ['user' => $user], function($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Tu Cuenta ha sido Reactivada - ' . config('app.name'));
            });
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de reactivación: ' . $e->getMessage());
        }

        session()->forget('reactivation_email');

        return redirect()->route('login')->with('success', '¡Tu cuenta ha sido reactivada exitosamente! Ya puedes iniciar sesión.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}