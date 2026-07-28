<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Property;
use App\Models\Appointment;
use App\Models\Lead;
use App\Models\SiteConfiguration;
use App\Exports\AuditLogExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditLogController extends Controller
{
    /**
     * Constructor del controlador de auditoria.
     *
     * Aplica el middleware de permisos 'ver logs de auditoria' a todos los metodos
     * de este controlador. Solo los usuarios que posean este permiso podran
     * acceder a cualquier accion de auditoria.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:ver logs de auditoria');
    }

    /**
     * Dashboard principal de auditoria.
     *
     * Muestra la vista central del sistema de auditoria con una tabla paginada
     * de todos los registros de auditoria y estadisticas generales del dashboard.
     *
     * Flujo de datos:
     * 1. Construye una consulta base sobre audit_logs con eager loading del usuario.
     * 2. Aplica filtros opcionales segun los parametros de la peticion:
     *    - user_id: filtra por un usuario especifico.
     *    - action: busca coincidencias parciales en los campos 'action' y 'event'.
     *    - entity: busca coincidencias parciales en el campo 'subject_type'.
     *    - date_from / date_to: filtra por rango de fechas en created_at.
     *    - search: busqueda libre sobre los campos 'description' e 'ip_address'.
     * 3. Pagina los resultados en 20 registros por pagina preservando los query string.
     * 4. Obtiene las listas de usuarios, acciones unicas y entidades unicas para
     *    poblar los campos de los filtros del formulario.
     * 5. Calcula las estadisticas del dashboard mediante getDashboardStats().
     * 6. Retorna la vista 'modulos.auditorias.dashboard' con todos los datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where(function($q) use ($request) {
                $q->where('action', 'like', '%' . $request->action . '%')
                  ->orWhere('event', 'like', '%' . $request->action . '%');
            });
        }

        if ($request->filled('entity')) {
            $query->where('subject_type', 'like', '%' . $request->entity . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('ip_address', 'like', '%' . $search . '%');
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Datos para filtros
        $users = User::orderBy('name')->get(['id', 'name']);
        $actions = $this->getUniqueActions();
        $entities = $this->getUniqueEntities();

        // Estadísticas del dashboard
        $stats = $this->getDashboardStats();

        return view('modulos.auditorias.dashboard', compact(
            'logs', 'users', 'actions', 'entities', 'stats'
        ));
    }

    /**
     * Vista de logs de actividad de usuarios.
     *
     * Muestra una lista de registros de auditoria filtrados exclusivamente a
     * entradas que tienen un usuario asociado (user_id no nulo). Permite filtrar
     * por usuario y rango de fechas.
     *
     * Flujo de datos:
     * 1. Construye la consulta base filtrando solo logs con user_id no nulo,
     *    ordenados de mas reciente a mas antiguo.
     * 2. Aplica filtros opcionales: user_id y rango de fechas (date_from, date_to).
     * 3. Pagina los resultados en 20 registros por pagina.
     * 4. Ejecuta una consulta SQL pura (CTE) que calcula la cantidad de auditorias
     *    de cada usuario en los ultimos 30 dias, retornando el top 10 de usuarios
     *    mas activos con su nombre, apellido y email.
     * 5. Si se proporciona un user_id en la peticion, busca el usuario completo
     *    y obtiene su historial paginado de auditorias (10 registros por pagina).
     *
     * La vista muestra tres paneles: la tabla general de logs, un ranking de
     * usuarios mas activos, y el historial detallado del usuario seleccionado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function userLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->whereNotNull('user_id')
            ->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        // Usuarios más activos
        $userActivity = DB::select("
            WITH user_audits AS (
                SELECT
                    users.id,
                    users.name,
                    users.last_name,
                    users.email,
                    (
                        SELECT COUNT(*)
                        FROM audit_logs
                        WHERE audit_logs.user_id = users.id
                        AND audit_logs.created_at >= ?
                    ) as audits_count
                FROM users
                WHERE users.deleted_at IS NULL
            )
            SELECT * FROM user_audits
            WHERE audits_count > 0
            ORDER BY audits_count DESC
            LIMIT 10
        ", [now()->subDays(30)->format('Y-m-d H:i:s')]);

        $userActivity = collect($userActivity);

        // Usuario seleccionado y su historial
        $selectedUser = null;
        $userHistory = null;

        if ($request->filled('user_id')) {
            $selectedUser = User::find($request->user_id);

            if ($selectedUser) {
                $userHistory = AuditLog::with('user')
                    ->where('user_id', $selectedUser->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->withQueryString();
            }
        }

        return view('modulos.auditorias.user-logs', compact(
            'logs', 'users', 'userActivity', 'selectedUser', 'userHistory'
        ));
    }

    /**
     * Endpoint AJAX para buscar usuarios y mostrarlos en un campo de autocompletar.
     *
     * Realiza una busqueda parcial (LIKE) sobre los campos 'name', 'last_name',
     * 'email' y la concatenacion de 'name' + 'last_name'. Retorna un maximo de
     * 20 resultados.
     *
     * Flujo de datos:
     * 1. Recibe el parametro 'q' con el texto de busqueda.
     * 2. Si la longitud del texto es menor a 2 caracteres, retorna un array vacio.
     * 3. Consulta la tabla users aplicando busqueda LIKE en los campos indicados,
     *    incluyendo un eager count de los logs de auditoria de los ultimos 30 dias
     *    para cada usuario (via relacion auditLogsAsAuthor).
     * 4. Mapea cada resultado a un array simplificado con: id, name (nombre completo),
     *    email, initials (iniciales) y audits_count.
     * 5. Retorna la respuesta como JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::where(function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$search}%"]);
        })
        ->withCount(['auditLogsAsAuthor' => function($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        }])
        ->orderBy('name')
        ->limit(20)
        ->get()
        ->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'initials' => $user->initials,
                'audits_count' => $user->audit_logs_as_author_count ?? 0,
            ];
        });

        return response()->json($users);
    }

    /**
     * Vista de logs de actividad de propiedades.
     *
     * Muestra un panel completo de auditoria enfocado en propiedades inmobiliarias,
     * incluyendo estadisticas por propiedad, ranking de propiedades con mas
     * actividad e historial detallado de una propiedad seleccionada.
     *
     * Flujo de datos:
     * 1. Obtiene todos los IDs de propiedades existentes.
     * 2. Ejecuta dos consultas agregadas sobre audit_logs filtrando por
     *    subject_type LIKE '%Property%':
     *    - Cuenta el total de eventos por cada subject_id.
     *    - Obtiene la fecha de la ultima actividad por cada subject_id.
     * 3. Consulta todas las propiedades con su imagen primaria y mapea cada una
     *    a un array con: id, title, location, price (formateado), status, views,
     *    image (URL de la imagen principal), events_count y last_activity
     *    (fecha formateada en zona horaria America/Caracas, o 'Nunca' si no hay actividad).
     * 4. Obtiene el top 10 de propiedades con mas eventos de auditoria.
     * 5. Si se proporciona un property_id, carga la propiedad completa con su imagen
     *    y obtiene su historial paginado de auditorias (10 registros por pagina).
     * 6. Obtiene la lista de acciones disponibles y la lista de propiedades publicadas
     *    para los filtros del formulario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function propertyLogs(Request $request)
    {
        // Obtener TODAS las propiedades con sus estadísticas (optimizado)
        $allPropertyIds = Property::pluck('id');
        $propertyEventCounts = AuditLog::where('subject_type', 'like', '%Property%')
            ->whereIn('subject_id', $allPropertyIds)
            ->select('subject_id', DB::raw('count(*) as total'))
            ->groupBy('subject_id')
            ->pluck('total', 'subject_id');

        $propertyLastActivities = AuditLog::where('subject_type', 'like', '%Property%')
            ->whereIn('subject_id', $allPropertyIds)
            ->select('subject_id', DB::raw('max(created_at) as last_date'))
            ->groupBy('subject_id')
            ->pluck('last_date', 'subject_id');

        $propertyStats = Property::with('primaryImage')
            ->orderBy('title')
            ->get()
            ->map(function($property) use ($propertyEventCounts, $propertyLastActivities) {
                $lastDate = $propertyLastActivities->get($property->id);

                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'location' => $property->location,
                    'price' => $property->formatted_price,
                    'status' => $property->status,
                    'views' => $property->views,
                    'image' => $property->primaryImage?->image_path
                        ? asset('storage/' . $property->primaryImage->image_path)
                        : null,
                    'events_count' => $propertyEventCounts->get($property->id, 0),
                    'last_activity' => $lastDate
                        ? \Carbon\Carbon::parse($lastDate)->setTimezone('America/Caracas')->format('d/m/Y H:i')
                        : 'Nunca',
                ];
            });

        // Top 10 propiedades con más actividad
        $topProperties = AuditLog::where('subject_type', 'like', '%Property%')
            ->whereNotNull('subject_id')
            ->select('subject_id', DB::raw('count(*) as total'))
            ->groupBy('subject_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->with('subject')
            ->get();

        // Si hay una propiedad seleccionada, obtener su historial
        $selectedProperty = null;
        $propertyHistory = null;
        $logs = collect();

        if ($request->filled('property_id')) {
            $selectedProperty = Property::with('primaryImage')->find($request->property_id);

            if ($selectedProperty) {
                $propertyHistory = AuditLog::with('user')
                    ->where('subject_type', 'like', '%Property%')
                    ->where('subject_id', $selectedProperty->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->withQueryString();
            }
        }

        // Acciones disponibles para filtrar
        $actions = AuditLog::where('subject_type', 'like', '%Property%')
            ->distinct()
            ->pluck('action');

        // Todas las propiedades para el filtro
        $properties = Property::where('status', 'publicada')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('modulos.auditorias.property-logs', compact(
            'propertyStats', 'topProperties', 'selectedProperty',
            'propertyHistory', 'actions', 'properties', 'logs'
        ));
    }

    /**
     * Vista de logs de actividad de citas.
     *
     * Muestra los registros de auditoria relacionados con la entidad Appointment.
     * Permite filtrar por ID de cita, status (buscando en el campo JSON new_values)
     * y rango de fechas.
     *
     * Flujo de datos:
     * 1. Construye la consulta base filtrando por subject_type LIKE '%Appointment%',
     *    con eager loading del usuario y orden descendente por fecha.
     * 2. Aplica filtros opcionales:
     *    - appointment_id: filtra por el subject_id especifico de la cita.
     *    - status: busca coincidencias en el campo JSON new_values con el patron
     *      '%"status":"<valor>"%' para detectar cambios de estado.
     *    - date_from / date_to: filtra por rango de fechas.
     * 3. Pagina los resultados en 20 registros por pagina.
     * 4. Calcula estadisticas generales de citas: total, pendientes, confirmadas,
     *    completadas y canceladas consultando directamente la tabla appointments.
     *
     * La vista muestra la tabla de logs filtrados junto con tarjetas de resumen
     * con los conteos por estado de cita.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function appointmentLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->where('subject_type', 'like', '%Appointment%')
            ->orderBy('created_at', 'desc');

        if ($request->filled('appointment_id')) {
            $query->where('subject_id', $request->appointment_id);
        }

        if ($request->filled('status')) {
            $query->where('new_values', 'like', '%"status":"' . $request->status . '"%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Estadísticas de citas
        $appointmentStats = [
            'total' => Appointment::count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
        ];

        return view('modulos.auditorias.appointment-logs', compact('logs', 'appointmentStats'));
    }

    /**
     * Vista de logs de actividad de leads (prospectos).
     *
     * Muestra los registros de auditoria relacionados con la entidad Lead.
     * Permite filtrar por ID de lead, status (buscando en el campo JSON new_values)
     * y rango de fechas.
     *
     * Flujo de datos:
     * 1. Construye la consulta base filtrando por subject_type LIKE '%Lead%',
     *    con eager loading del usuario y orden descendente por fecha.
     * 2. Aplica filtros opcionales:
     *    - lead_id: filtra por el subject_id especifico del lead.
     *    - status: busca coincidencias en el campo JSON new_values con el patron
     *      '%"status":"<valor>"%' para detectar cambios de estado.
     *    - date_from / date_to: filtra por rango de fechas.
     * 3. Pagina los resultados en 20 registros por pagina.
     * 4. Calcula estadisticas generales de leads: total, nuevos, contactados,
     *    calificados, cerrado_ganado y cerrado_perdido consultando la tabla leads.
     *
     * La vista muestra la tabla de logs filtrados junto con tarjetas de resumen
     * con los conteos por estado de lead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function leadLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->where('subject_type', 'like', '%Lead%')
            ->orderBy('created_at', 'desc');

        if ($request->filled('lead_id')) {
            $query->where('subject_id', $request->lead_id);
        }

        if ($request->filled('status')) {
            $query->where('new_values', 'like', '%"status":"' . $request->status . '"%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Estadísticas de leads
        $leadStats = [
            'total' => Lead::count(),
            'nuevo' => Lead::where('status', 'nuevo')->count(),
            'contactado' => Lead::where('status', 'contactado')->count(),
            'calificado' => Lead::where('status', 'calificado')->count(),
            'cerrado_ganado' => Lead::where('status', 'cerrado_ganado')->count(),
            'cerrado_perdido' => Lead::where('status', 'cerrado_perdido')->count(),
        ];

        return view('modulos.auditorias.lead-logs', compact('logs', 'leadStats'));
    }

    /**
     * Vista de logs de actividad del sistema.
     *
     * Muestra los registros de auditoria que no pertenecen a un modulo de negocio
     * especifico: cambios en configuracion del sitio, gestion de roles, permisos
     * y eventos de autenticacion (login/logout).
     *
     * Flujo de datos:
     * 1. Construye la consulta base con un WHERE OR que incluye:
     *    - subject_type nulo (eventos generales del sistema).
     *    - subject_type LIKE '%SiteConfiguration%' (cambios de configuracion).
     *    - subject_type LIKE '%Role%' (gestion de roles).
     *    - subject_type LIKE '%Permission%' (gestion de permisos).
     *    - action igual a 'login' o 'logout' (eventos de autenticacion).
     * 2. Aplica un filtro de tipo (type) que permite refinar a una de las categorias:
     *    config, auth, role o permission.
     * 3. Aplica filtros de rango de fechas (date_from, date_to).
     * 4. Pagina los resultados en 20 registros por pagina.
     * 5. Construye un array de tipos con sus etiquetas para el selector de filtros.
     * 6. Calcula estadisticas de configuracion: total de cambios en configuracion,
     *    el ultimo cambio registrado y la configuracion actual del sitio.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function systemLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->where(function($q) {
                $q->whereNull('subject_type')
                  ->orWhere('subject_type', 'like', '%SiteConfiguration%')
                  ->orWhere('subject_type', 'like', '%Role%')
                  ->orWhere('subject_type', 'like', '%Permission%')
                  ->orWhere('action', 'login')
                  ->orWhere('action', 'logout');
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            if ($request->type === 'config') {
                $query->where('subject_type', 'like', '%SiteConfiguration%');
            } elseif ($request->type === 'auth') {
                $query->whereIn('action', ['login', 'logout']);
            } elseif ($request->type === 'role') {
                $query->where('subject_type', 'like', '%Role%');
            } elseif ($request->type === 'permission') {
                $query->where('subject_type', 'like', '%Permission%');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        $types = [
            'all' => 'Todos',
            'config' => 'Configuraciones del Sitio',
            'auth' => 'Autenticación (Login/Logout)',
            'role' => 'Roles',
            'permission' => 'Permisos',
        ];

        // Estadísticas de configuración
        $configStats = [
            'total_config_changes' => AuditLog::where('subject_type', 'like', '%SiteConfiguration%')->count(),
            'last_config_change' => AuditLog::where('subject_type', 'like', '%SiteConfiguration%')
                ->orderBy('created_at', 'desc')
                ->first(),
            'current_config' => SiteConfiguration::getConfig(),
        ];

        return view('modulos.auditorias.system-logs', compact('logs', 'types', 'configStats'));
    }

    /**
     * Centro de reportes y exportacion de datos de auditoria.
     *
     * Pagina principal de reportes que muestra KPIs globales, contadores por modulo,
     * graficas de actividad y opciones de exportacion. Sirve como punto de partida
     * para generar exportaciones en CSV, Excel y PDF.
     *
     * Flujo de datos:
     * 1. Calcula los KPIs principales contando registros en cada tabla:
     *    total de logs, usuarios, propiedades, citas, leads y cambios de configuracion.
     * 2. Cuenta los logs por modulo para la exportacion: usuarios, propiedades,
     *    citas, leads y sistema (incluyendo subject_type nulo, SiteConfiguration,
     *    Role y Permission).
     * 3. Genera datos para graficas:
     *    - logsByDay: logs por dia en los ultimos 30 dias (array asociativo fecha => total).
     *    - logsByAction: logs agrupados por tipo de accion, ordenados por frecuencia.
     *    - logsByUser: top 10 usuarios con mas logs, usando un CTE con LEFT JOIN.
     *    - logsByHour: distribucion de logs por hora en las ultimas 24 horas.
     * 4. Obtiene contadores de logs de hoy y del mes actual.
     * 5. Carga la configuracion actual del sitio y la lista de usuarios para filtros.
     * 6. Retorna la vista 'modulos.auditorias.reports' con todos los datos
     *    necesarios para renderizar las graficas y formularios de exportacion.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function reports(Request $request)
    {
        // KPIs principales
        $totalLogs = AuditLog::count();
        $totalUsers = User::count();
        $totalProperties = Property::count();
        $totalAppointments = Appointment::count();
        $totalLeads = Lead::count();
        $totalConfigChanges = AuditLog::where('subject_type', 'like', '%SiteConfiguration%')->count();

        //  CONTADORES POR MÓDULO PARA EXPORTACIÓN
        $userLogsCount = AuditLog::where('subject_type', 'like', '%User%')->count();
        $propertyLogsCount = AuditLog::where('subject_type', 'like', '%Property%')->count();
        $appointmentLogsCount = AuditLog::where('subject_type', 'like', '%Appointment%')->count();
        $leadLogsCount = AuditLog::where('subject_type', 'like', '%Lead%')->count();
        $systemLogsCount = AuditLog::where(function($q) {
            $q->whereNull('subject_type')
              ->orWhere('subject_type', 'like', '%SiteConfiguration%')
              ->orWhere('subject_type', 'like', '%Role%')
              ->orWhere('subject_type', 'like', '%Permission%');
        })->count();

        // Logs por día (últimos 30 días)
        $logsByDay = AuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Logs por acción
        $logsByAction = AuditLog::selectRaw('action, COUNT(*) as total')
            ->whereNotNull('action')
            ->groupBy('action')
            ->orderBy('total', 'desc')
            ->get()
            ->toArray();

        // Logs por usuario (top 10)
        $logsByUser = DB::select("
            WITH user_audits AS (
                SELECT
                    users.id,
                    users.name,
                    COUNT(audit_logs.id) as total
                FROM users
                LEFT JOIN audit_logs ON users.id = audit_logs.user_id
                WHERE users.deleted_at IS NULL
                GROUP BY users.id, users.name
            )
            SELECT * FROM user_audits
            WHERE total > 0
            ORDER BY total DESC
            LIMIT 10
        ");

        $logsByUser = collect($logsByUser)->map(function($item) {
            return [
                'name' => $item->name ?? 'Sistema',
                'total' => $item->total
            ];
        })->toArray();

        // Actividad por hora (últimas 24 horas)
        $logsByHour = AuditLog::selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as total')
            ->where('created_at', '>=', now()->subHours(24))
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get()
            ->pluck('total', 'hour')
            ->toArray();

        // Total de logs de hoy
        $todayLogs = AuditLog::whereDate('created_at', today())->count();

        // Total de logs de este mes
        $thisMonthLogs = AuditLog::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Configuración actual
        $currentConfig = SiteConfiguration::getConfig();

        //  USUARIOS PARA FILTRO (exportación)
        $users = User::orderBy('name')->get(['id', 'name', 'last_name']);

        //  EXPORTACIONES RECIENTES 
        $recentExports = [];

        return view('modulos.auditorias.reports', compact(
            'totalLogs',
            'totalUsers',
            'totalProperties',
            'totalAppointments',
            'totalLeads',
            'totalConfigChanges',
            'userLogsCount',
            'propertyLogsCount',
            'appointmentLogsCount',
            'leadLogsCount',
            'systemLogsCount',
            'logsByDay',
            'logsByAction',
            'logsByUser',
            'logsByHour',
            'todayLogs',
            'thisMonthLogs',
            'currentConfig',
            'users',
            'recentExports'
        ));
    }

    /**
     * Endpoint AJAX para obtener datos de graficas de reportes.
     *
     * Retorna conjuntos de datos en formato JSON segun el tipo solicitado,
     * utilizado para actualizar las graficas de la pagina de reportes sin
     * recargar la pagina completa.
     *
     * Tipos de datos soportados (parametro 'type'):
     * - 'daily': logs agrupados por dia en los ultimos 30 dias.
     *   Retorna array asociativo fecha => total.
     * - 'actions': logs agrupados por tipo de accion, ordenados por frecuencia.
     *   Retorna array de objetos {action, total}.
     * - 'users': top 10 usuarios con mas logs (usando CTE con LEFT JOIN sobre
     *   users y audit_logs). Retorna array de objetos {name, total}.
     *   Los usuarios sin nombre se muestran como 'Sistema'.
     * - 'hourly': distribucion de logs por hora en las ultimas 24 horas.
     *   Retorna array asociativo hora => total.
     * - 'config': logs de cambios en configuracion del sitio (subject_type LIKE
     *   '%SiteConfiguration%') agrupados por dia en los ultimos 30 dias.
     *   Retorna array asociativo fecha => total.
     * - Cualquier otro tipo retorna un array vacio.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReportData(Request $request)
    {
        $type = $request->type;
        $data = [];

        switch ($type) {
            case 'daily':
                $data = AuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as total')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get()
                    ->pluck('total', 'date')
                    ->toArray();
                break;

            case 'actions':
                $data = AuditLog::selectRaw('action, COUNT(*) as total')
                    ->whereNotNull('action')
                    ->groupBy('action')
                    ->orderBy('total', 'desc')
                    ->get()
                    ->toArray();
                break;

            case 'users':
                $data = DB::select("
                    WITH user_audits AS (
                        SELECT
                            users.name,
                            COUNT(audit_logs.id) as total
                        FROM users
                        LEFT JOIN audit_logs ON users.id = audit_logs.user_id
                        WHERE users.deleted_at IS NULL
                        GROUP BY users.id, users.name
                    )
                    SELECT * FROM user_audits
                    WHERE total > 0
                    ORDER BY total DESC
                    LIMIT 10
                ");

                $data = collect($data)->map(function($item) {
                    return [
                        'name' => $item->name ?? 'Sistema',
                        'total' => $item->total
                    ];
                })->toArray();
                break;

            case 'hourly':
                $data = AuditLog::selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as total')
                    ->where('created_at', '>=', now()->subHours(24))
                    ->groupBy('hour')
                    ->orderBy('hour', 'asc')
                    ->get()
                    ->pluck('total', 'hour')
                    ->toArray();
                break;

            case 'config':
                $data = AuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as total')
                    ->where('subject_type', 'like', '%SiteConfiguration%')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get()
                    ->pluck('total', 'date')
                    ->toArray();
                break;

            default:
                $data = [];
        }

        return response()->json($data);
    }

    /**
     * Muestra el detalle completo de un registro de auditoria especifico.
     *
     * Flujo de datos:
     * 1. Busca el registro de auditoria por su ID con eager loading del usuario.
     *    Si no existe, lanza una excepcion 404 (findOrFail).
     * 2. Si el log tiene un subject_type y subject_id asociados, intenta cargar
     *    el modelo relacionado via la relacion polimorfica 'subject'. Si ocurre
     *    cualquier excepcion (por ejemplo, si la entidad fue eliminada), captura
     *    el error y asigna null al subject.
     * 3. Retorna la vista 'modulos.auditorias.show' con el log y su sujeto
     *    asociado (si existe).
     *
     * @param  int  $id  Identificador unico del registro de auditoria.
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        $subject = null;
        if ($log->subject_type && $log->subject_id) {
            try {
                $subject = $log->subject;
            } catch (\Exception $e) {
                $subject = null;
            }
        }

        return view('modulos.auditorias.show', compact('log', 'subject'));
    }

    /**
     * Exporta registros de auditoria a formato CSV con filtros avanzados.
     *
     * Genera un archivo CSV descargable con los registros de auditoria filtrados
     * segun los parametros proporcionados. El archivo se genera directamente en
     * el buffer de salida usando php://output para evitar la creacion de archivos
     * temporales en disco.
     *
     * Filtros aplicados:
     * - entity: filtra por modulo/entidad. Valores soportados:
     *   'all' (sin filtro), 'users' (%User%), 'properties' (%Property%),
     *   'appointments' (%Appointment%), 'leads' (%Lead%), 'system' (combina
     *   subject_type nulo, SiteConfiguration, Role y Permission).
     * - date_from / date_to: filtra por rango de fechas en created_at.
     * - user_id: filtra por un usuario especifico.
     * - action: busqueda parcial en campos 'action' y 'event'.
     *
     * Columnas del CSV:
     * ID, Usuario, Email Usuario, Accion, Evento, Entidad, ID Entidad,
     * Descripcion, IP, URL, Fecha.
     *
     * La fecha se formatea en zona horaria America/Caracas con patron d/m/Y H:i:s.
     * El nombre del archivo incluye la fecha actual: auditoria_YYYY-MM-DD.csv.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = AuditLog::with('user');

        //  FILTRO POR ENTIDAD / MÓDULO
        if ($request->filled('entity')) {
            $entity = $request->entity;

            if ($entity === 'all') {
                // Todos los logs - no aplicar filtro
            } elseif ($entity === 'users') {
                $query->where('subject_type', 'like', '%User%');
            } elseif ($entity === 'properties') {
                $query->where('subject_type', 'like', '%Property%');
            } elseif ($entity === 'appointments') {
                $query->where('subject_type', 'like', '%Appointment%');
            } elseif ($entity === 'leads') {
                $query->where('subject_type', 'like', '%Lead%');
            } elseif ($entity === 'system') {
                $query->where(function($q) {
                    $q->whereNull('subject_type')
                      ->orWhere('subject_type', 'like', '%SiteConfiguration%')
                      ->orWhere('subject_type', 'like', '%Role%')
                      ->orWhere('subject_type', 'like', '%Permission%');
                });
            }
        }

        //  FILTRO POR FECHAS
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // FILTRO POR USUARIO
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        //  FILTRO POR ACCIÓN
        if ($request->filled('action')) {
            $query->where(function($q) use ($request) {
                $q->where('action', 'like', '%' . $request->action . '%')
                  ->orWhere('event', 'like', '%' . $request->action . '%');
            });
        }

        $logs = $query->limit(5000)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=auditoria_' . date('Y-m-d') . '.csv',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // Encabezados mejorados
            fputcsv($file, [
                'ID',
                'Usuario',
                'Email Usuario',
                'Acción',
                'Evento',
                'Entidad',
                'ID Entidad',
                'Descripción',
                'IP',
                'URL',
                'Fecha'
            ]);

            foreach ($logs as $log) {
                $entity = $log->subject_type ? class_basename($log->subject_type) : 'Sistema';
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->full_name : 'Sistema',
                    $log->user ? $log->user->email : '',
                    $log->action ?? $log->event ?? 'N/A',
                    $log->event ?? 'N/A',
                    $entity,
                    $log->subject_id ?? 'N/A',
                    $log->description ?? 'N/A',
                    $log->ip_address ?? 'N/A',
                    $log->url ?? 'N/A',
                    $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta registros de auditoria a formato Excel (.xlsx) con filtros avanzados.
     *
     * Utiliza la libreria Maatwebsite/Excel para generar un archivo Excel
     * descargable. Delega la construccion de la coleccion de datos a la clase
     * AuditLogExport, pasando los mismos filtros que la exportacion CSV.
     *
     * Filtros aplicados (via clase AuditLogExport):
     * - entity: modulo de entidades a exportar (all, users, properties, etc.).
     * - date_from / date_to: rango de fechas.
     * - user_id: usuario especifico.
     * - action: tipo de accion de auditoria.
     *
     * El titulo del reporte se genera dinamicamente con getExportTitle() combinando
     * el nombre del modulo, el formato y la fecha actual.
     *
     * Nombre del archivo: auditoria_{entity}_YYYY-MM-DD_HHMMSS.xlsx.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request)
    {
        $entity = $request->input('entity', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id') ? (int) $request->input('user_id') : null;
        $action = $request->input('action');
        $title = $this->getExportTitle($entity, 'Excel');

        return Excel::download(
            new AuditLogExport($entity, $dateFrom, $dateTo, $userId, $action, $title),
            'auditoria_' . $entity . '_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    /**
     * Exporta registros de auditoria a formato PDF con filtros avanzados.
     *
     * Genera un archivo PDF descargable utilizando la libreria DomPDF. Primero
     * obtiene la coleccion de datos filtrados a traves de la clase AuditLogExport
     * (misma logica que Excel y CSV), luego renderiza la vista 'pdfs.audit-report'
     * y la convierte a PDF en formato apaisado A4.
     *
     * Filtros aplicados (via clase AuditLogExport):
     * - entity: modulo de entidades a exportar (all, users, properties, etc.).
     * - date_from / date_to: rango de fechas.
     * - user_id: usuario especifico.
     * - action: tipo de accion de auditoria.
     *
     * Antes de renderizar, construye un array de filtros legibles con etiquetas
     * amigables para el usuario (por ejemplo, 'users' => 'Usuarios', 'created'
     * => 'Creacion'), incluyendo el nombre completo del usuario si se filtro
     * por uno especifico.
     *
     * El PDF se genera en formato A4 apaisado (landscape) y el archivo se nombra
     * como: auditoria_{entity}_YYYY-MM-DD_HHMMSS.pdf.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPdf(Request $request)
    {
        $entity = $request->input('entity', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id') ? (int) $request->input('user_id') : null;
        $action = $request->input('action');
        $title = $this->getExportTitle($entity, 'PDF');

        $export = new AuditLogExport($entity, $dateFrom, $dateTo, $userId, $action, $title);
        $logs = $export->collection();

        $entityLabels = [
            'all' => 'Todos', 'users' => 'Usuarios', 'properties' => 'Propiedades',
            'appointments' => 'Citas', 'leads' => 'Leads', 'services' => 'Servicios',
            'categories' => 'Categorías', 'roles' => 'Roles', 'system' => 'Sistema',
        ];
        $actionLabels = [
            'created' => 'Creación', 'updated' => 'Actualización',
            'deleted' => 'Eliminación', 'login' => 'Login', 'logout' => 'Logout',
        ];

        $filters = [
            'Entidad' => $entityLabels[$entity] ?? $entity,
            'Desde' => $dateFrom ?? 'N/A',
            'Hasta' => $dateTo ?? 'N/A',
            'Usuario' => $userId ? (User::find($userId)->full_name ?? 'N/A') : 'Todos',
            'Acción' => $actionLabels[$action] ?? ($action ?? 'Todas'),
        ];

        $pdf = Pdf::loadView('pdfs.audit-report', compact('logs', 'title', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('auditoria_' . $entity . '_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Genera el titulo descriptivo para los reportes exportados.
     *
     * Combina el nombre del modulo de auditoria con el formato de exportacion
     * y la fecha actual en zona horaria America/Caracas.
     *
     * Ejemplo de salida: "Auditoria de Propiedades -- Excel -- 26/07/2026"
     *
     * @param  string  $entity  Clave del modulo (all, users, properties, appointments, leads, services, categories, roles, system).
     * @param  string  $format  Formato de exportacion (CSV, Excel, PDF).
     * @return string  Titulo formateado para el reporte.
     */
    private function getExportTitle(string $entity, string $format): string
    {
        $titles = [
            'all' => 'Reporte General de Auditoría',
            'users' => 'Auditoría de Usuarios',
            'properties' => 'Auditoría de Propiedades',
            'appointments' => 'Auditoría de Citas',
            'leads' => 'Auditoría de Leads',
            'services' => 'Auditoría de Servicios',
            'categories' => 'Auditoría de Categorías',
            'roles' => 'Auditoría de Roles',
            'system' => 'Auditoría del Sistema',
        ];

        return ($titles[$entity] ?? 'Auditoría') . ' — ' . $format . ' — ' . now()->setTimezone('America/Caracas')->format('d/m/Y');
    }

    /**
     * Endpoint AJAX para obtener las estadisticas del dashboard de auditoria.
     *
     * Retorna el mismo conjunto de datos que getDashboardStats() pero en formato
     * JSON, permitiendo actualizar las tarjetas y graficas del dashboard sin
     * recargar la pagina completa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardData(Request $request)
    {
        $stats = $this->getDashboardStats();
        return response()->json($stats);
    }

    /**
     * Endpoint AJAX para buscar propiedades publicadas y mostrarlas en un campo
     * de autocompletar.
     *
     * Flujo de datos:
     * 1. Recibe el parametro 'q' con el texto de busqueda.
     * 2. Si la longitud del texto es menor a 2 caracteres, retorna un array vacio.
     * 3. Consulta propiedades con status 'publicada', aplicando busqueda LIKE
     *    sobre los campos 'title', 'id' e 'location'.
     * 4. Carga la imagen primaria de cada propiedad (eager loading).
     * 5. Mapea cada resultado a un array simplificado con: id, title, location,
     *    price (formateado) e image (URL completa de la imagen principal via asset).
     * 6. Retorna la respuesta como JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProperties(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $properties = Property::where('status', 'publicada')
            ->where(function($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            })
            ->with('primaryImage')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(function($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'location' => $property->location,
                    'price' => $property->formatted_price,
                    'image' => $property->primaryImage?->image_path
                        ? asset('storage/' . $property->primaryImage->image_path)
                        : null,
                ];
            });

        return response()->json($properties);
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    /**
     * Obtiene la lista de acciones unicas registradas en la tabla de auditoria.
     *
     * Consulta los valores distintos de los campos 'action' y 'event', los combina
     * en una sola coleccion eliminando duplicados, y retorna un array indexado
     * con todas las acciones existentes. Se utiliza para poblar el selector de
     * filtros de acciones en el dashboard.
     *
     * @return \Illuminate\Support\Collection  Coleccion de strings con las acciones unicas.
     */
    private function getUniqueActions()
    {
        $actions = AuditLog::select('action')
            ->distinct()
            ->whereNotNull('action')
            ->pluck('action')
            ->merge(AuditLog::select('event')->distinct()->whereNotNull('event')->pluck('event'))
            ->unique()
            ->values();

        return $actions;
    }

    /**
     * Obtiene la lista de entidades unicas registradas en la tabla de auditoria.
     *
     * Consulta los valores distintos del campo 'subject_type', extrae el nombre
     * de la clase (ultima parte del namespace completo separado por '\'), elimina
     * duplicados y retorna un array indexado con los nombres simples de las entidades.
     *
     * Ejemplo: 'App\Models\Property' se convierte en 'Property'.
     *
     * Se utiliza para poblar el selector de filtros de entidades en el dashboard.
     *
     * @return \Illuminate\Support\Collection  Coleccion de strings con los nombres de entidades unicas.
     */
    private function getUniqueEntities()
    {
        return AuditLog::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(function($type) {
                $parts = explode('\\', $type);
                return end($parts);
            })
            ->unique()
            ->values();
    }

    /**
     * Calcula todas las estadisticas del dashboard de auditoria.
     *
     * Metodo privado que reune todos los indicadores clave necesarios para las
     * vistas del dashboard y los reportes. Es invocado por index() y
     * getDashboardData().
     *
     * Estadisticas calculadas:
     * - total_logs: total de registros en la tabla audit_logs.
     * - today_logs: registros creados desde el inicio del dia actual.
     * - total_users: total de usuarios registrados.
     * - total_properties: total de propiedades registradas.
     * - total_appointments: total de citas registradas.
     * - total_leads: total de leads registrados.
     * - total_config_changes: registros de auditoria sobre SiteConfiguration.
     * - logs_by_action: top 5 acciones mas frecuentes (action, count), ordenadas
     *   por frecuencia descendente.
     * - logs_by_entity: cantidad de logs agrupados por categoria de entidad usando
     *   una expresion CASE que clasifica subject_type en: Propiedades, Citas,
     *   Leads, Usuarios, Configuracion del Sitio, Roles, Permisos o Sistema.
     * - recent_activity: ultimos 10 registros de auditoria con usuario cargado.
     * - logs_by_day: cantidad de logs por dia en los ultimos 30 dias, como array
     *   asociativo fecha => total.
     *
     * @return array<string, mixed>  Array asociativo con todas las estadisticas.
     */
    private function getDashboardStats()
    {
        $now = now();
        $today = $now->copy()->startOfDay();

        return [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::where('created_at', '>=', $today)->count(),
            'total_users' => User::count(),
            'total_properties' => Property::count(),
            'total_appointments' => Appointment::count(),
            'total_leads' => Lead::count(),
            'total_config_changes' => AuditLog::where('subject_type', 'like', '%SiteConfiguration%')->count(),

            'logs_by_action' => AuditLog::select('action', DB::raw('count(*) as total'))
                ->whereNotNull('action')
                ->groupBy('action')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),

            'logs_by_entity' => AuditLog::select(
                    DB::raw("CASE
                        WHEN subject_type LIKE '%Property%' THEN 'Propiedades'
                        WHEN subject_type LIKE '%Appointment%' THEN 'Citas'
                        WHEN subject_type LIKE '%Lead%' THEN 'Leads'
                        WHEN subject_type LIKE '%User%' THEN 'Usuarios'
                        WHEN subject_type LIKE '%SiteConfiguration%' THEN 'Configuración del Sitio'
                        WHEN subject_type LIKE '%Role%' THEN 'Roles'
                        WHEN subject_type LIKE '%Permission%' THEN 'Permisos'
                        ELSE 'Sistema'
                    END as entity"),
                    DB::raw('count(*) as total')
                )
                ->groupBy('entity')
                ->get(),

            'recent_activity' => AuditLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),

            'logs_by_day' => AuditLog::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as total')
                )
                ->where('created_at', '>=', $now->copy()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->pluck('total', 'date')
                ->toArray(),
        ];
    }
}
