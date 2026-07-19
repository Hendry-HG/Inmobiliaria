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
        'hero_image_paths' => 'array',
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
     * Crear configuración por defecto SIN imágenes
     */
    public static function createDefault()
    {
        return self::create([
            'hero_badge' => 'Exclusividad & Confort',
            'hero_title_line1' => 'El Arte de',
            'hero_title_line2' => 'Vivir Bien',
            'hero_subtitle' => 'Descubre una curaduría exclusiva de propiedades de lujo en las mejores zonas de Venezuela.',
            'hero_images' => [], // ← ARRAY VACÍO
            'hero_image_paths' => null,
            'featured_badge' => 'Colección Exclusiva',
            'featured_title' => 'Propiedades Destacadas',
            'featured_properties' => [],
            'support_whatsapp' => '58XXXXXXXXX',
            'support_instagram' => 'msoinmobiliaria',
            'support_phone' => null,
            'support_email' => null,
            'footer_text' => '© ' . date('Y') . ' MSO Inmobiliaria. Todos los derechos reservados.',
        ]);
    }

    /**
     * Obtener todas las imágenes del hero (combinando URLs y subidas)
     */
    public function getHeroImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];

        // Si hay imágenes subidas localmente, combinarlas
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
