<?php


namespace App\Listeners;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Log;

/**
 * Listener que limpia la cache de permisos de usuario cuando ocurre un evento relevante.
 *
 * Se ejecuta cuando los permisos o roles de un usuario cambian (por ejemplo,
 * al sincronizar permisos via Spatie). Intenta obtener el usuario del evento
 * y limpia su cache personalizada. Si no puede identificar al usuario,
 * realiza una limpieza global de cache.
 *
 * Eventos que disparan este listener:
 * - Rol o permiso sincronizado/actualizado.
 *
 * Flujo:
 * 1. Intenta obtener el usuario desde el evento (via $event->user, $event->subject o getUser()).
 * 2. Si encuentra al usuario, crea un PermissionService y limpia su cache.
 * 3. Si no encuentra al usuario, realiza limpieza global de cache.
 * 4. Registra el resultado en el log de Laravel.
 *
 * @package App\Listeners
 */
class ClearPermissionCache
{
    /**
     * Instancia del servicio de permisos para limpieza global.
     *
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * Constructor: inyecta el servicio de permisos.
     *
     * @param PermissionService $permissionService Servicio de permisos para gestion de cache.
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Maneja el evento: limpia la cache de permisos del usuario afectado.
     *
     * @param mixed $event Instancia del evento que disparo la limpieza.
     * @return void
     */
    public function handle($event)
    {
        try {
            // Obtener el usuario del evento
            $user = null;

            if (isset($event->user)) {
                $user = $event->user;
            } elseif (isset($event->subject)) {
                $user = $event->subject;
            } elseif (method_exists($event, 'getUser')) {
                $user = $event->getUser();
            }

            if ($user) {
                // Crear una instancia del servicio para ese usuario específico
                $service = new PermissionService($user);
                $service->clearCache();

                Log::info('Caché de permisos limpiada', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'event' => class_basename($event)
                ]);
            } else {
                // Si no se puede obtener el usuario, limpiar toda la caché
                $this->permissionService->clearCache();

                Log::info('Caché de permisos limpiada (global)', [
                    'event' => class_basename($event)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error limpiando caché de permisos', [
                'event' => class_basename($event ?? 'unknown'),
                'error' => $e->getMessage()
            ]);
        }
    }
}
