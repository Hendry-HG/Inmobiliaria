<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteConfiguration extends Model
{
    protected $fillable = [
        'hero_badge',
        'hero_title_line1',
        'hero_title_line2',
        'hero_subtitle',
        'hero_images',
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
     * Obtener configuración con caché
     */
    public static function getConfig()
    {
        return Cache::remember('site_config', 3600, function() {
            return self::first() ?? self::createDefault();
        });
    }

    /**
     * Limpiar caché de configuración
     */
    public static function clearCache()
    {
        Cache::forget('site_config');
        Cache::forget('featured_properties');
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
            'hero_images' => [],
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
     *  Sincronizar propiedades destacadas automáticamente al guardar
     */
    protected static function booted()
    {
        static::saved(function ($config) {
            if ($config->wasChanged('featured_properties')) {
                $ids = $config->featured_properties ?? [];

                // Desmarcar todas las propiedades como destacadas
                Property::where('is_featured', true)->update(['is_featured' => false]);

                // Marcar las nuevas propiedades destacadas
                if (!empty($ids)) {
                    Property::whereIn('id', $ids)
                        ->where('status', 'publicada')
                        ->update(['is_featured' => true]);
                }
            }

            // Limpiar caché en CADA guardado para reflejar cambios al instante
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    /**
     *  Obtener propiedades destacadas
     */
    public function getFeaturedProperties()
    {
        return Cache::remember('featured_properties', 3600, function() {
            // Nivel 1: IDs en la configuración
            if (!empty($this->featured_properties)) {
                $properties = Property::whereIn('id', $this->featured_properties)
                    ->where('status', 'publicada')
                    ->with(['primaryImage', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($properties->isNotEmpty()) {
                    return $properties;
                }
            }

            // Nivel 2: Propiedades con is_featured = true
            $properties = Property::with(['primaryImage', 'user'])
                ->where('status', 'publicada')
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            if ($properties->isNotEmpty()) {
                return $properties;
            }

            // Nivel 3: Propiedades más recientes (fallback final)
            return Property::with(['primaryImage', 'user'])
                ->where('status', 'publicada')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        });
    }
}
