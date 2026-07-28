<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo que almacena las imagenes asociadas a una propiedad inmobiliaria.
 *
 * Cada propiedad puede tener multiples imagenes, una de las cuales es marcada
 * como primaria (is_primary). El modelo gestiona tanto la imagen original
 * como una miniatura generada automaticamente (300x200px).
 *
 * Al eliminar una imagen, el modelo elimina automaticamente los archivos fisicos
 * (original y miniatura) del disco 'public' via el evento boot() de Eloquent.
 *
 * Relaciones:
 * - property: Propiedad a la que pertenece la imagen.
 *
 * Campos clave:
 * - image_path: Ruta relativa de la imagen original en el disco 'public'.
 * - thumbnail_path: Ruta relativa de la miniatura en el disco 'public'.
 * - is_primary: Indica si esta es la imagen principal de la propiedad.
 * - order: Numero de orden para controlar la secuencia de visualizacion.
 *
 * Atributos virtuales:
 * - url: URL completa de la imagen original.
 * - thumbnail_url: URL completa de la miniatura (fallback a url si no existe).
 * - exists: Indica si el archivo fisico existe en el disco.
 *
 * @package App\Models
 */
class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'image_path', 'thumbnail_path', 'caption',
        'order', 'is_primary', 'mime_type', 'size'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Boot del modelo: registra el evento de eliminacion para limpiar archivos fisicos.
     *
     * Antes de eliminar el registro de la base de datos, elimina tanto la imagen
     * original como la miniatura del disco de almacenamiento 'public'.
     */
    protected static function booted()
    {
        static::deleting(function ($image) {
            // Eliminar imagen original
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Eliminar miniatura
            if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                Storage::disk('public')->delete($image->thumbnail_path);
            }
        });
    }

    /**
     * Obtiene la propiedad a la que pertenece la imagen.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo Property.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Obtiene la URL completa de la imagen original.
     *
     * Genera la URL publica concatenando la ruta base de 'storage' con
     * la ruta relativa almacenada en image_path.
     *
     * @return string URL completa de la imagen original.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Obtiene la URL completa de la miniatura.
     *
     * Si existe una miniatura, retorna su URL. De lo contrario, retorna
     * la URL de la imagen original como fallback.
     *
     * @return string URL completa de la miniatura o de la imagen original.
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return $this->url;
    }

    /**
     * Verifica si el archivo fisico de la imagen existe en el disco.
     *
     * @return bool true si el archivo existe en el disco 'public', false en caso contrario.
     */
    public function getExistsAttribute()
    {
        return Storage::disk('public')->exists($this->image_path);
    }
}
