<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * Middleware de verificación de token CSRF.
 *
 * Extiende el middleware CSRF nativo de Laravel para proteger las rutas
 * de la aplicación contra ataques de falsificación de solicitudes cruzadas.
 * Se ejecuta en todas las peticiones HTTP que no estén excluidas.
 *
 * Las URIs definidas en $except no requieren verificación CSRF.
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * URIs que deben excluirse de la verificación CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
