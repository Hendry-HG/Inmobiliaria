<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogSuccessfulLogout
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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
