<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountActive
{
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
