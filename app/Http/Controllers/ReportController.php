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
        $totalProperties = Property::count();
        $totalAppointments = Appointment::count();
        $totalLeads = Lead::count();
        $totalUsers = User::count();

        $activeProperties = Property::where('status', 'publicada')->count();
        $newLeadsThisWeek = Lead::where('created_at', '>=', now()->startOfWeek())->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();
        $todayAppointments = Appointment::whereDate('scheduled_date', today())->count();

        $closedDeals = Lead::where('status', 'cerrado_ganado')->count();
        $conversionRate = $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0;

        // Gráficos
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

        $recentLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('modulos.reportes.index', compact(
            'totalProperties',
            'totalAppointments',
            'totalLeads',
            'totalUsers',
            'activeProperties',
            'newLeadsThisWeek',
            'completedAppointments',
            'todayAppointments',
            'conversionRate',
            'closedDeals',
            'propertiesByCategory',
            'appointmentsByStatus',
            'leadsByStatus',
            'topAsesores',
            'recentLogs'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $data = [
            'titulo' => 'Reporte de Métricas - ' . now()->setTimezone('America/Caracas')->format('d/m/Y'),
            'fecha_generacion' => now()->setTimezone('America/Caracas')->format('d/m/Y H:i:s'),
            'metricas' => [
                'Total Propiedades' => Property::count(),
                'Propiedades Activas' => Property::where('status', 'publicada')->count(),
                'Total Leads' => Lead::count(),
                'Leads Nuevos (semana)' => Lead::where('created_at', '>=', now()->startOfWeek())->count(),
                'Total Citas' => Appointment::count(),
                'Citas Completadas' => Appointment::where('status', 'completed')->count(),
                'Citas Pendientes' => Appointment::where('status', 'pending')->count(),
                'Tasa de Conversión' => $this->getConversionRate() . '%',
                'Total Usuarios' => User::count(),
            ],
            'propiedades_por_categoria' => $this->getPropertiesByCategory(),
            'citas_por_estado' => $this->getAppointmentsByStatus(),
            'leads_por_estado' => $this->getLeadsByStatus(),
            'top_asesores' => $this->getTopAsesores(),
        ];

        if ($format === 'csv') {
            return $this->exportCsv($data);
        }

        return $this->exportPdf($data);
    }

    private function exportCsv($data)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=reporte_' . date('Y-m-d') . '.csv',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['REPORTE DE MÉTRICAS']);
            fputcsv($file, ['Fecha Generación:', $data['fecha_generacion']]);
            fputcsv($file, []);

            fputcsv($file, ['MÉTRICAS PRINCIPALES']);
            foreach ($data['metricas'] as $key => $value) {
                fputcsv($file, [$key, $value]);
            }
            fputcsv($file, []);

            fputcsv($file, ['PROPIEDADES POR CATEGORÍA']);
            fputcsv($file, ['Categoría', 'Cantidad']);
            foreach ($data['propiedades_por_categoria'] as $item) {
                fputcsv($file, [$item['label'], $item['value']]);
            }
            fputcsv($file, []);

            fputcsv($file, ['CITAS POR ESTADO']);
            fputcsv($file, ['Estado', 'Cantidad']);
            foreach ($data['citas_por_estado'] as $item) {
                fputcsv($file, [$item['label'], $item['value']]);
            }
            fputcsv($file, []);

            fputcsv($file, ['LEADS POR ESTADO']);
            fputcsv($file, ['Estado', 'Cantidad']);
            foreach ($data['leads_por_estado'] as $item) {
                fputcsv($file, [$item['label'], $item['value']]);
            }
            fputcsv($file, []);

            fputcsv($file, ['TOP ASESORES']);
            fputcsv($file, ['Asesor', 'Leads Captados']);
            foreach ($data['top_asesores'] as $item) {
                fputcsv($file, [$item['name'], $item['leads']]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPdf($data)
    {
        // Verificar si dompdf está instalado
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            // Si no está instalado, devolver un mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Dompdf no está instalado. Ejecuta: composer require barryvdh/laravel-dompdf'
            ], 500);
        }

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modulos.reportes.export-pdf', compact('data'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download('reporte_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==========================================
    // MÉTODOS AUXILIARES
    // ==========================================

    private function getConversionRate()
    {
        $totalLeads = Lead::count();
        $closedDeals = Lead::where('status', 'cerrado_ganado')->count();
        return $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0;
    }

    private function getPropertiesByCategory()
    {
        return Property::select('category_id', DB::raw('count(*) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(function($item) {
                return [
                    'label' => $item->category ? $item->category->name : 'Sin categoría',
                    'value' => $item->total
                ];
            })->toArray();
    }

    private function getAppointmentsByStatus()
    {
        $statusMap = [
            'pending' => 'Pendientes',
            'confirmed' => 'Confirmadas',
            'completed' => 'Completadas',
            'cancelled' => 'Canceladas',
            'reprogrammed' => 'Reprogramadas'
        ];

        return Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function($item) use ($statusMap) {
                return [
                    'label' => $statusMap[$item->status] ?? ucfirst($item->status),
                    'value' => $item->total
                ];
            })->toArray();
    }

    private function getLeadsByStatus()
    {
        $statusMap = [
            'nuevo' => 'Nuevos',
            'contactado' => 'Contactados',
            'en_negociacion' => 'En Negociación',
            'cerrado_ganado' => 'Cerrados',
            'cerrado_perdido' => 'Perdidos',
            'inactivo' => 'Inactivos'
        ];

        return Lead::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function($item) use ($statusMap) {
                return [
                    'label' => $statusMap[$item->status] ?? ucfirst($item->status),
                    'value' => $item->total
                ];
            })->toArray();
    }

    private function getTopAsesores()
    {
        return User::role('Asesor Inmobiliario')
            ->withCount('leads')
            ->orderBy('leads_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'leads' => $user->leads_count
                ];
            })->toArray();
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

            default:
                $data = [];
        }

        return response()->json($data);
    }
}
