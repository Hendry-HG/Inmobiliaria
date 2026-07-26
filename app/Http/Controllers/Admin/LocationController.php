<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use App\Traits\AuditTrait;
use App\Traits\LocationCheckTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Controlador de administracion de ubicaciones geograficas.
 *
 * Gestiona la jerarquia geografica completa del sistema:
 * Pais -> Estado -> Municipio -> Parroquia -> Ciudad
 *
 * Cada nivel se representa con un modelo independiente (Country, State,
 * Municipality, Parish, City) y se relaciona con su nivel superior a
 * traves de claves foraneas.
 *
 * Utiliza LocationCheckTrait para validar permisos de acceso segun el
 * nivel geografico solicitado y AuditTrait para registrar cada accion
 * (creacion, eliminacion) en el historial de auditoria del sistema.
 *
 * La vista 'modulos.locations.nested' se reutiliza para todos los niveles
 * intermedios (Estado, Municipio, Parroquia, Ciudad), recibiendo los datos
 * del padre y los items a mostrar como parametros dinamicos.
 *
 * Las eliminaciones en cada nivel verifican que no existan registros hijos
 * antes de permitir la eliminacion (cascada inversa de validacion).
 * Se invalida la cache de la API despues de cada operacion de escritura.
 */
class LocationController extends Controller
{
    use LocationCheckTrait, AuditTrait;

    const CACHE_DURATION = 86400;

    /**
     * Constructor del controlador.
     *
     * Aplica middleware de autenticacion a todos los metodos excepto
     * los endpoints publicos de la API para selects dinamicos del
     * formulario de registro (paises, estados, municipios, parroquias y ciudades).
     */
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

    /**
     * Obtiene la lista de paises disponibles para el formulario de registro.
     *
     * Endpoint publico (sin autenticacion). Retorna todos los paises
     * ordenados alfabeticamente, con campos 'id' y 'name'.
     * Resultado almacenado en cache con la clave 'api_countries_list'.
     *
     * @return \Illuminate\Database\Eloquent\Collection Coleccion de paises con id y nombre.
     */
    public function getCountriesForRegister()
    {
        return Cache::remember('api_countries_list', self::CACHE_DURATION, function () {
            return Country::orderBy('name')->get(['id', 'name']);
        });
    }

    /**
     * Obtiene los estados de un pais especifico para selects dinamicos.
     *
     * Endpoint publico. Filtra los estados por el ID del pais proporcionado,
     * retornandolos ordenados alfabeticamente. Utiliza cache con clave
     * unica por pais para optimizar las consultas repetidas.
     *
     * @param int $countryId Identificador del pais padre.
     * @return \Illuminate\Database\Eloquent\Collection Coleccion de estados con id y nombre.
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
     * Obtiene los municipios de un estado especifico para selects dinamicos.
     *
     * Endpoint publico. Filtra los municipios por el ID del estado proporcionado,
     * retornandolos ordenados alfabeticamente. Utiliza cache con clave
     * unica por estado.
     *
     * @param int $stateId Identificador del estado padre.
     * @return \Illuminate\Database\Eloquent\Collection Coleccion de municipios con id y nombre.
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
     * Obtiene las parroquias de un municipio especifico para selects dinamicos.
     *
     * Endpoint publico. Filtra las parroquias por el ID del municipio proporcionado,
     * retornandolas ordenadas alfabeticamente. Utiliza cache con clave
     * unica por municipio.
     *
     * @param int $municipalityId Identificador del municipio padre.
     * @return \Illuminate\Database\Eloquent\Collection Coleccion de parroquias con id y nombre.
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
     * Obtiene las ciudades de una parroquia especifica para selects dinamicos.
     *
     * Endpoint publico. Filtra las ciudades por el ID de la parroquia proporcionada,
     * retornandolas ordenadas alfabeticamente. Utiliza cache con clave
     * unica por parroquia.
     *
     * @param int $parishId Identificador de la parroquia padre.
     * @return \Illuminate\Database\Eloquent\Collection Coleccion de ciudades con id y nombre.
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

    // checkPermission() se hereda del trait LocationCheckTrait

    // ================================================================
    // PAÍS - CRUD
    // ================================================================

    /**
     * Lista todos los paises registrados en el sistema.
     *
     * Requiere el permiso 'ver paises'. Retorna los paises ordenados
     * alfabeticamente con paginacion de 10 registros por pagina.
     * Muestra la vista principal de ubicaciones.
     *
     * @return \Illuminate\View\View Vista 'modulos.locations.index' con la coleccion paginada de paises.
     */
    public function indexCountries()
    {
        $this->checkPermission('ver paises');
        $countries = Country::orderBy('name')->paginate(10);
        return view('modulos.locations.index', compact('countries'));
    }

    /**
     * Crea un nuevo pais en el sistema.
     *
     * Requiere el permiso 'crear paises'. Valida que el nombre del pais
     * sea unico, contenga solo letras, espacios, guiones y puntos.
     * Registra la creacion en el log de auditoria y limpia la cache
     * de ubicaciones para mantener la coherencia de los datos.
     *
     * @param \Illuminate\Http\Request $request Solicitud con el campo 'name' requerido.
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
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
            $country = Country::create(['name' => $name]);

            $this->logCreated($country, Auth::user()->full_name . ' creó el país \'' . $country->name . '\'');

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

    /**
     * Elimina un pais del sistema.
     *
     * Requiere el permiso 'eliminar paises'. Verifica que el pais no
     * tenga estados asignados antes de permitir la eliminacion para
     * mantener la integridad referencial de la jerarquia geografica.
     * Registra la eliminacion en auditoria y limpia la cache.
     *
     * @param \App\Models\Country $country Pais a eliminar (resuelto por route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
    public function destroyCountry(Country $country)
    {
        $this->checkPermission('eliminar paises');

        if ($country->states()->exists()) {
            return back()->with('error', 'No se puede eliminar el país porque tiene estados asignados.');
        }

        try {
            $this->logDeleted($country, Auth::user()->full_name . ' eliminó el país \'' . $country->name . '\'');
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

    /**
     * Lista los estados de un pais especifico.
     *
     * Requiere el permiso 'ver estados'. Utiliza la vista generica
     * 'modulos.locations.nested' pasando los datos del padre (Pais)
     * y la configuracion de navegacion correspondiente al nivel de Estado.
     * Los items se ordenan alfabeticamente con paginacion de 10 registros.
     *
     * @param int $countryId Identificador del pais al que pertenecen los estados.
     * @return \Illuminate\View\View Vista anidada de ubicaciones con los estados del pais.
     */
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

    /**
     * Crea un nuevo estado dentro de un pais.
     *
     * Requiere el permiso 'crear estados'. Valida que el nombre sea
     * unico dentro del pais especifico (no a nivel global), evitando
     * duplicados por pais. Registra la creacion en auditoria y limpia
     * la cache de estados para el pais afectado.
     *
     * @param \Illuminate\Http\Request $request Solicitud con campos 'name' y 'country_id'.
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
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
            $state = State::create([
                'name' => $name,
                'country_id' => $request->country_id
            ]);

            $this->logCreated($state, Auth::user()->full_name . ' creó el estado \'' . $state->name . '\'');

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

    /**
     * Elimina un estado del sistema.
     *
     * Requiere el permiso 'eliminar estados'. Verifica que el estado
     * no tenga municipios asignados antes de permitir la eliminacion.
     * Registra la accion en auditoria y limpia la cache del pais padre.
     *
     * @param \App\Models\State $state Estado a eliminar (resuelto por route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
    public function destroyState(State $state)
    {
        $this->checkPermission('eliminar estados');

        if ($state->municipalities()->exists()) {
            return back()->with('error', 'No se puede eliminar el estado porque tiene municipios asignados.');
        }

        try {
            $countryId = $state->country_id;
            $this->logDeleted($state, Auth::user()->full_name . ' eliminó el estado \'' . $state->name . '\'');
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

    /**
     * Lista los municipios de un estado especifico.
     *
     * Requiere el permiso 'ver municipios'. Utiliza la vista generica
     * 'modulos.locations.nested' pasando los datos del padre (Estado)
     * y la configuracion de navegacion correspondiente al nivel de Municipio.
     *
     * @param int $stateId Identificador del estado al que pertenecen los municipios.
     * @return \Illuminate\View\View Vista anidada de ubicaciones con los municipios del estado.
     */
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

    /**
     * Crea un nuevo municipio dentro de un estado.
     *
     * Requiere el permiso 'crear municipios'. Valida que el nombre sea
     * unico dentro del estado especifico. Registra la creacion en auditoria
     * y limpia la cache de municipios para el estado afectado.
     *
     * @param \Illuminate\Http\Request $request Solicitud con campos 'name' y 'state_id'.
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
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
            $municipality = Municipality::create([
                'name' => $name,
                'state_id' => $request->state_id
            ]);

            $this->logCreated($municipality, Auth::user()->full_name . ' creó el municipio \'' . $municipality->name . '\'');

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

    /**
     * Elimina un municipio del sistema.
     *
     * Requiere el permiso 'eliminar municipios'. Verifica que el municipio
     * no tenga parroquias asignadas antes de permitir la eliminacion.
     * Registra la accion en auditoria y limpia la cache del estado padre.
     *
     * @param \App\Models\Municipality $municipality Municipio a eliminar (resuelto por route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
    public function destroyMunicipality(Municipality $municipality)
    {
        $this->checkPermission('eliminar municipios');

        if ($municipality->parishes()->exists()) {
            return back()->with('error', 'No se puede eliminar el municipio porque tiene parroquias asignadas.');
        }

        try {
            $stateId = $municipality->state_id;
            $this->logDeleted($municipality, Auth::user()->full_name . ' eliminó el municipio \'' . $municipality->name . '\'');
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

    /**
     * Lista las parroquias de un municipio especifico.
     *
     * Requiere el permiso 'ver parroquias'. Utiliza la vista generica
     * 'modulos.locations.nested' pasando los datos del padre (Municipio)
     * y la configuracion de navegacion correspondiente al nivel de Parroquia.
     *
     * @param int $municipalityId Identificador del municipio al que pertenecen las parroquias.
     * @return \Illuminate\View\View Vista anidada de ubicaciones con las parroquias del municipio.
     */
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

    /**
     * Crea una nueva parroquia dentro de un municipio.
     *
     * Requiere el permiso 'crear parroquias'. Valida que el nombre sea
     * unico dentro del municipio especifico. Registra la creacion en auditoria
     * y limpia la cache de parroquias para el municipio afectado.
     *
     * @param \Illuminate\Http\Request $request Solicitud con campos 'name' y 'municipality_id'.
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
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
            $parish = Parish::create([
                'name' => $name,
                'municipality_id' => $request->municipality_id
            ]);

            $this->logCreated($parish, Auth::user()->full_name . ' creó la parroquia \'' . $parish->name . '\'');

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

    /**
     * Elimina una parroquia del sistema.
     *
     * Requiere el permiso 'eliminar parroquias'. Verifica que la parroquia
     * no tenga ciudades asignadas antes de permitir la eliminacion.
     * Registra la accion en auditoria y limpia la cache del municipio padre.
     *
     * @param \App\Models\Parish $parish Parroquia a eliminar (resuelto por route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
    public function destroyParish(Parish $parish)
    {
        $this->checkPermission('eliminar parroquias');

        if ($parish->cities()->exists()) {
            return back()->with('error', 'No se puede eliminar la parroquia porque tiene ciudades asignadas.');
        }

        try {
            $municipalityId = $parish->municipality_id;
            $this->logDeleted($parish, Auth::user()->full_name . ' eliminó la parroquia \'' . $parish->name . '\'');
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

    /**
     * Lista las ciudades de una parroquia especifica.
     *
     * Requiere el permiso 'ver ciudades'. Utiliza la vista generica
     * 'modulos.locations.nested' pasando los datos del padre (Parroquia)
     * y la configuracion de navegacion correspondiente al nivel de Ciudad.
     * Este es el ultimo nivel de la jerarquia geografica.
     *
     * @param int $parishId Identificador de la parroquia a la que pertenecen las ciudades.
     * @return \Illuminate\View\View Vista anidada de ubicaciones con las ciudades de la parroquia.
     */
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

    /**
     * Crea una nueva ciudad dentro de una parroquia.
     *
     * Requiere el permiso 'crear ciudades'. Valida que el nombre sea
     * unico dentro de la parroquia especifica. Registra la creacion
     * en auditoria y limpia la cache de ciudades para la parroquia afectada.
     *
     * @param \Illuminate\Http\Request $request Solicitud con campos 'name' y 'parish_id'.
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
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
            $city = City::create([
                'name' => $name,
                'parish_id' => $request->parish_id
            ]);

            $this->logCreated($city, Auth::user()->full_name . ' creó la ciudad \'' . $city->name . '\'');

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

    /**
     * Elimina una ciudad del sistema.
     *
     * Requiere el permiso 'eliminar ciudades'. Al ser la ciudad el ultimo
     * nivel de la jerarquia geografica, no verifica dependencias hijos.
     * Registra la accion en auditoria y limpia la cache de la parroquia padre.
     *
     * @param \App\Models\City $city Ciudad a eliminar (resuelto por route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirige con mensaje de exito o error.
     */
    public function destroyCity(City $city)
    {
        $this->checkPermission('eliminar ciudades');

        try {
            $parishId = $city->parish_id;
            $this->logDeleted($city, Auth::user()->full_name . ' eliminó la ciudad \'' . $city->name . '\'');
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

    /**
     * Limpia la cache de ubicaciones geograficas afectadas por una operacion.
     *
     * Invalida las claves de cache especificas de cada nivel de la jerarquia
     * segun los IDs proporcionados. Siempre limpia las claves globales de
     * paises y codigos de telefono. Los parametros son opcionales y se
     * utilizan solo para el nivel que fue modificado.
     *
     * @param int|null $countryId        ID del pais para limpiar cache de estados.
     * @param int|null $stateId          ID del estado para limpiar cache de municipios.
     * @param int|null $municipalityId   ID del municipio para limpiar cache de parroquias.
     * @param int|null $parishId         ID de la parroquia para limpiar cache de ciudades.
     * @return void
     */
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
