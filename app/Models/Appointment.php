<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'property_id', 'asesor_id',
        'scheduled_date', 'end_date',
        'status',
        'contact_name', 'contact_phone', 'contact_email',
        'message', 'notes', 'result_notes',
        'client_attended', 'property_sold'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'end_date' => 'datetime',
        'client_attended' => 'boolean',
        'property_sold' => 'boolean',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeForRole($query, $user)
    {
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            return $query;
        }
        if ($user->hasRole('Auditor')) {
            return $query;
        }
        if ($user->hasRole('Asesor Inmobiliario')) {
            return $query->where('asesor_id', $user->id);
        }
        return $query->where('user_id', $user->id);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getTimeAttribute()
    {
        return $this->scheduled_date ? $this->scheduled_date->format('H:i') : '--:--';
    }

    public function getFullAddressAttribute()
    {
        return $this->property ? $this->property->full_location : 'Dirección no disponible';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            'rescheduled' => 'Reprogramada'
        ];
        return $labels[$this->status] ?? $this->status;
    }
}
