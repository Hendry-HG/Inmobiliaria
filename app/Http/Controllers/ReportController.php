<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Lead;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver reportes');
    }

    public function index()
    {
        // Datos para los KPIs
        $totalProperties = Property::count();
        $totalAppointments = Appointment::count();
        $totalLeads = Lead::count();
        $totalUsers = User::count();

        // Citas de hoy
        $todayAppointments = Appointment::whereDate('scheduled_date', today())->count();

        // Propiedades destacadas
        $featuredProperties = Property::where('is_featured', true)->count();

        // Tasa de conversión
        $closedDeals = Lead::where('status', 'cerrado_ganado')->count();
        $conversionRate = $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0;

        // Datos para gráficos
        $propertiesByCategory = Property::select('category_id', DB::raw('count(*) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(function($item) {
                return [
                    'label' => $item->category ? $item->category->name : 'Sin categoría',
                    'value' => $item->total
                ];
            });

        $appointmentsByStatus = Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function($item) {
                $statusMap = [
                    'pending' => 'Pendientes',
                    'confirmed' => 'Confirmadas',
                    'completed' => 'Completadas',
                    'cancelled' => 'Canceladas',
                    'reprogrammed' => 'Reprogramadas'
                ];
                return [
                    'label' => $statusMap[$item->status] ?? ucfirst($item->status),
                    'value' => $item->total
                ];
            });

        $leadsByStatus = Lead::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function($item) {
                $statusMap = [
                    'nuevo' => 'Nuevos',
                    'contactado' => 'Contactados',
                    'en_negociacion' => 'En Negociación',
                    'cerrado_ganado' => 'Cerrados',
                    'cerrado_perdido' => 'Perdidos',
                    'inactivo' => 'Inactivos'
                ];
                return [
                    'label' => $statusMap[$item->status] ?? ucfirst($item->status),
                    'value' => $item->total
                ];
            });

        // Top asesores
        $topAsesores = User::role('Asesor Inmobiliario')
            ->withCount('leads')
            ->orderBy('leads_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'leads' => $user->leads_count,
                    'avatar' => $user->profile_photo_url ?? null
                ];
            });

        return view('modulos.reportes.index', compact(
            'totalProperties',
            'totalAppointments',
            'totalLeads',
            'totalUsers',
            'todayAppointments',
            'featuredProperties',
            'conversionRate',
            'closedDeals',
            'propertiesByCategory',
            'appointmentsByStatus',
            'leadsByStatus',
            'topAsesores'
        ));
    }

    public function getData(Request $request)
    {
        $type = $request->type;
        $data = [];

        switch ($type) {
            case 'properties':
                $data = Property::orderBy('views', 'desc')
                    ->take(10)
                    ->get(['id', 'title', 'views', 'price'])
                    ->map(function ($item) {
                        return [
                            'titulo' => $item->title,
                            'vistas' => $item->views ?? 0,
                            'precio' => number_format($item->price, 2, ',', '.'),
                            'id' => $item->id
                        ];
                    });
                break;

            case 'appointments':
                $data = Appointment::selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $statusMap = [
                            'pending' => 'Pendientes',
                            'confirmed' => 'Confirmadas',
                            'completed' => 'Completadas',
                            'cancelled' => 'Canceladas',
                            'reprogrammed' => 'Reprogramadas'
                        ];
                        $label = $statusMap[$item->status] ?? ucfirst($item->status);
                        return [$label => $item->total];
                    });
                break;

            case 'leads':
                $data = User::role('Asesor Inmobiliario')
                    ->withCount('leads')
                    ->orderBy('leads_count', 'desc')
                    ->limit(5)
                    ->get(['name', 'leads_count'])
                    ->map(function ($asesor) {
                        return [
                            'asesor' => $asesor->name,
                            'total' => $asesor->leads_count
                        ];
                    });
                break;

            case 'performance':
                $totalLeads = Lead::count();
                $closedDeals = Lead::where('status', 'cerrado_ganado')->count();
                $data = [
                    'total_leads' => $totalLeads,
                    'closed_deals' => $closedDeals,
                    'conversion_rate' => $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0
                ];
                break;

            default:
                $data = [];
        }

        return response()->json($data);
    }
}
