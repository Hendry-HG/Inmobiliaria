<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa un pais en la jerarquia geografica del sistema.
 *
 * Pais es el nivel mas alto de la jerarquia geografica: Country > State > Municipality > Parish > City.
 * Almacena informacion de formato telefonico (codigo de pais, mascara, longitudes)
 * utilizada para validacion de telefonos de usuarios y propiedades.
 *
 * Relaciones:
 * - states: Estados/regiones que pertenecen al pais.
 * - users: Usuarios registrados con direccion en este pais.
 * - properties: Propiedades ubicadas en este pais.
 *
 * Atributos virtuales:
 * - phone_mask: Mascara de formato telefonico (ej: '000-0000000' para Venezuela).
 * - phone_max_length: Longitud maxima del numero telefonico local.
 *
 * @package App\Models
 */
class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'phone_code', 'phone_format', 'phone_min_length', 'phone_max_length'
    ];

    /**
     * Obtiene todos los estados que pertenecen al pais.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo State.
     */
    public function states()
    {
        return $this->hasMany(State::class);
    }

    /**
     * Obtiene todos los usuarios registrados con direccion en este pais.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo User.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtiene todas las propiedades ubicadas en este pais.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Property.
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Obtiene la mascara de formato telefonico para el pais.
     *
     * Si el pais tiene un formato personalizado (phone_format), lo retorna directamente.
     * De lo contrario, busca un formato por defecto segun el codigo de pais telefónico.
     *
     * @return string Mascara de formato (ej: '000-0000000' para Venezuela).
     */
    public function getPhoneMaskAttribute()
    {
        if ($this->phone_format) {
            return $this->phone_format;
        }

        // Formatos por defecto según código de país
        $defaults = [
            '58' => '000-0000000',      // Venezuela: 412 1234567
            '1' => '000-000-0000',       // USA/Canadá: 123-456-7890
            '52' => '000-000-0000',      // México: 55 1234 5678
            '54' => '000-000-0000',      // Argentina: 11 1234-5678
            '57' => '000-000-0000',      // Colombia: 300 123 4567
            '34' => '000-000-000',       // España: 612 345 678
            '56' => '0-0000-0000',       // Chile: 9 1234 5678
            '51' => '000-000-000',       // Perú: 987 654 321
        ];

        return $defaults[$this->phone_code] ?? '000-000-0000';
    }

    /**
     * Obtiene la longitud maxima del numero telefonico local (sin codigo de pais).
     *
     * Si el pais tiene una longitud personalizada (phone_max_length), la retorna.
     * De lo contrario, busca una longitud por defecto segun el codigo de pais.
     *
     * @return int Longitud maxima en digitos del numero local.
     */
    public function getPhoneMaxLengthAttribute()
    {
        if ($this->attributes['phone_max_length'] ?? null) {
            return $this->attributes['phone_max_length'];
        }

        // Longitudes por país (solo dígitos locales)
        $lengths = [
            '58' => 10,  // Venezuela: 4121234567 = 10 dígitos
            '1' => 10,   // USA: 1234567890 = 10 dígitos
            '52' => 10,  // México: 10 dígitos
            '54' => 10,  // Argentina: 10 dígitos
            '57' => 10,  // Colombia: 10 dígitos
            '34' => 9,   // España: 9 dígitos
        ];

        return $lengths[$this->phone_code] ?? 10;
    }
}
