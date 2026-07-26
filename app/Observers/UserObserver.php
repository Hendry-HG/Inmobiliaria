<?php
// app/Observers/UserObserver.php

namespace App\Observers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Observador de usuarios.
 *
 * Se encarga de registrar en la tabla de auditoria (AuditLog) todas las
 * operaciones CRUD realizadas sobre el modelo User. Cada registro incluye
 * la identidad del usuario autenticado que ejecuto la accion, los valores
 * anteriores y posteriores, la direccion IP, el user-agent y la URL
 * de la peticion.
 *
 * Campos excluidos por seguridad: password y remember_token.
 */
class UserObserver
{
    /**
     * Se ejecuta despues de crear un usuario.
     *
     * Registra en AuditLog la creacion del usuario, incluyendo todos
     * sus atributos excepto los campos sensibles (password, remember_token).
     * Si no hay usuario autenticado (creacion desde sistema), se registra
     * 'Sistema' como autor de la accion.
     *
     * @param  \App\Models\User  $user  El modelo recien creado.
     * @return void
     */
    public function created(User $user)
    {
        $newValues = $user->toArray();
        unset($newValues['password'], $newValues['remember_token']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'created',
            'action' => 'created',
            'subject_type' => 'App\Models\User',
            'subject_id' => $user->id,
            'old_values' => null,
            'new_values' => $newValues,
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' CREÓ al usuario ' . $user->full_name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    /**
     * Se ejecuta despues de actualizar un usuario.
     *
     * Compara los valores originales con los nuevos para detectar cambios
     * reales (excluyendo updated_at). Solo genera un registro de auditoria
     * si al menos un campo fue modificado. Los valores nuevos incluyen
     * todos los atributos excepto password y remember_token por seguridad.
     * En la descripcion se enumera cada campo que cambio con su valor
     * anterior y nuevo.
     *
     * @param  \App\Models\User  $user  El modelo actualizado.
     * @return void
     */
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
            $newValues = $user->toArray();
            unset($newValues['password'], $newValues['remember_token']);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'action' => 'updated',
                'subject_type' => 'App\Models\User',
                'subject_id' => $user->id,
                'old_values' => $original,
                'new_values' => $newValues,
                'description' => (Auth::user()?->full_name ?? 'Sistema') . ' ACTUALIZÓ al usuario ' . $user->full_name . '. Cambios: ' . implode(', ', $changes),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        }
    }

    /**
     * Se ejecuta despues de eliminar (soft delete o hard delete) un usuario.
     *
     * Registra en AuditLog los valores que tenia el usuario antes de ser
     * eliminado, excluyendo los campos sensibles (password, remember_token).
     * No se registran valores nuevos porque el registro ya no existe.
     *
     * @param  \App\Models\User  $user  El modelo que fue eliminado.
     * @return void
     */
    public function deleted(User $user)
    {
        $oldValues = $user->toArray();
        unset($oldValues['password'], $oldValues['remember_token']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'deleted',
            'action' => 'deleted',
            'subject_type' => 'App\Models\User',
            'subject_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => null,
            'description' => (Auth::user()?->full_name ?? 'Sistema') . ' ELIMINÓ al usuario ' . $user->full_name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}
