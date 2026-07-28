<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\SiteConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Comando artisan para sincronizar propiedades destacadas.
 *
 * Lee la lista de IDs de propiedades destacadas desde SiteConfiguration
 * y actualiza el campo is_featured en la tabla properties. Desmarca todas
 * las propiedades previamente destacadas y marca solo las que esten
 * en la lista actual con estado 'publicada'. Limpia las claves de cache
 * relacionadas con el home y configuracion del sitio.
 *
 * Uso: php artisan properties:sync-featured
 *
 * @package App\Console\Commands
 */
class SyncFeaturedProperties extends Command
{
    /**
     * Firma y descripcion del comando artisan.
     *
     * @var string
     */
    protected $signature = 'properties:sync-featured';
    protected $description = 'Sincroniza propiedades destacadas';

    /**
     * Ejecuta el comando: sincroniza propiedades destacadas y limpia cache.
     *
     * Flujo:
     * 1. Obtiene la lista de IDs desde SiteConfiguration.
     * 2. Desmarca todas las propiedades con is_featured = true.
     * 3. Marca las propiedades en la lista que tengan estado 'publicada'.
     * 4. Limpia las claves de cache del home y configuracion.
     *
     * @return int 0 en exito.
     */
    public function handle()
    {
        $config = SiteConfiguration::getConfig();
        $ids = $config->featured_properties ?? [];

        // Desmarcar todas
        Property::where('is_featured', true)->update(['is_featured' => false]);

        // Marcar las nuevas
        if (!empty($ids)) {
            $count = Property::whereIn('id', $ids)
                ->where('status', 'publicada')
                ->update(['is_featured' => true]);

            $this->info(" {$count} propiedades sincronizadas");
        } else {
            $this->warn('No hay propiedades destacadas');
        }

        //  LIMPIAR CACHÉ
        Cache::forget('home_states');
        Cache::forget('home_recent_properties');
        Cache::forget('site_config');
        Cache::forget('featured_properties');

        $this->info(' Caché limpiada');
    }
}
