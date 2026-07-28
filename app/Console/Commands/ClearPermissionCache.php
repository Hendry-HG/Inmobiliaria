<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Comando artisan para limpiar la cache de permisos del sistema.
 *
 * Ejecuta Cache::flush() para eliminar todas las claves de cache
 * almacenadas, incluyendo las de permisos de Spatie. Util despues
 * de sincronizar permisos o cuando se detectan permisos desactualizados.
 *
 * Precaucion: Limpia TODA la cache, no solo la de permisos.
 *
 * Uso: php artisan permission:clear-cache
 *
 * @package App\Console\Commands
 */
class ClearPermissionCache extends Command
{
    /**
     * Firma del comando artisan.
     *
     * @var string
     */
    protected $signature = 'permission:clear-cache';
    protected $description = 'Limpiar toda la caché de permisos';

    /**
     * Ejecuta la limpieza de cache.
     *
     * @return int 0 en exito.
     */
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
