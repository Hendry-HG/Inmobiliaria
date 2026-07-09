<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'property_id', 'asesor_id', 'name', 'email', 'phone',
        'id_type', 'id_number', 'source', 'source_detail', 'interest_type',
        'budget_min', 'budget_max', 'preferences', 'status', 'notes',
        'last_contact', 'contact_count'
    ];

    protected $casts = [
        'preferences' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'last_contact' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'nuevo');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cerrado_ganado', 'cerrado_perdido', 'inactivo']);
    }

    public function markAsContacted()
    {
        $this->update([
            'status' => 'contactado',
            'last_contact' => now(),
            'contact_count' => $this->contact_count + 1
        ]);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'nuevo' => 'Nuevo',
            'contactado' => 'Contactado',
            'calificado' => 'Calificado',
            'cerrado_ganado' => 'Ganado',
            'cerrado_perdido' => 'Perdido',
            'inactivo' => 'Inactivo'
        ];
        return $labels[$this->status] ?? $this->status;
    }
}
