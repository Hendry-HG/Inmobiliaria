<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificacion que se envia al asesor inmobiliario cuando recibe un nuevo
 * mensaje en el sistema de chat de la plataforma.
 *
 * Momento de disparo:
 * Se crea cuando un usuario (lead, cliente o asesor) envia un mensaje dentro
 * de una conversacion activa. La notificacion se envia al destinatario del mensaje.
 *
 * Canal de entrega:
 * Se almacena exclusivamente en la base de datos (tabla 'notifications') para ser
 * consultada desde el panel de notificaciones del usuario.
 *
 * Datos que transporta:
 * - Nombre del remitente del mensaje.
 * - Vista previa (preview) del contenido del mensaje.
 * - Identificador de la conversacion para acceder directamente a ella.
 */
class NewMessageNotification extends Notification
{
    use Queueable;

    /**
     * Constructor de la notificacion.
     *
     * @param string $senderName     Nombre del usuario que envio el mensaje.
     * @param string $preview        Vista previa o fragmento del contenido del mensaje.
     * @param int    $conversationId Identificador de la conversacion a la que pertenece el mensaje.
     */
    public function __construct(
        public string $senderName,
        public string $preview,
        public int $conversationId
    ) {}

    /**
     * Define los canales por los cuales se entrega la notificacion.
     * Utiliza unicamente el canal de base de datos.
     *
     * @param  object $notifiable Modelo de usuario que recibira la notificacion.
     * @return array<int, string> Canales de entrega ['database'].
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Serializa la notificacion a un arreglo para almacenarlo en la base de datos.
     *
     * Estructura del arreglo:
     * - title:   Titulo corto de la notificacion.
     * - message: Mensaje con el nombre del remitente y la vista previa del mensaje.
     * - type:    Tipo visual para el frontend ('info' para notificacion informativa).
     * - url:     Ruta de redireccion al hacer clic (chat/index abre la interfaz de chat).
     * - data:    Arreglo con el remitente, el ID de la conversacion y la preview.
     *
     * @param  object $notifiable Modelo de usuario que recibira la notificacion.
     * @return array<string, mixed> Arreglo serializado para la tabla notifications.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nuevo Mensaje',
            'message' => "{$this->senderName}: \"{$this->preview}\"",
            'type' => 'info',
            'url' => route('chat.index'),
            'data' => [
                'sender_name' => $this->senderName,
                'conversation_id' => $this->conversationId,
                'preview' => $this->preview,
            ],
        ];
    }
}
