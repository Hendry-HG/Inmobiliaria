<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_type',
        'action',
        'event',
        'subject_type',
        'subject_id',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'tags',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function getActionLabelAttribute(): string
    {
        $labels = [
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            'restored' => 'Restauración',
            'login' => 'Inicio de Sesión',
            'logout' => 'Cierre de Sesión',
        ];

        $key = $this->action ?? $this->event;
        return $labels[$key] ?? $key ?? 'Desconocida';
    }

    public function getActionColorAttribute(): string
    {
        $colors = [
            'created' => 'bg-green-50 text-green-700 border-green-200',
            'updated' => 'bg-blue-50 text-blue-700 border-blue-200',
            'deleted' => 'bg-red-50 text-red-700 border-red-200',
            'restored' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'login' => 'bg-purple-50 text-purple-700 border-purple-200',
            'logout' => 'bg-gray-50 text-gray-700 border-gray-200',
        ];

        $key = $this->action ?? $this->event;
        return $colors[$key] ?? 'bg-slate-50 text-slate-700 border-slate-200';
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action)->orWhere('event', $action);
    }

    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    public function scopeBySubject($query, $type, $id = null)
    {
        $query->where('subject_type', $type);
        if ($id) {
            $query->where('subject_id', $id);
        }
        return $query;
    }
}
