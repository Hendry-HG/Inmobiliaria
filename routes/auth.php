<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
       ->middleware('throttle:10,1');

    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle:5,1');

    // Recuperación de contraseña
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email')
        ->middleware('throttle:3,5');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update')
        ->middleware('throttle:5,1');

    // Cuenta inactiva
    Route::get('/account/inactive', [LoginController::class, 'showInactiveAccount'])
        ->name('account.inactive');
    Route::post('/account/reactivation/request', [LoginController::class, 'requestReactivation'])
        ->name('account.reactivation.request')
        ->middleware('throttle:3,5');
    Route::get('/account/reactivate', [LoginController::class, 'reactivateAccount'])
        ->name('account.reactivate');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
