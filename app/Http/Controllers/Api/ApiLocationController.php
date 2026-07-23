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

class ApiLocationController extends Controller
{
    const CACHE_DURATION = 3600; // 1 hora

    /**
     * Obtener todos los Países
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
     * Obtener Estados de un País
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
     * Obtener Municipios de un Estado
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
     * Obtener Parroquias de un Municipio
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
     * Obtener Ciudades de una Parroquia
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

    private function generatePlaceholder($mask)
    {
        $placeholder = str_replace('0', '1', $mask);
        $placeholder = str_replace('1', 'X', $placeholder);
        return $placeholder;
    }
}
