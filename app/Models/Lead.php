<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Lead - Representa un prospecto o cliente potencial en el sistema.
 *
 * Un lead se genera cuando un usuario muestra interés en una propiedad,
 * ya sea a través de un formulario de contacto, una consulta directa o
 * un seguimiento manual por parte de un asesor inmobiliario.
 *
 * Flujo de datos:
 * El lead inicia con status 'nuevo', progresa a 'contactado', luego
 * a 'calificado' y finalmente se cierra como 'cerrado_ganado' o
 * 'cerrado_perdido'. Un lead inactivo indica que fue descartado.
 *
 * Relaciones principales:
 * - User (usuario que generó el lead, generalmente el cliente)
 * - Property (propiedad que generó el interés)
 * - User (asesor asignado para el seguimiento)
 *
 * Cada lead almacena información de contacto, fuente de origen,
 * tipo de interés, rango de presupuesto y preferencias adicionales.
 */
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

    /**
     * Relación con el usuario que generó el lead (cliente potencial).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la propiedad que despertó el interés del lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relación con el asesor inmobiliario asignado para dar seguimiento al lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    /**
     * Filtra leads con estado 'nuevo' (sin contacto inicial).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'nuevo');
    }

    /**
     * Filtra leads activos (excluye los cerrados e inactivos).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cerrado_ganado', 'cerrado_perdido', 'inactivo']);
    }

    /**
     * Marca el lead como contactado, registra la fecha del último contacto
     * e incrementa el contador de intentos de contacto.
     */
    public function markAsContacted()
    {
        $this->update([
            'status' => 'contactado',
            'last_contact' => now(),
            'contact_count' => $this->contact_count + 1
        ]);
    }

    /**
     * Obtiene la etiqueta legible del estado del lead.
     * Convierte los valores internos del estado a sus equivalentes
     * en español para su visualización en la interfaz.
     *
     * @return string etiqueta del estado en español
     */
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
