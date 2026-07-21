<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiLocationController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PhoneController;

/*
|--------------------------------------------------------------------------
| API Pública
|--------------------------------------------------------------------------
*/

Route::prefix('api')->name('api.')->middleware('throttle:60,1')->group(function () {
    // Ubicaciones
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/countries', [ApiLocationController::class, 'getCountries'])->name('countries');
        Route::get('/states/{countryId}', [ApiLocationController::class, 'getStates'])->name('states');
        Route::get('/municipalities/{stateId}', [ApiLocationController::class, 'getMunicipalities'])->name('municipalities');
        Route::get('/parishes/{municipalityId}', [ApiLocationController::class, 'getParishes'])->name('parishes');
        Route::get('/cities/{parishId}', [ApiLocationController::class, 'getCities'])->name('cities');
    });

    // Registro
    Route::prefix('register')->name('register.')->group(function () {
        Route::get('/countries', [LocationController::class, 'getCountriesForRegister'])->name('countries');
        Route::get('/states/{countryId}', [LocationController::class, 'getStatesForRegister'])->name('states');
        Route::get('/municipalities/{stateId}', [LocationController::class, 'getMunicipalitiesForRegister'])->name('municipalities');
        Route::get('/parishes/{municipalityId}', [LocationController::class, 'getParishesForRegister'])->name('parishes');
        Route::get('/cities/{parishId}', [LocationController::class, 'getCitiesForRegister'])->name('cities');
    });

    // Teléfonos
    Route::prefix('phone')->name('phone.')->group(function () {
        Route::get('/presets', [PhoneController::class, 'getPresets'])->name('presets');
        Route::get('/codes', [PhoneController::class, 'getPhoneCodes'])->name('codes');
        Route::get('/config/{countryId}', [PhoneController::class, 'getPhoneConfig'])->name('config');
    });
});
