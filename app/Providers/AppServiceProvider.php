<?php

/**
 * Proveedor de servicios principal de la aplicacion.
 *
 * AppServiceProvider es el proveedor de servicios base que se registra
 * automaticamente en toda aplicacion Laravel. Se encarga de dos tareas
 * fundamentales:
 *
 *   1. register():  Registra servicios en el contenedor de dependencias
 *      de la aplicacion. Se ejecuta antes de que los servicios esten
 *      disponibles para su uso.
 *
 *   2. boot():      Se ejecuta una vez que todos los proveedores han sido
 *      registrados. Se utiliza para registrar bindings, vistas compartidas,
 *      event listeners, y otra logica de inicializacion.
 *
 * Servicios registrados:
 *   - PermissionService: Servicio de gestion de permisos que se resuelve
 *     dinamicamente con el usuario autenticado actual.
 *
 * Vistas compartidas:
 *   - 'permissionService': Disponible en todas las vistas de dashboard,
 *     modulos y layouts del panel de administracion. Permite verificar
 *     permisos desde las plantillas Blade.
 *
 * @package App\Providers
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra servicios en el contenedor de dependencias.
     *
     * Este metodo se ejecuta durante la fase de bootstrap de la aplicacion,
     * antes de que los servicios esten listos para su uso. Aqui se definen
     * los bindings del contenedor de inversion (IoC) y las configuraciones
     * basicas de los servicios.
     *
     * Servicios registrados:
     *   - PermissionService: Se vincula como una instancia nueva cada vez
     *     que se resuelve, pasando el usuario autenticado actual como
     *     parametro. Si no hay usuario autenticado, Auth::user() retorna null.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PermissionService::class, function ($app) {
            return new PermissionService(Auth::user());
        });
    }

    /**
     * Metodo de inicializacion post-registro.
     *
     * Se ejecuta despues de que todos los proveedores han sido registrados.
     * Se utiliza para registrar View Composers, que inyectan datos en
     * las vistas de forma automatica.
     *
     * View Composers registrados:
     *   - Para las vistas 'dashboard.*', 'modulos.*' y 'layouts.dashboard':
     *     Inyecta la variable '$permissionService' en todas estas vistas,
     *     siempre que el usuario este autenticado. Esto permite a las
     *     plantillas Blade verificar permisos del usuario actual mediante
     *     el metodo $permissionService->hasPermission() o similares.
     *
     * @return void
     */
    public function boot(): void
    {
        View::composer(['dashboard.*', 'modulos.*', 'layouts.dashboard'], function ($view) {
            if (Auth::check()) {
                $view->with('permissionService', app(PermissionService::class));
            }
        });
    }
}
