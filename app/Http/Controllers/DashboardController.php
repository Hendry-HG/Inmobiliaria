<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Country;
use App\Models\User;
use App\Models\Property;
use App\Models\Appointment;
use App\Models\Lead;
use App\Models\AuditLog;
use App\Models\Favorite;
use App\Services\PermissionService;

class DashboardController extends Controller
{
/**
 * Controlador de Dashboard
 *
 * Gestiona la redireccion y renderizacion de dashboards segun el rol del usuario.
 * Implementa dashboards especificos para: Super Admin, Administrador, Asesor
 * Inmobiliario, Auditor y Cliente. Incluye un dashboard generico para roles
 * personalizados. Valida permisos antes de cada renderizado.
 *
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Redirige al dashboard correspondiente segun el rol del usuario.
     *
     * Flujo de datos:
     * 1. Verifica que el usuario este autenticado
     * 2. Valida que la cuenta del usuario este activa (desactiva sesion si no)
     * 3. Instancia PermissionService para determinar la ruta del dashboard
     * 4. Verifica si hay una URL de redireccion segura en el query string
     * 5. Verifica si hay una URL intendida en la sesion
     * 6. Redirige al dashboard correspondiente al rol del usuario
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        // Crear una nueva instancia del servicio con el usuario autenticado
        $permissionService = new PermissionService($user);
        $dashboardRoute = $permissionService->getDashboardRoute();

        \Illuminate\Support\Facades\Log::info('DashboardController - index', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'main_role' => $permissionService->getMainRole(),
            'all_roles' => $permissionService->getRoles(),
            'dashboard_route' => $dashboardRoute
        ]);

        // Si hay una URL de redirección segura
        if ($request->has('redirect') && !empty($request->redirect)) {
            $redirectUrl = $request->redirect;
            if ($this->isSafeUrl($redirectUrl)) {
                return redirect($redirectUrl);
            }
        }

        // Si hay una URL intendida en sesión
        if (session()->has('url.intended')) {
            $intendedUrl = session()->get('url.intended');
            if ($this->isSafeUrl($intendedUrl)) {
                session()->forget('url.intended');
                return redirect($intendedUrl);
            }
            session()->forget('url.intended');
        }

        // Redirigir al dashboard correspondiente
        return redirect($dashboardRoute);
    }

    /**
     * Valida si una URL es segura para redireccionar.
     *
     * Flujo de datos:
     * 1. Rechaza URLs vacias
     * 2. Acepta rutas relativas que inician con '/'
     * 3. Para URLs absolutas, valida que el host coincida con el servidor actual
     * 4. Previene ataques de redireccion abierta (open redirect)
     *
     * @param string $url URL a validar
     * @return bool True si la URL es segura, false en caso contrario
     */
    private function isSafeUrl($url)
    {
        if (empty($url)) return false;

        if (str_starts_with($url, '/')) {
            return true;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['host']) && $parsedUrl['host'] === request()->getHost()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dashboard exclusivo para el rol Super Admin.
     *
     * Flujo de datos:
     * 1. Verifica permisos de Super Admin via PermissionService
     * 2. Recopila metricas globales: propiedades, usuarios, citas del dia, leads activos
     * 3. Obtiene propiedades y usuarios recientes (ultimos 5)
     * 4. Aplica filtros de busqueda, rol, estado y pais sobre usuarios
     * 5. Retorna vista con metricas, usuarios paginados, roles y paises
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function superAdminDashboard(Request $request)
    {
        $user = Auth::user();

        // Crear una nueva instancia del servicio con el usuario autenticado
        $permissionService = new PermissionService($user);

        if (!$permissionService->hasRole('Super Admin')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $totalProperties = Property::count();
        $totalUsers = User::count();
        $todayAppointments = Appointment::whereDate('scheduled_date', today())->count();
        $activeLeads = Lead::whereNotIn('status', ['cerrado_ganado', 'cerrado_perdido'])->count();
        $recentProperties = Property::with('user')->latest()->limit(5)->get();
        $recentUsers = User::latest()->limit(5)->get();

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('id_number', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        if ($request->filled('country')) {
            $query->where('country_id', $request->country);
        }

        $query->orderBy('id', 'desc');
        $users = $query->paginate(15)->withQueryString();

        $roles = Role::all();
        $countries = Country::orderBy('name')->pluck('name', 'id');

        return view('dashboard.super-admin.index', compact(
            'user', 'totalProperties', 'totalUsers', 'todayAppointments',
            'activeLeads', 'recentProperties', 'recentUsers', 'users', 'roles', 'countries'
        ));
    }

    /**
     * Dashboard exclusivo para los roles Administrador y Super Admin.
     *
     * Flujo de datos:
     * 1. Verifica permisos de Administrador o Super Admin via PermissionService
     * 2. Recopila metricas: propiedades totales, citas pendientes, leads nuevos,
     *    propiedades pendientes de revision
     * 3. Aplica filtros de busqueda, rol, estado y pais sobre usuarios
     * 4. Retorna vista con metricas, usuarios paginados, roles y paises
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function adminDashboard(Request $request)
    {
        $user = Auth::user();

        // Crear una nueva instancia del servicio con el usuario autenticado
        $permissionService = new PermissionService($user);

        if (!$permissionService->hasAnyRole(['Administrador', 'Super Admin'])) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $totalProperties = Property::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $newLeads = Lead::where('status', 'nuevo')->count();
        $pendingProperties = Property::where('status', 'pendiente')->count();

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('id_number', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        if ($request->filled('country')) {
            $query->where('country_id', $request->country);
        }

        $query->orderBy('id', 'desc');
        $users = $query->paginate(15)->withQueryString();

        $roles = Role::all();
        $countries = Country::orderBy('name')->pluck('name', 'id');

        return view('dashboard.admin.index', compact(
            'user', 'totalProperties', 'pendingAppointments', 'newLeads',
            'pendingProperties', 'users', 'roles', 'countries'
        ));
    }

    /**
     * Dashboard exclusivo para el rol Asesor Inmobiliario.
     *
     * Flujo de datos:
     * 1. Verifica permisos de Asesor Inmobiliario via PermissionService
     * 2. Carga todas las citas del asesor con relaciones (propiedad, asesor, cliente)
     * 3. Calcula metricas personales: propiedades propias, citas de hoy, pendientes,
     *    leads nuevos, leads totales y tasa de conversion
     * 4. Obtiene proximas 10 citas programadas y 10 leads recientes
     * 5. Retorna vista con todas las metricas y datos del asesor
     *
     * @return \Illuminate\View\View
     */
    public function asesorDashboard()
    {
        $user = Auth::user();

        // Crear una nueva instancia del servicio con el usuario autenticado
        $permissionService = new PermissionService($user);

        if (!$permissionService->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $appointments = Appointment::with(['property', 'asesor', 'user'])
            ->where('asesor_id', $user->id)
            ->orderBy('scheduled_date', 'desc')
            ->get();

        $myProperties = Property::where('user_id', $user->id)->count();

        $todayAppointments = Appointment::where('asesor_id', $user->id)
            ->whereDate('scheduled_date', '=', date('Y-m-d'))
            ->count();

        $pendingAppointments = Appointment::where('asesor_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $newLeads = Lead::where('asesor_id', $user->id)
            ->where('status', 'nuevo')
            ->count();

        $totalLeads = Lead::where('asesor_id', $user->id)->count();
        $convertedLeads = Lead::where('asesor_id', $user->id)
            ->where('status', 'cerrado_ganado')
            ->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100) : 0;

        $upcomingAppointments = Appointment::where('asesor_id', $user->id)
            ->where('scheduled_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['property', 'user'])
            ->orderBy('scheduled_date', 'asc')
            ->limit(10)
            ->get();

        $recentLeads = Lead::where('asesor_id', $user->id)
            ->with('property')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.asesor.index', compact(
            'user',
            'appointments',
            'myProperties',
            'todayAppointments',
            'pendingAppointments',
            'newLeads',
            'totalLeads',
            'conversionRate',
            'upcomingAppointments',
            'recentLeads'
        ));
    }

    /**
     * Dashboard exclusivo para el rol Auditor.
     *
     * Flujo de datos:
     * 1. Verifica permisos de Auditor via PermissionService
     * 2. Obtiene metricas generales: propiedades, usuarios y citas totales
     * 3. Consulta registros de auditoria: total, hoy y 15 mas recientes
     * 4. Maneja excepciones si la tabla de auditoria no existe
     * 5. Retorna vista con metricas y registros de auditoria
     *
     * @return \Illuminate\View\View
     */
    public function auditorDashboard()
    {
        $user = Auth::user();

        // Crear una nueva instancia del servicio con el usuario autenticado
        $permissionService = new PermissionService($user);

        if (!$permissionService->hasRole('Auditor')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $totalProperties = Property::count();
        $totalUsers = User::count();
        $totalAppointments = Appointment::count();

        try {
            $logsCount = AuditLog::count();
            $todayLogs = AuditLog::whereDate('created_at', today())->count();
            $recentLogs = AuditLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();
        } catch (\Exception $e) {
            $logsCount = 0;
            $todayLogs = 0;
            $recentLogs = collect();
        }

        return view('dashboard.auditor.index', compact(
            'user',
            'totalProperties',
            'totalUsers',
            'totalAppointments',
            'logsCount',
            'todayLogs',
            'recentLogs'
        ));
    }

    /**
     * Dashboard exclusivo para el rol Cliente.
     *
     * Flujo de datos:
     * 1. Verifica permisos de Cliente via PermissionService
     * 2. Obtiene propiedades favoritas del cliente (ultimas 3 publicadas)
     * 3. Cuenta citas proximas y obtiene la siguiente cita programada
     * 4. Recupera las 5 ultimas citas y las 3 propiedades mas recientes
     * 5. Retorna vista con favoritos, citas, propiedades y conteo de mensajes sin leer
     *
     * @return \Illuminate\View\View
     */
    public function clienteDashboard()
    {
        $user = Auth::user();

        // Crear una nueva instancia del servicio con el usuario autenticado
        $permissionService = new PermissionService($user);

        if (!$permissionService->hasRole('Cliente')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $favoritesCount = Favorite::where('user_id', $user->id)->count();

        $favoriteIds = Favorite::where('user_id', $user->id)
            ->pluck('property_id')
            ->toArray();

        $favoriteProperties = Property::whereIn('id', $favoriteIds)
            ->where('status', 'publicada')
            ->with('primaryImage')
            ->latest()
            ->limit(3)
            ->get();

        $upcomingAppointments = Appointment::where('user_id', $user->id)
            ->where('scheduled_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $nextAppointment = Appointment::where('user_id', $user->id)
            ->where('scheduled_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['property', 'asesor'])
            ->orderBy('scheduled_date', 'asc')
            ->first();

        $appointments = Appointment::where('user_id', $user->id)
            ->with(['property', 'asesor'])
            ->latest()
            ->limit(5)
            ->get();

        $pendingAppointmentsCount = Appointment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $recentProperties = Property::where('status', 'publicada')
            ->with('primaryImage')
            ->latest()
            ->limit(3)
            ->get();

        $unreadMessagesCount = 0;

        return view('dashboard.cliente.index', compact(
            'user',
            'favoritesCount',
            'upcomingAppointments',
            'nextAppointment',
            'favoriteProperties',
            'appointments',
            'recentProperties',
            'unreadMessagesCount',
            'pendingAppointmentsCount'
        ));
    }

    /**
     * Dashboard generico para roles personalizados sin dashboard dedicado.
     *
     * Flujo de datos:
     * 1. Instancia PermissionService para verificar permisos individuales
     * 2. Muestra metricas condicionales segun los permisos del usuario:
     *    - ver propiedades: muestra total de propiedades
     *    - ver leads: muestra total de leads
     *    - ver citas: muestra total de citas
     *    - ver usuarios: muestra total de usuarios
     * 3. Carga propiedades recientes y citas proximas solo si tiene permiso
     * 4. Retorna vista generica con metricas y datos disponibles
     *
     * @return \Illuminate\View\View
     */
    public function genericDashboard()
    {
        $user = Auth::user();

        // Crear una nueva instancia del servicio con el usuario autenticado
        $permissionService = new PermissionService($user);

        $stats = [
            'total_properties' => $permissionService->hasPermission('ver propiedades') ? Property::count() : 0,
            'total_leads' => $permissionService->hasPermission('ver leads') ? Lead::count() : 0,
            'total_appointments' => $permissionService->hasPermission('ver citas') ? Appointment::count() : 0,
            'total_users' => $permissionService->hasPermission('ver usuarios') ? User::count() : 0,
        ];

        $recentProperties = $permissionService->hasPermission('ver propiedades')
            ? Property::with('primaryImage')->latest()->limit(5)->get()
            : collect();

        $upcomingAppointments = $permissionService->hasPermission('ver citas')
            ? Appointment::where('scheduled_date', '>=', now())
                ->whereIn('status', ['pending', 'confirmed'])
                ->with('property')
                ->latest('scheduled_date')
                ->limit(5)
                ->get()
            : collect();

        return view('dashboard.generic', compact(
            'user',
            'stats',
            'recentProperties',
            'upcomingAppointments'
        ));
    }
}
