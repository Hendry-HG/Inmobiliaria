<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Controlador Base
 *
 * Clase abstracta base para todos los controladores de la aplicacion.
 * Proporciona funcionalidades compartidas de autorizacion y validacion
 * a traves de los traits AuthorizesRequests y ValidatesRequests.
 * Todos los controladores del sistema extienden de esta clase.
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}