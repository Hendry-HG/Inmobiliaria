<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

/**
 * Listener que registra en la auditoria cada inicio de sesion exitoso.
 *
 * Se ejecuta automaticamente despues de que un usuario se autentica
 * correctamente. Crea un registro en audit_logs con la accion 'login',
 * incluyendo metadatos de la peticion (IP, user agent, URL).
 *
 * @package App\Listeners
 */
class LogSuccessfulLogin
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
     * Registra el inicio de sesion en la tabla de auditoria.
     *
     * @param Login $event Evento de autenticacion exitosa de Laravel.
     * @return void
     */
    public function handle(Login $event): void
    {
        AuditLog::create([
            'user_id' => $event->user->id,
            'action' => 'login',
            'event' => 'login',
            'subject_type' => 'App\Models\User',
            'subject_id' => $event->user->id,
            'description' => "Inicio de sesión de {$event->user->name}",
            'old_values' => null,
            'new_values' => null,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'url' => $this->request->fullUrl(),
        ]);
    }
}
