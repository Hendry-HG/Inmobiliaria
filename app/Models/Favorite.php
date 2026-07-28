<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa la relacion de favorito entre un usuario y una propiedad.
 *
 * Permite a los usuarios guardar propiedades de interes para acceso rapedio.
 * Cada registro indica que un usuario especifico ha marcado una propiedad como favorita.
 *
 * Relaciones:
 * - user: Usuario que marco la propiedad como favorita.
 * - property: Propiedad marcada como favorita.
 *
 * @package App\Models
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'property_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene el usuario que marco la propiedad como favorita.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la propiedad marcada como favorita.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo Property.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
