<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SecurityQuestionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Api\ApiLocationController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\Admin\PhoneController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Admin\SiteConfigController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\Api\AppointmentSettingController;
use App\Http\Controllers\Admin\ServiceController;

// =====================================================
// PÁGINA PRINCIPAL
// =====================================================
Route::get('/', [HomeController::class, 'index'])->name('home');

// =====================================================
// CATÁLOGO PÚBLICO (SIN AUTENTICACIÓN)
// =====================================================
Route::get('/catalogo', [PropertyController::class, 'catalog'])->name('catalogo.index');
Route::get('/propiedad/{property}', [PropertyController::class, 'showPublic'])->name('catalogo.show');
Route::get('/propiedad/ver/{id}', [PropertyController::class, 'showById'])->name('propiedad.ver');
Route::get('/api/properties/count', [PropertyController::class, 'countProperties']);

// =====================================================
// RUTAS PÚBLICAS PARA SERVICIOS
// =====================================================
Route::get('/servicios', [ServiceController::class, 'publicIndex'])->name('servicios.public');

// =====================================================
// RUTAS PÚBLICAS PARA CITAS
// =====================================================
Route::get('/citas/create', [AppointmentController::class, 'createPublic'])->name('citas.create');

// =====================================================
// RUTA PÚBLICA PARA LEADS - Formulario de Valoración
// =====================================================
Route::post('/solicitar-valoracion', [LeadController::class, 'storePublic'])->name('lead.store.public');

// =====================================================
// RUTAS DE AUTENTICACIÓN (GUEST) CON RATE LIMITING
// =====================================================
Route::middleware('guest')->group(function () {
    // Login - 10 intentos por minuto
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:10,1');
    
    // ✅ REGISTER CORREGIDO - 5 intentos por minuto
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle:5,1');
    
    // Recuperación de contraseña - 3 intentos cada 5 minutos
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
    Route::get('/account/inactive', [LoginController::class, 'showInactiveAccount'])->name('account.inactive');
    Route::post('/account/reactivation/request', [LoginController::class, 'requestReactivation'])
        ->name('account.reactivation.request')
        ->middleware('throttle:3,5');
    Route::get('/account/reactivate', [LoginController::class, 'reactivateAccount'])->name('account.reactivate');
});

// Logout - SIN RATE LIMITING
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// =====================================================
// RUTAS DE SEGURIDAD (PÚBLICAS) CON RATE LIMITING
// =====================================================

// Recuperación con preguntas de seguridad
Route::get('/recuperar-preguntas', [SecurityQuestionsController::class, 'showRecoveryForm'])
    ->name('security.recovery.form');
Route::post('/recuperar-verificar-email', [SecurityQuestionsController::class, 'verifyEmail'])
    ->name('security.verify.email')
    ->middleware('throttle:3,5');
Route::get('/preguntas-seguridad', [SecurityQuestionsController::class, 'showQuestions'])
    ->name('security.questions.show');
Route::post('/verificar-respuestas', [SecurityQuestionsController::class, 'verifyAnswers'])
    ->name('security.verify.answers')
    ->middleware('throttle:5,1');

// Reactivación con preguntas de seguridad
Route::get('/reactivar-cuenta', [SecurityQuestionsController::class, 'showReactivationForm'])
    ->name('security.reactivation.form');
Route::post('/reactivar-verificar-email', [SecurityQuestionsController::class, 'reactivateAccount'])
    ->name('security.reactivation.verify.email')
    ->middleware('throttle:3,5');
Route::get('/reactivar-preguntas', [SecurityQuestionsController::class, 'showReactivationQuestions'])
    ->name('security.reactivation.questions');
Route::post('/reactivar-verificar-respuestas', [SecurityQuestionsController::class, 'verifyReactivationAnswers'])
    ->name('security.reactivation.verify.answers')
    ->middleware('throttle:5,1');

// =====================================================
// API PÚBLICA - SIN AUTENTICACIÓN (CON CACHÉ)
// =====================================================

// API de Ubicaciones - CON CACHÉ IMPLÍCITO
Route::prefix('api/locations')->name('api.locations.')->group(function () {
    Route::get('/countries', [ApiLocationController::class, 'getCountries']);
    Route::get('/states/{countryId}', [ApiLocationController::class, 'getStates']);
    Route::get('/municipalities/{stateId}', [ApiLocationController::class, 'getMunicipalities']);
    Route::get('/parishes/{municipalityId}', [ApiLocationController::class, 'getParishes']);
    Route::get('/cities/{parishId}', [ApiLocationController::class, 'getCities']);
})->middleware('throttle:60,1'); // 60 peticiones por minuto

// API de Registro
Route::prefix('api/register')->group(function () {
    Route::get('/countries', [LocationController::class, 'getCountriesForRegister']);
    Route::get('/states/{countryId}', [LocationController::class, 'getStatesForRegister']);
    Route::get('/municipalities/{stateId}', [LocationController::class, 'getMunicipalitiesForRegister']);
    Route::get('/parishes/{municipalityId}', [LocationController::class, 'getParishesForRegister']);
    Route::get('/cities/{parishId}', [LocationController::class, 'getCitiesForRegister']);
})->middleware('throttle:60,1');

// API de Teléfonos
Route::get('/api/phone-presets', [PhoneController::class, 'getPresets'])->name('api.phone-presets');
Route::get('/api/phone-codes', [PhoneController::class, 'getPhoneCodes'])->name('api.phone-codes');
Route::get('/api/phone-config/{countryId}', [PhoneController::class, 'getPhoneConfig'])->name('api.phone-config');

// =====================================================
// RUTAS PROTEGIDAS (AUTH)
// =====================================================
Route::middleware(['auth', 'check.account.active'])->group(function () {

    // =====================================================
    // API PARA OBTENER SLOTS DISPONIBLES (USANDO API CONTROLLER)
    // =====================================================
    Route::prefix('api/appointments')->name('api.appointments.')->group(function () {
        Route::get('/available-slots', [AppointmentSettingController::class, 'getAvailableSlots'])
            ->name('slots')
            ->middleware('throttle:60,1');

        Route::get('/available-days', [AppointmentSettingController::class, 'getAvailableDays'])
            ->name('days')
            ->middleware('throttle:60,1');
    });

    // Dashboard General
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =====================================================
    // MÓDULO DE FAVORITOS (TODOS LOS ROLES)
    // =====================================================
    Route::prefix('favorites')->name('favorites.')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::post('/{propertyId}', [FavoriteController::class, 'store'])->name('store');
        Route::delete('/{propertyId}', [FavoriteController::class, 'destroy'])->name('destroy');
        Route::get('/{propertyId}/check', [FavoriteController::class, 'check'])->name('check');
        Route::get('/count/total', [FavoriteController::class, 'count'])->name('count');
        Route::delete('/clear-all', [FavoriteController::class, 'clearAll'])->name('clear-all');
    });

    // =====================================================
    // MÓDULO DE LEADS
    // =====================================================
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
        Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
        Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
        Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
        Route::post('/{lead}/change-status', [LeadController::class, 'changeStatus'])->name('change-status');
        Route::get('/api/data', [LeadController::class, 'getLeadsData'])->name('api.data');
    });

    // =====================================================
    // MÓDULO DE NOTIFICACIONES
    // =====================================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unreadCount');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-read', [NotificationController::class, 'clearRead'])->name('clearRead');
    });

    // =====================================================
    // MÓDULO DE CITAS
    // =====================================================
    Route::prefix('citas')->name('citas.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::post('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('update-status');
        Route::post('/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('reschedule');

        // Configuración (solo admin)
        Route::get('/configuracion', [AppointmentSettingController::class, 'index'])->name('configuracion');
        Route::post('/configuracion', [AppointmentSettingController::class, 'update'])->name('configuracion.update');
        Route::post('/exceptions', [AppointmentSettingController::class, 'addException'])->name('exceptions.add');
        Route::delete('/exceptions', [AppointmentSettingController::class, 'removeException'])->name('exceptions.remove');
    });

    // =====================================================
    // MÓDULO DE CHAT
    // =====================================================
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/messages/{conversationId}', [ChatController::class, 'getMessages'])->name('messages');
        Route::post('/start', [ChatController::class, 'startConversation'])->name('start');
        Route::post('/start-cliente', [ChatController::class, 'startConversationWithCliente'])->name('start-cliente');
        Route::post('/send/{conversationId}', [ChatController::class, 'sendMessage'])->name('send');
        Route::post('/read/{conversationId}', [ChatController::class, 'markAsRead'])->name('read');
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/conversations', [ChatController::class, 'getConversations'])->name('conversations');
        Route::get('/asesores/disponibles', [ChatController::class, 'getAvailableAsesores'])->name('asesores');
        Route::post('/presence', [ChatController::class, 'updatePresence'])->name('presence');
        Route::post('/typing/{conversationId}', [ChatController::class, 'setTypingStatus'])->name('typing.set');
        Route::delete('/conversation/{conversationId}', [ChatController::class, 'deleteConversation'])->name('delete-conversation');
        Route::delete('/message/{messageId}', [ChatController::class, 'deleteMessage'])->name('delete-message');
        Route::put('/message/{messageId}', [ChatController::class, 'editMessage'])->name('edit-message');
    });

    // =====================================================
    // PERFIL DE USUARIO
    // =====================================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/update', [ProfileController::class, 'update'])->name('update.post');
    });

    // =====================================================
    // RUTAS POR ROL
    // =====================================================

    // Cliente
    Route::middleware(['role:Cliente'])->prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'clienteDashboard'])->name('dashboard');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    // Asesor Inmobiliario
    Route::middleware(['role:Asesor Inmobiliario'])->prefix('asesor')->name('asesor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'asesorDashboard'])->name('dashboard');
        Route::resource('properties', PropertyController::class);
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
        Route::get('/leads', [LeadController::class, 'index'])->name('leads');
    });

    // =====================================================
    // AUDITOR - RUTAS
    // =====================================================
    Route::middleware(['role:Auditor'])->prefix('auditor')->name('auditor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'auditorDashboard'])->name('dashboard');
        Route::get('/logs', [AuditLogController::class, 'index'])->name('logs');
        Route::get('/logs/{id}', [AuditLogController::class, 'show'])->name('logs.show');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    // Super Admin
    Route::middleware(['role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'superAdminDashboard'])->name('dashboard');
        Route::resource('roles', RoleController::class);
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    // =====================================================
    // ADMIN
    // =====================================================
    Route::middleware(['role:Super Admin|Administrador'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Usuarios
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/users/{user}/modal-show', [UserController::class, 'modalShow'])->name('users.modal-show');
        Route::get('/users/{user}/modal-edit', [UserController::class, 'modalEdit'])->name('users.modal-edit');
        Route::get('/users/{user}/modal-delete', [UserController::class, 'modalDelete'])->name('users.modal-delete');

        // Propiedades
        Route::resource('properties', PropertyController::class);

        // Ubicaciones
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/', [LocationController::class, 'indexCountries'])->name('index');
            Route::post('/country', [LocationController::class, 'storeCountry'])->name('country.store');
            Route::delete('/country/{country}', [LocationController::class, 'destroyCountry'])->name('country.destroy');
            Route::get('/states/{countryId}', [LocationController::class, 'indexStates'])->name('states.index');
            Route::post('/state', [LocationController::class, 'storeState'])->name('state.store');
            Route::delete('/state/{state}', [LocationController::class, 'destroyState'])->name('state.destroy');
            Route::get('/municipalities/{stateId}', [LocationController::class, 'indexMunicipalities'])->name('municipalities.index');
            Route::post('/municipality', [LocationController::class, 'storeMunicipality'])->name('municipality.store');
            Route::delete('/municipality/{municipality}', [LocationController::class, 'destroyMunicipality'])->name('municipality.destroy');
            Route::get('/parishes/{municipalityId}', [LocationController::class, 'indexParishes'])->name('parishes.index');
            Route::post('/parish', [LocationController::class, 'storeParish'])->name('parish.store');
            Route::delete('/parish/{parish}', [LocationController::class, 'destroyParish'])->name('parish.destroy');
            Route::get('/cities/{parishId}', [LocationController::class, 'indexCities'])->name('cities.index');
            Route::post('/city', [LocationController::class, 'storeCity'])->name('city.store');
            Route::delete('/city/{city}', [LocationController::class, 'destroyCity'])->name('city.destroy');
        });

        // Teléfonos
        Route::prefix('phones')->name('phones.')->group(function () {
            Route::get('/', [PhoneController::class, 'index'])->name('index');
            Route::get('/{country}/edit', [PhoneController::class, 'edit'])->name('edit');
            Route::put('/{country}', [PhoneController::class, 'update'])->name('update');
            Route::post('/bulk-update', [PhoneController::class, 'bulkUpdate'])->name('bulk-update');
        });

        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');

        // Configuración del sitio
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('/', [SiteConfigController::class, 'index'])->name('index');
            Route::put('/', [SiteConfigController::class, 'update'])->name('update');
            Route::get('/reset', [SiteConfigController::class, 'reset'])->name('reset');
            Route::delete('/delete-image/{index}', [SiteConfigController::class, 'deleteImage'])->name('delete-image');
        });

        // Servicios
        Route::prefix('servicios')->name('servicios.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
            Route::get('/create', [ServiceController::class, 'create'])->name('create');
            Route::post('/', [ServiceController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ServiceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ServiceController::class, 'update'])->name('update');
            Route::delete('/{id}', [ServiceController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [ServiceController::class, 'reorder'])->name('reorder');
        });
    });

    // =====================================================
    // MÓDULO DE AUDITORÍA
    // =====================================================
    Route::middleware(['permission:ver logs de auditoria'])->prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/users', [AuditLogController::class, 'userLogs'])->name('user-logs');
        Route::get('/properties', [AuditLogController::class, 'propertyLogs'])->name('property-logs');
        Route::get('/appointments', [AuditLogController::class, 'appointmentLogs'])->name('appointment-logs');
        Route::get('/leads', [AuditLogController::class, 'leadLogs'])->name('lead-logs');
        Route::get('/system', [AuditLogController::class, 'systemLogs'])->name('system-logs');
        Route::get('/search-users', [AuditLogController::class, 'searchUsers'])->name('search-users');
        Route::get('/search-properties', [AuditLogController::class, 'searchProperties'])->name('search-properties');
        Route::get('/reports', [AuditLogController::class, 'reports'])->name('reports');
        Route::get('/reports/data', [AuditLogController::class, 'getReportData'])->name('reports.data');
        Route::get('/export/csv', [AuditLogController::class, 'export'])->name('export');
        Route::get('/api/dashboard-data', [AuditLogController::class, 'getDashboardData'])->name('api.data');
        Route::get('/{id}', [AuditLogController::class, 'show'])->name('show');
    });

    // =====================================================
    // MÓDULO DE REPORTES GERENCIALES
    // =====================================================
    Route::middleware(['permission:ver reportes'])->prefix('reportes')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/datos', [ReportController::class, 'getData'])->name('data');
    });
});