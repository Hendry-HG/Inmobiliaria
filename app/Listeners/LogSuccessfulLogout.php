<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

/**
 * Listener que registra en la auditoria cada cierre de sesion.
 *
 * Se ejecuta automaticamente cuando un usuario cierra sesion.
 * Crea un registro en audit_logs con la accion 'logout',
 * incluyendo metadatos de la peticion (IP, user agent, URL).
 *
 * @package App\Listeners
 */
class LogSuccessfulLogout
{
    /**
     * Instancia de la peticion HTTP actual.
     *
     * @var Request
     */
    protected $request;

    /**
     * Constructor: inyecta la peticion HTTP.
     *
     * @param Request $request Peticion HTTP para obtener IP y user agent.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Registra el cierre de sesion en la tabla de auditoria.
     *
     * Verifica que el evento contenga un usuario valido antes de crear
     * el registro de auditoria.
     *
     * @param Logout $event Evento de cierre de sesion de Laravel.
     * @return void
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            AuditLog::create([
                'user_id' => $event->user->id,
                'action' => 'logout',
                'event' => 'logout',
                'subject_type' => 'App\Models\User',
                'subject_id' => $event->user->id,
                'description' => "Cierre de sesión de {$event->user->name}",
                'old_values' => null,
                'new_values' => null,
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'url' => $this->request->fullUrl(),
            ]);
        }
    }
}
