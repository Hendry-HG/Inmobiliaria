<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\AppointmentController;

/*
|--------------------------------------------------------------------------
| Web Routes - PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Catálogo público
Route::prefix('catalogo')->name('catalogo.')->group(function () {
    Route::get('/', [PropertyController::class, 'catalog'])->name('index');
    Route::get('/{property}', [PropertyController::class, 'showPublic'])->name('show');
});

// Propiedades públicas
Route::prefix('propiedad')->name('propiedad.')->group(function () {
    Route::get('/ver/{id}', [PropertyController::class, 'showById'])->name('ver');
});

// API pública - Contador de propiedades
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/properties/count', [PropertyController::class, 'countProperties'])->name('properties.count');
});

// Servicios públicos
Route::get('/servicios', [ServiceController::class, 'publicIndex'])->name('servicios.public');

// Citas públicas
Route::get('/citas/create', [AppointmentController::class, 'createPublic'])->name('citas.create');

// Leads públicos - Formulario de valoración
Route::post('/solicitar-valoracion', [LeadController::class, 'storePublic'])
    ->name('lead.store.public')
    ->middleware('throttle:5,1');

// =============================================
//  RUTA PARA REFRESCAR TOKEN CSRF
// =============================================
Route::get('/refresh-csrf', function () {
    if (request()->ajax()) {
        session()->regenerateToken();
        return response()->json([
            'csrf_token' => csrf_token()
        ]);
    }
    return response()->json(['error' => 'Invalid request'], 400);
})->name('refresh.csrf');

// =============================================
//   IMPORTANTE: ESTO CARGA LOS DEMÁS ARCHIVOS
//  Los archivos deben estar en la carpeta /routes
// =============================================
require __DIR__.'/auth.php';
require __DIR__.'/security.php';
require __DIR__.'/api-public.php';
require __DIR__.'/admin.php';
