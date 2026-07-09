<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    // Cache duration: 1 hora para datos de ubicación (casi no cambian)
    const CACHE_DURATION = 3600;

    public function __construct()
    {
        // Los métodos de API para registro NO requieren autenticación
        $this->middleware(['auth', 'role:Super Admin|Administrador'])->except([
            'getCountriesForRegister',
            'getStatesForRegister',
            'getMunicipalitiesForRegister',
            'getParishesForRegister',
            'getCitiesForRegister'
        ]);
    }

    // ================== MÉTODOS PARA API (SELECTS DINÁMICOS) OPTIMIZADOS ==================

    /**
     * Obtener todos los países (con caché)
     */
    public function getCountriesForRegister()
    {
        return Cache::remember('api_countries_list', self::CACHE_DURATION, function () {
            return Country::orderBy('name')->get(['id', 'name']);
        });
    }

    /**
     * Obtener estados por país (con caché)
     */
    public function getStatesForRegister($countryId)
    {
        $cacheKey = "api_states_country_{$countryId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($countryId) {
            return State::where('country_id', $countryId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    /**
     * Obtener municipios por estado (con caché)
     */
    public function getMunicipalitiesForRegister($stateId)
    {
        $cacheKey = "api_municipalities_state_{$stateId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($stateId) {
            return Municipality::where('state_id', $stateId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    /**
     * Obtener parroquias por municipio (con caché)
     */
    public function getParishesForRegister($municipalityId)
    {
        $cacheKey = "api_parishes_municipality_{$municipalityId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($municipalityId) {
            return Parish::where('municipality_id', $municipalityId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    /**
     * Obtener ciudades por parroquia (con caché)
     */
    public function getCitiesForRegister($parishId)
    {
        $cacheKey = "api_cities_parish_{$parishId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($parishId) {
            return City::where('parish_id', $parishId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    // ================== PAÍS ==================

    public function indexCountries()
    {
         $countries = Country::orderBy('id', 'asc')->paginate(10);
        return view('modulos.locations.index', compact('countries'));
    }

    public function storeCountry(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        // Verificar si ya existe
        $existingCountry = Country::where('name', $request->name)->first();

        if ($existingCountry) {
            return redirect()->back()->with('error', 'Este país ya existe.');
        }

        Country::create($request->only('name'));

        // Limpiar caché de países
        Cache::forget('api_countries_list');

        return redirect()->back()->with('success', 'País creado correctamente.');
    }

    public function destroyCountry(Country $country)
    {
        if ($country->states()->exists()) {
            return back()->with('error', 'No se puede eliminar el país porque tiene estados asignados.');
        }

        $country->delete();

        // Limpiar caché de países
        Cache::forget('api_countries_list');

        return back()->with('success', 'País eliminado.');
    }

    // ================== ESTADO ==================

    public function indexStates($countryId)
    {
        $parent = Country::findOrFail($countryId);
        // CORREGIDO: Ordenar por ID ascendente
        $items = State::where('country_id', $countryId)->orderBy('id', 'asc')->paginate(10);

        return view('modulos.locations.nested', [
            'parent' => $parent,
            'items' => $items,
            'level' => 'Estado',
            'parentName' => 'País',
            'createRoute' => route('admin.locations.state.store', ['countryId' => $countryId]),
            'backRoute' => route('admin.locations.index'),
            'parentId' => $countryId,
            'destroyRoute' => 'admin.locations.state.destroy'
        ]);
    }

    public function storeState(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id'
        ]);

        State::create($request->all());

        // Limpiar caché de estados de este país
        Cache::forget("api_states_country_{$request->country_id}");

        return back()->with('success', 'Estado agregado.');
    }

    public function destroyState(State $state)
    {
        $countryId = $state->country_id;

        if ($state->municipalities()->exists()) {
            return back()->with('error', 'No se puede eliminar el estado porque tiene municipios asignados.');
        }

        $state->delete();

        // Limpiar caché de estados
        Cache::forget("api_states_country_{$countryId}");

        return back()->with('success', 'Estado eliminado.');
    }

    // ================== MUNICIPIO ==================

    public function indexMunicipalities($stateId)
    {
         $parent = State::findOrFail($stateId);
        // CORREGIDO: Ordenar por ID ascendente
        $items = Municipality::where('state_id', $stateId)->orderBy('id', 'asc')->paginate(10);

        return view('modulos.locations.nested', [
            'parent' => $parent,
            'items' => $items,
            'level' => 'Municipio',
            'parentName' => 'Estado',
            'createRoute' => route('admin.locations.municipality.store', ['stateId' => $stateId]),
            'backRoute' => route('admin.locations.states.index', $parent->country_id),
            'parentId' => $stateId,
            'destroyRoute' => 'admin.locations.municipality.destroy'
        ]);
    }

    public function storeMunicipality(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'state_id' => 'required|exists:states,id'
        ]);

        Municipality::create($request->all());

        // Limpiar caché de municipios de este estado
        Cache::forget("api_municipalities_state_{$request->state_id}");

        return back()->with('success', 'Municipio agregado.');
    }

    public function destroyMunicipality(Municipality $municipality)
    {
        $stateId = $municipality->state_id;

        if ($municipality->parishes()->exists()) {
            return back()->with('error', 'No se puede eliminar el municipio porque tiene parroquias asignadas.');
        }

        $municipality->delete();

        // Limpiar caché de municipios
        Cache::forget("api_municipalities_state_{$stateId}");

        return back()->with('success', 'Municipio eliminado.');
    }

    // ================== PARROQUIA ==================

    public function indexParishes($municipalityId)
    {
         $parent = Municipality::findOrFail($municipalityId);
        // CORREGIDO: Ordenar por ID ascendente
        $items = Parish::where('municipality_id', $municipalityId)->orderBy('id', 'asc')->paginate(10);

        return view('modulos.locations.nested', [
            'parent' => $parent,
            'items' => $items,
            'level' => 'Parroquia',
            'parentName' => 'Municipio',
            'createRoute' => route('admin.locations.parish.store', ['municipalityId' => $municipalityId]),
            'backRoute' => route('admin.locations.municipalities.index', $parent->state_id),
            'parentId' => $municipalityId,
            'destroyRoute' => 'admin.locations.parish.destroy'
        ]);
    }

    public function storeParish(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'municipality_id' => 'required|exists:municipalities,id'
        ]);

        Parish::create($request->all());

        // Limpiar caché de parroquias de este municipio
        Cache::forget("api_parishes_municipality_{$request->municipality_id}");

        return back()->with('success', 'Parroquia agregada.');
    }

    public function destroyParish(Parish $parish)
    {
        $municipalityId = $parish->municipality_id;

        if ($parish->cities()->exists()) {
            return back()->with('error', 'No se puede eliminar la parroquia porque tiene ciudades asignadas.');
        }

        $parish->delete();

        // Limpiar caché de parroquias
        Cache::forget("api_parishes_municipality_{$municipalityId}");

        return back()->with('success', 'Parroquia eliminada.');
    }

    // ================== CIUDAD ==================

    public function indexCities($parishId)
    {
       $parent = Parish::findOrFail($parishId);
        // CORREGIDO: Ordenar por ID ascendente
        $items = City::where('parish_id', $parishId)->orderBy('id', 'asc')->paginate(10);

        return view('modulos.locations.nested', [
            'parent' => $parent,
            'items' => $items,
            'level' => 'Ciudad',
            'parentName' => 'Parroquia',
            'createRoute' => route('admin.locations.city.store', ['parishId' => $parishId]),
            'backRoute' => route('admin.locations.parishes.index', $parent->municipality_id),
            'parentId' => $parishId,
            'destroyRoute' => 'admin.locations.city.destroy'
        ]);
    }

    public function storeCity(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parish_id' => 'required|exists:parishes,id'
        ]);

        City::create($request->all());

        // Limpiar caché de ciudades de esta parroquia
        Cache::forget("api_cities_parish_{$request->parish_id}");

        return back()->with('success', 'Ciudad agregada.');
    }

    public function destroyCity(City $city)
    {
        $parishId = $city->parish_id;
        $city->delete();

        // Limpiar caché de ciudades
        Cache::forget("api_cities_parish_{$parishId}");

        return back()->with('success', 'Ciudad eliminada.');
    }
}
