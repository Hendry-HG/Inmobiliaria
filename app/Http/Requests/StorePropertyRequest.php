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
            /** Descripcion detallada de la propiedad. Obligatorio y de tipo texto. */
            'description' => 'required|string',
            /** Precio de la propiedad. Obligatorio, numerico y no puede ser negativo. */
            'price' => 'required|numeric|min:0',
            /** Tipo de operacion: venta, alquiler o ambos. Solo valores permitidos. */
            'type' => ['required', Rule::in(['venta', 'alquiler', 'venta/alquiler'])],
            /** Estado de la publicacion. Solo valores del enumerado permitido. */
            'status' => ['required', Rule::in(['borrador', 'pendiente', 'publicada', 'vendida', 'alquilada', 'inactiva'])],
            /** Pais al que pertenece la propiedad. Debe existir en la tabla countries. */
            'country_id' => 'required|exists:countries,id',
            /** Estado/region al que pertenece la propiedad. Debe existir en la tabla states. */
            'state_id' => 'required|exists:states,id',
            /** Municipio al que pertenece la propiedad. Debe existir en la tabla municipalities. */
            'municipality_id' => 'required|exists:municipalities,id',
            /** Parroquia (opcional). Debe existir en la tabla parishes si se proporciona. */
            'parish_id' => 'nullable|exists:parishes,id',
            /** Ciudad (opcional). Debe existir en la tabla cities si se proporciona. */
            'city_id' => 'nullable|exists:cities,id',
            /** Direccion fisica de la propiedad. Opcional, maximo 500 caracteres. */
            'address' => 'nullable|string|max:500',
            /** Cantidad de habitaciones. Opcional, entero no negativo. */
            'bedrooms' => 'nullable|integer|min:0',
            /** Cantidad de banos. Opcional, entero no negativo. */
            'bathrooms' => 'nullable|integer|min:0',
            /** Cantidad de espacios de estacionamiento. Opcional, entero no negativo. */
            'parking_spaces' => 'nullable|integer|min:0',
            /** Superficie en metros cuadrados. Opcional, numerico no negativo. */
            'area' => 'nullable|numeric|min:0',
            /** Categoria de la propiedad. Opcional, debe existir en la tabla categories. */
            'category_id' => 'nullable|exists:categories,id',
            /** Arreglo de imagenes obligatorio. Minimo 1 imagen, maximo 15 imagenes. */
            'images' => 'required|array|min:1|max:15',
            /** Cada imagen debe ser un archivo de imagen JPG o PNG, maximo 2MB. */
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
        ];
    }
}
