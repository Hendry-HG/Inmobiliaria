<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controlador de páginas legales
 *
 * Renderiza los documentos legales públicos del sitio:
 * - Términos y Condiciones
 * - Política de Privacidad
 * - Política de Cookies
 *
 * Todas las rutas son públicas y no requieren autenticación.
 */
class LegalController extends Controller
{
    /**
     * Muestra los Términos y Condiciones del sitio.
     */
    public function terms(): View
    {
        return view('legal.terms');
    }

    /**
     * Muestra la Política de Privacidad del sitio.
     */
    public function privacy(): View
    {
        return view('legal.privacy');
    }

    /**
     * Muestra la Política de Cookies del sitio.
     */
    public function cookies(): View
    {
        return view('legal.cookies');
    }
}
