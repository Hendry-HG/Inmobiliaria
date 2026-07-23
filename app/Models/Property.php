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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function countryRelation()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function municipalityRelation()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function parishRelation()
    {
        return $this->belongsTo(Parish::class, 'parish_id');
    }

    public function cityRelation()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country()
    {
        return $this->countryRelation();
    }

    public function state()
    {
        return $this->stateRelation();
    }

    public function municipality()
    {
        return $this->municipalityRelation();
    }

    public function parish()
    {
        return $this->parishRelation();
    }

    public function city()
    {
        return $this->cityRelation();
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function isFavoritedBy($user)
    {
        if (!$user) return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    // ==========================================
    // ACCESSORS - IMÁGENES (VERSIÓN PRODUCCIÓN)
    // ==========================================

    public function getPrimaryImageAttribute()
    {
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

    public function getCityNameAttribute()
    {
        if ($this->city_id && $this->cityRelation) {
            return $this->cityRelation->name;
        }
        return $this->city ?: null;
    }

    public function getStateNameAttribute()
    {
        if ($this->state_id && $this->stateRelation) {
            return $this->stateRelation->name;
        }
        return $this->state ?: null;
    }

    public function getMunicipalityNameAttribute()
    {
        if ($this->municipality_id && $this->municipalityRelation) {
            return $this->municipalityRelation->name;
        }
        return null;
    }

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

    public function scopePublished($query)
    {
        return $query->where('status', 'publicada');
    }

    public function scopeForSale($query)
    {
        return $query->whereIn('type', ['venta', 'venta/alquiler']);
    }

    public function scopeForRent($query)
    {
        return $query->whereIn('type', ['alquiler', 'venta/alquiler']);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                     ->where(function($q) {
                         $q->whereNull('featured_until')
                           ->orWhere('featured_until', '>=', now());
                     });
    }

    public function scopeInCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeInState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeInMunicipality($query, $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    public function scopeInParish($query, $parishId)
    {
        return $query->where('parish_id', $parishId);
    }

    public function scopeInCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePriceRange($query, $min, $max)
    {
        if ($min) $query->where('price', '>=', $min);
        if ($max) $query->where('price', '<=', $max);
        return $query;
    }

    // ==========================================
    // MÉTODOS
    // ==========================================

    public function incrementViews()
    {
        $this->increment('views');
    }

    public function incrementInquiries()
    {
        $this->increment('inquiries');
    }
}
