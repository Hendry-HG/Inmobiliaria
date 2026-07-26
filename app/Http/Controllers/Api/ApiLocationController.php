<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

/**
 * Controlador API de Ubicaciones Geograficas
 *
 * Proporciona endpoints JSON para la jerarquia geografica del sistema:
 * Paises > Estados > Municipios > Parroquias > Ciudades. Incluye
 * configuracion telefonica por pais con codigo, formato y longitudes.
 * Utiliza Cache para optimizar consultas frecuentes (1 hora).
 *
 * @package App\Http\Controllers\Api
 */
class ApiLocationController extends Controller
{
    /**
     * Duracion del cache en segundos (1 hora).
     * @var int
     */
    const CACHE_DURATION = 3600; // 1 hora

    /**
     * Obtiene todos los paises ordenados alfabeticamente.
     *
     * Flujo de datos:
     * 1. Consulta el cache 'api_countries_all' (1 hora de duracion)
     * 2. Si no existe en cache, consulta la tabla countries con id y name
     * 3. Retorna JSON con la lista de paises
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountries(): JsonResponse
    {
        $cacheKey = "api_countries_all";

        $countries = Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            return Country::orderBy('name')
                ->get(['id', 'name']);
        });

        return response()->json($countries);
    }

    /**
     * Obtiene los estados de un pais especifico.
     *
     * Flujo de datos:
     * 1. Consulta el cache 'api_states_{countryId}' (1 hora)
     * 2. Filtra estados por country_id con orden alfabeticos
     * 3. Retorna JSON con la lista de estados del pais
     *
     * @param int $countryId Identificador del pais
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStates($countryId): JsonResponse
    {
        $cacheKey = "api_states_{$countryId}";

        $states = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($countryId) {
            return State::where('country_id', $countryId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        return response()->json($states);
    }

    /**
     * Obtiene los municipios de un estado especifico.
     *
     * Flujo de datos:
     * 1. Consulta el cache 'api_municipalities_{stateId}' (1 hora)
     * 2. Filtra municipios por state_id con orden alfabeticos
     * 3. Retorna JSON con la lista de municipios del estado
     *
     * @param int $stateId Identificador del estado
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMunicipalities($stateId): JsonResponse
    {
        $cacheKey = "api_municipalities_{$stateId}";

        $municipalities = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($stateId) {
            return Municipality::where('state_id', $stateId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        return response()->json($municipalities);
    }

    /**
     * Obtiene las parroquias de un municipio especifico.
     *
     * Flujo de datos:
     * 1. Consulta el cache 'api_parishes_{municipalityId}' (1 hora)
     * 2. Filtra parroquias por municipality_id con orden alfabeticos
     * 3. Retorna JSON con la lista de parroquias del municipio
     *
     * @param int $municipalityId Identificador del municipio
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParishes($municipalityId): JsonResponse
    {
        $cacheKey = "api_parishes_{$municipalityId}";

        $parishes = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($municipalityId) {
            return Parish::where('municipality_id', $municipalityId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        return response()->json($parishes);
    }

    /**
     * Obtiene las ciudades de una parroquia especifica.
     *
     * Flujo de datos:
     * 1. Consulta el cache 'api_cities_{parishId}' (1 hora)
     * 2. Filtra ciudades por parish_id con orden alfabeticos
     * 3. Retorna JSON con la lista de ciudades de la parroquia
     *
     * @param int $parishId Identificador de la parroquia
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities($parishId): JsonResponse
    {
        $cacheKey = "api_cities_{$parishId}";

        $cities = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($parishId) {
            return City::where('parish_id', $parishId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        return response()->json($cities);
    }

    /**
     * Obtiene la configuracion telefonica de un pais.
     *
     * Flujo de datos:
     * 1. Busca el pais por su identificador
     * 2. Si no existe, retorna valores por defecto de Venezuela (+58)
     * 3. Retorna JSON con codigo telefonico, mascara, longitudes minima y maxima,
     *    y un placeholder generado a partir de la mascara
     *
     * @param int $countryId Identificador del pais
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPhoneConfig($countryId)
    {
        $country = Country::find($countryId);

        if (!$country) {
            return response()->json([
                'code' => '+58',
                'mask' => '000-0000000',
                'minLength' => 10,
                'maxLength' => 10,
                'placeholder' => '412 1234567'
            ]);
        }

        return response()->json([
            'code' => '+' . $country->phone_code,
            'mask' => $country->phone_mask ?? '000-0000000',
            'minLength' => $country->phone_min_length ?? 10,
            'maxLength' => $country->phone_max_length ?? 10,
            'placeholder' => $this->generatePlaceholder($country->phone_mask ?? '000-0000000')
        ]);
    }

    /**
     * Genera un ejemplo de numero de telefono a partir de una mascara.
     *
     * Flujo de datos:
     * 1. Reemplaza los digitos '0' de la mascara por '1'
     * 2. Luego reemplaza los '1' resultantes por 'X' para mostrar formato
     *
     * @param string $mask Mascara del telefono (ej: '000-0000000')
     * @return string Placeholder generado (ej: 'XXX-XXXXXXX')
     */
    private function generatePlaceholder($mask)
    {
        $placeholder = str_replace('0', '1', $mask);
        $placeholder = str_replace('1', 'X', $placeholder);
        return $placeholder;
    }
}
