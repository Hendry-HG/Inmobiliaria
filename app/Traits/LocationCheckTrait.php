<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait que proporciona un metodo de verificacion de permisos para entidades geograficas.
 *
 * Este trait es utilizado por los controladores que gestionan entidades
 * geograficas (paises, estados, municipios, parroquias, ciudades) para
 * verificar que el usuario autenticado tenga los permisos necesarios
 * antes de realizar operaciones CRUD sobre dichas entidades.
 *
 * Flujo de verificacion:
 * 1. Se obtiene el usuario autenticado desde la sesion actual.
 * 2. Si no hay usuario autenticado, se aborta con codigo 401.
 * 3. Si el usuario tiene rol de Super Admin o Administrador, se concede acceso automatico.
 * 4. Si el usuario tiene el permiso especifico, se concede acceso.
 * 5. Si no cumple ninguna condicion, se registra un warning en el log
 *    y se aborta con codigo 403.
 */
trait LocationCheckTrait
{
    /**
     * Verifica si el usuario autenticado tiene un permiso especifico.
     *
     * Metodo central de control de acceso para operaciones sobre entidades
     * geograficas. Aplica una jerarquia de permisos:
     * - Los roles Super Admin y Administrador tienen acceso total sin necesidad
     *   de permisos especificos.
     * - Los demas usuarios deben poseer el permiso indicado por el parametro.
     * - Si el acceso es denegado, se genera un registro de advertencia en el log
     *   con el ID del usuario, el permiso requerido y la direccion IP.
     *
     * @param  string $permission Identificador del permiso a verificar (ej: 'create-country').
     * @return bool true si el usuario tiene permiso. En caso contrario, aborta la ejecucion.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Aborts 401 si no esta autenticado,
     *                                                                  aborts 403 si no tiene permiso.
     */
    private function checkPermission(string $permission): bool
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'No estás autenticado.');
        }

        if ($user->hasRole('Super Admin') || $user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasPermissionTo($permission)) {
            return true;
        }

        Log::warning('Acceso denegado', [
            'user_id' => $user->id,
            'permission' => $permission,
            'ip' => request()->ip(),
        ]);

        abort(403, 'No tienes permiso para realizar esta acción.');
    }
}
