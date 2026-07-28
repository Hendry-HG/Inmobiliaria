<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa un estado/region dentro de un pais.
 *
 * Forma el segundo nivel de la jerarquia geografica: Country > State > Municipality > Parish > City.
 * Cada estado pertenece a un pais y contiene municipios que a su vez contienen parroquias.
 *
 * Relaciones:
 * - country: Pais al que pertenece el estado.
 * - municipalities: Municipios que pertenecen al estado.
 * - properties: Propiedades ubicadas en este estado.
 *
 * @package App\Models
 */
class State extends Model
{
    protected $fillable = [
        'name',
        'country_id'
    ];

    /**
     * Obtiene el pais al que pertenece el estado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo Country.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Obtiene todos los municipios que pertenecen al estado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Municipality.
     */
    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }

    /**
     * Obtiene todas las propiedades ubicadas en este estado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Property.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'state_id');
    }
}
