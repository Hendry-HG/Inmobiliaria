<?php
// app/Http/Controllers/Admin/PhoneController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PhoneController extends Controller
{
    const CACHE_DURATION = 86400; // 24 horas

    public function __construct()
    {
        $this->middleware(['auth', 'role:Super Admin|Administrador'])->except([
            'getPresets',
            'getPhoneCodes',
            'getPhoneConfig'
        ]);
    }

    public function index()
    {
        $countries = Country::orderBy('name')->paginate(20);
        return view('modulos.phones.index', compact('countries'));
    }

    public function edit(Country $country)
    {
        return view('modulos.phones.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:3',
                'regex:/^[A-Z]{3}$/',
                Rule::unique('countries')->ignore($country->id)
            ],
            'phone_code' => [
                'required',
                'string',
                'max:5',
                'regex:/^\+[0-9]{1,4}$/',
                Rule::unique('countries')->ignore($country->id)
            ],
            'phone_format' => [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9\-\(\)\s\+]+$/'
            ],
            'phone_min_length' => 'required|integer|min:5|max:15',
            'phone_max_length' => 'required|integer|min:5|max:15|gte:phone_min_length',
        ], [
            'code.unique' => 'Este código ISO ya está en uso por otro país.',
            'code.regex' => 'El código ISO debe tener 3 letras mayúsculas.',
            'phone_code.unique' => 'Este código telefónico ya está en uso por otro país.',
            'phone_code.regex' => 'El código telefónico debe comenzar con + seguido de números.',
            'phone_format.regex' => 'El formato solo puede contener números, guiones, paréntesis y espacios.',
            'phone_max_length.gte' => 'La longitud máxima debe ser mayor o igual a la mínima.',
        ]);

        try {
            // Sanitizar entrada
            if (isset($validated['code'])) {
                $validated['code'] = strtoupper(preg_replace('/[^A-Z]/', '', $validated['code']));
            }

            if (!str_starts_with($validated['phone_code'], '+')) {
                $validated['phone_code'] = '+' . $validated['phone_code'];
            }

            $country->update($validated);

            // Limpiar caché
            Cache::forget('api_phone_presets');
            Cache::forget('api_phone_codes');
            Cache::forget("api_phone_config_{$country->id}");

            return redirect()->route('admin.phones.index')
                ->with('success', "Configuración de {$country->name} actualizada correctamente.");
        } catch (\Exception $e) {
            Log::error('Error actualizando configuración telefónica', [
                'country_id' => $country->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la configuración.');
        }
    }

    public function bulkUpdate(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('actualizar configuracion telefonica')) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $validated = $request->validate([
            'countries' => 'required|array|max:50',
            'countries.*.id' => 'required|exists:countries,id',
            'countries.*.code' => 'nullable|string|max:3|regex:/^[A-Z]{3}$/',
            'countries.*.phone_code' => 'required|string|max:5|regex:/^\+[0-9]{1,4}$/',
            'countries.*.phone_format' => 'required|string|max:50|regex:/^[0-9\-\(\)\s\+]+$/',
            'countries.*.phone_min_length' => 'required|integer|min:5|max:15',
            'countries.*.phone_max_length' => 'required|integer|min:5|max:15|gte:countries.*.phone_min_length',
        ]);

        $updated = 0;
        $errors = [];

        foreach ($validated['countries'] as $data) {
            try {
                $country = Country::find($data['id']);
                if ($country) {
                    if (isset($data['code'])) {
                        $data['code'] = strtoupper(preg_replace('/[^A-Z]/', '', $data['code']));
                    }
                    if (!str_starts_with($data['phone_code'], '+')) {
                        $data['phone_code'] = '+' . $data['phone_code'];
                    }
                    $country->update($data);
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error actualizando país ID {$data['id']}: " . $e->getMessage();
            }
        }

        Cache::forget('api_phone_presets');
        Cache::forget('api_phone_codes');

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => "{$updated} países actualizados, pero hubo errores.",
                'errors' => $errors
            ], 207);
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} países actualizados correctamente."
        ]);
    }

    /**
     * API pública para obtener presets de teléfonos - SIN AUTENTICACIÓN
     */
    public function getPresets()
    {
        return Cache::remember('api_phone_presets', self::CACHE_DURATION, function () {
            return response()->json([
                'america' => [
                    ['name' => 'Estados Unidos / Canadá', 'code' => '+1', 'iso' => 'USA', 'format' => '000-000-0000', 'min' => 10, 'max' => 10],
                    ['name' => 'México', 'code' => '+52', 'iso' => 'MEX', 'format' => '000-000-0000', 'min' => 10, 'max' => 10],
                    ['name' => 'Argentina', 'code' => '+54', 'iso' => 'ARG', 'format' => '000-000-0000', 'min' => 10, 'max' => 10],
                    ['name' => 'Brasil', 'code' => '+55', 'iso' => 'BRA', 'format' => '(00) 00000-0000', 'min' => 10, 'max' => 11],
                    ['name' => 'Colombia', 'code' => '+57', 'iso' => 'COL', 'format' => '000-000-0000', 'min' => 10, 'max' => 10],
                    ['name' => 'Venezuela', 'code' => '+58', 'iso' => 'VEN', 'format' => '000-0000000', 'min' => 10, 'max' => 10],
                    ['name' => 'Chile', 'code' => '+56', 'iso' => 'CHL', 'format' => '0-0000-0000', 'min' => 9, 'max' => 9],
                    ['name' => 'Perú', 'code' => '+51', 'iso' => 'PER', 'format' => '000-000-000', 'min' => 9, 'max' => 9],
                    ['name' => 'Ecuador', 'code' => '+593', 'iso' => 'ECU', 'format' => '00-000-0000', 'min' => 9, 'max' => 9],
                    ['name' => 'Bolivia', 'code' => '+591', 'iso' => 'BOL', 'format' => '0-000-0000', 'min' => 8, 'max' => 8],
                    ['name' => 'Paraguay', 'code' => '+595', 'iso' => 'PRY', 'format' => '000-000000', 'min' => 9, 'max' => 9],
                    ['name' => 'Uruguay', 'code' => '+598', 'iso' => 'URY', 'format' => '0-000-0000', 'min' => 8, 'max' => 8],
                    ['name' => 'Costa Rica', 'code' => '+506', 'iso' => 'CRI', 'format' => '0000-0000', 'min' => 8, 'max' => 8],
                    ['name' => 'Panamá', 'code' => '+507', 'iso' => 'PAN', 'format' => '0000-0000', 'min' => 8, 'max' => 8],
                    ['name' => 'República Dominicana', 'code' => '+1', 'iso' => 'DOM', 'format' => '000-000-0000', 'min' => 10, 'max' => 10],
                ],
                'europa' => [
                    ['name' => 'España', 'code' => '+34', 'iso' => 'ESP', 'format' => '000-000-000', 'min' => 9, 'max' => 9],
                    ['name' => 'Francia', 'code' => '+33', 'iso' => 'FRA', 'format' => '0-00-00-00-00', 'min' => 9, 'max' => 9],
                    ['name' => 'Alemania', 'code' => '+49', 'iso' => 'DEU', 'format' => '0000-0000000', 'min' => 10, 'max' => 11],
                    ['name' => 'Italia', 'code' => '+39', 'iso' => 'ITA', 'format' => '000-000-0000', 'min' => 10, 'max' => 10],
                    ['name' => 'Reino Unido', 'code' => '+44', 'iso' => 'GBR', 'format' => '0000-000000', 'min' => 10, 'max' => 10],
                    ['name' => 'Portugal', 'code' => '+351', 'iso' => 'PRT', 'format' => '000-000-000', 'min' => 9, 'max' => 9],
                    ['name' => 'Países Bajos', 'code' => '+31', 'iso' => 'NLD', 'format' => '00-00000000', 'min' => 9, 'max' => 9],
                    ['name' => 'Bélgica', 'code' => '+32', 'iso' => 'BEL', 'format' => '000-00-00-00', 'min' => 8, 'max' => 9],
                    ['name' => 'Suiza', 'code' => '+41', 'iso' => 'CHE', 'format' => '000-000-0000', 'min' => 9, 'max' => 9],
                ],
                'asia' => [
                    ['name' => 'China', 'code' => '+86', 'iso' => 'CHN', 'format' => '000-0000-0000', 'min' => 11, 'max' => 11],
                    ['name' => 'Japón', 'code' => '+81', 'iso' => 'JPN', 'format' => '00-0000-0000', 'min' => 10, 'max' => 10],
                    ['name' => 'Corea del Sur', 'code' => '+82', 'iso' => 'KOR', 'format' => '00-0000-0000', 'min' => 10, 'max' => 10],
                    ['name' => 'India', 'code' => '+91', 'iso' => 'IND', 'format' => '00000-00000', 'min' => 10, 'max' => 10],
                ],
            ]);
        });
    }

    public function getPhoneCodes()
    {
        return Cache::remember('api_phone_codes', self::CACHE_DURATION, function () {
            $countries = Country::whereNotNull('phone_code')
                ->where('phone_code', '!=', '')
                ->orderBy('name')
                ->get(['id', 'name', 'code as iso', 'phone_code', 'phone_format', 'phone_min_length', 'phone_max_length']);

            return response()->json($countries);
        });
    }

    public function getPhoneConfig($countryId)
    {
        $cacheKey = "api_phone_config_{$countryId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($countryId) {
            $country = Country::find($countryId);

            if (!$country || !$country->phone_code) {
                return response()->json([
                    'code' => '+58',
                    'mask' => '000-0000000',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'placeholder' => '412 1234567'
                ]);
            }

            return response()->json([
                'code' => $country->phone_code,
                'mask' => $country->phone_format ?? '000-0000000',
                'minLength' => $country->phone_min_length ?? 10,
                'maxLength' => $country->phone_max_length ?? 10,
                'placeholder' => $this->generatePlaceholder($country->phone_format ?? '000-0000000')
            ]);
        });
    }

    private function generatePlaceholder($mask)
    {
        $numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $result = '';
        $numIndex = 0;

        for ($i = 0; $i < strlen($mask); $i++) {
            if ($mask[$i] === '0') {
                $result .= $numbers[$numIndex % count($numbers)];
                $numIndex++;
            } else {
                $result .= $mask[$i];
            }
        }

        return $result;
    }
}