<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use App\Http\Middleware\CheckAccountActive;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\RefreshUserPermissions;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrar middleware alias
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.account.active' => CheckAccountActive::class,
            'security.headers' => SecurityHeaders::class,
            'refresh.permissions' => RefreshUserPermissions::class,
        ]);

        $middleware->append(SecurityHeaders::class);
        $middleware->append(RefreshUserPermissions::class);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\HttpException $e, Illuminate\Http\Request $request) {
            if ($e->getPrevious() instanceof TokenMismatchException) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Tu sesión ha expirado. Recarga la página e inténtalo de nuevo.'], 419);
                }

                return redirect()->route('login', ['session_expired' => 1]);
            }
        });
    })->create();
