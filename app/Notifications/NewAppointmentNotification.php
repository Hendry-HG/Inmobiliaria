<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificacion que se envia al asesor inmobiliario cuando un cliente agenda
 * una nueva cita de visita a una propiedad.
 *
 * Momento de disparo:
 * Se crea cuando un cliente confirma el agendamiento de una cita desde la
 * plataforma, indicando la propiedad a visitar y la fecha programada.
 *
 * Canal de entrega:
 * Se almacena exclusivamente en la base de datos (tabla 'notifications') para ser
 * consultada desde el panel de notificaciones del usuario.
 *
 * Datos que transporta:
 * - Nombre del cliente que agendo la cita.
 * - Titulo/nombre de la propiedad a visitar.
 * - Fecha y hora programadas para la cita.
 */
class NewAppointmentNotification extends Notification
{
    use Queueable;

    /**
     * Constructor de la notificacion.
     *
     * @param string $clientName    Nombre completo del cliente que agendo la cita.
     * @param string $propertyTitle Titulo o nombre identificador de la propiedad a visitar.
     * @param string $scheduledDate Fecha y hora programadas para la cita (formato legible).
     */
    public function __construct(
        public string $clientName,
        public string $propertyTitle,
        public string $scheduledDate
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
     * - message: Mensaje descriptivo con nombre del cliente, propiedad y fecha.
     * - type:    Tipo visual para el frontend ('info' para notificacion informativa).
     * - url:     Ruta de redireccion al hacer clic (listado de citas).
     * - data:    Arreglo con los datos detallados de la cita.
     *
     * @param  object $notifiable Modelo de usuario que recibira la notificacion.
     * @return array<string, mixed> Arreglo serializado para la tabla notifications.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nueva Cita Agendada',
            'message' => "{$this->clientName} agendó una cita para \"{$this->propertyTitle}\" el {$this->scheduledDate}.",
            'type' => 'info',
            'url' => route('citas.index'),
            'data' => [
                'client_name' => $this->clientName,
                'property_title' => $this->propertyTitle,
                'scheduled_date' => $this->scheduledDate,
            ],
        ];
    }
}
