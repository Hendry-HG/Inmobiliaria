<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa una parroquia dentro de un municipio.
 *
 * Forma el cuarto nivel de la jerarquia geografica: Country > State > Municipality > Parish > City.
 * Cada parroquia pertenece a un municipio y contiene ciudades.
 * La parroquia es una division administrativa comun en Venezuela y otros paises latinoamericanos.
 *
 * Relaciones:
 * - municipality: Municipio al que pertenece la parroquia.
 * - cities: Ciudades que pertenecen a la parroquia.
 * - properties: Propiedades ubicadas en esta parroquia.
 *
 * @package App\Models
 */
class Parish extends Model
{
    protected $fillable = [
        'name',
        'municipality_id'
    ];

    /**
     * Obtiene el municipio al que pertenece la parroquia.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo Municipality.
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Obtiene todas las ciudades que pertenecen a la parroquia.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo City.
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Obtiene todas las propiedades ubicadas en esta parroquia.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Property.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'parish_id');
    }
}
