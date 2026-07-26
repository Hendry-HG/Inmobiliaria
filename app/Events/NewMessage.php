<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de nuevo mensaje en el sistema de chat en tiempo real.
 *
 * Se dispara cuando un usuario envía un nuevo mensaje en una conversación.
 * Implementa ShouldBroadcast para transmitir el mensaje a través de WebSocket
 * al canal privado del destinatario. Carga la relación del usuario que envió
 * el mensaje para incluir su nombre y avatar.
 *
 * Canal de difusión: chat.user.{receiver_id}
 * Nombre del evento: NewMessage
 *
 * Datos enviados al canal:
 * - message: Objeto con id, contenido, ID del autor, nombre del autor,
 *   avatar del autor, fecha de creación, hora formateada y estado de lectura.
 * - conversation_id: ID de la conversación donde se envió el mensaje.
 *
 * @package App\Events
 */
class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Message Modelo del mensaje enviado, con relación de usuario cargada. */
    public $message;

    /** @var int ID de la conversación donde se envió el mensaje. */
    public $conversation_id;

    /** @var int ID del destinatario que recibirá el evento. */
    public $receiver_id;

    /**
     * Crea una nueva instancia del evento.
     *
     * @param Message $message Modelo del mensaje enviado.
     * @param int $conversation_id ID de la conversación.
     * @param int $receiver_id ID del destinatario del evento.
     */
    public function __construct(Message $message, $conversation_id, $receiver_id)
    {
        $this->message = $message->load('user');
        $this->conversation_id = $conversation_id;
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
        return 'NewMessage';
    }

    /**
     * Define los datos que se transmitirán por WebSocket.
     *
     * @return array Datos serializados del mensaje y la conversación.
     */
    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'user_id' => $this->message->user_id,
                'user_name' => $this->message->user->name,
                'user_avatar' => $this->message->user->profile_photo_url,
                'created_at' => $this->message->created_at->toISOString(),
                'formatted_time' => $this->message->created_at->format('H:i'),
                'is_read' => $this->message->is_read,
            ],
            'conversation_id' => $this->conversation_id,
        ];
    }
}