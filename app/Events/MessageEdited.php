<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de edición de mensaje en el sistema de chat en tiempo real.
 *
 * Se dispara cuando un usuario edita el contenido de un mensaje existente.
 * Implementa ShouldBroadcast para transmitir la actualización a través de WebSocket
 * al canal privado del destinatario.
 *
 * Canal de difusión: chat.user.{receiver_id}
 * Nombre del evento: MessageEdited
 *
 * Datos enviados al canal:
 * - message_id: ID del mensaje editado.
 * - conversation_id: ID de la conversación donde se realizó la edición.
 * - new_content: Nuevo contenido del mensaje después de la edición.
 * - receiver_id: ID del destinatario que recibirá la notificación.
 *
 * @package App\Events
 */
class MessageEdited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var int ID del mensaje que fue editado. */
    public $message_id;

    /** @var int ID de la conversación donde se encuentra el mensaje. */
    public $conversation_id;

    /** @var string Nuevo contenido del mensaje después de la edición. */
    public $new_content;

    /** @var int ID del destinatario que recibirá la notificación de edición. */
    public $receiver_id;

    /**
     * Crea una nueva instancia del evento.
     *
     * @param int $message_id ID del mensaje editado.
     * @param int $conversation_id ID de la conversación.
     * @param string $new_content Nuevo contenido del mensaje.
     * @param int $receiver_id ID del destinatario del evento.
     */
    public function __construct($message_id, $conversation_id, $new_content, $receiver_id)
    {
        $this->message_id = $message_id;
        $this->conversation_id = $conversation_id;
        $this->new_content = $new_content;
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
        return 'MessageEdited';
    }
}