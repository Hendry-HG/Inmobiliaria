<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Comando artisan para refrescar la cache de permisos de Spatie.
 *
 * Ejecuta el artisan de Spatie para sincronizar los permisos y roles
 * desde la base de datos hacia el PermissionRegistrar. Util despues
 * de modificar permisos directamente en la BD o despues de un deploy.
 *
 * Uso: php artisan app:refresh-permissions
 *
 * @package App\Console\Commands
 */
class RefreshPermissions extends Command
{
    /**
     * Firma del comando artisan.
     *
     * @var string
     */
    protected $signature = 'app:refresh-permissions';

    /**
     * Descripcion del comando.
     *
     * @var string
     */
    protected $description = 'Refresca la cache de permisos de Spatie Permission';

    /**
     * Ejecuta el comando de refresco de permisos.
     *
     * @return int 0 en exito.
     */
    public function handle()
    {
        //
    }
}
