<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa un mensaje dentro de una conversacion de chat.
 *
 * Cada mensaje esta asociado a una conversacion y al usuario que lo envio.
 * Los mensajes se transmiten en tiempo real via WebSockets (evento NewMessage)
 * y se almacenan en la base de datos para persistencia y consulta historica.
 *
 * Relaciones:
 * - conversation: Conversacion a la que pertenece el mensaje.
 * - user: Usuario que envio el mensaje.
 *
 * Campos clave:
 * - content: Texto del mensaje enviado.
 * - is_read: Indica si el destinatario ha leido el mensaje.
 * - read_at: Timestamp de cuando el mensaje fue leido (null si no leido).
 *
 * @package App\Models
 */
class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'conversation_id', 'user_id', 'content', 'is_read', 'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Obtiene la conversacion a la que pertenece el mensaje.
     *
     * @return BelongsTo Relacion con el modelo Conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Obtiene el usuario que envio el mensaje.
     *
     * @return BelongsTo Relacion con el modelo User (autor del mensaje).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
