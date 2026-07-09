<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->thumbnail_path
            ? asset('storage/' . $this->thumbnail_path)
            : $this->url;
    }
}
