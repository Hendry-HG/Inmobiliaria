<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\State;
use App\Models\SiteConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class HomeController extends Controller
{
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
