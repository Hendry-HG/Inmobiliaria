<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

   
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return $this->url;
    }

    
    public function getExistsAttribute()
    {
        return Storage::disk('public')->exists($this->image_path);
    }
}
