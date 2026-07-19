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

    // ==========================================
    // EVENTO PARA ELIMINAR ARCHIVOS FÍSICOS
    // ==========================================
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

    // Accessor para la URL de la imagen
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    // Accessor para la URL de la miniatura
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return $this->url;
    }

    // Accessor para verificar si el archivo existe
    public function getExistsAttribute()
    {
        return Storage::disk('public')->exists($this->image_path);
    }
}
