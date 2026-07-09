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

class DashboardController extends Controller
{
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

        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        // Verificar redirección manual
        if ($request->has('redirect') && !empty($request->redirect)) {
            $redirectUrl = $request->redirect;
            if ($this->isSafeUrl($redirectUrl)) {
                return redirect($redirectUrl);
            }
        }

        // Verificar URL intended
        if (session()->has('url.intended')) {
            $intendedUrl = session()->get('url.intended');
            if ($this->isSafeUrl($intendedUrl)) {
                session()->forget('url.intended');
                return redirect($intendedUrl);
            }
            session()->forget('url.intended');
        }

        // Redirigir según rol
        if (in_array('Super Admin', $userRoles)) {
            return redirect()->route('super-admin.dashboard');
        } elseif (in_array('Administrador', $userRoles)) {
            return redirect()->route('admin.dashboard');
        } elseif (in_array('Asesor Inmobiliario', $userRoles)) {
            return redirect()->route('asesor.dashboard');
        } elseif (in_array('Auditor', $userRoles)) {
            return redirect()->route('auditor.dashboard');
        } elseif (in_array('Cliente', $userRoles)) {
            return redirect()->route('cliente.dashboard');
        }

        return redirect()->route('home');
    }

    private function isSafeUrl($url)
    {
        if (empty($url)) return false;

        // URLs relativas son seguras
        if (str_starts_with($url, '/')) {
            return true;
        }

        // Verificar URLs completas
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['host']) && $parsedUrl['host'] === request()->getHost()) {
                return true;
            }
        }

        return false;
    }

    public function superAdminDashboard(Request $request)
    {
        $user = Auth::user();

        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        if (!in_array('Super Admin', $userRoles)) {
            abort(403);
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

    public function adminDashboard(Request $request)
    {
        $user = Auth::user();

        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        if (!in_array('Administrador', $userRoles) && !in_array('Super Admin', $userRoles)) {
            abort(403);
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

    public function asesorDashboard()
    {
        $user = Auth::user();

        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        if (!in_array('Asesor Inmobiliario', $userRoles)) {
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
            'conversionRate',
            'upcomingAppointments',
            'recentLeads'
        ));
    }

    public function auditorDashboard()
    {
        $user = Auth::user();

        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        if (!in_array('Auditor', $userRoles)) {
            abort(403);
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

    public function clienteDashboard()
    {
        $user = Auth::user();

        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        if (!in_array('Cliente', $userRoles)) {
            abort(403);
        }

        $favoritesCount = $user->favorites()->count();

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

        $favoriteProperties = $user->favoriteProperties()
            ->with('primaryImage')
            ->latest('favorites.created_at')
            ->limit(3)
            ->get();

        $appointments = Appointment::where('user_id', $user->id)
            ->with(['property', 'asesor'])
            ->latest()
            ->limit(5)
            ->get();

        $recentProperties = Property::published()
            ->with('primaryImage')
            ->latest()
            ->limit(3)
            ->get();

        $unreadMessagesCount = 0;

        $pendingAppointmentsCount = Appointment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return view('dashboard.cliente.index', compact(
            'user', 'favoritesCount', 'upcomingAppointments', 'nextAppointment',
            'favoriteProperties', 'appointments', 'recentProperties',
            'unreadMessagesCount', 'pendingAppointmentsCount'
        ));
    }
}
