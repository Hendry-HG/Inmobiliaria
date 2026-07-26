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

    
    public function getPrimaryImageAttribute()
    {
        if ($this->relationLoaded('gallery')) {
            return $this->gallery->first() ?? null;
        }
        return $this->gallery()->first() ?? null;
    }

    
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
