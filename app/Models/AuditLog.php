<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que almacena el registro de auditoria de acciones realizadas en el sistema.
 *
 * Cada entrada en audit_logs captura quién realizó una acción, qué se hizo,
 * sobre qué entidad, los valores antes y después del cambio, y metadatos
 * de la petición HTTP (IP, user agent, URL).
 *
 * Utilizado por:
 * - AuditTrait: Registra automaticamente creaciones, actualizaciones y eliminaciones.
 * - Listeners (LogSuccessfulLogin, LogSuccessfulLogout): Registran sesiones.
 * - Controladores: Registran acciones criticas manualmente.
 *
 * Relaciones:
 * - user: Usuario que realizó la acción.
 * - subject: Entidad afectada (polimórfico: Property, User, Appointment, etc.).
 *
 * Campos clave:
 * - action/event: Tipo de evento ('created', 'updated', 'deleted', 'login', 'logout').
 * - old_values/new_values: JSON con valores anteriores y posteriores al cambio.
 * - subject_type/subject_id: Identificación polimórfica de la entidad afectada.
 * - ip_address/user_agent/url: Metadatos de la petición HTTP para rastreo.
 *
 * @package App\Models
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_type',
        'action',
        'event',
        'subject_type',
        'subject_id',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'tags',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Obtiene el usuario que realizó la acción de auditoría.
     *
     * @return BelongsTo Relacion con el modelo User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtiene la entidad afectada por la acción de auditoría (polimórfico).
     *
     * Permite acceder al modelo original (Property, User, Appointment, etc.)
     * sobre el cual se realizó la acción.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo Relación polimórfica con la entidad.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Obtiene una etiqueta legible en español para la acción de auditoría.
     *
     * Mapea los identificadores internos ('created', 'updated', etc.) a sus
     * equivalentes en español para mostrar en la interfaz de usuario.
     *
     * @return string Etiqueta de la acción (ej: 'Creación', 'Actualización').
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            'restored' => 'Restauración',
            'login' => 'Inicio de Sesión',
            'logout' => 'Cierre de Sesión',
        ];

        $key = $this->action ?? $this->event;
        return $labels[$key] ?? $key ?? 'Desconocida';
    }

    /**
     * Obtiene las clases CSS de color para la acción de auditoría.
     *
     * Retorna clases de Tailwind CSS que definen el color de fondo, texto y borde
     * según el tipo de acción, para usar en badges/etiquetas en la interfaz.
     *
     * @return string Cadena de clases CSS de Tailwind para estilizar la acción.
     */
    public function getActionColorAttribute(): string
    {
        $colors = [
            'created' => 'bg-green-50 text-green-700 border-green-200',
            'updated' => 'bg-blue-50 text-blue-700 border-blue-200',
            'deleted' => 'bg-red-50 text-red-700 border-red-200',
            'restored' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'login' => 'bg-purple-50 text-purple-700 border-purple-200',
            'logout' => 'bg-gray-50 text-gray-700 border-gray-200',
        ];

        $key = $this->action ?? $this->event;
        return $colors[$key] ?? 'bg-slate-50 text-slate-700 border-slate-200';
    }

    /**
     * Scope que filtra registros de auditoría por usuario específico.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @param int $userId ID del usuario por el cual filtrar.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro aplicado.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope que filtra registros de auditoría por tipo de acción.
     *
     * Busca en ambos campos 'action' y 'event' para compatibilidad con
     * diferentes formatos de registro.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @param string $action Tipo de acción a filtrar ('created', 'updated', 'login', etc.).
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro aplicado.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action)->orWhere('event', $action);
    }

    /**
     * Scope que filtra registros de auditoría por rango de fechas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @param string|null $from Fecha de inicio (formato Y-m-d). Null para sin límite inferior.
     * @param string|null $to Fecha de fin (formato Y-m-d). Null para sin límite superior.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro de rango aplicado.
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope que filtra registros de auditoría por tipo de entidad afectada.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @param string $type Clase completa de la entidad (ej: 'App\Models\Property').
     * @param int|null $id ID específico de la entidad. Null para todas las instancias del tipo.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro de entidad aplicado.
     */
    public function scopeBySubject($query, $type, $id = null)
    {
        $query->where('subject_type', $type);
        if ($id) {
            $query->where('subject_id', $id);
        }
        return $query;
    }
}
