<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SidebarPermissionController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PhoneController;
use App\Http\Controllers\Admin\SiteConfigController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\Api\AppointmentSettingController;

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Autenticación)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.account.active'])->group(function () {

    // =============================================
    // API DE APPOINTMENTS (AUTH)
    // =============================================
    Route::prefix('api/appointments')->name('api.appointments.')->middleware('throttle:60,1')->group(function () {
        Route::get('/available-slots', [AppointmentSettingController::class, 'getAvailableSlots'])->name('slots');
        Route::get('/available-days', [AppointmentSettingController::class, 'getAvailableDays'])->name('days');
    });

    // =============================================
    //  DASHBOARD GENERAL
    // =============================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/generic', [DashboardController::class, 'genericDashboard'])->name('dashboard.generic');

    // =============================================
    //  MÓDULO DE FAVORITOS
    // =============================================
    Route::prefix('favorites')->name('favorites.')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::post('/{propertyId}', [FavoriteController::class, 'store'])->name('store');
        Route::delete('/{propertyId}', [FavoriteController::class, 'destroy'])->name('destroy');
        Route::get('/{propertyId}/check', [FavoriteController::class, 'check'])->name('check');
        Route::get('/count/total', [FavoriteController::class, 'count'])->name('count');
        Route::delete('/clear-all', [FavoriteController::class, 'clearAll'])->name('clear-all');
    });

    // =============================================
    //  MÓDULO DE LEADS
    // =============================================
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])
            ->name('index')
            ->middleware('permission:ver leads');

        Route::get('/{lead}', [LeadController::class, 'show'])
            ->name('show')
            ->middleware('permission:ver leads');

        Route::get('/{lead}/edit', [LeadController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:editar lead');

        Route::put('/{lead}', [LeadController::class, 'update'])
            ->name('update')
            ->middleware('permission:editar lead');

        Route::delete('/{lead}', [LeadController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:eliminar lead');

        Route::post('/{lead}/change-status', [LeadController::class, 'changeStatus'])
            ->name('change-status')
            ->middleware('permission:cambiar estado lead');

        Route::get('/api/data', [LeadController::class, 'getLeadsData'])
            ->name('api.data')
            ->middleware('permission:ver leads');
    });

    // =============================================
    //  MÓDULO DE NOTIFICACIONES
    // =============================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unreadCount');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-read', [NotificationController::class, 'clearRead'])->name('clearRead');
    });

    // =============================================
    //  MÓDULO DE CITAS
    // =============================================
    Route::prefix('citas')->name('citas.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])
            ->name('index')
            ->middleware('permission:ver citas');

        Route::post('/', [AppointmentController::class, 'store'])
            ->name('store')
            ->middleware('permission:crear cita');

        Route::post('/{appointment}/status', [AppointmentController::class, 'updateStatus'])
            ->name('update-status')
            ->middleware('permission:editar cita');

        Route::post('/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])
            ->name('reschedule')
            ->middleware('permission:editar cita');

        Route::get('/configuracion', [AppointmentSettingController::class, 'index'])
            ->name('configuracion')
            ->middleware('permission:ver configuración');

        Route::post('/configuracion', [AppointmentSettingController::class, 'update'])
            ->name('configuracion.update')
            ->middleware('permission:editar configuración');

        Route::post('/exceptions', [AppointmentSettingController::class, 'addException'])
            ->name('exceptions.add')
            ->middleware('permission:editar configuración');

        Route::delete('/exceptions', [AppointmentSettingController::class, 'removeException'])
            ->name('exceptions.remove')
            ->middleware('permission:editar configuración');
    });

    // =============================================
    //  MÓDULO DE CHAT
    // =============================================
    Route::prefix('chat')->name('chat.')->middleware('permission:chat access')->group(function () {
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

    // =============================================
    //  PERFIL DE USUARIO
    // =============================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/update', [ProfileController::class, 'update'])->name('update.post');
    });

    // =============================================
    //  DASHBOARDS POR ROL
    // =============================================

    // Cliente
    Route::prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'clienteDashboard'])->name('dashboard');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    // Asesor Inmobiliario
    Route::prefix('asesor')->name('asesor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'asesorDashboard'])->name('dashboard');
        Route::resource('properties', PropertyController::class);
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
        Route::get('/leads', [LeadController::class, 'index'])->name('leads');
    });

    // Auditor
    Route::prefix('auditor')->name('auditor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'auditorDashboard'])->name('dashboard');
        Route::get('/logs', [AuditLogController::class, 'index'])->name('logs');
        Route::get('/logs/{id}', [AuditLogController::class, 'show'])->name('logs.show');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    // =============================================
    //  SUPER ADMIN
    // =============================================
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'superAdminDashboard'])->name('dashboard');
        Route::resource('roles', RoleController::class);

        Route::prefix('sidebar-permissions')->name('sidebar-permissions.')->group(function () {
            Route::get('/', [SidebarPermissionController::class, 'index'])->name('index');
            Route::get('/{role}/edit', [SidebarPermissionController::class, 'edit'])->name('edit');
            Route::put('/{role}', [SidebarPermissionController::class, 'update'])->name('update');
        });

        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    // =============================================
    //  ADMIN
    // =============================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // USUARIOS
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:ver usuarios');
            Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('permission:crear usuario');
            Route::post('/', [UserController::class, 'store'])->name('store')->middleware('permission:crear usuario');
            Route::get('/{user}', [UserController::class, 'show'])->name('show')->middleware('permission:ver usuarios');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('permission:editar usuario');
            Route::put('/{user}', [UserController::class, 'update'])->name('update')->middleware('permission:editar usuario');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('permission:eliminar usuario');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:editar usuario');
            Route::get('/{user}/modal-show', [UserController::class, 'modalShow'])->name('modal-show')->middleware('permission:ver usuarios');
            Route::get('/{user}/modal-edit', [UserController::class, 'modalEdit'])->name('modal-edit')->middleware('permission:editar usuario');
            Route::get('/{user}/modal-delete', [UserController::class, 'modalDelete'])->name('modal-delete')->middleware('permission:eliminar usuario');
        });

        // CATEGORÍAS
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index')->middleware('permission:ver categorias');
            Route::get('/create', [CategoryController::class, 'create'])->name('create')->middleware('permission:crear categoria');
            Route::post('/', [CategoryController::class, 'store'])->name('store')->middleware('permission:crear categoria');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit')->middleware('permission:editar categoria');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update')->middleware('permission:editar categoria');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy')->middleware('permission:eliminar categoria');
            Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:editar categoria');
        });

        // PROPIEDADES
        Route::resource('properties', PropertyController::class)->middleware('permission:ver propiedades');

        // UBICACIONES
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/', [LocationController::class, 'indexCountries'])->name('index')->middleware('permission:ver paises');
            Route::post('/country', [LocationController::class, 'storeCountry'])->name('country.store')->middleware('permission:crear paises');
            Route::delete('/country/{country}', [LocationController::class, 'destroyCountry'])->name('country.destroy')->middleware('permission:eliminar paises');
            Route::get('/states/{countryId}', [LocationController::class, 'indexStates'])->name('states.index')->middleware('permission:ver estados');
            Route::post('/state', [LocationController::class, 'storeState'])->name('state.store')->middleware('permission:crear estados');
            Route::delete('/state/{state}', [LocationController::class, 'destroyState'])->name('state.destroy')->middleware('permission:eliminar estados');
            Route::get('/municipalities/{stateId}', [LocationController::class, 'indexMunicipalities'])->name('municipalities.index')->middleware('permission:ver municipios');
            Route::post('/municipality', [LocationController::class, 'storeMunicipality'])->name('municipality.store')->middleware('permission:crear municipios');
            Route::delete('/municipality/{municipality}', [LocationController::class, 'destroyMunicipality'])->name('municipality.destroy')->middleware('permission:eliminar municipios');
            Route::get('/parishes/{municipalityId}', [LocationController::class, 'indexParishes'])->name('parishes.index')->middleware('permission:ver parroquias');
            Route::post('/parish', [LocationController::class, 'storeParish'])->name('parish.store')->middleware('permission:crear parroquias');
            Route::delete('/parish/{parish}', [LocationController::class, 'destroyParish'])->name('parish.destroy')->middleware('permission:eliminar parroquias');
            Route::get('/cities/{parishId}', [LocationController::class, 'indexCities'])->name('cities.index')->middleware('permission:ver ciudades');
            Route::post('/city', [LocationController::class, 'storeCity'])->name('city.store')->middleware('permission:crear ciudades');
            Route::delete('/city/{city}', [LocationController::class, 'destroyCity'])->name('city.destroy')->middleware('permission:eliminar ciudades');
        });

        // TELÉFONOS
        Route::prefix('phones')->name('phones.')->group(function () {
            Route::get('/', [PhoneController::class, 'index'])->name('index')->middleware('permission:ver configuración');
            Route::get('/{country}/edit', [PhoneController::class, 'edit'])->name('edit')->middleware('permission:editar configuración');
            Route::put('/{country}', [PhoneController::class, 'update'])->name('update')->middleware('permission:editar configuración');
            Route::post('/bulk-update', [PhoneController::class, 'bulkUpdate'])->name('bulk-update')->middleware('permission:editar configuración');
        });

        // FAVORITOS
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');

        // CONFIGURACIÓN DEL SITIO
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('/', [SiteConfigController::class, 'index'])->name('index')->middleware('permission:ver configuración');
            Route::put('/', [SiteConfigController::class, 'update'])->name('update')->middleware('permission:editar configuración');
            Route::get('/reset', [SiteConfigController::class, 'reset'])->name('reset')->middleware('permission:editar configuración');
            Route::delete('/delete-image/{index}', [SiteConfigController::class, 'deleteImage'])->name('delete-image')->middleware('permission:editar configuración');
        });

        // SERVICIOS
        Route::prefix('servicios')->name('servicios.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index')->middleware('permission:ver servicios');
            Route::get('/create', [ServiceController::class, 'create'])->name('create')->middleware('permission:crear servicios');
            Route::post('/', [ServiceController::class, 'store'])->name('store')->middleware('permission:crear servicios');
            Route::get('/{id}', [ServiceController::class, 'show'])->name('show')->middleware('permission:ver servicios');
            Route::get('/{id}/edit', [ServiceController::class, 'edit'])->name('edit')->middleware('permission:editar servicios');
            Route::put('/{id}', [ServiceController::class, 'update'])->name('update')->middleware('permission:editar servicios');
            Route::delete('/{id}', [ServiceController::class, 'destroy'])->name('destroy')->middleware('permission:eliminar servicios');
            Route::post('/reorder', [ServiceController::class, 'reorder'])->name('reorder')->middleware('permission:editar servicios');
        });
    });

    // =============================================
    //  MÓDULO DE AUDITORÍA
    // =============================================
    Route::prefix('audit-logs')->name('audit-logs.')->middleware('permission:ver logs de auditoria')->group(function () {
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

    // =============================================
    //  MÓDULO DE REPORTES GERENCIALES
    // =============================================
    Route::prefix('reportes')->name('reports.')->middleware('permission:ver reportes')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/datos', [ReportController::class, 'getData'])->name('data');
        Route::get('/exportar', [ReportController::class, 'export'])->name('export');
    });
});
