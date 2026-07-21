<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SecurityQuestionsController;

/*
|--------------------------------------------------------------------------
| Rutas de Seguridad
|--------------------------------------------------------------------------
*/

Route::prefix('seguridad')->name('security.')->middleware('throttle:5,1')->group(function () {
    // Recuperación con preguntas de seguridad
    Route::get('/recuperar', [SecurityQuestionsController::class, 'showRecoveryForm'])
        ->name('recovery.form');
    Route::post('/verificar-email', [SecurityQuestionsController::class, 'verifyEmail'])
        ->name('verify.email')
        ->middleware('throttle:3,5');
    Route::get('/preguntas', [SecurityQuestionsController::class, 'showQuestions'])
        ->name('questions.show');
    Route::post('/verificar-respuestas', [SecurityQuestionsController::class, 'verifyAnswers'])
        ->name('verify.answers')
        ->middleware('throttle:5,1');

    // Reactivación con preguntas de seguridad
    Route::get('/reactivar', [SecurityQuestionsController::class, 'showReactivationForm'])
        ->name('reactivation.form');
    Route::post('/reactivar-verificar-email', [SecurityQuestionsController::class, 'reactivateAccount'])
        ->name('reactivation.verify.email')
        ->middleware('throttle:3,5');
    Route::get('/reactivar-preguntas', [SecurityQuestionsController::class, 'showReactivationQuestions'])
        ->name('reactivation.questions');
    Route::post('/reactivar-verificar-respuestas', [SecurityQuestionsController::class, 'verifyReactivationAnswers'])
        ->name('reactivation.verify.answers')
        ->middleware('throttle:5,1');
});
