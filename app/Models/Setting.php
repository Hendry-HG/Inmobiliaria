<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que almacena configuraciones generales del sistema en pares clave-valor.
 *
 * Proporciona una forma flexible de guardar ajustes de la aplicacion sin
 * necesidad de tablas dedicadas. Cada configuracion tiene una clave unica,
 * un valor (almacenado como JSON), un tipo para casteo automatico y un grupo
 * para organizacion.
 *
 * Metodos estaticos:
 * - get(): Obtiene el valor de una configuracion por su clave.
 * - set(): Crea o actualiza una configuracion.
 *
 * @package App\Models
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'type', 'group', 'label', 'description', 'order'
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Obtiene el valor de una configuracion por su clave.
     *
     * Busca la configuracion en la base de datos y aplica el casteo
     * correspondiente segun su tipo (boolean, number, json, text).
     * Si no encuentra la clave, retorna el valor por defecto proporcionado.
     *
     * @param string $key Clave de la configuracion a buscar.
     * @param mixed $default Valor por defecto si no se encuentra la configuracion.
     * @return mixed Valor de la configuracion casteado al tipo correspondiente.
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        return match($setting->type) {
            'boolean' => (bool) $value,
            'number' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Crea o actualiza una configuracion en la base de datos.
     *
     * Utiliza updateOrCreate para garantizar que solo exista una entrada
     * por clave. Si la clave ya existe, actualiza el valor; si no, crea
     * un nuevo registro.
     *
     * @param string $key Clave unica de la configuracion.
     * @param mixed $value Valor a almacenar (se guarda como JSON).
     * @param string $type Tipo de configuracion para casteo ('text', 'boolean', 'number', 'json').
     * @param string $group Grupo de organizacion de la configuracion.
     * @return \App\Models\Setting Instancia del modelo creado o actualizado.
     */
    public static function set($key, $value, $type = 'text', $group = 'general')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group
            ]
        );
    }
}
