<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\SiteConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncFeaturedProperties extends Command
{
    protected $signature = 'properties:sync-featured';
    protected $description = 'Sincroniza propiedades destacadas';

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
