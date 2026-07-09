<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'period',
        'data',
        'file_path',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'data' => 'array', // Convertir JSON a Array automáticamente
        'sent_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que generó el reporte
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para buscar reportes por tipo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('report_type', $type);
    }
}
