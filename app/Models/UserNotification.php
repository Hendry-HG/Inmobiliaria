<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que almacena las notificaciones internas de un usuario.
 *
 * A diferencia de las notificaciones de Laravel (tabla notifications), este modelo
 * gestiona notificaciones personalizadas del sistema con campos adicionales como
 * tipo, URL de redireccion y datos extra. Las notificaciones se transmiten en
 * tiempo real via WebSockets (evento NewNotification) y se almacenan para
 * consulta historica en el panel de notificaciones.
 *
 * Relaciones:
 * - user: Usuario que recibe la notificacion.
 *
 * Campos clave:
 * - title: Titulo breve de la notificacion.
 * - message: Cuerpo del mensaje de la notificacion.
 * - type: Categoria visual ('info', 'success', 'warning', 'error').
 * - url: URL a la que redirige al hacer clic en la notificacion.
 * - data: Datos adicionales en formato JSON.
 * - read_at: Timestamp de cuando fue leida (null si no leida).
 *
 * Scopes:
 * - unread: Filtra notificaciones no leidas.
 * - read: Filtra notificaciones leidas.
 *
 * @package App\Models
 */
class UserNotification extends Model
{
    use HasFactory;

    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'url',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Obtiene el usuario que recibe la notificacion.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marca la notificacion como leida estableciendo read_at al timestamp actual.
     *
     * @return void
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Verifica si la notificacion ha sido leida.
     *
     * @return bool true si la notificacion tiene read_at establecido (fue leida).
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Scope que filtra solo las notificaciones no leidas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro de no leidas.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope que filtra solo las notificaciones leidas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro de leidas.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
