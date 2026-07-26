<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Clase encargada de exportar los registros de auditoria a formato Excel (XLSX).
 *
 * Implementa las interfaces de Maatwebsite\Excel para definir la coleccion de datos,
 * los encabezados de columna, el mapeo de cada fila, los estilos de la hoja y el
 * ajuste automatico del ancho de columnas.
 *
 * Flujo de exportacion:
 * 1. Se instancian los filtros opcionales (entidad, rango de fechas, usuario, accion).
 * 2. La coleccion se obtiene consultando la tabla audit_logs con relaciones precargadas.
 * 3. Cada fila se mapea con campos formateados (fechas en zona horaria de Caracas,
 *    etiquetas en espanol para acciones y entidades).
 * 4. Se aplican estilos al encabezado (fila 1) y se ajusta el ancho de columnas.
 */
class AuditLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * Filtro de entidad para acotar la exportacion a un tipo de modelo especifico.
     * Valores validos: users, properties, appointments, leads, services, categories, roles, system.
     *
     * @var string|null
     */
    protected $entity;

    /**
     * Fecha de inicio del rango de filtrado (formato YYYY-MM-DD).
     *
     * @var string|null
     */
    protected $dateFrom;

    /**
     * Fecha de fin del rango de filtrado (formato YYYY-MM-DD).
     *
     * @var string|null
     */
    protected $dateTo;

    /**
     * Identificador del usuario para filtrar registros de auditoria.
     *
     * @var int|null
     */
    protected $userId;

    /**
     * Texto de busqueda para filtrar por tipo de accion o evento registrado.
     *
     * @var string|null
     */
    protected $action;

    /**
     * Titulo del reporte que se muestra en la interfaz de exportacion.
     *
     * @var string
     */
    protected $title;

    /**
     * Constructor de la clase de exportacion.
     *
     * @param string|null $entity   Entidad/modelo por el cual se filtra (null para todas).
     * @param string|null $dateFrom Fecha de inicio del rango (null para sin limite inferior).
     * @param string|null $dateTo   Fecha de fin del rango (null para sin limite superior).
     * @param int|null    $userId   ID del usuario propietario de los registros (null para todos).
     * @param string|null $action   Texto de busqueda para la accion/evento (null para todas).
     * @param string      $title    Titulo descriptivo del reporte.
     */
    public function __construct(?string $entity = null, ?string $dateFrom = null, ?string $dateTo = null, ?int $userId = null, ?string $action = null, string $title = 'Reporte de Auditoría')
    {
        $this->entity = $entity;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->userId = $userId;
        $this->action = $action;
        $this->title = $title;
    }

    /**
     * Construye y ejecuta la consulta que obtiene los registros de auditoria
     * aplicando los filtros activos: entidad, rango de fechas, usuario y accion.
     *
     * La entidad 'system' incluye registros con subject_type nulo o asociados a
     * modelos de configuracion del sitio, roles y permisos.
     * Los resultados se ordenan de manera descendente por fecha de creacion.
     *
     * @return \Illuminate\Database\Eloquent\Collection Coleccion de modelos AuditLog filtrados.
     */
    public function collection()
    {
        $query = AuditLog::with('user');

        if ($this->entity && $this->entity !== 'all') {
            // Mapa de nombres de ruta (slugs) a nombres de clase de modelo Eloquent
            $entityMap = [
                'users' => 'User',
                'properties' => 'Property',
                'appointments' => 'Appointment',
                'leads' => 'Lead',
                'services' => 'Service',
                'categories' => 'Category',
                'roles' => 'Role',
                'system' => null,
            ];

            if ($this->entity === 'system') {
                // Registros del sistema: subject_type nulo o perteneciente a configuracion/roles/permisos
                $query->where(function ($q) {
                    $q->whereNull('subject_type')
                      ->orWhere('subject_type', 'like', '%SiteConfiguration%')
                      ->orWhere('subject_type', 'like', '%Role%')
                      ->orWhere('subject_type', 'like', '%Permission%');
                });
            } elseif (isset($entityMap[$this->entity])) {
                // Filtrado por tipo de entidad especifico usando LIKE sobre subject_type
                $query->where('subject_type', 'like', '%' . $entityMap[$this->entity] . '%');
            }
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }
        if ($this->action) {
            // Busqueda en los campos 'action' y 'event' para mayor cobertura
            $query->where(function ($q) {
                $q->where('action', 'like', '%' . $this->action . '%')
                  ->orWhere('event', 'like', '%' . $this->action . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Define los encabezados de las columnas del archivo Excel exportado.
     * El orden corresponde exactamente al orden de los campos retornados en map().
     *
     * @return array<int, string> Lista de titulos de columna.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Hora',
            'Usuario',
            'Email',
            'Acción',
            'Entidad',
            'Descripción',
            'IP',
            'URL',
        ];
    }

    /**
     * Transforma un registro individual de AuditLog en un arreglo de valores planos
     * que seran escritos como fila en la hoja de calculo.
     *
     * Mapeos realizados:
     * - subject_type se convierte al nombre corto de la entidad (o 'Sistema' si es nulo).
     * - Las fechas se convierten a la zona horaria de America/Caracas y se formatean
     *   por separado en fecha (d/m/Y) y hora (H:i:s).
     * - Las acciones se traducen a etiquetas en espanol mediante getActionLabel().
     * - Las entidades se traducen a etiquetas en espanol mediante getEntityLabel().
     *
     * @param  \App\Models\AuditLog $log Registro de auditoria a transformar.
     * @return array<int, string|null> Arreglo con los valores de la fila.
     */
    public function map($log): array
    {
        $entity = $log->subject_type ? class_basename($log->subject_type) : 'Sistema';

        $date = $log->created_at ? $log->created_at->setTimezone('America/Caracas') : null;

        return [
            $log->id,
            $date ? $date->format('d/m/Y') : 'N/A',
            $date ? $date->format('H:i:s') : 'N/A',
            $log->user ? $log->user->full_name : 'Sistema',
            $log->user ? $log->user->email : '',
            $this->getActionLabel($log->action ?? $log->event ?? ''),
            $this->getEntityLabel($entity),
            $log->description ?? 'N/A',
            $log->ip_address ?? 'N/A',
            $log->url ?? 'N/A',
        ];
    }

    /**
     * Define los estilos visuales de la hoja de calculo.
     * Aplica negrita y tamano de fuente 11 a la fila de encabezados (fila 1).
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Hoja de calculo activa.
     * @return array<int, array<string, array<string, mixed>>> Arreglo de estilos indexado por fila.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

    /**
     * Convierte el identificador tecnico de una accion de auditoria a su etiqueta
     * legible en espanol. Si la accion no tiene una traduccion definida, retorna
     * la cadena con la primera letra en mayuscula.
     *
     * @param  string $action Identificador de la accion (created, updated, deleted, login, logout, etc.).
     * @return string         Etiqueta traducida en espanol.
     */
    private function getActionLabel(string $action): string
    {
        return match($action) {
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            'login' => 'Inicio de Sesión',
            'logout' => 'Cierre de Sesión',
            default => ucfirst($action),
        };
    }

    /**
     * Convierte el nombre de la clase de modelo Eloquent a su etiqueta legible
     * en espanol. Si la entidad no tiene una traduccion definida, retorna el
     * nombre original sin modificar.
     *
     * @param  string $entity Nombre de la clase de modelo (User, Property, Appointment, etc.).
     * @return string         Etiqueta traducida en espanol.
     */
    private function getEntityLabel(string $entity): string
    {
        return match($entity) {
            'User' => 'Usuario',
            'Property' => 'Propiedad',
            'Appointment' => 'Cita',
            'Lead' => 'Lead',
            'Service' => 'Servicio',
            'Category' => 'Categoría',
            'Role' => 'Rol',
            'SiteConfiguration' => 'Config. Sitio',
            default => $entity,
        };
    }
}
