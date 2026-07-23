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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    const CACHE_DURATION = 86400; // 24 horas para datos de ubicación

    public function __construct()
    {
        $this->middleware(['auth'])->except([
            'getCountriesForRegister',
            'getStatesForRegister',
            'getMunicipalitiesForRegister',
            'getParishesForRegister',
            'getCitiesForRegister'
        ]);
    }

    // ================================================================
    // MÉTODOS PARA API (SELECTS DINÁMICOS) - PÚBLICOS
    // ================================================================

    public function getCountriesForRegister()
    {
        return Cache::remember('api_countries_list', self::CACHE_DURATION, function () {
            return Country::orderBy('name')->get(['id', 'name']);
        });
    }

    public function getStatesForRegister($countryId)
    {
        $cacheKey = "api_states_country_{$countryId}";
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($countryId) {
            return State::where('country_id', $countryId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    public function getMunicipalitiesForRegister($stateId)
    {
        $cacheKey = "api_municipalities_state_{$stateId}";
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($stateId) {
            return Municipality::where('state_id', $stateId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    public function getParishesForRegister($municipalityId)
    {
        $cacheKey = "api_parishes_municipality_{$municipalityId}";
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($municipalityId) {
            return Parish::where('municipality_id', $municipalityId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    public function getCitiesForRegister($parishId)
    {
        $cacheKey = "api_cities_parish_{$parishId}";
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($parishId) {
            return City::where('parish_id', $parishId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    // ================================================================
    // MÉTODO DE VERIFICACIÓN DE PERMISOS - VERSIÓN PRODUCCIÓN
    // ================================================================

    /**
     * Verifica si el usuario autenticado tiene el permiso requerido
     *
     * @param string $permission Nombre del permiso a verificar
     * @return bool
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function checkPermission($permission)
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('LocationController - Intento de acceso sin autenticación', [
                'ip' => request()->ip(),
                'permission' => $permission
            ]);
            abort(401, 'No estás autenticado.');
        }

        try {
            // =============================================================
            // MÉTODO 1: USAR SPATIE DIRECTAMENTE (MÁS RÁPIDO)
            // =============================================================
            // Primero intentamos usar los métodos nativos de Spatie
            if (method_exists($user, 'hasRole') && method_exists($user, 'hasPermissionTo')) {
                // Super Admin siempre tiene acceso
                if ($user->hasRole('Super Admin')) {
                    Log::info('LocationController - Super Admin acceso concedido', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'permission' => $permission
                    ]);
                    return true;
                }

                // Administrador siempre tiene acceso
                if ($user->hasRole('Administrador')) {
                    Log::info('LocationController - Administrador acceso concedido', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'permission' => $permission
                    ]);
                    return true;
                }

                // Verificar permiso específico
                if ($user->hasPermissionTo($permission)) {
                    Log::info('LocationController - Permiso específico concedido', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'permission' => $permission
                    ]);
                    return true;
                }
            }

            // =============================================================
            // MÉTODO 2: FALLBACK CON CONSULTA DIRECTA A BD
            // =============================================================
            // Si los métodos de Spatie no están disponibles, usamos consulta directa
            Log::info('LocationController - Usando fallback de consulta directa a BD', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Verificar si es Super Admin o Administrador por consulta directa
            $userRoles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $user->id)
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->pluck('roles.name')
                ->toArray();

            Log::info('LocationController - Roles encontrados en BD', [
                'user_id' => $user->id,
                'roles' => $userRoles
            ]);

            // Super Admin siempre tiene acceso
            if (in_array('Super Admin', $userRoles)) {
                Log::info('LocationController - Super Admin acceso concedido (fallback)', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                return true;
            }

            // Administrador siempre tiene acceso
            if (in_array('Administrador', $userRoles)) {
                Log::info('LocationController - Administrador acceso concedido (fallback)', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                return true;
            }

            // Verificar permiso específico por consulta directa
            $hasPermission = DB::table('model_has_permissions')
                ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
                ->where('model_has_permissions.model_id', $user->id)
                ->where('model_has_permissions.model_type', 'App\\Models\\User')
                ->where('permissions.name', $permission)
                ->exists();

            if ($hasPermission) {
                Log::info('LocationController - Permiso concedido (fallback BD)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'permission' => $permission
                ]);
                return true;
            }

            // =============================================================
            // ACCESO DENEGADO
            // =============================================================
            Log::warning('LocationController - Acceso denegado', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_roles' => $userRoles,
                'required_permission' => $permission,
                'ip' => request()->ip(),
                'url' => request()->fullUrl()
            ]);

            abort(403, 'No tienes permiso para realizar esta acción.');

        } catch (\Exception $e) {
            // =============================================================
            // ERROR EN LA VERIFICACIÓN
            // =============================================================
            Log::error('LocationController - Error verificando permisos', [
                'user_id' => $user->id ?? 'unknown',
                'permission' => $permission,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // En producción, si hay error, denegamos acceso por seguridad
            abort(403, 'Error al verificar permisos. Contacta al administrador.');
        }
    }

    // ================================================================
    // PAÍS - CRUD
    // ================================================================

    public function indexCountries()
    {
        $this->checkPermission('ver paises');
        $countries = Country::orderBy('name')->paginate(10);
        return view('modulos.locations.index', compact('countries'));
    }

    public function storeCountry(Request $request)
    {
        $this->checkPermission('crear paises');

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúñÑ\s\-\.]+$/',
                'unique:countries,name'
            ]
        ]);

        try {
            $name = trim(strip_tags($request->name));
            Country::create(['name' => $name]);

            $this->clearLocationCache();

            return redirect()->back()->with('success', 'País creado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error creando país', [
                'name' => $request->name,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al crear el país.');
        }
    }

    public function destroyCountry(Country $country)
    {
        $this->checkPermission('eliminar paises');

        if ($country->states()->exists()) {
            return back()->with('error', 'No se puede eliminar el país porque tiene estados asignados.');
        }

        try {
            $countryName = $country->name;
            $country->delete();

            $this->clearLocationCache();

            return back()->with('success', "País '{$countryName}' eliminado correctamente.");
        } catch (\Exception $e) {
            Log::error('Error eliminando país', [
                'country_id' => $country->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al eliminar el país.');
        }
    }

    // ================================================================
    // ESTADO - CRUD
    // ================================================================

    public function indexStates($countryId)
    {
        $this->checkPermission('ver estados');
        $parent = Country::findOrFail($countryId);
        $items = State::where('country_id', $countryId)
            ->orderBy('name')
            ->paginate(10);

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
        $this->checkPermission('crear estados');

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúñÑ\s\-\.]+$/',
                Rule::unique('states')->where('country_id', $request->country_id)
            ],
            'country_id' => 'required|exists:countries,id'
        ]);

        try {
            $name = trim(strip_tags($request->name));
            State::create([
                'name' => $name,
                'country_id' => $request->country_id
            ]);

            $this->clearLocationCache($request->country_id);

            return back()->with('success', 'Estado agregado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error creando estado', [
                'name' => $request->name,
                'country_id' => $request->country_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al crear el estado.');
        }
    }

    public function destroyState(State $state)
    {
        $this->checkPermission('eliminar estados');

        if ($state->municipalities()->exists()) {
            return back()->with('error', 'No se puede eliminar el estado porque tiene municipios asignados.');
        }

        try {
            $countryId = $state->country_id;
            $stateName = $state->name;
            $state->delete();

            $this->clearLocationCache($countryId);

            return back()->with('success', "Estado '{$stateName}' eliminado correctamente.");
        } catch (\Exception $e) {
            Log::error('Error eliminando estado', [
                'state_id' => $state->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al eliminar el estado.');
        }
    }

    // ================================================================
    // MUNICIPIO - CRUD
    // ================================================================

    public function indexMunicipalities($stateId)
    {
        $this->checkPermission('ver municipios');
        $parent = State::findOrFail($stateId);
        $items = Municipality::where('state_id', $stateId)
            ->orderBy('name')
            ->paginate(10);

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
        $this->checkPermission('crear municipios');

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúñÑ\s\-\.]+$/',
                Rule::unique('municipalities')->where('state_id', $request->state_id)
            ],
            'state_id' => 'required|exists:states,id'
        ]);

        try {
            $name = trim(strip_tags($request->name));
            Municipality::create([
                'name' => $name,
                'state_id' => $request->state_id
            ]);

            $this->clearLocationCache(null, $request->state_id);

            return back()->with('success', 'Municipio agregado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error creando municipio', [
                'name' => $request->name,
                'state_id' => $request->state_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al crear el municipio.');
        }
    }

    public function destroyMunicipality(Municipality $municipality)
    {
        $this->checkPermission('eliminar municipios');

        if ($municipality->parishes()->exists()) {
            return back()->with('error', 'No se puede eliminar el municipio porque tiene parroquias asignadas.');
        }

        try {
            $stateId = $municipality->state_id;
            $municipalityName = $municipality->name;
            $municipality->delete();

            $this->clearLocationCache(null, $stateId);

            return back()->with('success', "Municipio '{$municipalityName}' eliminado correctamente.");
        } catch (\Exception $e) {
            Log::error('Error eliminando municipio', [
                'municipality_id' => $municipality->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al eliminar el municipio.');
        }
    }

    // ================================================================
    // PARROQUIA - CRUD
    // ================================================================

    public function indexParishes($municipalityId)
    {
        $this->checkPermission('ver parroquias');
        $parent = Municipality::findOrFail($municipalityId);
        $items = Parish::where('municipality_id', $municipalityId)
            ->orderBy('name')
            ->paginate(10);

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
        $this->checkPermission('crear parroquias');

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúñÑ\s\-\.]+$/',
                Rule::unique('parishes')->where('municipality_id', $request->municipality_id)
            ],
            'municipality_id' => 'required|exists:municipalities,id'
        ]);

        try {
            $name = trim(strip_tags($request->name));
            Parish::create([
                'name' => $name,
                'municipality_id' => $request->municipality_id
            ]);

            $this->clearLocationCache(null, null, $request->municipality_id);

            return back()->with('success', 'Parroquia agregada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error creando parroquia', [
                'name' => $request->name,
                'municipality_id' => $request->municipality_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al crear la parroquia.');
        }
    }

    public function destroyParish(Parish $parish)
    {
        $this->checkPermission('eliminar parroquias');

        if ($parish->cities()->exists()) {
            return back()->with('error', 'No se puede eliminar la parroquia porque tiene ciudades asignadas.');
        }

        try {
            $municipalityId = $parish->municipality_id;
            $parishName = $parish->name;
            $parish->delete();

            $this->clearLocationCache(null, null, $municipalityId);

            return back()->with('success', "Parroquia '{$parishName}' eliminada correctamente.");
        } catch (\Exception $e) {
            Log::error('Error eliminando parroquia', [
                'parish_id' => $parish->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al eliminar la parroquia.');
        }
    }

    // ================================================================
    // CIUDAD - CRUD
    // ================================================================

    public function indexCities($parishId)
    {
        $this->checkPermission('ver ciudades');
        $parent = Parish::findOrFail($parishId);
        $items = City::where('parish_id', $parishId)
            ->orderBy('name')
            ->paginate(10);

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
        $this->checkPermission('crear ciudades');

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúñÑ\s\-\.]+$/',
                Rule::unique('cities')->where('parish_id', $request->parish_id)
            ],
            'parish_id' => 'required|exists:parishes,id'
        ]);

        try {
            $name = trim(strip_tags($request->name));
            City::create([
                'name' => $name,
                'parish_id' => $request->parish_id
            ]);

            $this->clearLocationCache(null, null, null, $request->parish_id);

            return back()->with('success', 'Ciudad agregada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error creando ciudad', [
                'name' => $request->name,
                'parish_id' => $request->parish_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al crear la ciudad.');
        }
    }

    public function destroyCity(City $city)
    {
        $this->checkPermission('eliminar ciudades');

        try {
            $parishId = $city->parish_id;
            $cityName = $city->name;
            $city->delete();

            $this->clearLocationCache(null, null, null, $parishId);

            return back()->with('success', "Ciudad '{$cityName}' eliminada correctamente.");
        } catch (\Exception $e) {
            Log::error('Error eliminando ciudad', [
                'city_id' => $city->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al eliminar la ciudad.');
        }
    }

    // ================================================================
    // LIMPIEZA DE CACHÉ
    // ================================================================

    private function clearLocationCache($countryId = null, $stateId = null, $municipalityId = null, $parishId = null)
    {
        try {
            if ($countryId) {
                Cache::forget("api_states_country_{$countryId}");
                Cache::forget("api_states_{$countryId}");
            }

            if ($stateId) {
                Cache::forget("api_municipalities_state_{$stateId}");
                Cache::forget("api_municipalities_{$stateId}");
            }

            if ($municipalityId) {
                Cache::forget("api_parishes_municipality_{$municipalityId}");
                Cache::forget("api_parishes_{$municipalityId}");
            }

            if ($parishId) {
                Cache::forget("api_cities_parish_{$parishId}");
                Cache::forget("api_cities_{$parishId}");
            }

            Cache::forget('api_countries_list');
            Cache::forget('api_countries_all');
            Cache::forget('api_phone_presets');
            Cache::forget('api_phone_codes');
        } catch (\Exception $e) {
            Log::error('Error limpiando caché de ubicaciones', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
