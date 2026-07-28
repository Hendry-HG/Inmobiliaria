<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa un municipio dentro de un estado.
 *
 * Forma el tercer nivel de la jerarquia geografica: Country > State > Municipality > Parish > City.
 * Cada municipio pertenece a un estado y contiene parroquias que a su vez contienen ciudades.
 *
 * Relaciones:
 * - state: Estado al que pertenece el municipio.
 * - parishes: Parroquias que pertenecen al municipio.
 * - properties: Propiedades ubicadas en este municipio.
 *
 * @package App\Models
 */
class Municipality extends Model
{
    protected $fillable = [
        'name',
        'state_id'
    ];

    /**
     * Obtiene el estado al que pertenece el municipio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo State.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Obtiene todas las parroquias que pertenecen al municipio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Parish.
     */
    public function parishes()
    {
        return $this->hasMany(Parish::class);
    }

    /**
     * Obtiene todas las propiedades ubicadas en este municipio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Property.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'municipality_id');
    }
}
