<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que almacena snapshots (instantaneas) de reportes gerenciales generados.
 *
 * Cada registro representa un reporte generado para un usuario en un periodo
 * especifico, incluyendo los datos en JSON, la ruta del archivo exportado
 * y el estado de envio. Permite historial de reportes y re-descarga sin
 * regeneracion.
 *
 * Relaciones:
 * - user: Usuario para el cual se genero el reporte.
 *
 * Campos clave:
 * - report_type: Tipo de reporte (ej: 'propiedades', 'citas', 'leads').
 * - period: Periodo del reporte (ej: '2024-01', '2024-Q1').
 * - data: Datos del reporte en formato JSON.
 * - file_path: Ruta del archivo exportado (PDF, Excel, etc.).
 * - status: Estado del reporte ('generated', 'sent', 'failed').
 * - sent_at: Timestamp de cuando fue enviado al usuario.
 *
 * @package App\Models
 */
class ReportSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'period',
        'data',
        'file_path',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'data' => 'array', // Convertir JSON a Array automáticamente
        'sent_at' => 'datetime',
    ];

    /**
     * Obtiene el usuario para el cual se genero el reporte.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope que filtra reportes por tipo de reporte.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @param string $type Tipo de reporte a filtrar.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro de tipo aplicado.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('report_type', $type);
    }
}
