<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de indicador de escritura en tiempo real.
 *
 * Se dispara cuando un usuario comienza o deja de escribir en un chat.
 * Implementa ShouldBroadcast para transmitir el evento a través de WebSocket
 * al canal privado del destinatario.
 *
 * Canal de difusión: chat.user.{receiver_id}
 * Nombre del evento: UserTyping
 *
 * Datos enviados al canal:
 * - user_id: ID del usuario que está escribiendo.
 * - user_name: Nombre del usuario que está escribiendo.
 * - conversation_id: ID de la conversación actual.
 * - is_typing: Estado de escritura (true/false).
 * - receiver_id: ID del destinatario que recibirá el evento.
 *
 * @package App\Events
 */
class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var int ID del usuario que está escribiendo. */
    public $user_id;

    /** @var string Nombre del usuario que está escribiendo. */
    public $user_name;

    /** @var int ID de la conversación actual. */
    public $conversation_id;

    /** @var bool Indica si el usuario está escribiendo (true) o dejó de escribir (false). */
    public $is_typing;

    /** @var int ID del destinatario que recibirá el evento de escritura. */
    public $receiver_id;

    /**
     * Crea una nueva instancia del evento.
     *
     * @param User $user Usuario que está escribiendo en el chat.
     * @param int $conversation_id ID de la conversación en curso.
     * @param bool $is_typing Estado de escritura del usuario.
     * @param int $receiver_id ID del destinatario del evento.
     */
    public function __construct(User $user, $conversation_id, $is_typing, $receiver_id)
    {
        $this->user_id = $user->id;
        $this->user_name = $user->name;
        $this->conversation_id = $conversation_id;
        $this->is_typing = $is_typing;
        $this->receiver_id = $receiver_id;
    }

    /**
     * Define el canal de difusión del evento.
     *
     * @return Channel Canal privado dirigido al destinatario del mensaje.
     */
    public function broadcastOn()
    {
        return new Channel('chat.user.' . $this->receiver_id);
    }

    /**
     * Define el nombre del evento que se transmitirá por WebSocket.
     *
     * @return string Nombre del evento para el cliente JavaScript.
     */
    public function broadcastAs()
    {
        return 'UserTyping';
    }
}