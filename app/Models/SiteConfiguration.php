<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteConfiguration extends Model
{
    protected $fillable = [
        'hero_badge',
        'hero_title_line1',
        'hero_title_line2',
        'hero_subtitle',
        'hero_images',
        'hero_image_paths',
        'featured_badge',
        'featured_title',
        'featured_properties',
        'support_whatsapp',
        'support_instagram',
        'support_phone',
        'support_email',
        'footer_text',
    ];

    protected $casts = [
        'hero_images' => 'array',
        'featured_properties' => 'array',
    ];

    /**
     * Obtener la configuración (singleton)
     */
    public static function getConfig()
    {
        return self::first() ?? self::createDefault();
    }

    /**
     * Crear configuración por defecto
     */
    public static function createDefault()
    {
        return self::create([
            'hero_badge' => 'Exclusividad & Confort',
            'hero_title_line1' => 'El Arte de',
            'hero_title_line2' => 'Vivir Bien',
            'hero_subtitle' => 'Descubre una curaduría exclusiva de propiedades de lujo en las mejores zonas de Venezuela.',
            'hero_images' => [
                'https://images.unsplash.com/photo-1600596542815-2495db0c5903?q=80&w=2000&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=2000&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=2000&auto=format&fit=crop'
            ],
            'featured_badge' => 'Colección Exclusiva',
            'featured_title' => 'Propiedades Destacadas',
            'featured_properties' => [],
            'support_whatsapp' => '58XXXXXXXXX',
            'support_instagram' => 'msoinmobiliaria',
        ]);
    }

    /**
     * Obtener todas las imágenes del hero (combinando URLs y subidas)
     */
    public function getHeroImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];

        if ($this->hero_image_paths) {
            $uploadedImages = json_decode($this->hero_image_paths, true) ?? [];
            $images = array_merge($images, $uploadedImages);
        }

        return $images;
    }

    /**
     * Obtener las propiedades destacadas
     */
    public function getFeaturedProperties()
    {
        if (empty($this->featured_properties)) {
            return collect();
        }

        return Property::whereIn('id', $this->featured_properties)
            ->where('status', 'publicada')
            ->with('primaryImage')
            ->get();
    }
}
