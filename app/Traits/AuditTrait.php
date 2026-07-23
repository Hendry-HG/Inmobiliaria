<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditTrait
{
    private static $audit_logged = [];

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

    protected function logCreated($subject, $description = null)
    {
        return $this->logAudit('created', $subject, null, $subject->toArray(), $description);
    }

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

    protected function logDeleted($subject, $description = null)
    {
        return $this->logAudit('deleted', $subject, $subject->toArray(), null, $description);
    }
}
