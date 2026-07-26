<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Category;
use App\Models\PropertyImage;
use App\Models\Appointment;
use App\Models\Favorite;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;

/**
 * Modelo Property - Representa una propiedad inmobiliaria en el sistema.
 *
 * Entidad central del sistema inmobiliario. Almacena toda la información
 * de una propiedad publicada por un usuario (asesor o administrador),
 * incluyendo ubicación geográfica jerárquica (pais, estado, municipio,
 * parroquia, ciudad), características físicas, precio, estado de
 * publicación y datos de visibilidad (destacada, vistas, consultas).
 *
 * Relaciones principales:
 * - User (propietario/creador de la publicación)
 * - Category (categoría de la propiedad: apartamento, casa, terreno, etc.)
 * - PropertyImage (galería de imágenes de la propiedad)
 * - Appointment (visitas programadas a esta propiedad)
 * - Favorite (propiedades guardadas por usuarios)
 * - Lead (prospectos interesados en esta propiedad)
 * - Conversation (conversaciones derivadas de esta propiedad)
 * - Country, State, Municipality, Parish, City (ubicación geográfica)
 *
 * Flujo de datos:
 * Un usuario crea una propiedad, la publica, los visitantes la exploran,
 * generan consultas (leads), programan visitas (appointments) y mantienen
 * conversaciones con el asesor asignado.
 */
class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'price', 'price_currency', 'location', 'address',
        'country_id', 'state_id', 'municipality_id', 'parish_id', 'city_id',
        'sector', 'city', 'state', 'country', 'zip_code', 'bedrooms', 'bathrooms',
        'parking_spaces', 'area', 'land_area', 'floors', 'year_built', 'type',
        'status', 'features', 'user_id', 'category_id',
        'views', 'inquiries', 'meta_data', 'is_featured', 'featured_until'
    ];

    protected $casts = [
        'features' => 'array',
        'meta_data' => 'array',
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    /**
     * Relación con el usuario propietario/creador de la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la categoría de la propiedad (apartamento, casa, etc.).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con el país de ubicación de la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function countryRelation()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Relación con el estado/estado de ubicación de la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * Relación con el municipio de ubicación de la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function municipalityRelation()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    /**
     * Relación con la parroquia de ubicación de la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parishRelation()
    {
        return $this->belongsTo(Parish::class, 'parish_id');
    }

    /**
     * Relación con la ciudad de ubicación de la propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cityRelation()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Alias de countryRelation() para compatibilidad con código existente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->countryRelation();
    }

    /**
     * Alias de stateRelation() para compatibilidad con código existente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->stateRelation();
    }

    /**
     * Alias de municipalityRelation() para compatibilidad con código existente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function municipality()
    {
        return $this->municipalityRelation();
    }

    /**
     * Alias de parishRelation() para compatibilidad con código existente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->parishRelation();
    }

    /**
     * Alias de cityRelation() para compatibilidad con código existente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->cityRelation();
    }

    /**
     * Relación con las imágenes de la propiedad, ordenadas por el campo 'order'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    /**
     * Relación con la imagen principal de la propiedad.
     * Retorna la imagen marcada como is_primary = true.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    /**
     * Relación con las citas/visitas programadas para esta propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Relación con los registros de favoritos de esta propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Verifica si una propiedad está en favoritos de un usuario específico.
     *
     * @param \App\Models\User|null $user Usuario a consultar
     * @return bool true si el usuario tiene esta propiedad en favoritos
     */
    public function isFavoritedBy($user)
    {
        if (!$user) return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    /**
     * Relación con los leads/prospectos interesados en esta propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Relación con las conversaciones generadas a partir de esta propiedad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    // ==========================================
    // ACCESSORS - IMÁGENES (VERSIÓN PRODUCCIÓN)
    // ==========================================

    /**
     * Obtiene el modelo de imagen principal de la propiedad.
     * Si las imágenes ya están cargadas en memoria, filtra de la colección;
     * de lo contrario, realiza una consulta a la base de datos.
     *
     * @return \App\Models\PropertyImage|null imagen principal o primera imagen disponible
     */
    public function getPrimaryImageAttribute()
    {
        if ($this->relationLoaded('images')) {
            return $this->images->where('is_primary', true)->first()
                   ?? $this->images->first();
        }
        return $this->images()->where('is_primary', true)->first()
               ?? $this->images()->first();
    }

    /**
     * OBTIENE LA URL DE LA IMAGEN PRINCIPAL
     * - Verifica que el archivo exista
     * - Si no existe, muestra placeholder con el título
     * - Funciona en local y producción
     */
    public function getPrimaryImageUrlAttribute()
    {
         $image = $this->primary_image;

    if ($image && !empty($image->image_path)) {
        // Limpiar la ruta
        $path = str_replace(['public/', 'storage/'], '', $image->image_path);

        // Generar la URL correcta
        return asset('storage/' . $path);
    }

    $title = $this->title ?? 'Propiedad';
    return 'https://ui-avatars.com/api/?name=' . urlencode($title) . '&background=c5a059&color=fff&size=400';
    }

    /**
     * OBTIENE LA URL DE LA MINIATURA
     */
    public function getThumbnailUrlAttribute()
    {
        $image = $this->primary_image;

        if ($image && !empty($image->thumbnail_path)) {
            $path = $image->thumbnail_path;
            $path = str_replace('public/', '', $path);
            $path = str_replace('storage/', '', $path);

            if (Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        }

        return $this->primary_image_url;
    }

    /**
     * ALIAS PARA primary_image_url (MÁS SIMPLE DE USAR)
     */
    public function getImageUrlAttribute()
    {
        return $this->primary_image_url;
    }

    // ==========================================
    // ACCESSORS - PRECIO Y UBICACIÓN
    // ==========================================

    /**
     * Obtiene el precio formateado con su símbolo de moneda.
     * Soporta USD ($), EUR (€) y VES (Bs.).
     *
     * @return string precio formateado, ej: "$ 150.000,00"
     */
    public function getFormattedPriceAttribute()
    {
        $currency = $this->price_currency ?? 'USD';
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'VES' => 'Bs. ',
        ];
        $symbol = $symbols[$currency] ?? '$';
        return $symbol . ' ' . number_format($this->price, 2);
    }

    /**
     * Construye la ubicación completa de la propiedad combinando los niveles
     * geográficos disponibles: país, estado, municipio, parroquia, ciudad y dirección.
     * Utiliza relaciones Eloquent cuando están disponibles y fallback a campos de texto.
     *
     * @return string ubicación completa separada por flechas, ej: "Venezuela → Caracas → ..."
     */
    public function getFullLocationAttribute()
    {
        $parts = [];

        if ($this->country_id && $this->countryRelation) {
            $parts[] = $this->countryRelation->name;
        } elseif ($this->country && is_string($this->country)) {
            $parts[] = $this->country;
        }

        if ($this->state_id && $this->stateRelation) {
            $parts[] = $this->stateRelation->name;
        } elseif ($this->state && is_string($this->state)) {
            $parts[] = $this->state;
        }

        if ($this->municipality_id && $this->municipalityRelation) {
            $parts[] = $this->municipalityRelation->name;
        }

        if ($this->parish_id && $this->parishRelation) {
            $parts[] = $this->parishRelation->name;
        }

        if ($this->city_id && $this->cityRelation) {
            $parts[] = $this->cityRelation->name;
        } elseif ($this->city && is_string($this->city)) {
            $parts[] = $this->city;
        }

        if ($this->address) {
            $parts[] = $this->address;
        }

        if (empty($parts)) {
            return $this->location ?: 'Ubicación no especificada';
        }

        return implode(' → ', $parts);
    }

    /**
     * Obtiene el nombre de la ciudad asociada a la propiedad.
     * Prioriza la relación Eloquent; si no existe, retorna el campo de texto.
     *
     * @return string|null nombre de la ciudad o null si no está configurada
     */
    public function getCityNameAttribute()
    {
        if ($this->city_id && $this->cityRelation) {
            return $this->cityRelation->name;
        }
        return $this->city ?: null;
    }

    /**
     * Obtiene el nombre del estado asociado a la propiedad.
     * Prioriza la relación Eloquent; si no existe, retorna el campo de texto.
     *
     * @return string|null nombre del estado o null si no está configurado
     */
    public function getStateNameAttribute()
    {
        if ($this->state_id && $this->stateRelation) {
            return $this->stateRelation->name;
        }
        return $this->state ?: null;
    }

    /**
     * Obtiene el nombre del municipio asociado a la propiedad.
     *
     * @return string|null nombre del municipio o null si no está configurado
     */
    public function getMunicipalityNameAttribute()
    {
        if ($this->municipality_id && $this->municipalityRelation) {
            return $this->municipalityRelation->name;
        }
        return null;
    }

    /**
     * Obtiene el nombre del país asociado a la propiedad.
     * Prioriza la relación Eloquent; si no existe, retorna el campo de texto.
     *
     * @return string|null nombre del país o null si no está configurado
     */
    public function getCountryNameAttribute()
    {
        if ($this->country_id && $this->countryRelation) {
            return $this->countryRelation->name;
        }
        return $this->country ?: null;
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Filtra propiedades con estado 'publicada' (visibles en el sitio público).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'publicada');
    }

    /**
     * Filtra propiedades disponibles para venta (tipo 'venta' o 'venta/alquiler').
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSale($query)
    {
        return $query->whereIn('type', ['venta', 'venta/alquiler']);
    }

    /**
     * Filtra propiedades disponibles para alquiler (tipo 'alquiler' o 'venta/alquiler').
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRent($query)
    {
        return $query->whereIn('type', ['alquiler', 'venta/alquiler']);
    }

    /**
     * Filtra propiedades destacadas cuya fecha de destacación no ha expirado.
     * Incluye propiedades sin fecha de expiración (featured_until null).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                     ->where(function($q) {
                         $q->whereNull('featured_until')
                           ->orWhere('featured_until', '>=', now());
                     });
    }

    /**
     * Filtra propiedades por país.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $countryId identificador del país
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Filtra propiedades por estado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $stateId identificador del estado
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /**
     * Filtra propiedades por municipio.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $municipalityId identificador del municipio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInMunicipality($query, $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    /**
     * Filtra propiedades por parroquia.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $parishId identificador de la parroquia
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInParish($query, $parishId)
    {
        return $query->where('parish_id', $parishId);
    }

    /**
     * Filtra propiedades por ciudad.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $cityId identificador de la ciudad
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Filtra propiedades por usuario propietario/creador.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId identificador del usuario
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra propiedades dentro de un rango de precios.
     * Si solo se especifica $min, filtra desde ese precio.
     * Si solo se especifica $max, filtra hasta ese precio.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|null $min precio mínimo
     * @param float|null $max precio máximo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriceRange($query, $min, $max)
    {
        if ($min) $query->where('price', '>=', $min);
        if ($max) $query->where('price', '<=', $max);
        return $query;
    }

    // ==========================================
    // MÉTODOS
    // ==========================================

    /**
     * Incrementa en 1 el contador de vistas de la propiedad.
     * Se invoca cada vez que un usuario visita el detalle de la propiedad.
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Incrementa en 1 el contador de consultas de la propiedad.
     * Se invoca cuando un usuario realiza una consulta sobre la propiedad.
     */
    public function incrementInquiries()
    {
        $this->increment('inquiries');
    }
}
