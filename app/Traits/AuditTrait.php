<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Trait que proporciona funcionalidad de auditoria para modelos Eloquent.
 *
 * Registra automaticamente acciones sobre entidades (creacion, actualizacion, eliminacion)
 * en la tabla 'audit_logs'. Cada registro incluye: usuario responsable, tipo de evento,
 * modelo afectado, valores anteriores y posteriores, descripcion legible, direccion IP,
 * user agent y URL de la peticion.
 *
 * Evita registros duplicados en la misma ejecucion mediante un array estatico de control.
 */
trait AuditTrait
{
    /**
     * Array estatico que almacena las claves de los registros ya creados en la ejecucion actual.
     * Clave formato: "ClaseModelo_ID_evento". Previene registros duplicados
     * cuando el mismo metodo de auditoria se invoca multiples veces para la misma entidad y evento.
     *
     * @var string[]
     */
    private static $audit_logged = [];

    /**
     * Registra una entrada de auditoria en la tabla 'audit_logs'.
     *
     * Flujo de datos:
     * 1. Genera una clave unica para evitar duplicados en la misma ejecucion.
     * 2. Obtiene el usuario autenticado (o 'Sistema' si no hay sesion).
     * 3. Si no se proporciona descripcion, la genera automaticamente con el formato:
     *    "[Usuario] [EVENTO] ClaseModelo 'NombreModelo'".
     * 4. Convierte oldValues y newValues a JSON.
     * 5. Inserta el registro en 'audit_logs' con todos los campos.
     *
     * Campos guardados en audit_logs:
     * - user_id: ID del usuario que realizo la accion.
     * - user_type: Clase completa del modelo de usuario (ej. App\Models\User).
     * - event: Tipo de evento ('created', 'updated', 'deleted', etc.).
     * - action: Mismo valor que 'event' (compatibilidad).
     * - subject_type: Clase completa del modelo afectado.
     * - subject_id: ID del modelo afectado.
     * - auditable_type: Igual a subject_type (compatibilidad con paquetes de auditoria).
     * - auditable_id: Igual a subject_id (compatibilidad con paquetes de auditoria).
     * - old_values: JSON con los valores previos al cambio.
     * - new_values: JSON con los valores posteriores al cambio.
     * - description: Descripcion legible de la accion.
     * - ip_address: Direccion IP del cliente.
     * - user_agent: Navegador/dispositivo del cliente.
     * - url: URL completa de la peticion HTTP.
     *
     * @param  string          $event       Tipo de evento (created, updated, deleted, restored, etc.).
     * @param  \Illuminate\Database\Eloquent\Model $subject Modelo afectado por la accion.
     * @param  array|null      $oldValues   Valores anteriores del modelo (antes del cambio).
     * @param  array|null      $newValues   Valores posteriores del modelo (despues del cambio).
     * @param  string|null     $description Descripcion personalizada del evento. Si es null, se genera automaticamente.
     * @return \App\Models\AuditLog|null     Instancia del registro creado, o null si se descarto por duplicado.
     */
    protected function logAudit($event, $subject, $oldValues = null, $newValues = null, $description = null)
    {
        $key = get_class($subject) . '_' . $subject->id . '_' . $event;
        if (in_array($key, self::$audit_logged)) {
            return null;
        }
        self::$audit_logged[] = $key;

        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userName = $user ? $user->full_name : 'Sistema';

        if (!$description) {
            $subjectName = $subject->title ?? $subject->name ?? '#' . $subject->id;
            $eventLabels = [
                'created' => 'CREÓ',
                'updated' => 'ACTUALIZÓ',
                'deleted' => 'ELIMINÓ',
                'restored' => 'RESTAURÓ',
            ];
            $eventLabel = $eventLabels[$event] ?? strtoupper($event);
            $description = "{$userName} {$eventLabel} " . class_basename($subject) . " '{$subjectName}'";
        }

        
        $oldJson = $this->toJson($oldValues);
        $newJson = $this->toJson($newValues);

        return AuditLog::create([
            'user_id' => $userId,
            'user_type' => $user ? get_class($user) : null,
            'event' => $event,
            'action' => $event,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'auditable_type' => get_class($subject),
            'auditable_id' => $subject->id,
            'old_values' => $oldJson,
            'new_values' => $newJson,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    /**
     * Convierte un valor a formato JSON valido para almacenamiento en la base de datos.
     *
     * Flujo:
     * - Si el valor es null, retorna null directamente.
     * - Si es un string, verifica si ya es un JSON valido. Si lo es, lo retorna sin modificar.
     *   Si no lo es, lo codifica a JSON.
     * - Para cualquier otro tipo (array, objeto), lo codifica a JSON.
     *
     * @param  mixed       $values Valor a convertir (null, string, array u objeto).
     * @return string|null         JSON codificado o null.
     */
    private function toJson($values)
    {
        if (is_null($values)) {
            return null;
        }

        if (is_string($values)) {
            json_decode($values);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $values;
            }
            return json_encode($values);
        }

        return json_encode($values);
    }

    /**
     * Registra la creacion de un modelo en la tabla 'audit_logs'.
     *
     * Almacena todos los campos del modelo nuevo en 'new_values' y deja 'old_values' en null,
     * ya que no existia un estado previo.
     *
     * @param  \Illuminate\Database\Eloquent\Model $subject     Modelo recien creado.
     * @param  string|null                          $description Descripcion personalizada del evento.
     * @return \App\Models\AuditLog|null                         Registro de auditoria creado.
     */
    protected function logCreated($subject, $description = null)
    {
        return $this->logAudit('created', $subject, null, $subject->toArray(), $description);
    }

    /**
     * Registra la actualizacion de un modelo en la tabla 'audit_logs'.
     *
     * Almacena los valores anteriores en 'old_values' y los valores actuales del modelo en 'new_values'.
     * Si no se proporciona descripcion y se recibe un array de cambios, genera una descripcion
     * automatica con el formato: "[Usuario] ACTUALIZO ClaseModelo 'Nombre'. Cambios: campo1, campo2".
     *
     * @param  \Illuminate\Database\Eloquent\Model $subject     Modelo actualizado.
     * @param  array                                $oldValues   Valores previos al cambio.
     * @param  array|null                           $changes     Lista de nombres de campos que cambiaron.
     * @param  string|null                          $description Descripcion personalizada del evento.
     * @return \App\Models\AuditLog|null                         Registro de auditoria creado.
     */
    protected function logUpdated($subject, $oldValues, $changes = null, $description = null)
    {
        $user = Auth::user();
        $userName = $user ? $user->full_name : 'Sistema';

        if (!$description && $changes) {
            $subjectName = $subject->title ?? $subject->name ?? '#' . $subject->id;
            $description = "{$userName} ACTUALIZÓ " . class_basename($subject) . " '{$subjectName}'. Cambios: " . implode(', ', $changes);
        }

        return $this->logAudit('updated', $subject, $oldValues, $subject->toArray(), $description);
    }

    /**
     * Registra la eliminacion de un modelo en la tabla 'audit_logs'.
     *
     * Almacena todos los campos del modelo eliminado en 'old_values' y deja 'new_values' en null,
     * ya que el modelo ya no existira despues de la accion.
     *
     * @param  \Illuminate\Database\Eloquent\Model $subject     Modelo eliminado.
     * @param  string|null                          $description Descripcion personalizada del evento.
     * @return \App\Models\AuditLog|null                         Registro de auditoria creado.
     */
    protected function logDeleted($subject, $description = null)
    {
        return $this->logAudit('deleted', $subject, $subject->toArray(), null, $description);
    }
}
