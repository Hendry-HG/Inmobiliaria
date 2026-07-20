<?php


namespace App\Listeners;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Log;

class ClearPermissionCache
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

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
