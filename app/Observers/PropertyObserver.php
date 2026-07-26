<?php
// app/Observers/PropertyObserver.php

namespace App\Observers;

use App\Models\Property;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Observador de propiedades inmobiliarias.
 *
 * Registra en la tabla AuditLog todas las operaciones CRUD sobre el
 * modelo Property. Permite un seguimiento completo del ciclo de vida
 * de cada propiedad: desde su creacion, pasando por cada actualizacion,
 * hasta su eliminacion.
 *
 * Cada entrada de auditoria captura: el usuario responsable de la accion,
 * los valores previos y nuevos, la IP del cliente, el user-agent y la URL
 * de la peticion HTTP que origino el cambio.
 */
class PropertyObserver
{
    /**
     * Se ejecuta despues de crear una propiedad.
     *
     * Registra en AuditLog la creacion de la propiedad con todos sus
     * atributos como valores nuevos. El valor anterior es nulo ya que
     * no existia previamente.
     *
     * @param  \App\Models\Property  $property  El modelo de propiedad recien creado.
     * @return void
     */
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

    /**
     * Se ejecuta despues de actualizar una propiedad.
     *
     * Compara los atributos originales con los nuevos para detectar
     * cambios reales (ignora updated_at). Solo se genera un registro
     * de auditoria si existen modificaciones. La descripcion incluye
     * un desglose de cada campo que fue alterado con sus valores
     * anterior y nuevo para facilitar la trazabilidad.
     *
     * @param  \App\Models\Property  $property  El modelo de propiedad actualizado.
     * @return void
     */
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

    /**
     * Se ejecuta despues de eliminar una propiedad.
     *
     * Registra en AuditLog el estado completo de la propiedad antes de
     * su eliminacion. Los valores nuevos son nulos porque el registro
     * deja de existir. Esto permite reconstruir el historial de la
     * propiedad eliminada a partir de los registros de auditoria.
     *
     * @param  \App\Models\Property  $property  El modelo de propiedad eliminado.
     * @return void
     */
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
