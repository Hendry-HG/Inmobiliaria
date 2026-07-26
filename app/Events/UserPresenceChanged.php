<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de cambio de presencia de usuario en tiempo real.
 *
 * Se dispara cuando un usuario cambia su estado de conexión (online/offline)
 * o actualiza su última vez visto. Implementa ShouldBroadcast para transmitir
 * el estado de presencia a través de WebSocket a los canales de presencia.
 *
 * Canal de difusión: presence.{user_id}
 * Nombre del evento: presence.changed
 *
 * Datos enviados al canal:
 * - user_id: ID del usuario cuyo estado cambió.
 * - is_online: Estado de conexión del usuario (true/false).
 * - last_seen_at: Última vez que el usuario fue visto (formato relativo).
 *
 * @package App\Events
 */
class UserPresenceChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var User Modelo del usuario cuyo estado de presencia cambió. */
    public $user;

    /**
     * Crea una nueva instancia del evento.
     *
     * @param User $user Usuario cuyo estado de presencia ha cambiado.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Define el canal de difusión del evento.
     *
     * @return array Array con el canal de presencia del usuario.
     */
    public function broadcastOn()
    {
        return [new Channel('presence.' . $this->user->id)];
    }

    /**
     * Define los datos que se transmitirán por WebSocket.
     *
     * @return array Datos serializados del estado de presencia del usuario.
     */
    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'is_online' => $this->user->is_online,
            'last_seen_at' => $this->user->last_seen_at?->diffForHumans()
        ];
    }

    /**
     * Define el nombre del evento que se transmitirá por WebSocket.
     *
     * @return string Nombre del evento para el cliente JavaScript.
     */
    public function broadcastAs()
    {
        return 'presence.changed';
    }
}
