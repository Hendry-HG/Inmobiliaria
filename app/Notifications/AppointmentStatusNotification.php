<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificacion que se envia al asesor inmobiliario cuando el estado de una
 * cita existente es modificado (confirmada, completada, cancelada o reprogramada).
 *
 * Momento de disparo:
 * Se crea cuando cualquiera de las partes (asesor o cliente) actualiza el estado
 * de una cita previamente agendada. La notificacion refleja el cambio de estado
 * anterior al nuevo estado.
 *
 * Canal de entrega:
 * Se almacena exclusivamente en la base de datos (tabla 'notifications') para ser
 * consultada desde el panel de notificaciones del usuario.
 *
 * Datos que transporta:
 * - Titulo de la propiedad asociada a la cita.
 * - Estado anterior de la cita.
 * - Estado nuevo de la cita.
 *
 * Logica de tipo visual:
 * - Si el nuevo estado es 'cancelled', se asigna tipo 'error' para resaltar
 *   la negatividad del cambio. Para cualquier otro estado se usa tipo 'info'.
 */
class AppointmentStatusNotification extends Notification
{
    use Queueable;

    /**
     * Constructor de la notificacion.
     *
     * @param string $propertyTitle Titulo o nombre identificador de la propiedad de la cita.
     * @param string $oldStatus     Estado anterior de la cita (pending, confirmed, completed, cancelled, reprogrammed).
     * @param string $newStatus     Estado nuevo de la cita.
     */
    public function __construct(
        public string $propertyTitle,
        public string $oldStatus,
        public string $newStatus
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
     * Mapeo de estados a etiquetas en espanol:
     * - pending: Pendiente
     * - confirmed: Confirmada
     * - completed: Completada
     * - cancelled: Cancelada
     * - reprogrammed: Reprogramada
     *
     * Estructura del arreglo:
     * - title:   Titulo corto de la notificacion.
     * - message: Mensaje descriptivo con la propiedad y el cambio de estado.
     * - type:    Tipo visual para el frontend ('error' si se cancela, 'info' en caso contrario).
     * - url:     Ruta de redireccion al hacer clic (listado de citas).
     * - data:    Arreglo con la propiedad y los estados anterior/nuevo.
     *
     * @param  object $notifiable Modelo de usuario que recibira la notificacion.
     * @return array<string, mixed> Arreglo serializado para la tabla notifications.
     */
    public function toArray(object $notifiable): array
    {
        // Mapa de identificadores de estado a sus etiquetas legibles en espanol
        $statusLabels = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            'reprogrammed' => 'Reprogramada',
        ];

        $oldLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        return [
            'title' => 'Estado de Cita Actualizado',
            'message' => "Tu cita para \"{$this->propertyTitle}\" cambió de \"{$oldLabel}\" a \"{$newLabel}\".",
            'type' => in_array($this->newStatus, ['cancelled']) ? 'error' : 'info',
            'url' => route('citas.index'),
            'data' => [
                'property_title' => $this->propertyTitle,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
            ],
        ];
    }
}
