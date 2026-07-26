<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificacion que se envia a un asesor/asesora inmobiliario cuando un lead
 * (cliente potencial) lo selecciona como su representante de compra o arrendamiento.
 *
 * Momento de disparo:
 * Se crea cuando un lead completo el formulario de contacto o selecciona un asesor
 * desde la plataforma, quedando vinculado a un tipo de propiedad y direccion específica.
 *
 * Canal de entrega:
 * Se almacena exclusivamente en la base de datos (tabla 'notifications') para ser
 * consultada desde el panel de notificaciones del usuario.
 *
 * Datos que transporta:
 * - Nombre, correo electronico y telefono del lead.
 * - Tipo de propiedad de interes.
 * - Direccion de la propiedad asociada.
 */
class NewLeadNotification extends Notification
{
    use Queueable;

    /**
     * Constructor de la notificacion.
     *
     * @param string      $leadName        Nombre completo del lead que selecciono al asesor.
     * @param string      $leadEmail       Correo electronico del lead.
     * @param string      $leadPhone       Numero de telefono del lead.
     * @param string|null $propertyType    Tipo de propiedad de interes (ej: apartamento, casa).
     * @param string|null $propertyAddress Direccion de la propiedad asociada al lead.
     */
    public function __construct(
        public string $leadName,
        public string $leadEmail,
        public string $leadPhone,
        public ?string $propertyType = null,
        public ?string $propertyAddress = null
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
     * - title:    Titulo corto de la notificacion.
     * - message:  Mensaje descriptivo con nombre del lead y detalles de la propiedad.
     * - type:     Tipo visual para el frontend ('success' para indicar nuevo lead).
     * - url:      Ruta de redireccion al hacer clic (listado de leads).
     * - data:     Arreglo con los datos detallados del lead.
     *
     * @param  object $notifiable Modelo de usuario que recibira la notificacion.
     * @return array<string, mixed> Arreglo serializado para la tabla notifications.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nuevo Lead Asignado',
            'message' => "El cliente \"{$this->leadName}\" te ha seleccionado como su asesor. Tipo: {$this->propertyType}, Dirección: {$this->propertyAddress}",
            'type' => 'success',
            'url' => route('leads.index'),
            'data' => [
                'lead_name' => $this->leadName,
                'lead_email' => $this->leadEmail,
                'lead_phone' => $this->leadPhone,
                'property_type' => $this->propertyType,
                'property_address' => $this->propertyAddress,
            ],
        ];
    }
}
