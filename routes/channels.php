<?php

/**
 * Archivo de autorizacion de canales de broadcast.
 *
 * Define los canales de broadcasting de Laravel Echo y la logica de
 * autorizacion para cada uno. Los canales determinan que usuarios
 * pueden escuchar eventos en tiempo real.
 *
 * Tipos de canales utilizados:
 *   - Privados (Broadcast::channel): Requieren autorizacion explícita
 *     mediante una Closure que retorna true/false.
 *   - No se utilizan canales de presencia en este archivo.
 *
 * Canales definidos:
 *   - chat.user.{id}              : Canal privado por usuario (notificaciones).
 *   - chat.typing.{conversationId}: Canal de indicadores de escritura.
 *   - user.{id}                   : Canal privado por usuario (perfil general).
 *
 * @package App\Providers
 */

use Illuminate\Support\Facades\Broadcast;

/**
 * -----------------------------------------------------------------------
 * CANAL PRIVADO: chat.user.{id}
 * -----------------------------------------------------------------------
 * Canal de notificaciones privado para cada usuario autenticado.
 * Se utiliza para enviar notificaciones en tiempo real (nuevos mensajes,
 * actualizaciones de estado, etc.) solo al usuario propietario del canal.
 *
 * Autorizacion: Solo el usuario cuyo ID coincide con el parametro {id}
 * puede suscribirse a este canal.
 *
 * Ejemplo de suscripcion: Echo.private('chat.user.1')
 */
Broadcast::channel('chat.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * -----------------------------------------------------------------------
 * CANAL PRIVADO: chat.typing.{conversationId}
 * -----------------------------------------------------------------------
 * Canal utilizado para transmitir indicadores de escritura (typing
 * indicators) en una conversacion de chat. Permite que todos los
 * participantes de una conversacion vean cuando alguien esta escribiendo.
 *
 * Autorizacion: Retorna true para todos los usuarios autenticados.
 * Esto permite que cualquier usuario autenticado emita y reciba eventos
 * de escritura en cualquier conversacion. La validacion de participantes
 * se realiza a nivel de controlador.
 *
 * Ejemplo de suscripcion: Echo.private('chat.typing.42')
 */
Broadcast::channel('chat.typing.{conversationId}', function ($user, $conversationId) {
    return true;
});

/**
 * -----------------------------------------------------------------------
 * CANAL PRIVADO: user.{id}
 * -----------------------------------------------------------------------
 * Canal de notificaciones general para cada usuario autenticado.
 * Se utiliza para eventos que no estan relacionados con el chat,
 * como actualizaciones de perfil, cambios de configuracion, o
 * notificaciones del sistema.
 *
 * Autorizacion: Solo el usuario cuyo ID coincide con el parametro {id}
 * puede suscribirse a este canal.
 *
 * Ejemplo de suscripcion: Echo.private('user.1')
 */
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
