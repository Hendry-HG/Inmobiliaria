<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa una imagen en la galeria de un servicio inmobiliario.
 *
 * Cada servicio puede tener multiples imagenes organizadas por un campo 'order'.
 * Las imagenes se almacenan en el disco 'public' y se acceden via la URL publica.
 *
 * Relaciones:
 * - service: Servicio al que pertenece la imagen.
 *
 * Campos clave:
 * - image_path: Ruta relativa de la imagen en el disco 'public'.
 * - title: Titulo descriptivo de la imagen.
 * - alt_text: Texto alternativo para accesibilidad (SEO y lectores de pantalla).
 * - order: Numero de orden para controlar la secuencia de visualizacion.
 * - is_active: Indica si la imagen esta visible en la interfaz publica.
 *
 * Atributos virtuales:
 * - image_url: URL completa de la imagen para acceso publico.
 *
 * @package App\Models
 */
class ServiceGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'image_path',
        'title',
        'alt_text',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtiene el servicio al que pertenece la imagen.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo Service.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Obtiene la URL completa de la imagen de la galeria.
     *
     * Genera la URL publica concatenando la ruta base de 'storage' con
     * la ruta relativa almacenada en image_path. Retorna null si no hay imagen.
     *
     * @return string|null URL completa de la imagen o null si no existe ruta.
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
