<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa una ciudad dentro de una parroquia.
 *
 * Forma el quinto y ultimo nivel de la jerarquia geografica:
 * Country > State > Municipality > Parish > City.
 * Cada ciudad pertenece a una parroquia y esta vinculada a las propiedades
 * ubicadas en esa zona.
 *
 * Relaciones:
 * - parish: Parroquia a la que pertenece la ciudad.
 * - properties: Propiedades ubicadas en esta ciudad.
 *
 * @package App\Models
 */
class City extends Model
{
    protected $fillable = [
        'name',
        'parish_id'
    ];

    /**
     * Obtiene la parroquia a la que pertenece la ciudad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo Parish.
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Obtiene todas las propiedades ubicadas en esta ciudad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Property.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'city_id');
    }
}
