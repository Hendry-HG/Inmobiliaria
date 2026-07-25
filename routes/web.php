<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// RUTA PRINCIPAL
Route::get('/', [HomeController::class, 'index'])->name('home');

// =============================================
//  RUTAS PÚBLICAS ADICIONALES (ANTES DE AUTH)
// =============================================
Route::prefix('catalogo')->name('catalogo.')->group(function () {
    Route::get('/', [App\Http\Controllers\PropertyController::class, 'catalog'])->name('index');
    Route::get('/{property}', [App\Http\Controllers\PropertyController::class, 'showPublic'])->name('show');
});


Route::prefix('propiedad')->name('propiedad.')->group(function () {
    Route::get('/ver/{property}', [App\Http\Controllers\PropertyController::class, 'showPublic'])->name('ver');
});

Route::get('/servicios', [App\Http\Controllers\Admin\ServiceController::class, 'publicIndex'])->name('servicios.public');
Route::post('/solicitar-valoracion', [App\Http\Controllers\LeadController::class, 'storePublic'])
    ->name('lead.store.public')
    ->middleware('throttle:5,1');

// =============================================
//  API PÚBLICA (EXCLUIDA DE CSRF PARA REGISTRO)
// =============================================
Route::prefix('api')->name('api.')->middleware('throttle:60,1')->group(function () {
    // Ubicaciones
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/countries', [App\Http\Controllers\Api\ApiLocationController::class, 'getCountries'])->name('countries');
        Route::get('/states/{countryId}', [App\Http\Controllers\Api\ApiLocationController::class, 'getStates'])->name('states');
        Route::get('/municipalities/{stateId}', [App\Http\Controllers\Api\ApiLocationController::class, 'getMunicipalities'])->name('municipalities');
        Route::get('/parishes/{municipalityId}', [App\Http\Controllers\Api\ApiLocationController::class, 'getParishes'])->name('parishes');
        Route::get('/cities/{parishId}', [App\Http\Controllers\Api\ApiLocationController::class, 'getCities'])->name('cities');
    });

    // Registro
    Route::prefix('register')->name('register.')->group(function () {
        Route::get('/countries', [App\Http\Controllers\Admin\LocationController::class, 'getCountriesForRegister'])->name('countries');
        Route::get('/states/{countryId}', [App\Http\Controllers\Admin\LocationController::class, 'getStatesForRegister'])->name('states');
        Route::get('/municipalities/{stateId}', [App\Http\Controllers\Admin\LocationController::class, 'getMunicipalitiesForRegister'])->name('municipalities');
        Route::get('/parishes/{municipalityId}', [App\Http\Controllers\Admin\LocationController::class, 'getParishesForRegister'])->name('parishes');
        Route::get('/cities/{parishId}', [App\Http\Controllers\Admin\LocationController::class, 'getCitiesForRegister'])->name('cities');
    });

    // Teléfonos
    Route::prefix('phone')->name('phone.')->group(function () {
        Route::get('/presets', [App\Http\Controllers\Admin\PhoneController::class, 'getPresets'])->name('presets');
        Route::get('/codes', [App\Http\Controllers\Admin\PhoneController::class, 'getPhoneCodes'])->name('codes');
        Route::get('/config/{countryId}', [App\Http\Controllers\Admin\PhoneController::class, 'getPhoneConfig'])->name('config');
    });
});

Route::get('/refresh-csrf', function () {
    if (request()->ajax()) {
        session()->regenerateToken();
        return response()->json(['csrf_token' => csrf_token()]);
    }
    return response()->json(['error' => 'Invalid request'], 400);
})->name('refresh.csrf');

// =============================================
//  RUTAS DE AUTENTICACIÓN
// =============================================
require __DIR__.'/auth.php';

// =============================================
//  RUTAS DE SEGURIDAD
// =============================================
require __DIR__.'/security.php';

// =============================================
//  RUTAS ADMIN (PROTEGIDAS)
// =============================================
require __DIR__.'/admin.php';
