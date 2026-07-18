<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Property;
use App\Models\Appointment;
use App\Models\Lead;
use App\Models\SiteConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver logs de auditoria');
    }

    /**
     * Dashboard principal de auditoría
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
     * Vista de logs de usuarios - CON HISTORIAL DEL USUARIO SELECCIONADO
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
     * BUSCAR USUARIOS PARA AUTOCOMPLETAR (AJAX)
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
     * Vista de logs de propiedades
     */
    public function propertyLogs(Request $request)
    {
        // Obtener TODAS las propiedades con sus estadísticas
        $propertyStats = Property::with('primaryImage')
            ->orderBy('title')
            ->get()
            ->map(function($property) {
                $eventsCount = AuditLog::where('subject_type', 'like', '%Property%')
                    ->where('subject_id', $property->id)
                    ->count();

                $lastActivity = AuditLog::where('subject_type', 'like', '%Property%')
                    ->where('subject_id', $property->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

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
                    'events_count' => $eventsCount,
                    'last_activity' => $lastActivity ? $lastActivity->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i') : 'Nunca',
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
     * Vista de logs de citas
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
     * Vista de logs de leads
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
     * Vista de logs del sistema (configuraciones, roles, permisos, etc)
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
     * VISTA DE REPORTES - CENTRO DE EXPORTACIÓN DE DATOS
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
     * API - DATOS PARA REPORTES (AJAX)
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
     * Detalle de un log específico
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
     * Exportar logs a CSV con filtros avanzados
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

        $logs = $query->get();

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
     * API para obtener datos del dashboard (AJAX)
     */
    public function getDashboardData(Request $request)
    {
        $stats = $this->getDashboardStats();
        return response()->json($stats);
    }

    /**
     * BUSCAR PROPIEDADES PARA AUTOCOMPLETAR (AJAX)
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
