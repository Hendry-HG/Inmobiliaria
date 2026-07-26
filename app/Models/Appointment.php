<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Appointment - Representa una cita o visita programada a una propiedad.
 *
 * Las citas son el mecanismo central para coordinar visitas presenciales
 * entre clientes y asesores inmobiliarios. Cada cita está vinculada a una
 * propiedad, un usuario que solicita la visita y un asesor que la atiende.
 *
 * Flujo de datos:
 * Una cita se crea con status 'pending', puede ser 'confirmed' por el asesor,
 * 'completed' tras la visita, 'cancelled' si se cancela, o 'rescheduled'
 * si se reprograma. Se registran datos de contacto del cliente, notas y
 * resultado de la visita (asistió/no asistió, propiedad vendida/no vendida).
 *
 * Relaciones principales:
 * - User (usuario que solicita la cita)
 * - Property (propiedad a visitar)
 * - User (asesor que atiende la cita)
 *
 * El scope forRole() controla la visibilidad de citas según el rol del
 * usuario: administradores ven todas, asesores ven las suyas, y usuarios
 * comunes ven solo las que solicitaron.
 */
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

  

    /**
     * Relación con el usuario que solicitó la cita.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con la propiedad que será visitada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relación con el asesor inmobiliario que atiende la cita.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    /**
     * Filtra las citas según el rol del usuario autenticado.
     *
     * - Super Admin / Administrador: ve todas las citas del sistema.
     * - Auditor: ve todas las citas (solo lectura).
     * - Asesor Inmobiliario: ve solo las citas donde es el asesor asignado.
     * - Usuarios comunes: ven solo las citas que ellos solicitaron.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user usuario para filtrar
     * @return \Illuminate\Database\Eloquent\Builder
     */
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

    /**
     * Filtra citas con estado 'pending' (pendientes de confirmación).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Obtiene la hora de la cita formateada (HH:MM).
     * Retorna '--:--' si no hay fecha programada.
     *
     * @return string hora formateada
     */
    public function getTimeAttribute()
    {
        return $this->scheduled_date ? $this->scheduled_date->format('H:i') : '--:--';
    }

    /**
     * Obtiene la dirección completa de la propiedad asociada a la cita.
     * Si la propiedad no tiene dirección, retorna un valor por defecto.
     *
     * @return string dirección de la propiedad o mensaje por defecto
     */
    public function getFullAddressAttribute()
    {
        return $this->property ? $this->property->full_location : 'Dirección no disponible';
    }

    /**
     * Obtiene la etiqueta legible del estado de la cita.
     * Convierte los valores internos del estado a sus equivalentes en español.
     *
     * @return string etiqueta del estado en español
     */
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
