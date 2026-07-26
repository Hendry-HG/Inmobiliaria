<?php

namespace App\Traits;

use App\Events\NewNotification as NewNotificationEvent;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

/**
 * Trait que permite enviar notificaciones a usuarios del sistema.
 *
 * Las notificaciones se almacenan en la tabla 'user_notifications' y se transmiten
 * en tiempo real al usuario mediante broadcasting (evento WebSocket 'NewNotification').
 *
 * Flujo general:
 * 1. Se valida que el usuario exista y tenga un ID valido.
 * 2. Se crea el registro en la tabla 'user_notifications' con titulo, mensaje, tipo, URL y datos extra.
 * 3. Se calcula el total de notificaciones no leidas del usuario.
 * 4. Se emite un evento de broadcasting con el conteo actualizado y los datos de la notificacion.
 * 5. Si ocurre un error, se registra en el log de Laravel como warning sin interrumpir la ejecucion.
 */
trait SendsNotifications
{
    /**
     * Envia una notificacion a un usuario especifico.
     *
     * Flujo de datos:
     * 1. Se valida que el usuario no sea null y tenga un ID valido; si no, se aborta silenciosamente.
     * 2. Se inserta un registro en 'user_notifications' con los campos:
     *    - user_id: ID del usuario destinatario.
     *    - title: Titulo breve de la notificacion.
     *    - message: Cuerpo del mensaje de la notificacion.
     *    - type: Categoria de la notificacion ('info', 'success', 'warning', 'error', etc.).
     *    - url: URL opcional a la que redirige al hacer clic en la notificacion.
     *    - data: Array opcional con datos adicionales en formato JSON.
     * 3. Se consulta el conteo total de notificaciones no leidas del usuario
     *    (campos donde 'read_at' es null).
     * 4. Se emite un evento de broadcasting 'NewNotification' al canal privado del usuario,
     *    incluyendo: ID del usuario, conteo de no leidas y los datos completos de la notificacion.
     * 5. Si cualquier paso falla, se captura la excepcion y se registra en el log con nivel warning.
     *
     * @param  \App\Models\User $user    Instancia del modelo de usuario destinatario.
     * @param  string           $title   Titulo de la notificacion.
     * @param  string           $message Cuerpo del mensaje de la notificacion.
     * @param  string           $type    Tipo/categoria de la notificacion (default: 'info').
     * @param  string|null      $url     URL opcional de redireccion al hacer clic en la notificacion.
     * @param  array|null       $data    Datos adicionales opcionales en formato array (se guarda como JSON).
     * @return void
     */
    protected function notifyUser($user, string $title, string $message, string $type = 'info', ?string $url = null, ?array $data = null): void
    {
        if (!$user || !$user->id) {
            return;
        }

        try {
            $notification = UserNotification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'url' => $url,
                'data' => $data,
            ]);

            $count = UserNotification::where('user_id', $user->id)->whereNull('read_at')->count();

            broadcast(new NewNotificationEvent($user->id, $count, $notification->toArray()));
        } catch (\Exception $e) {
            Log::warning('Error enviando notificación al usuario ' . $user->id . ': ' . $e->getMessage());
        }
    }
}
