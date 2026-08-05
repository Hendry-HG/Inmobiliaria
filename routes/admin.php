<?php

/**
 * Archivo de rutas administrativas del sistema inmobiliario.
 *
 * Este archivo define todas las rutas protegidas del panel de administracion.
 * Todas las rutas requieren autenticacion y que la cuenta del usuario este activa.
 *
 * Estructura general de middleware aplicado:
 *   - 'auth'                     : Verifica que el usuario este autenticado.
 *   - 'check.account.active'     : Verifica que la cuenta del usuario no este desactivada.
 *   - 'permission:<nombre>'      : Verifica que el usuario posea un permiso especifico del sistema de roles y permisos.
 *   - 'throttle:<limitas>,<minutos>' : Limita la cantidad de peticiones en un periodo de tiempo.
 *
 * Modulos incluidos:
 *   - API de citas (disponibilidad de slots y dias)
 *   - Dashboard general
 *   - Favoritos
 *   - Leads
 *   - Notificaciones
 *   - Citas (CRUD y configuracion)
 *   - Chat en tiempo real
 *   - Perfil de usuario
 *   - Dashboards por rol (cliente, asesor, auditor)
 *   - Super Administrador (roles y permisos del sidebar)
 *   - Administrador (usuarios, categorias, propiedades, ubicaciones, telefonos, servicios, configuracion del sitio)
 *   - Auditoria de registros
 *   - Reportes gerenciales
 *
 * @package App\Providers
 */

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
| Rutas Protegidas (Requieren Autenticacion)
|--------------------------------------------------------------------------
|
| Todas las rutas dentro de este grupo requieren que el usuario este
| autenticado (middleware 'auth') y que su cuenta este activa
| (middleware 'check.account.active').
|
*/

Route::middleware(['auth', 'check.account.active'])->group(function () {

    /**
     * -----------------------------------------------------------------------
     * API DE APPOINTMENTS (AUTENTICADA)
     * -----------------------------------------------------------------------
     * Endpoints API que devuelven la disponibilidad de citas para el usuario
     * autenticado. Se aplican las siguientes restricciones:
     *   - Prefijo:       /api/appointments
     *   - Throttle:      60 peticiones por minuto (proteccion contra abuso).
     *   - Middleware:     auth (solo usuarios autenticados).
     *
     * Rutas:
     *   GET /available-slots  - Devuelve los horarios disponibles para una fecha.
     *   GET /available-days   - Devuelve los dias disponibles en el calendario.
     */
    Route::prefix('api/appointments')->name('api.appointments.')->middleware('throttle:60,1')->group(function () {
        Route::get('/available-slots', [AppointmentSettingController::class, 'getAvailableSlots'])->name('slots');
        Route::get('/available-days', [AppointmentSettingController::class, 'getAvailableDays'])->name('days');
    });

    /**
     * -----------------------------------------------------------------------
     * DASHBOARD GENERAL
     * -----------------------------------------------------------------------
     * Rutas que muestran los paneles principales del sistema.
     * Redirigen al dashboard apropiado segun el rol del usuario.
     *
     * Rutas:
     *   GET /dashboard         - Panel principal, redirige al dashboard del rol.
     *   GET /dashboard/generic - Dashboard generico sin personalizacion por rol.
     */
    // =============================================
    //  DASHBOARD GENERAL
    // =============================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/generic', [DashboardController::class, 'genericDashboard'])->name('dashboard.generic');

    /**
     * -----------------------------------------------------------------------
     * MODULO DE FAVORITOS
     * -----------------------------------------------------------------------
     * Permite a los usuarios autenticados gestionar sus propiedades favoritas.
     * No requiere permisos especificos adicionales, cualquier usuario
     * autenticado puede agregar, eliminar y consultar sus favoritos.
     *
     * Prefijo: /favorites | Nombre base: favorites.
     *
     * Rutas:
     *   GET    /                    - Lista de propiedades favoritas del usuario.
     *   POST   /{propertyId}        - Agrega una propiedad a favoritos.
     *   DELETE /{propertyId}        - Elimina una propiedad de favoritos.
     *   GET    /{propertyId}/check  - Verifica si una propiedad es favorita.
     *   GET    /count/total         - Devuelve el total de favoritos del usuario.
     *   DELETE /clear-all           - Elimina todos los favoritos del usuario.
     */
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

    /**
     * -----------------------------------------------------------------------
     * MODULO DE LEADS
     * -----------------------------------------------------------------------
     * Gestiona los leads (prospectos) captados por el sistema inmobiliario.
     * Cada ruta requiere un permiso especifico para asegurar el control de
     * acceso granular:
     *   - 'ver leads'             : Consultar listado y detalle de leads.
     *   - 'editar lead'           : Modificar datos de un lead existente.
     *   - 'eliminar lead'         : Eliminar un lead del sistema.
     *   - 'cambiar estado lead'   : Actualizar el estado del ciclo de vida del lead.
     *
     * Prefijo: /leads | Nombre base: leads.
     */
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

    /**
     * -----------------------------------------------------------------------
     * MODULO DE NOTIFICACIONES
     * -----------------------------------------------------------------------
     * Administra las notificaciones internas del sistema para el usuario
     * autenticado. No requiere permisos adicionales especificos, ya que
     * cada usuario solo puede acceder a sus propias notificaciones.
     *
     * Prefijo: /notifications | Nombre base: notifications.
     *
     * Rutas:
     *   GET    /                  - Lista de notificaciones del usuario.
     *   POST   /{id}/mark-as-read - Marca una notificacion como leida.
     *   POST   /mark-all-as-read  - Marca todas las notificaciones como leidas.
     *   GET    /unread-count      - Devuelve la cantidad de notificaciones sin leer.
     *   DELETE /{id}              - Elimina una notificacion especifica.
     *   DELETE /clear-read        - Elimina todas las notificaciones leidas.
     */
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

    /**
     * -----------------------------------------------------------------------
     * MODULO DE CITAS
     * -----------------------------------------------------------------------
     * Gestiona las citas inmobiliarias y su configuracion. Incluye el CRUD
     * de citas, el reprogramado, y la administracion de la configuracion
     * de disponibilidad (dias, horarios y excepciones).
     *
     * Prefijo: /citas | Nombre base: citas.
     *
     * Permisos requeridos por ruta:
     *   - 'ver citas'             : Listar y consultar citas.
     *   - 'crear cita'            : Registrar una nueva cita.
     *   - 'editar cita'           : Modificar estado o reprogramar citas.
     *   - 'ver configuracion'     : Consultar la configuracion de disponibilidad.
     *   - 'editar configuracion'  : Modificar horarios, dias y excepciones.
     */
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

    /**
     * -----------------------------------------------------------------------
     * MODULO DE CHAT EN TIEMPO REAL
     * -----------------------------------------------------------------------
     * Proporciona funcionalidad de mensajeria en tiempo real entre usuarios
     * del sistema (asesores, clientes, etc.). Utiliza canales de broadcast
     * para la entrega de mensajes instantaneos.
     *
     * Prefijo: /chat | Nombre base: chat.
     * Middleware adicional: 'permission:chat access' (solo usuarios con
     *   acceso al chat pueden usar este modulo).
     *
     * Funcionalidades:
     *   - Iniciar conversaciones (entre asesores o con clientes).
     *   - Enviar y recibir mensajes en tiempo real.
     *   - Marcar mensajes como leidos.
     *   - Gestionar presencia de usuarios (en linea / fuera de linea).
     *   - Indicadores de escritura (typing indicators).
     *   - Eliminar conversaciones y mensajes individuales.
     *   - Editar mensajes enviados.
     */
    // =============================================
    //  MÓDULO DE CHAT
    // =============================================
    Route::prefix('chat')->name('chat.')->middleware('permission:chat access')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/messages/{conversationId}', [ChatController::class, 'getMessages'])->name('messages');
        Route::post('/start', [ChatController::class, 'startConversation'])->name('start');
        Route::post('/start-cliente', [ChatController::class, 'startConversationWithCliente'])->name('start-cliente');
        Route::post('/start-staff', [ChatController::class, 'startConversationInternal'])->name('start-staff');
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

    /**
     * -----------------------------------------------------------------------
     * PERFIL DE USUARIO
     * -----------------------------------------------------------------------
     * Permite a los usuarios autenticados consultar y actualizar su perfil.
     * No requiere permisos adicionales especificos, ya que cada usuario
     * gestiona su propia informacion personal.
     *
     * Prefijo: /profile | Nombre base: profile.
     *
     * Rutas:
     *   GET  /         - Muestra el formulario/datos del perfil.
     *   PUT  /update   - Actualiza la informacion del perfil.
     *   POST /update   - Alternativa POST para actualizacion del perfil.
     */
    // =============================================
    //  PERFIL DE USUARIO
    // =============================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/update', [ProfileController::class, 'update'])->name('update.post');
    });

    /**
     * -----------------------------------------------------------------------
     * DASHBOARDS POR ROL
     * -----------------------------------------------------------------------
     * Cada tipo de usuario tiene un dashboard personalizado que muestra
     * informacion relevante a su rol dentro del sistema inmobiliario.
     * Las rutas dentro de cada grupo de rol estan disponibles para todos
     * los usuarios autenticados (la autorizacion a nivel de vista se
     * controla desde los controladores o middleware adicionales).
     *
     * Roles soportados:
     *   - Cliente:         Dashboard con favoritos y propiedades de interes.
     *   - Asesor:          Dashboard con propiedades asignadas, leads y favoritos.
     *   - Auditor:         Dashboard con logs de auditoria y favoritos.
     */

    // --- Cliente ---
    Route::prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'clienteDashboard'])->name('dashboard');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    // --- Asesor Inmobiliario ---
    Route::prefix('asesor')->name('asesor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'asesorDashboard'])->name('dashboard');
        Route::resource('properties', PropertyController::class);
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
        Route::get('/leads', [LeadController::class, 'index'])->name('leads');
    });

    // --- Auditor ---
    Route::prefix('auditor')->name('auditor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'auditorDashboard'])->name('dashboard');
        Route::get('/logs', [AuditLogController::class, 'index'])->name('logs');
        Route::get('/logs/{id}', [AuditLogController::class, 'show'])->name('logs.show');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    });

    /**
     * -----------------------------------------------------------------------
     * SUPER ADMINISTRADOR
     * -----------------------------------------------------------------------
     * Panel de alto nivel reservado para el rol de Super Administrador.
     * Permite gestionar roles del sistema y configurar los permisos de
     * visualizacion del sidebar para cada rol.
     *
     * Prefijo: /super-admin | Nombre base: super-admin.
     *
     * Funcionalidades:
     *   - Dashboard exclusivo del super administrador.
     *   - CRUD completo de roles del sistema.
     *   - Gestion de permisos del sidebar por rol (que menus ve cada rol).
     *   - Acceso a favoritos.
     */
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

    /**
     * -----------------------------------------------------------------------
     * ADMINISTRADOR
     * -----------------------------------------------------------------------
     * Panel de administracion principal del sistema inmobiliario. Contiene
     * todos los modulos de gestion operativa con control de acceso granular
     * mediante permisos especificos para cada operacion CRUD.
     *
     * Prefijo: /admin | Nombre base: admin.
     *
     * Submodulos incluidos:
     *   - Usuarios:          CRUD completo con permisos por accion (ver, crear,
     *                         editar, eliminar). Incluye modal para vista
     *                         rapida, edicion y confirmacion de eliminacion.
     *   - Categorias:        CRUD de categorias de propiedades con toggle de
     *                         estado (activar/desactivar).
     *   - Propiedades:       Gestion completa de propiedades inmobiliarias
     *                         mediante resource routes.
     *   - Ubicaciones:       Jerarquia geografica completa (Paises -> Estados ->
     *                         Municipios -> Parroquias -> Ciudades). Cada nivel
     *                         tiene permisos de ver, crear y eliminar.
     *   - Telefonos:         Configuracion de codigos de telephone por pais,
     *                         con soporte para actualizacion masiva.
     *   - Favoritos:         Acceso a la lista de propiedades favoritas.
     *   - Configuracion:     Ajustes generales del sitio, incluyendo imagenes,
     *                         reset de configuracion y eliminacion de imagenes.
     *   - Servicios:         CRUD de servicios inmobiliarios con soporte para
     *                         reordenamiento (drag and drop).
     */
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
            Route::get('/check-field', [UserController::class, 'checkUnique'])->name('check-field')->middleware('permission:ver usuarios');
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
            Route::post('/reset', [SiteConfigController::class, 'reset'])->name('reset')->middleware('permission:editar configuración');
            Route::delete('/delete-image', [SiteConfigController::class, 'deleteImage'])->name('delete-image')->middleware('permission:editar configuración');
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

    /**
     * -----------------------------------------------------------------------
     * MODULO DE AUDITORIA DE REGISTROS
     * -----------------------------------------------------------------------
     * Permite consultar, filtrar y exportar los logs de auditoria del
     * sistema. Registra todas las acciones realizadas por los usuarios.
     *
     * Prefijo: /audit-logs | Nombre base: audit-logs.
     * Middleware: 'permission:ver logs de auditoria' (solo usuarios con
     *   permiso de consulta de auditoria).
     *
     * Funcionalidades:
     *   - Listado general de logs de auditoria.
     *   - Filtros por modulo: usuarios, propiedades, citas, leads, sistema.
     *   - Busqueda de usuarios y propiedades para filtrado.
     *   - Reportes con datos agregados.
     *   - Exportacion en multiples formatos: CSV, Excel y PDF.
     *   - API de datos para dashboards de auditoria.
     *   - Detalle individual de un registro de auditoria.
     */
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
        Route::get('/export/excel', [AuditLogController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [AuditLogController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/api/dashboard-data', [AuditLogController::class, 'getDashboardData'])->name('api.data');
        Route::get('/{id}', [AuditLogController::class, 'show'])->name('show');
    });

    /**
     * -----------------------------------------------------------------------
     * MODULO DE REPORTES GERENCIALES
     * -----------------------------------------------------------------------
     * Genera reportes consolidados del rendimiento del sistema inmobiliario
     * para toma de decisiones gerenciales.
     *
     * Prefijo: /reportes | Nombre base: reports.
     * Middleware: 'permission:ver reportes' (solo usuarios autorizados
     *   para consultar reportes gerenciales).
     *
     * Rutas:
     *   GET /          - Panel principal de reportes.
     *   GET /datos     - Devuelve los datos del reporte en formato JSON.
     *   GET /exportar  - Exporta el reporte a un archivo descargable.
     */
    // =============================================
    //  MÓDULO DE REPORTES GERENCIALES
    // =============================================
    Route::prefix('reportes')->name('reports.')->middleware('permission:ver reportes')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/datos', [ReportController::class, 'getData'])->name('data');
        Route::get('/exportar', [ReportController::class, 'export'])->name('export');
    });
});
