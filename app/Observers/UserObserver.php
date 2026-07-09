<?php
// app/Observers/UserObserver.php

namespace App\Observers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function created(User $user)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'created',
            'action' => 'created',
            'subject_type' => 'App\Models\User',
            'subject_id' => $user->id,
            'old_values' => null,
            'new_values' => $user->toArray(),
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' CREÓ al usuario ' . $user->full_name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function updated(User $user)
    {
        $original = $user->getOriginal();
        $changes = [];

        foreach ($user->getChanges() as $key => $value) {
            if ($key !== 'updated_at' && isset($original[$key])) {
                $changes[] = "{$key}: '" . ($original[$key] ?? 'vacío') . "' → '" . ($value ?? 'vacío') . "'";
            }
        }

        if (!empty($changes)) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'action' => 'updated',
                'subject_type' => 'App\Models\User',
                'subject_id' => $user->id,
                'old_values' => $original,
                'new_values' => $user->toArray(),
                'description' => (Auth::user()?->full_name ?? 'Sistema') . ' ACTUALIZÓ al usuario ' . $user->full_name . '. Cambios: ' . implode(', ', $changes),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        }
    }

    public function deleted(User $user)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'deleted',
            'action' => 'deleted',
            'subject_type' => 'App\Models\User',
            'subject_id' => $user->id,
            'old_values' => $user->toArray(),
            'new_values' => null,
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' ELIMINÓ al usuario ' . $user->full_name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}
