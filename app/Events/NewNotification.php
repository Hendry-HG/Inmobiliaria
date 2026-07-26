<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento broadcasting que se dispara cuando una nueva notificacion es creada
 * para un usuario especifico.
 *
 * Flujo del broadcast:
 * 1. Se emite el evento desde el servicio/observador correspondiente pasando
 *    el ID del usuario destino, el conteo actualizado de notificaciones y
 *    los datos de la notificacion.
 * 2. El evento se transmite por un canal privado 'user.{id}' lo que garantiza
 *    que solo el usuario propietario reciba la notificacion en tiempo real.
 * 3. El nombre del evento en el canal WebSocket es 'new-notification'.
 * 4. El payload contiene el conteo total de notificaciones no leidas y el
 *    arreglo con los datos de la notificacion recien creada.
 *
 * Uso en el frontend:
 * Escuchar el canal privado 'user.{id}' y el evento '.new-notification'
 * para actualizar la campana de notificaciones y mostrar un toast/alerta.
 */
class NewNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Constructor del evento.
     *
     * @param int   $userId       ID del usuario que recibira la notificacion por WebSocket.
     * @param int   $count        Numero total de notificaciones no leidas del usuario.
     * @param array $notification Arreglo con los datos de la notificacion (title, message, type, url, data).
     */
    public function __construct(
        public int $userId,
        public int $count,
        public array $notification
    ) {}

    /**
     * Define el o los canales de broadcast donde se emite el evento.
     * Utiliza un canal privado basado en el ID del usuario para asegurar
     * que la notificacion solo llegue al usuario destino.
     *
     * @return array<int, \Illuminate\Broadcasting\PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    /**
     * Define el nombre del evento que se transmite por el canal WebSocket.
     * Los clientes escuchan este nombre especifico para recibir la notificacion.
     *
     * @return string Nombre del evento: 'new-notification'.
     */
    public function broadcastAs(): string
    {
        return 'new-notification';
    }

    /**
     * Construye el payload que se envia al cliente a traves del WebSocket.
     *
     * @return array{count: int, notification: array} Datos serializados del evento.
     */
    public function broadcastWith(): array
    {
        return [
            'count' => $this->count,
            'notification' => $this->notification,
        ];
    }
}
