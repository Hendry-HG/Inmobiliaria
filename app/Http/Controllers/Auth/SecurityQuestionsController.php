<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class SecurityQuestionsController extends Controller
{
    public function showRecoveryForm()
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();
        return view('auth.security-recovery');
    }

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

    public function showReactivationForm()
    {
        //  REGENERAR TOKEN AL MOSTRAR EL FORMULARIO
        session()->regenerateToken();
        return view('auth.security-reactivation');
    }

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