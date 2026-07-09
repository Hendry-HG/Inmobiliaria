<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'icon',
        'color',
        'badge',
        'image',
        'features',
        'external_url',
        'order',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Relación con la galería de imágenes
     */
    public function gallery()
    {
        return $this->hasMany(ServiceGallery::class)->orderBy('order');
    }

    /**
     * Obtener la primera imagen de la galería
     */
    public function getPrimaryImageAttribute()
    {
        return $this->gallery()->first() ?? null;
    }

    /**
     * Boot del modelo para generar slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = \Illuminate\Support\Str::slug($service->title);
            }
        });
    }
}
