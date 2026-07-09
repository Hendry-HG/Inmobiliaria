<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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
