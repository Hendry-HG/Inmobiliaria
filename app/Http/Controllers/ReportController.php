<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Reportes
 *
 * Responsable de generar y exportar reportes metricos del sistema inmobiliario.
 * Proporciona estadisticas globales de propiedades, leads, citas y asesores.
 * Soporta exportacion en formato CSV y PDF.
 *
 * @package App\Http\Controllers
 */
class ReportController extends Controller
{
    /**
     * Constructor del controlador.
     * Aplica middleware de verificacion de permiso 'ver reportes'.
     */
    public function __construct()
    {
        $this->middleware('permission:ver reportes');
    }

    /**
     * Muestra la vista principal de reportes con metricas consolidadas.
     *
     * Recopila contadores totales de propiedades, citas, leads y usuarios.
     * Calcula la tasa de conversion (leads cerrados vs total).
     * Genera datos para graficos: propiedades por categoria, citas por estado,
     * leads por estado y ranking de asesores.
     *
     * Flujo de datos:
     * 1. Consulta contadores globales de cada modelo
     * 2. Agrega datos para graficos usando agrupaciones SQL
     * 3. Calcula tendencias reales vs el mes anterior
     * 4. Obtiene los 5 asesores con mas leads captados y su rendimiento
     * 5. Retorna la vista con todas las variables compactadas
     *
     * @return \Illuminate\View\View
     */
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

        // Tendencias reales vs mes anterior
        $propsThisMonth = Property::where('created_at', '>=', now()->startOfMonth())->count();
        $propsLastMonth = Property::where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())->count();
        $trendProperties = $propsLastMonth > 0 ? round((($propsThisMonth - $propsLastMonth) / $propsLastMonth) * 100, 1) : 0;

        $leadsThisMonth = Lead::where('created_at', '>=', now()->startOfMonth())->count();
        $leadsLastMonth = Lead::where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())->count();
        $trendLeads = $leadsLastMonth > 0 ? round((($leadsThisMonth - $leadsLastMonth) / $leadsLastMonth) * 100, 1) : 0;

        $apptsThisMonth = Appointment::where('created_at', '>=', now()->startOfMonth())->count();
        $apptsLastMonth = Appointment::where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())->count();
        $trendAppointments = $apptsLastMonth > 0 ? round((($apptsThisMonth - $apptsLastMonth) / $apptsLastMonth) * 100, 1) : 0;

        $closedThisMonth = Lead::where('status', 'cerrado_ganado')
            ->where('created_at', '>=', now()->startOfMonth())->count();
        $convThisMonth = $leadsThisMonth > 0 ? round(($closedThisMonth / $leadsThisMonth) * 100, 1) : 0;
        $closedLastMonth = Lead::where('status', 'cerrado_ganado')
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())->count();
        $convLastMonth = $leadsLastMonth > 0 ? round(($closedLastMonth / $leadsLastMonth) * 100, 1) : 0;
        $trendConversion = round($convThisMonth - $convLastMonth, 1);

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

        $topAsesoresUsers = User::role('Asesor Inmobiliario')
            ->withCount([
                'leads',
                'leads as closed_leads_count' => fn ($q) => $q->where('status', 'cerrado_ganado'),
                'appointmentsAsAsesor as appointments_count',
                'properties as properties_count',
            ])
            ->orderBy('leads_count', 'desc')
            ->limit(5)
            ->get();

        $maxLeads = $topAsesoresUsers->max('leads_count') ?? 1;

        $topAsesores = $topAsesoresUsers->map(function($user) use ($maxLeads) {
            $conversion = $user->leads_count > 0
                ? round(($user->closed_leads_count / $user->leads_count) * 100, 1)
                : 0;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'leads' => $user->leads_count,
                'closed_leads' => $user->closed_leads_count,
                'appointments' => $user->appointments_count,
                'properties' => $user->properties_count,
                'conversion' => $conversion,
                'rendimiento' => $maxLeads > 0 ? round(($user->leads_count / $maxLeads) * 100, 1) : 0,
            ];
        });

        $topAsesoresJson = $topAsesores->map(function($a) {
            return [
                'full_name' => $a['full_name'] ?? ($a['name'] ?? ''),
                'total' => $a['leads'] ?? 0,
                'rendimiento' => $a['rendimiento'] ?? 0,
            ];
        })->values();

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
            'trendProperties',
            'trendLeads',
            'trendAppointments',
            'trendConversion',
            'propertiesByCategory',
            'appointmentsByStatus',
            'leadsByStatus',
            'topAsesores',
            'topAsesoresJson'
        ));
    }

    /**
     * Exporta el reporte de metricas en el formato solicitado (CSV o PDF).
     *
     * Recopila todas las metricas del sistema y las prepara en una estructura
     * unificada para exportacion. Delega la generacion del archivo al metodo
     * correspondiente segun el formato seleccionado.
     *
     * Flujo de datos:
     * 1. Obtiene el formato solicitado del request (default: csv)
     * 2. Compila metricas, propiedades por categoria, citas por estado,
     *    leads por estado y top asesores en un array estructurado
     * 3. Delega a exportCsv() o exportPdf() segun el formato
     *
     * @param \Illuminate\Http\Request $request
     * @param string $request->format Formato de exportacion: 'csv' o 'pdf'
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Http\JsonResponse
     */
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

    /**
     * Genera y descarga el reporte en formato CSV con codificacion UTF-8.
     *
     * Flujo de datos:
     * 1. Prepara headers HTTP para descarga de archivo CSV
     * 2. Abre stream php://output y escribe BOM UTF-8
     * 3. Escribe secciones: metricas principales, propiedades por categoria,
     *    citas por estado, leads por estado y top asesores
     * 4. Retorna respuesta streaming con el archivo generado
     *
     * @param array $data Estructura de datos del reporte con metricas y graficos
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
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

    /**
     * Genera y descarga el reporte en formato PDF usando DomPDF.
     *
     * Verifica que la libreria barryvdh/laravel-dompdf este instalada.
     * Si no esta disponible, retorna un error JSON. Utiliza la vista
     * 'modulos.reportes.export-pdf' como plantilla para el documento.
     *
     * Flujo de datos:
     * 1. Valida la existencia de la clase Pdf de DomPDF
     * 2. Carga la vista Blade con los datos del reporte
     * 3. Configura formato A4 vertical
     * 4. Genera la descarga del PDF con nombre basado en la fecha actual
     *
     * @param array $data Estructura de datos del reporte
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
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

    /**
     * Calcula la tasa de conversion de leads cerrados ganados.
     *
     * Flujo de datos:
     * 1. Cuenta el total de leads registrados en el sistema
     * 2. Cuenta los leads con estado 'cerrado_ganado'
     * 3. Calcula el porcentaje con un decimal de precision
     * 4. Retorna 0 si no existen leads para evitar division por cero
     *
     * @return float Tasa de conversion en porcentaje (ej: 25.3)
     */
    private function getConversionRate()
    {
        $totalLeads = Lead::count();
        $closedDeals = Lead::where('status', 'cerrado_ganado')->count();
        return $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0;
    }

    /**
     * Obtiene el conteo de propiedades agrupadas por categoria.
     *
     * Flujo de datos:
     * 1. Consulta propiedades agrupadas por category_id con conteo
     * 2. Carga la relacion 'category' para obtener el nombre
     * 3. Mapea cada resultado a un array con label (nombre) y value (cantidad)
     * 4. Maneja propiedades sin categoria asignada como 'Sin categoria'
     *
     * @return array Lista de categorias con sus respectivos conteos
     */
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

    /**
     * Obtiene el conteo de citas agrupadas por estado.
     *
     * Flujo de datos:
     * 1. Consulta citas agrupadas por campo 'status' con conteo
     * 2. Mapea los codigos de estado interno a etiquetas legibles en espanol
     * 3. Retorna array de objetos con label y value para graficos
     *
     * @return array Lista de estados de cita con sus respectivos conteos
     */
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

    /**
     * Obtiene el conteo de leads agrupados por estado.
     *
     * Flujo de datos:
     * 1. Consulta leads agrupados por campo 'status' con conteo
     * 2. Mapea los codigos de estado interno a etiquetas en espanol
     * 3. Incluye estados: nuevo, contactado, en_negociacion, cerrado_ganado,
     *    cerrado_perdido e inactivo
     *
     * @return array Lista de estados de lead con sus respectivos conteos
     */
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

    /**
     * Obtiene el ranking de los 5 asesores inmobiliarios con mas leads captados.
     *
     * Flujo de datos:
     * 1. Filtra usuarios con rol 'Asesor Inmobiliario'
     * 2. Cuenta los leads asociados a cada asesor mediante withCount
     * 3. Ordena de mayor a menor cantidad de leads
     * 4. Limita resultados a los 5 primeros
     *
     * @return array Lista de asesores con nombre y cantidad de leads
     */
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

    /**
     * Endpoint AJAX que retorna datos dinamicos para graficos del reporte.
     *
     * Flujo de datos:
     * 1. Recibe el tipo de datos solicitado via parametro 'type'
     * 2. Segun el tipo ejecuta la consulta correspondiente:
     *    - 'properties': Top 10 propiedades por vistas con titulo, precio e id
     *    - 'appointments': Citas agrupadas por estado con conteo
     *    - 'leads': Top 5 asesores por cantidad de leads captados
     * 3. Retorna los datos en formato JSON para renderizado en frontend
     *
     * @param \Illuminate\Http\Request $request
     * @param string $request->type Tipo de datos: 'properties', 'appointments' o 'leads'
     * @return \Illuminate\Http\JsonResponse
     */
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
                $asesores = User::role('Asesor Inmobiliario')
                    ->withCount('leads')
                    ->orderBy('leads_count', 'desc')
                    ->limit(5)
                    ->get();
                $max = $asesores->max('leads_count') ?? 1;
                $data = $asesores->map(function ($asesor) use ($max) {
                    return [
                        'id' => $asesor->id,
                        'asesor' => $asesor->full_name,
                        'total' => $asesor->leads_count,
                        'rendimiento' => $max > 0 ? round(($asesor->leads_count / $max) * 100, 1) : 0,
                    ];
                });
                break;

            default:
                $data = [];
        }

        return response()->json($data);
    }
}
