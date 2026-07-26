<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\State;
use App\Models\SiteConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

/**
 * Controlador de Inicio
 *
 * Renderiza la pagina principal del sitio inmobiliario.
 * Carga datos publicos como estados con conteo de propiedades,
 * propiedades destacadas y la ultima propiedad publicada.
 * Utiliza Cache para optimizar las consultas frecuentes.
 *
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Muestra la pagina de inicio del sitio.
     *
     * Flujo de datos:
     * 1. Consulta los estados de Venezuela con conteo de propiedades publicadas
     *    (cacheado por 1 hora en 'home_states')
     * 2. Obtiene las propiedades destacadas desde SiteConfiguration
     * 3. Recupera la propiedad mas reciente publicada
     *    (cacheado por 30 minutos en 'home_recent_properties')
     * 4. Retorna la vista 'home' con estados, propiedades destacadas y recientes
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $venezuelaId = 1;

        // Estados
        $states = Cache::remember('home_states', 3600, function() use ($venezuelaId) {
            return State::where('country_id', $venezuelaId)
                ->withCount(['properties' => function($q) {
                    $q->where('status', 'publicada');
                }])
                ->orderBy('name')
                ->get();
        });

        // Propiedades destacadas
        $config = SiteConfiguration::getConfig();
        $featuredProperties = $config->getFeaturedProperties();

        // SOLO 1 PROPIEDAD RECIENTE
        $recentProperties = Cache::remember('home_recent_properties', 1800, function() {
            return Property::with(['primaryImage', 'user'])
                ->where('status', 'publicada')
                ->latest()
                ->limit(1)
                ->get();
        });

        return view('home', compact('states', 'featuredProperties', 'recentProperties'));
    }
}
