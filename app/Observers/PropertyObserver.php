<?php
// app/Observers/PropertyObserver.php

namespace App\Observers;

use App\Models\Property;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class PropertyObserver
{
    public function created(Property $property)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'created',
            'action' => 'created',
            'subject_type' => 'App\Models\Property',
            'subject_id' => $property->id,
            'old_values' => null,
            'new_values' => $property->toArray(),
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' CREÓ la propiedad "' . $property->title . '"',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function updated(Property $property)
    {
        $original = $property->getOriginal();
        $changes = [];

        foreach ($property->getChanges() as $key => $value) {
            if ($key !== 'updated_at' && isset($original[$key])) {
                $changes[] = "{$key}: '" . ($original[$key] ?? 'vacío') . "' → '" . ($value ?? 'vacío') . "'";
            }
        }

        if (!empty($changes)) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'action' => 'updated',
                'subject_type' => 'App\Models\Property',
                'subject_id' => $property->id,
                'old_values' => $original,
                'new_values' => $property->toArray(),
                'description' => (Auth::user()?->full_name ?? 'Sistema') . ' ACTUALIZÓ la propiedad "' . $property->title . '". Cambios: ' . implode(', ', $changes),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        }
    }

    public function deleted(Property $property)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'deleted',
            'action' => 'deleted',
            'subject_type' => 'App\Models\Property',
            'subject_id' => $property->id,
            'old_values' => $property->toArray(),
            'new_values' => null,
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' ELIMINÓ la propiedad "' . $property->title . '"',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}
