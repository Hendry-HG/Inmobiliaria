<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Solicitud de formulario para la actualizacion de una propiedad inmobiliaria existente.
 *
 * Controla el acceso y las reglas de validacion al modificar una propiedad.
 * Los Asesores Inmobiliarios solo pueden actualizar propiedades que les pertenezcan.
 * Super Admin y Administradores pueden actualizar cualquier propiedad.
 * Permite manejar imagenes existentes (eliminadas) y nuevas imagenes,
 * garantizando que la propiedad siempre tenga al menos 1 imagen.
 */
class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determina si el usuario autenticado puede realizar esta solicitud.
     *
     * Verifica primero que el usuario tenga un rol permitido (Super Admin,
     * Administrador o Asesor Inmobiliario). Adicionalmente, los Asesores
     * Inmobiliarios solo pueden modificar propiedades cuyo user_id coincida
     * con su propio ID, lo que garantiza que no alteren propiedades ajenas.
     *
     * @return bool true si el usuario tiene permiso para actualizar la propiedad, false en caso contrario.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $property = $this->route('property');

        if (!$user || !($user->hasRole('Super Admin') || $user->hasRole('Administrador') || $user->hasRole('Asesor Inmobiliario'))) {
            return false;
        }

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Define las reglas de validacion para los campos de una propiedad existente.
     *
     * Calcula dinamicamente el total de imagenes considerando las imagenes
     * existentes que no seran eliminadas mas las nuevas imagenes subidas.
     * Si el total supera 15, bloquea la carga de nuevas imagenes con 'max:0'.
     * Las imagenes no son obligatorias en actualizacion (a diferencia de creacion),
     * ya que la propiedad ya puede tener imagenes existentes.
     *
     * @return array<string, mixed> Arreglo asociativo donde las claves son los nombres
     *                               de los campos y los valores son las reglas de validacion.
     */
    public function rules(): array
    {
        $property = $this->route('property');

        $deletedImages = [];
        if ($this->filled('deleted_images')) {
            $ids = explode(',', $this->input('deleted_images'));
            $deletedImages = $property->images()->whereIn('id', $ids)->pluck('id')->toArray();
        }

        $existingImagesCount = $property->images()->whereNotIn('id', $deletedImages)->count();
        $newImagesCount = $this->hasFile('images') ? count($this->file('images')) : 0;
        $totalImages = $existingImagesCount + $newImagesCount;

        return [
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
            'images' => $totalImages > 15 ? 'max:0' : 'nullable|array|max:15',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Define los mensajes de error personalizados para las reglas de validacion.
     *
     * Proporciona retroalimentacion clara en espanol sobre las restricciones
     * de imagenes durante la actualizacion de una propiedad.
     *
     * @return array<string, string> Arreglo asociativo donde las claves son las reglas
     *                               de validacion y los valores son los mensajes en espanol.
     */
    public function messages(): array
    {
        return [
            'images.max' => 'Máximo 15 imágenes en total.',
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

    /**
     * Hook que se ejecuta despues de que las reglas de validacion base han sido aplicadas.
     *
     * Realiza una validacion adicional para garantizar que, despues de considerar
     * las imagenes eliminadas y las nuevas imagenes subidas, la propiedad
     * mantenga al menos 1 imagen. Esto evita que quede una propiedad sin imagenes
     * visibles para el usuario final.
     *
     * @param  \Illuminate\Validation\Validator $validator Instancia del validador de Laravel.
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $property = $this->route('property');

            $deletedImages = [];
            if ($this->filled('deleted_images')) {
                $ids = explode(',', $this->input('deleted_images'));
                $deletedImages = $property->images()->whereIn('id', $ids)->pluck('id')->toArray();
            }

            $existingImagesCount = $property->images()->whereNotIn('id', $deletedImages)->count();
            $newImagesCount = $this->hasFile('images') ? count($this->file('images')) : 0;
            $totalImages = $existingImagesCount + $newImagesCount;

            if ($totalImages < 1) {
                $validator->errors()->add('images', 'La propiedad debe tener al menos 1 imagen.');
            }
        });
    }
}
