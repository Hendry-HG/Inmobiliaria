<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use App\Traits\AuditTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Servicio centralizado para la gestion de propiedades inmobiliarias.
 *
 * Encargado de:
 * - Construir cadenas de ubicacion geografica a partir de IDs de subdivisiones.
 * - Procesar y almacenar imagenes (originales y miniaturas).
 * - Enviar notificaciones internas por creacion y actualizacion de propiedades.
 * - Calcular diferencias entre versiones de un formulario (change tracking).
 * - Limpiar el contenido HTML de la descripcion de la propiedad.
 *
 * Utiliza AuditTrait para registrar auditorias de las operaciones realizadas.
 */
class PropertyService
{
    use AuditTrait;

    /**
     * Construye una cadena de ubicacion legible a partir de los IDs geograficos.
     *
     * Flujo:
     * 1. Extrae los IDs de pais, estado, municipio, parroquia y ciudad del array $data.
     * 2. Si ningun ID esta presente, retorna la direccion libre como fallback.
     * 3. Consulta cada modelo geografico (Country, State, Municipality, Parish, City)
     *    solo si el ID correspondiente fue proporcionado.
     * 4. Obtiene el atributo 'name' de cada modelo encontrado y los ordena de mayor
     *    a menor nivel geografico (pais -> estado -> municipio -> parroquia -> ciudad).
     * 5. Si existe una direccion libre en $data['address'], la anexa al final.
     * 6. Une todas las partes con ', ' y retorna la cadena resultante.
     *
     * Ejemplo de salida: "Venezuela, Aragua, Maracay, Girardot, 5 de Julio, Av. Principal"
     *
     * @param array $data Array asociativo con claves: country_id, state_id, municipality_id,
     *                     parish_id, city_id y address (todas opcionales).
     * @return string Cadena de ubicacion separada por comas.
     */
    public function buildLocationString(array $data): string
    {
        $ids = array_filter([
            $data['country_id'] ?? null,
            $data['state_id'] ?? null,
            $data['municipality_id'] ?? null,
            $data['parish_id'] ?? null,
            $data['city_id'] ?? null,
        ]);

        if (empty($ids)) {
            return $data['address'] ?? '';
        }

        $models = collect();
        if (!empty($data['country_id'])) $models->push(Country::find($data['country_id']));
        if (!empty($data['state_id'])) $models->push(State::find($data['state_id']));
        if (!empty($data['municipality_id'])) $models->push(Municipality::find($data['municipality_id']));
        if (!empty($data['parish_id'])) $models->push(Parish::find($data['parish_id']));
        if (!empty($data['city_id'])) $models->push(City::find($data['city_id']));

        $parts = $models->filter()->pluck('name')->values()->toArray();

        if (!empty($data['address'])) {
            $parts[] = $data['address'];
        }

        return implode(', ', $parts);
    }

    /**
     * Procesa las imagenes asociadas a una propiedad: eliminacion de imagenes obsoletas
     * y creacion de registros con sus archivos almacenados.
     *
     * Flujo de eliminacion (solo en modo actualizacion):
     * 1. Si $isUpdate es true y $deletedIds no esta vacio, busca las imagenes
     *    correspondientes a esos IDs dentro de la propiedad.
     * 2. Para cada imagen encontrada, elimina los archivos fisicos (original y miniatura)
     *    y luego elimina el registro de la base de datos.
     *
     * Flujo de creacion:
     * 1. Verifica si ya existe una imagen primaria en la propiedad (solo en actualizacion).
     * 2. Obtiene el maximo valor de 'order' actual para calcular el orden de las nuevas.
     * 3. Para cada archivo subido:
     *    a. Almacena el archivo original en el disco 'public' dentro de 'properties/'.
     *    b. Genera una miniatura de 300x200px mediante generateThumbnail().
     *    c. Crea un registro PropertyImage con los metadatos: ruta, es_primaria (solo
     *       la primera imagen de una creacion nueva), orden secuencial, mime_type y size.
     *
     * @param Property $property Instancia de la propiedad padre.
     * @param array $files Array de objetos UploadedFile con las imagenes a procesar.
     * @param bool $isUpdate Si es true, indica que es una actualizacion (permite eliminacion).
     * @param array $deletedIds Array de IDs de imagenes a eliminar (solo en actualizacion).
     * @return void
     */
    public function processImages(Property $property, array $files, bool $isUpdate = false, array $deletedIds = []): void
    {
        if ($isUpdate && !empty($deletedIds)) {
            $imagesToDelete = $property->images()->whereIn('id', $deletedIds)->get();
            foreach ($imagesToDelete as $image) {
                $this->deleteImageFiles($image);
                $image->delete();
            }
        }

        if (empty($files)) {
            return;
        }

        $hasPrimary = $isUpdate ? $property->images()->where('is_primary', true)->exists() : false;
        $currentMaxOrder = $isUpdate ? ($property->images()->max('order') ?? 0) : 0;

        foreach ($files as $index => $file) {
            $path = $file->store('properties', 'public');
            $thumbnailPath = $this->generateThumbnail($file);

            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'is_primary' => !$hasPrimary && $index === 0,
                'order' => $currentMaxOrder + $index + 1,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Elimina todas las imagenes asociadas a una propiedad, incluyendo sus archivos fisicos.
     *
     * Recorre todas las imagenes relacionadas con la propiedad y para cada una:
     * 1. Llama a deleteImageFiles() para eliminar los archivos del disco 'public'.
     * 2. Elimina el registro de la base de datos.
     *
     * Utilizado cuando se elimina una propiedad completa para evitar archivos huerfanos.
     *
     * @param Property $property Instancia de la propiedad cuyas imagenes se eliminaran.
     * @return void
     */
    public function deleteAllImages(Property $property): void
    {
        foreach ($property->images as $image) {
            $this->deleteImageFiles($image);
            $image->delete();
        }
    }

    /**
     * Genera una miniatura de la imagen recibida con dimensiones fijas de 300x200px.
     *
     * Flujo:
     * 1. Utiliza Intervention Image para cargar la imagen desde el archivo UploadedFile.
     * 2. Aplica fit(300, 200) que redimensiona y recorta la imagen para ajustarla exactamente
     *    a las dimensiones especificadas, manteniendo la proporcion.
     * 3. Genera un nombre unico para la miniatura con prefijo 'thumb_' y uniqid().
     * 4. Codifica la imagen como JPG con calidad 80 y la almacena en disco 'public'
     *    dentro del directorio 'properties/thumbnails/'.
     * 5. Retorna la ruta relativa del archivo almacenado.
     *
     * En caso de error (imagen corrupta, archivo no valido, etc.), registra un warning
     * en el log y retorna null para que el proceso de creacion continúe sin miniatura.
     *
     * @param UploadedFile $file Archivo de imagen original subido por el usuario.
     * @return string|null Ruta de la miniatura almacenada o null si fallo la generacion.
     */
    private function generateThumbnail(UploadedFile $file): ?string
    {
        try {
            $image = \Intervention\Image\Facades\Image::make($file);
            $thumbnail = $image->fit(300, 200);
            $thumbnailName = 'thumb_' . uniqid() . '.jpg';
            $thumbnailPath = 'properties/thumbnails/' . $thumbnailName;
            Storage::disk('public')->put($thumbnailPath, (string) $thumbnail->encode('jpg', 80));
            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::warning('Error generando thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Elimina los archivos fisicos de una imagen del disco 'public' de almacenamiento.
     *
     * Elimina tanto el archivo de imagen original (image_path) como su miniatura
     * asociada (thumbnail_path). Verifica la existencia de cada archivo antes de
     * intentar eliminarlo para evitar errores.
     *
     * Este metodo no elimina el registro de la base de datos; solo limpia los archivos.
     * El responsable de eliminar el registro debe hacerlo por separado.
     *
     * @param PropertyImage|mixed $image Instancia del modelo de imagen con las rutas a eliminar.
     * @return void
     */
    private function deleteImageFiles($image): void
    {
        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
            Storage::disk('public')->delete($image->thumbnail_path);
        }
    }

    /**
     * Envia notificaciones internas cuando se crea una nueva propiedad.
     *
     * Flujo:
     * 1. Busca todos los usuarios con los roles: Super Admin, Administrador y Auditor.
     * 2. Para cada usuario encontrado, excluye al creador de la propiedad
     *    (no se notifica a quien realizo la accion).
     * 3. Crea una notificacion de tipo 'info' con:
     *    - Titulo: "Nueva Propiedad Creada".
     *    - Mensaje: describe quien creo la propiedad y su titulo.
     *    - URL de destino: ruta de vista de la propiedad asesor.
     *    - Datos meta: incluye el property_id para referencia interna.
     *
     * @param Property $property La propiedad recien creada.
     * @param User $creator El usuario que creo la propiedad.
     * @return void
     */
    public function notifyCreation(Property $property, User $creator): void
    {
        $roles = ['Super Admin', 'Administrador', 'Auditor'];
        $usersToNotify = User::role($roles)->get();

        foreach ($usersToNotify as $user) {
            if ($user->id === $creator->id) continue;
            $user->createNotification(
                'Nueva Propiedad Creada',
                "El asesor {$creator->name} ha creado una nueva propiedad: {$property->title}",
                'info',
                route('asesor.properties.show', $property->id),
                ['property_id' => $property->id]
            );
        }
    }

    /**
     * Envia notificaciones internas cuando se actualiza una propiedad existente.
     *
     * A diferencia de notifyCreation, esta notificacion solo se envia a los Auditores,
     * ya que son los responsables de revisar cambios en las propiedades.
     *
     * Flujo:
     * 1. Busca todos los usuarios con el rol 'Auditor'.
     * 2. Para cada auditor, excluye al usuario que realizo la actualizacion.
     * 3. Crea una notificacion de tipo 'warning' con:
     *    - Titulo: "Propiedad Actualizada".
     *    - Mensaje: describe que propiedad fue actualizada y por quien.
     *    - URL de destino: ruta de vista de la propiedad asesor.
     *    - Datos meta: incluye el property_id.
     *
     * @param Property $property La propiedad que fue actualizada.
     * @param User $updater El usuario que realizo la actualizacion.
     * @return void
     */
    public function notifyUpdate(Property $property, User $updater): void
    {
        $auditors = User::role('Auditor')->get();
        foreach ($auditors as $auditor) {
            if ($auditor->id === $updater->id) continue;
            $auditor->createNotification(
                'Propiedad Actualizada',
                "La propiedad {$property->title} ha sido actualizada por {$updater->name}",
                'warning',
                route('asesor.properties.show', $property->id),
                ['property_id' => $property->id]
            );
        }
    }

    /**
     * Calcula los campos modificados entre dos versiones de una propiedad.
     *
     * Implementa el patron de change tracking: compara los valores antiguos con los nuevos
     * y genera una lista legible de los cambios detectados.
     *
     * Flujo:
     * 1. Define un diccionario de etiquetas en español para cada campo tecnico
     *    (ej: 'bedrooms' -> 'habitaciones', 'price' -> 'precio').
     * 2. Itera sobre todos los valores nuevos.
     * 3. Para cada campo, verifica si existia un valor antiguo y si es diferente al nuevo.
     * 4. Excluye el campo 'updated_at' ya que es una marca de tiempo automatica.
     * 5. Genera una cadena con formato: "etiqueta: 'valor_anterior' -> 'valor_nuevo'".
     *    Si algun valor es null, lo muestra como 'vacio'.
     * 6. Retorna un array de strings, uno por cada campo modificado.
     *
     * Ejemplo de salida: ["precio: '150000' -> '180000'", "habitaciones: '3' -> '4'"]
     *
     * @param array $oldValues Valores anteriores de la propiedad (indexed por campo).
     * @param array $newValues Valores nuevos de la propiedad (indexed por campo).
     * @return array Array de strings descriptivos con los cambios detectados.
     */
    public function computeChanges(array $oldValues, array $newValues): array
    {
        $fieldLabels = [
            'title' => 'título', 'description' => 'descripción',
            'price' => 'precio', 'status' => 'estado', 'type' => 'tipo',
            'location' => 'ubicación', 'address' => 'dirección',
            'bedrooms' => 'habitaciones', 'bathrooms' => 'baños',
            'parking_spaces' => 'estacionamientos', 'area' => 'área',
            'land_area' => 'área de terreno', 'floors' => 'pisos',
            'year_built' => 'año de construcción',
        ];

        $changes = [];
        foreach ($newValues as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] != $value && $key !== 'updated_at') {
                $label = $fieldLabels[$key] ?? $key;
                $changes[] = "{$label}: '" . ($oldValues[$key] ?? 'vacío') . "' → '" . ($value ?? 'vacío') . "'";
            }
        }

        return $changes;
    }

    /**
     * Limpia el HTML de la descripcion de la propiedad eliminando etiquetas peligrosas.
     *
     * Utiliza strip_tags() con una whitelist de etiquetas HTML permitidas:
     * - Estructura: p, br, h1, h2, h3, h4
     * - Formato de texto: strong, em, u
     * - Listas: ul, ol, li
     *
     * Etiquetas como script, iframe, form, input, event handlers (onclick, onload),
     * y cualquier otra etiqueta no incluida en la whitelist seran eliminadas.
     * Esto previene ataques de XSS almacenado en el campo de descripcion.
     *
     * @param string $description Descripcion HTML ingresada por el usuario.
     * @return string Descripcion sanitizada con solo las etiquetas permitidas.
     */
    public function cleanDescription(string $description): string
    {
        return strip_tags($description, '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4>');
    }
}
