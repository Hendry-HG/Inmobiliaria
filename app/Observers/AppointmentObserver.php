<?php
// app/Observers/AppointmentObserver.php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Observador de citas (visitas/agendamientos).
 *
 * Registra en la tabla AuditLog todas las operaciones CRUD sobre el
 * modelo Appointment. Permite rastrear el ciclo completo de cada cita,
 * desde su solicitud inicial hasta su estado final (completada, cancelada
 * o reprogramada).
 *
 * La descripcion de cada registro de auditoria incluye el titulo de la
 * propiedad asociada a la cita para facilitar la identificacion visual
 * en el historial. Si la propiedad no esta disponible, se muestra su ID.
 */
class AppointmentObserver
{
    /**
     * Se ejecuta despues de crear una cita.
     *
     * Registra en AuditLog la solicitud de la cita con todos sus atributos.
     * La descripcion indica quien solicito la cita y para que propiedad,
     * utilizando el titulo de la propiedad o su ID como respaldo.
     *
     * @param  \App\Models\Appointment  $appointment  El modelo de cita recien creado.
     * @return void
     */
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

    /**
     * Se ejecuta despues de actualizar una cita.
     *
     * Detecta los campos que realmente cambiaron (excluyendo updated_at)
     * y genera un registro de auditoria solo si hubo modificaciones.
     * La descripcion incluye la traduccion del nuevo estado de la cita
     * (Pendiente, Confirmada, Completada, Cancelada, Reprogramada) para
     * que el historial sea comprensible sin necesidad de interpretar
     * los valores internos del sistema.
     *
     * @param  \App\Models\Appointment  $appointment  El modelo de cita actualizado.
     * @return void
     */
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

    /**
     * Se ejecuta despues de eliminar una cita.
     *
     * Registra en AuditLog la cancelacion/eliminacion de la cita con
     * todos sus atributos como valores previos. La descripcion indica
     * que la cita fue cancelada y para que propiedad, permitiendo un
     * historial completo de la relacion entre citas y propiedades.
     *
     * @param  \App\Models\Appointment  $appointment  El modelo de cita eliminado.
     * @return void
     */
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
