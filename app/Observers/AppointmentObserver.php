<?php
// app/Observers/AppointmentObserver.php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AppointmentObserver
{
    public function created(Appointment $appointment)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'created',
            'action' => 'created',
            'subject_type' => 'App\Models\Appointment',
            'subject_id' => $appointment->id,
            'old_values' => null,
            'new_values' => $appointment->toArray(),
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' SOLICITÓ una cita para "' . ($appointment->property?->title ?? 'Propiedad #' . $appointment->property_id) . '"',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function updated(Appointment $appointment)
    {
        $original = $appointment->getOriginal();
        $changes = [];

        foreach ($appointment->getChanges() as $key => $value) {
            if ($key !== 'updated_at' && isset($original[$key])) {
                $changes[] = "{$key}: '" . ($original[$key] ?? 'vacío') . "' → '" . ($value ?? 'vacío') . "'";
            }
        }

        if (!empty($changes)) {
            $statusLabels = [
                'pending' => 'Pendiente',
                'confirmed' => 'Confirmada',
                'completed' => 'Completada',
                'cancelled' => 'Cancelada',
                'rescheduled' => 'Reprogramada'
            ];
            $statusLabel = $statusLabels[$appointment->status] ?? $appointment->status;

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'action' => 'updated',
                'subject_type' => 'App\Models\Appointment',
                'subject_id' => $appointment->id,
                'old_values' => $original,
                'new_values' => $appointment->toArray(),
                'description' => (Auth::user()?->full_name ?? 'Sistema') . ' ACTUALIZÓ la cita para "' . ($appointment->property?->title ?? 'Propiedad #' . $appointment->property_id) . '" a "' . $statusLabel . '"',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        }
    }

    public function deleted(Appointment $appointment)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'deleted',
            'action' => 'deleted',
            'subject_type' => 'App\Models\Appointment',
            'subject_id' => $appointment->id,
            'old_values' => $appointment->toArray(),
            'new_values' => null,
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' CANCELÓ la cita para "' . ($appointment->property?->title ?? 'Propiedad #' . $appointment->property_id) . '"',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}
