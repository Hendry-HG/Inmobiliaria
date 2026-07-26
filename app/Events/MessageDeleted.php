<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de eliminación de mensaje en el sistema de chat en tiempo real.
 *
 * Se dispara cuando un usuario elimina un mensaje de una conversación.
 * Implementa ShouldBroadcast para notificar la eliminación a través de WebSocket
 * al canal privado del destinatario.
 *
 * Canal de difusión: chat.user.{receiver_id}
 * Nombre del evento: MessageDeleted
 *
 * Datos enviados al canal:
 * - message_id: ID del mensaje eliminado.
 * - conversation_id: ID de la conversación donde se realizó la eliminación.
 * - receiver_id: ID del destinatario que recibirá la notificación.
 *
 * @package App\Events
 */
class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var int ID del mensaje que fue eliminado. */
    public $message_id;

    /** @var int ID de la conversación donde se encuentra el mensaje eliminado. */
    public $conversation_id;

    /** @var int ID del destinatario que recibirá la notificación de eliminación. */
    public $receiver_id;

    /**
     * Crea una nueva instancia del evento.
     *
     * @param int $message_id ID del mensaje eliminado.
     * @param int $conversation_id ID de la conversación.
     * @param int $receiver_id ID del destinatario del evento.
     */
    public function __construct($message_id, $conversation_id, $receiver_id)
    {
        $this->message_id = $message_id;
        $this->conversation_id = $conversation_id;
        $this->receiver_id = $receiver_id;
    }

    public function broadcastOn()
    {
        return new Channel('chat.user.' . $this->receiver_id);
    }

    public function broadcastAs()
    {
        return 'MessageDeleted';
    }
}