<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditTrait
{
    //  ARRAY PARA CONTROLAR DUPLICADOS
    private static $audit_logged = [];

    protected function logAudit($event, $subject, $oldValues = null, $newValues = null, $description = null)
    {
        //  EVITAR DUPLICADOS
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

        return AuditLog::create([
            'user_id' => $userId,
            'event' => $event,
            'action' => $event,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
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
