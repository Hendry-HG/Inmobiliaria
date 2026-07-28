<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa una conversacion de chat entre un cliente y un asesor inmobiliario.
 *
 * Cada conversacion esta vinculada a dos usuarios: el cliente que inicia el contacto
 * y el asesor inmobiliario que responde. Las conversaciones contienen mensajes en
 * tiempo real (via WebSockets) y se utilizan para la comunicacion directa sobre
 * propiedades, citas y consultas generales.
 *
 * Relaciones:
 * - client: Usuario cliente que participa en la conversacion.
 * - asesor: Usuario asesor inmobiliario que participa en la conversacion.
 * - messages: Todos los mensajes de la conversacion, ordenados cronologicamente.
 * - lastMessage: El ultimo mensaje de la conversacion (para vistas de listado).
 *
 * Campos clave:
 * - last_message_at: Timestamp del ultimo mensaje, util para ordenar conversaciones.
 * - is_active: Bandera que permite archivar/conversaciones sin eliminarlas.
 *
 * @package App\Models
 */
class Conversation extends Model
{
    protected $table = 'conversations';

    protected $fillable = [
        'client_id', 'asesor_id', 'subject', 'last_message_at', 'is_active'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Obtiene el usuario cliente que participa en la conversacion.
     *
     * @return BelongsTo Relacion con el modelo User (cliente).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Obtiene el usuario asesor inmobiliario que participa en la conversacion.
     *
     * @return BelongsTo Relacion con el modelo User (asesor).
     */
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    /**
     * Obtiene todos los mensajes de la conversacion ordenados cronologicamente.
     *
     * @return HasMany Relacion con el modelo Message, ordenados por fecha de creacion ascendente.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    /**
     * Obtiene el ultimo mensaje de la conversacion.
     *
     * Util para mostrar la vista previa del ultimo mensaje en listados de conversaciones.
     *
     * @return HasOne Relacion con el modelo Message (ultimo mensaje).
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latest();
    }
}
