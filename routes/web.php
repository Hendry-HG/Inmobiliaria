<?php

/**
 * Archivo de rutas web del sistema inmobiliario.
 *
 * Este archivo define las rutas publicas (accesibles sin autenticacion) y
 * incluye los archivos de rutas protegidas (autenticacion, seguridad, admin).
 *
 * Estructura general:
 *   1. Rutas publicas:    Accesibles para cualquier visitante del sitio.
 *   2. Rutas API publica: Endpoints JSON sin autenticacion (con throttle).
 *   3. Rutas protegidas:  Incluidas via require (auth, security, admin).
 *
 * @package App\Providers
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rutas principales de la aplicacion web. Las rutas publicas se definen
| directamente en este archivo, mientras que las rutas protegidas se
| cargan desde sus archivos correspondientes.
|
*/

/**
 * -----------------------------------------------------------------------
 * RUTA PRINCIPAL
 * -----------------------------------------------------------------------
 * Pagina de inicio del sitio web inmobiliario. Es la primera vista que
 * muestra el sistema al visitante. No requiere autenticacion.
 */
// RUTA PRINCIPAL
Route::get('/', [HomeController::class, 'index'])->name('home');

/**
 * -----------------------------------------------------------------------
 * RUTAS PUBLICAS ADICIONALES (CATALOGO DE PROPIEDADES)
 * -----------------------------------------------------------------------
 * Rutas de catalogo y consulta de propiedades inmobiliarias. Estas rutas
 * son publicas y no requieren autenticacion. Cualquier visitante puede
 * navegar el catalogo de propiedades disponibles.
 *
 * Rutas:
 *   GET /catalogo              - Catalogo completo de propiedades.
 *   GET /catalogo/{property}   - Detalle de una propiedad especifica.
 *   GET /propiedad/ver/{property} - Variante de vista de propiedad (alias).
 */
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

/**
 * -----------------------------------------------------------------------
 * API PUBLICA (SIN AUTENTICACION)
 * -----------------------------------------------------------------------
 * Endpoints API que devuelven datos en formato JSON para ser consumidos
 * desde formularios publicos (registro, busqueda de ubicaciones, codigos
 * de telefono, etc.).
 *
 * Throttle: 60 peticiones por minuto por IP (proteccion contra abuso).
 *
 * Submodulos:
 *   - Ubicaciones:  Devuelve la jerarquia geografica (paises, estados,
 *                    municipios, parroquias, ciudades) para el catalogo.
 *   - Registro:     Devuelve las ubicaciones disponibles en el formulario
 *                    de registro de usuarios.
 *   - Telefonos:    Devuelve presets, codigos de telephone y configuracion
 *                    por pais para el formulario de registro.
 */
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

/**
 * -----------------------------------------------------------------------
 * ENDPOINT DE RENOVACION DEL TOKEN CSRF
 * -----------------------------------------------------------------------
 * Permite a las peticiones AJAX renovar el token CSRF de la sesion.
 * Esto es util cuando el token expira o la sesion se ha regenerado.
 * Solo responde a peticiones XMLHttpRequest/fetch (verifica cabecera X-Requested-With).
 */
Route::get('/refresh-csrf', function () {
    if (request()->ajax()) {
        session()->regenerateToken();
        return response()->json(['csrf_token' => csrf_token()]);
    }
    return response()->json(['error' => 'Invalid request'], 400);
})->name('refresh.csrf');

/**
 * -----------------------------------------------------------------------
 * RUTAS DE AUTENTICACION
 * -----------------------------------------------------------------------
 * Incluye las rutas de inicio de cierre de sesion, restablecimiento de
 * contrasena y verificacion de correo electronico.
 * Archivo: routes/auth.php
 */
// =============================================
//  RUTAS DE AUTENTICACIÓN
// =============================================
require __DIR__.'/auth.php';

/**
 * -----------------------------------------------------------------------
 * RUTAS DE SEGURIDAD
 * -----------------------------------------------------------------------
 * Incluye las rutas relacionadas con la seguridad de la aplicacion,
 * como verificacion de dos factores, confirmacion de contrasena, etc.
 * Archivo: routes/security.php
 */
// =============================================
//  RUTAS DE SEGURIDAD
// =============================================
require __DIR__.'/security.php';

/**
 * -----------------------------------------------------------------------
 * RUTAS ADMIN (PROTEGIDAS)
 * -----------------------------------------------------------------------
 * Incluye todas las rutas administrativas del sistema, que requieren
 * autenticacion y cuenta activa. Incluye dashboards por rol, CRUD de
 * entidades, configuracion, chat, auditoria y reportes.
 * Archivo: routes/admin.php
 */
// =============================================
//  RUTAS ADMIN (PROTEGIDAS)
// =============================================
require __DIR__.'/admin.php';
