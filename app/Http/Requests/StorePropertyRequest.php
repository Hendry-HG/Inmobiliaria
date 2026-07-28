<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Solicitud de formulario para la creacion de una nueva propiedad inmobiliaria.
 *
 * Controla el acceso y las reglas de validacion al registrar una propiedad.
 * Solo los usuarios con rol de Super Admin, Administrador o Asesor Inmobiliario
 * pueden crear propiedades. Todas las imagenes son obligatorias al momento
 * de la creacion.
 */
class StorePropertyRequest extends FormRequest
{
    /**
     * Determina si el usuario autenticado puede realizar esta solicitud.
     *
     * Permite el acceso exclusivamente a usuarios con los siguientes roles:
     * - Super Admin: acceso total al sistema.
     * - Administrador: acceso de gestion general.
     * - Asesor Inmobiliario: puede crear propiedades propias.
     *
     * @return bool true si el usuario tiene un rol permitido, false en caso contrario.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->hasRole('Super Admin') || $user->hasRole('Administrador') || $user->hasRole('Asesor Inmobiliario'));
    }

    /**
     * Define las reglas de validacion para los campos de una nueva propiedad.
     *
     * @return array<string, mixed> Arreglo asociativo donde las claves son los nombres
     *                               de los campos y los valores son las reglas de validacion.
     */
    public function rules(): array
    {
        return [
            /** Titulo de la propiedad. Obligatorio, texto y limite de 255 caracteres. */
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'price' => 'required|numeric|min:0|max:99999999999',
            'type' => ['required', Rule::in(['venta', 'alquiler', 'venta/alquiler'])],
            'status' => ['required', Rule::in(['borrador', 'pendiente', 'publicada', 'vendida', 'alquilada', 'inactiva'])],
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'municipality_id' => 'required|exists:municipalities,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'nullable|string|max:500',
            'bedrooms' => 'nullable|integer|min:0|max:50',
            'bathrooms' => 'nullable|integer|min:0|max:50',
            'parking_spaces' => 'nullable|integer|min:0|max:100',
            'area' => 'nullable|numeric|min:0|max:99999999',
            'land_area' => 'nullable|numeric|min:0|max:99999999',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'required|array|min:1|max:15',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Define los mensajes de error personalizados para las reglas de validacion.
     *
     * Estos mensajes reemplazan los valores por defecto de Laravel para brindar
     * retroalimentacion mas clara al usuario cuando ocurren errores de validacion
     * relacionados con las imagenes.
     *
     * @return array<string, string> Arreglo asociativo donde las claves son las reglas
     *                               de validacion y los valores son los mensajes en espanol.
     */
    public function messages(): array
    {
        return [
            'images.required' => 'Debes subir al menos 1 imagen de la propiedad.',
            'images.min' => 'Debes subir al menos 1 imagen de la propiedad.',
            'images.max' => 'Máximo 15 imágenes permitidas.',
            'images.*.mimes' => 'Solo se permiten imágenes en formato JPG o PNG.',
            'images.*.max' => 'Cada imagen debe pesar menos de 2MB.',
            'description.max' => 'La descripción no puede exceder 10,000 caracteres.',
            'price.max' => 'El precio no puede exceder $99,999,999,999.',
            'bedrooms.max' => 'No pueden haber más de 50 habitaciones.',
            'bathrooms.max' => 'No pueden haber más de 50 baños.',
            'parking_spaces.max' => 'No pueden haber más de 100 espacios de estacionamiento.',
            'area.max' => 'El área no puede exceder 99,999,999 m².',
            'land_area.max' => 'El área del terreno no puede exceder 99,999,999 m².',
        ];
    }
}
