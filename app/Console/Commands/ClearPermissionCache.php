<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearPermissionCache extends Command
{
    protected $signature = 'permission:clear-cache';
    protected $description = 'Limpiar toda la caché de permisos';

    public function handle()
    {
        $count = 0;
        $keys = Cache::getStore()->getPrefix() . 'user_permissions_*';

        // Limpiar caché de permisos
        Cache::flush();
        $this->info('Caché de permisos limpiada correctamente.');

        return 0;
    }
}
