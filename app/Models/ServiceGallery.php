<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Relación inversa con Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Accessor para la URL de la imagen
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
