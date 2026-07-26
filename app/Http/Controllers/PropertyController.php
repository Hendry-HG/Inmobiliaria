<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Category;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use App\Models\User;
use App\Traits\AuditTrait;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    use AuditTrait;

    protected PropertyService $propertyService;

    /**
     * Inyeccion de dependencias del controlador.
     *
     * Resuelve el servicio PropertyService mediante inyeccion de dependencias
     * para delegar la logica de procesamiento de imagenes, construccion de
     * ubicacion y notificaciones. Configura middleware de autenticacion,
     * excluyendo las rutas publicas (catalogo, vista publica, conteo y
     * consulta por ID). Aplica throttling de 60 peticiones por minuto
     * a las vistas publicas para prevenir abuso.
     *
     * @param PropertyService $propertyService Servicio que encapsula la logica
     *        de negocio relacionada con propiedades (imagenes, ubicacion,
     *        notificaciones y calculo de cambios).
     * @return void
     */
    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
        $this->middleware('auth')->except(['catalog', 'showPublic', 'countProperties', 'showById']);
        $this->middleware('throttle:60,1')->only(['catalog', 'showPublic']);
    }

    /**
     * Lista de propiedades del panel de administracion/asesor.
     *
     * Muestra todas las propiedades al administrador y Super Admin, pero solo
     * las propias al Asesor Inmobiliario (filtrado por user_id). Soporta
     * busqueda por texto libre (titulo, descripcion, direccion), filtros por
     * estado, tipo, ubicacion geografica jerarquica (pais, estado, municipio,
     * parroquia, ciudad) y rango de precios. Los administradores pueden
     * filtrar por asesor especifico. Los datos geograficos se cachean 24h.
     *
     * Retorna la vista index o, en peticiones AJAX, el HTML parcial de las
     * filas junto con la paginacion y el total para actualizacion dinamica.
     *
     * Flujo de consultas con alcance por rol:
     * - Super Admin/Admin: ven todas las propiedades con filtros completos.
     * - Asesor Inmobiliario: solo sus propias propiedades.
     *
     * @param Request $request Solicitud HTTP con parametros opcionales de
     *        filtrado: status, type, search, country_id, state_id,
     *        municipality_id, parish_id, city_id, min_price, max_price, user_id.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $query = Property::with([
            'user', 'category', 'primaryImage',
            'countryRelation', 'stateRelation', 'municipalityRelation',
            'parishRelation', 'cityRelation'
        ]);

        if ($user->hasRole('Asesor Inmobiliario')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', $search)
                  ->orWhere('description', 'LIKE', $search)
                  ->orWhere('address', 'LIKE', $search);
            });
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->filled('parish_id')) {
            $query->where('parish_id', $request->parish_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if (($user->hasRole('Super Admin') || $user->hasRole('Administrador')) && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $properties = $query->latest()->paginate(10)->withQueryString();

        $categories = Category::all();
        $countries = Cache::remember('countries_list', 86400, function() {
            return Country::orderBy('name')->get();
        });

        $states = collect();
        $municipalities = collect();
        $parishes = collect();
        $cities = collect();

        if ($request->filled('country_id')) {
            $states = Cache::remember("states_country_{$request->country_id}", 86400, function() use ($request) {
                return State::where('country_id', $request->country_id)->orderBy('name')->get();
            });
        }

        if ($request->filled('state_id')) {
            $municipalities = Cache::remember("municipalities_state_{$request->state_id}", 86400, function() use ($request) {
                return Municipality::where('state_id', $request->state_id)->orderBy('name')->get();
            });
        }

        if ($request->filled('municipality_id')) {
            $parishes = Cache::remember("parishes_municipality_{$request->municipality_id}", 86400, function() use ($request) {
                return Parish::where('municipality_id', $request->municipality_id)->orderBy('name')->get();
            });
        }

        if ($request->filled('parish_id')) {
            $cities = Cache::remember("cities_parish_{$request->parish_id}", 86400, function() use ($request) {
                return City::where('parish_id', $request->parish_id)->orderBy('name')->get();
            });
        }

        $asesores = collect();
        if ($user->hasRole('Super Admin') || $user->hasRole('Administrador')) {
            $asesores = User::role('Asesor Inmobiliario')->orderBy('name')->get();
        }

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('modulos.propiedades._rows', compact(
                    'properties', 'isAdmin', 'isAsesor'
                ))->render(),
                'pagination' => $properties->links()->toHtml(),
                'total' => $properties->total()
            ]);
        }

        return view('modulos.propiedades.index', compact(
            'properties', 'categories', 'countries', 'states',
            'municipalities', 'parishes', 'cities', 'asesores',
            'isAdmin', 'isAsesor'
        ));
    }

    /**
     * Catalogo publico de propiedades para visitantes no autenticados.
     *
     * Expone unicamente las propiedades con estado "publicada" y ubicadas
     * en Venezuela (country_id = 1). No requiere autenticacion. Permite
     * filtrado por tipo de propiedad, categoria, texto libre, ubicacion
     * geografica, precio, habitaciones, banos, espacios de estacionamiento
     * y rango de area. Soporta ordenamiento por precio (asc/desc) y
     * antiguedad/antiguedad.
     *
     * La consulta incluye conteo de propiedades por cada estado, municipio
     * y ciudad para mostrar badges con la cantidad de inmuebles disponibles
     * en los filtros del catalogo. Se pagina con 12 elementos por pagina.
     *
     * Flujo:
     * 1. Filtra propiedades publicadas de Venezuela con filtros dinamicos.
     * 2. Carga categorias, estados con conteo de propiedades.
     * 3. Si se filtra por estado, carga municipios con conteo.
     * 4. Si se filtra por municipio, carga ciudades a traves de parroquias.
     * 5. Retorna la vista del catalogo con todos los datos para los filtros.
     *
     * @param Request $request Solicitud HTTP con parametros opcionales:
     *        type, category_id, search, state_id, municipality_id, city_id,
     *        min_price, max_price, bedrooms, bathrooms, parking_spaces,
     *        min_area, max_area, order_by.
     * @return \Illuminate\View\View
     */
    public function catalog(Request $request)
    {
        $venezuelaId = 1;

        $query = Property::with([
            'user', 'category', 'primaryImage',
            'countryRelation', 'stateRelation', 'municipalityRelation', 'cityRelation'
        ])->where('status', 'publicada')->where('country_id', $venezuelaId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', $search)
                  ->orWhere('description', 'LIKE', $search)
                  ->orWhere('address', 'LIKE', $search);
            });
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        if ($request->filled('parking_spaces')) {
            $query->where('parking_spaces', '>=', $request->parking_spaces);
        }

        if ($request->filled('min_area')) {
            $query->where('area', '>=', $request->min_area);
        }

        if ($request->filled('max_area')) {
            $query->where('area', '<=', $request->max_area);
        }

        $orderBy = $request->get('order_by', 'latest');
        switch ($orderBy) {
            case 'price_asc': $query->orderBy('price', 'asc'); break;
            case 'price_desc': $query->orderBy('price', 'desc'); break;
            case 'oldest': $query->oldest(); break;
            default: $query->latest(); break;
        }

        $properties = $query->paginate(12)->withQueryString();

        $categories = Category::orderBy('name')->get();

        $states = State::where('country_id', $venezuelaId)
            ->withCount(['properties' => function($q) {
                $q->where('status', 'publicada');
            }])
            ->orderBy('name')
            ->get();

        $municipalities = collect();
        if ($request->filled('state_id')) {
            $municipalities = Municipality::where('state_id', $request->state_id)
                ->withCount(['properties' => function($q) {
                    $q->where('status', 'publicada');
                }])
                ->orderBy('name')
                ->get();
        }

        $cities = collect();
        if ($request->filled('municipality_id')) {
            $parishIds = Parish::where('municipality_id', $request->municipality_id)->pluck('id');

            if ($parishIds->isNotEmpty()) {
                $cities = City::whereIn('parish_id', $parishIds)
                    ->withCount(['properties' => function($q) {
                        $q->where('status', 'publicada');
                    }])
                    ->orderBy('name')
                    ->get();
            }
        }

        $totalProperties = Property::where('status', 'publicada')
            ->where('country_id', $venezuelaId)
            ->count();

        return view('modulos.catalogo.index', compact(
            'properties', 'categories', 'states',
            'municipalities', 'cities', 'totalProperties'
        ));
    }

    /**
     * Muestra el detalle publico de una propiedad individual.
     *
     * Expone la informacion completa de una propiedad publicada al publico
     * general sin requerir autenticacion. Verifica que la propiedad este en
     * estado "publicada"; de lo contrario retorna 404. Incrementa el
     * contador de visitas de la propiedad para metricas de popularidad.
     *
     * Carga todas las relaciones necesarias (imagenes, usuario propietario,
     * categoria y toda la jerarquia geografica). Tambien obtiene hasta 4
     * propiedades similares filtradas por misma categoria, ciudad o estado
     * para la seccion de recomendaciones.
     *
     * Flujo:
     * 1. Valida que la propiedad sea publicada.
     * 2. Carga relaciones (imagenes, usuario, categoria, ubicacion).
     * 3. Incrementa contador de vistas.
     * 4. Busca propiedades similares (misma categoria, ciudad o estado).
     * 5. Retorna vista de detalle del catalogo.
     *
     * @param Property $property Modelo de propiedad resuelto por route model binding.
     * @return \Illuminate\View\View
     */
    public function showPublic(Property $property)
    {
        if ($property->status !== 'publicada') {
            abort(404);
        }

        $property->load([
            'images', 'user', 'category',
            'countryRelation', 'stateRelation',
            'municipalityRelation', 'parishRelation', 'cityRelation'
        ]);

        $property->incrementViews();

        $similarProperties = Property::where('status', 'publicada')
            ->where('id', '!=', $property->id)
            ->where(function($q) use ($property) {
                $q->where('category_id', $property->category_id)
                  ->orWhere('city_id', $property->city_id)
                  ->orWhere('state_id', $property->state_id);
            })
            ->with('primaryImage')
            ->latest()
            ->limit(4)
            ->get();

        return view('modulos.catalogo.show', compact('property', 'similarProperties'));
    }

    /**
     * Muestra una propiedad publicada por su ID numerico.
     *
     * Endpoint alternativo de consulta publica que busca la propiedad
     * directamente por su identificador sin route model binding. Retorna 404
     * si la propiedad no existe o no esta en estado "publicada". Incrementa
     * el contador de visitas. Util para enlaces directos o deep links donde
     * se conoce el ID de la propiedad.
     *
     * Flujo:
     * 1. Busca la propiedad por ID con relaciones cargadas.
     * 2. Valida que sea publicada; aborta 404 con mensaje si no lo es.
     * 3. Incrementa contador de vistas.
     * 4. Retorna vista de detalle del catalogo.
     *
     * @param int $id Identificador numerico de la propiedad a consultar.
     * @return \Illuminate\View\View
     */
    public function showById($id)
    {
        $property = Property::with([
            'images', 'user', 'category',
            'stateRelation', 'municipalityRelation', 'cityRelation'
        ])->findOrFail($id);

        if ($property->status !== 'publicada') {
            abort(404, 'La propiedad no está disponible públicamente.');
        }

        $property->incrementViews();

        return view('modulos.catalogo.show', compact('property'));
    }

    /**
     * Retorna el conteo de propiedades publicadas filtradas por ubicacion.
     *
     * Endpoint JSON utilizado dinamicamente en el frontend para mostrar la
     * cantidad de propiedades disponibles al aplicar filtros geograficos
     * (estado, pais, ciudad). Solo cuenta propiedades con estado "publicada".
     * No requiere autenticacion.
     *
     * @param Request $request Solicitud HTTP con filtros opcionales:
     *        state_id, country_id, city_id.
     * @return \Illuminate\Http\JsonResponse JSON con la clave "count" y el
     *         numero total de propiedades que coinciden con los filtros.
     */
    public function countProperties(Request $request)
    {
        $query = Property::where('status', 'publicada');

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        return response()->json(['count' => $query->count()]);
    }

    /**
     * Muestra el formulario de creacion de propiedades.
     *
     * Retorna la vista con el formulario para crear una nueva propiedad.
     * Verifica que el usuario autenticado tenga uno de los roles permitidos:
     * Super Admin, Administrador o Asesor Inmobiliario; de lo contrario
     * aborta con 403. Carga las categorias y paises cacheados como datos
     * iniciales para los selectores del formulario. Las colecciones de
     * estados, municipios, parroquias y ciudades se cargan vacias porque
     * se obtienen dinamicamente via AJAX segun la seleccion del usuario.
     *
     * Flujo:
     * 1. Valida permisos del usuario (Super Admin, Administrador o Asesor).
     * 2. Carga categorias y paises (cacheados 24h).
     * 3. Inicializa colecciones geograficas vacias para cascada AJAX.
     * 4. Retorna vista de creacion con datos para los selectores.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para crear propiedades.');
        }

        $categories = Category::all();
        $countries = Cache::remember('countries_list', 86400, function() {
            return Country::orderBy('name')->get();
        });

        $states = collect();
        $municipalities = collect();
        $parishes = collect();
        $cities = collect();

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        return view('modulos.propiedades.create', compact(
            'categories', 'countries', 'states',
            'municipalities', 'parishes', 'cities',
            'isAdmin', 'isAsesor'
        ));
    }

    /**
     * Almacena una nueva propiedad en el sistema.
     *
     * Recibe los datos validados por StorePropertyRequest (que aplica las
     * reglas de validacion del formulario) y delega al PropertyService las
     * operaciones de negocio complejas. Procesa el flujo completo de creacion:
     *
     * Flujo:
     * 1. Extrae los datos excluyendo campos controlados (images, is_featured, etc.).
     * 2. Asigna el user_id del usuario autenticado como propietario.
     * 3. Construye la cadena de ubicacion a traves de PropertyService.
     * 4. Limpia la descripcion de contenido no deseado via PropertyService.
     * 5. Crea el registro de la propiedad en base de datos.
     * 6. Procesa las imagenes subidas via PropertyService (almacenamiento
     *    y generacion de thumbnail).
     * 7. Registra la accion en el registro de auditoria.
     * 8. Envia notificacion de creacion al asesor responsable.
     * 9. Redirige al indice correspondiente segun el rol (asesor o admin).
     *
     * En caso de error, se registra en el log y se retorna a la vista
     * anterior con los datos de entrada y un mensaje de error.
     *
     * @param StorePropertyRequest $request Solicitud validada con los datos
     *        de la propiedad y archivos de imagen adjuntos.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePropertyRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $data = $request->except(['images', 'deleted_images', 'is_featured', 'featured_until']);
            $data['user_id'] = $user->id;
            $data['features'] = $request->input('features', []);
            $data['location'] = $this->propertyService->buildLocationString($data);
            $data['is_featured'] = false;
            $data['description'] = $this->propertyService->cleanDescription($data['description']);

            $property = Property::create($data);

            $this->propertyService->processImages($property, $request->file('images', []));

            $this->logCreated($property, ($user->full_name ?? 'Sistema') . ' CREÓ la propiedad "' . $property->title . '"');

            $this->propertyService->notifyCreation($property, $user);

            $redirectRoute = $user->hasRole('Asesor Inmobiliario')
                ? route('asesor.properties.index')
                : route('admin.properties.index');

            return redirect($redirectRoute)->with('success', 'Propiedad creada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al crear propiedad', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al crear la propiedad.']);
        }
    }

    /**
     * Muestra el formulario de edicion de una propiedad existente.
     *
     * Retorna el formulario de edicion pre-cargado con los datos actuales
     * de la propiedad. Aplica control de acceso en dos niveles: primero
     * verifica que el usuario tenga un rol valido (Super Admin, Administrador
     * o Asesor Inmobiliario), y luego verifica que el Asesor Inmobiliario
     * solo pueda editar sus propias propiedades.
     *
     * Flujo:
     * 1. Valida que el usuario Asesor Inmobiliario sea el dueno de la propiedad.
     * 2. Valida que el usuario tenga un rol autorizado para editar.
     * 3. Carga las imagenes y toda la jerarquia geografica de la propiedad.
     * 4. Carga categorias, paises y las colecciones geograficas correspondientes
     *    a la ubicacion actual de la propiedad para pre-llenar los selectores.
     * 5. Retorna la vista de creacion en modo edicion (reutiliza el mismo
     *    formulario que store con el modelo property pre-cargado).
     *
     * @param Property $property Modelo de propiedad resuelto por route model binding.
     * @return \Illuminate\View\View
     */
    public function edit(Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            abort(403, 'No tienes permiso para editar esta propiedad.');
        }

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para editar propiedades.');
        }

        $property->load(['images', 'countryRelation', 'stateRelation', 'municipalityRelation', 'parishRelation', 'cityRelation']);

        $categories = Category::all();
        $countries = Cache::remember('countries_list', 86400, function() {
            return Country::orderBy('name')->get();
        });

        $states = State::where('country_id', $property->country_id)->orderBy('name')->get();
        $municipalities = Municipality::where('state_id', $property->state_id)->orderBy('name')->get();
        $parishes = Parish::where('municipality_id', $property->municipality_id)->orderBy('name')->get();
        $cities = City::where('parish_id', $property->parish_id)->orderBy('name')->get();

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        return view('modulos.propiedades.create', compact(
            'property', 'categories', 'countries', 'states',
            'municipalities', 'parishes', 'cities',
            'isAdmin', 'isAsesor'
        ));
    }

    /**
     * Actualiza una propiedad existente con soporte de gestion de imagenes.
     *
     * Recibe los datos validados por UpdatePropertyRequest y aplica las
     * actualizaciones de forma transaccional. Maneja la eliminacion de
     * imagenes marcadas por el usuario y el procesamiento de nuevas imagenes
     * simultaneamente. Genera un registro de auditoria con los cambios
     * detectados y envia notificacion al usuario afectado.
     *
     * Flujo:
     * 1. Recopila los IDs de imagenes que el usuario solicito eliminar.
     * 2. Captura los valores antiguos para comparacion de auditoria.
     * 3. Actualiza los datos de la propiedad (excluyendo campos controlados).
     * 4. Reconstruye la cadena de ubicacion y limpia la descripcion.
     * 5. Delega a PropertyService el procesamiento de imagenes (nuevas y
     *    eliminacion de las marcadas como deleted_images).
     * 6. Calcula los cambios entre valores antiguos y nuevos para auditoria.
     * 7. Registra la actualizacion en el log de auditoria si hay cambios.
     * 8. Envia notificacion de actualizacion al usuario relevante.
     * 9. Redirige al indice segun el rol del usuario.
     *
     * @param UpdatePropertyRequest $request Solicitud validada con los datos
     *        actualizados de la propiedad.
     * @param Property $property Modelo de propiedad a actualizar.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePropertyRequest $request, Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        $deletedImages = [];
        if ($request->filled('deleted_images')) {
            $ids = explode(',', $request->input('deleted_images'));
            $deletedImages = $property->images()
                ->whereIn('id', $ids)
                ->pluck('id')
                ->toArray();
        }

        try {
            $oldValues = $property->toArray();

            $data = $request->except(['images', 'deleted_images', 'is_featured', 'featured_until']);
            $data['features'] = $request->input('features', []);
            $data['location'] = $this->propertyService->buildLocationString($data);
            $data['description'] = $this->propertyService->cleanDescription($data['description']);

            $property->update($data);

            $this->propertyService->processImages($property, $request->file('images', []), true, $deletedImages);

            $changes = $this->propertyService->computeChanges($oldValues, $data);
            if (!empty($changes)) {
                $this->logUpdated($property, $oldValues, $changes);
            }

            $this->propertyService->notifyUpdate($property, $user);

            $redirectRoute = $user->hasRole('Asesor Inmobiliario')
                ? route('asesor.properties.index')
                : route('admin.properties.index');

            return redirect($redirectRoute)->with('success', 'Propiedad actualizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar propiedad', [
                'property_id' => $property->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al actualizar la propiedad.']);
        }
    }

    /**
     * Muestra el detalle de una propiedad para el panel administrativo.
     *
     * Diferente a showPublic (catalogo externo), este metodo muestra la
     * propiedad dentro del area restringida del panel. Aplica control de
     * acceso: el Asesor Inmobiliario solo puede ver sus propias propiedades.
     * Carga todas las relaciones, incrementa el contador de vistas y
     * retorna la vista de detalle administrativa.
     *
     * Flujo:
     * 1. Verifica que el Asesor Inmobiliario sea dueno de la propiedad.
     * 2. Carga relaciones completas (imagenes, usuario, categoria, ubicacion).
     * 3. Incrementa el contador de visitas.
     * 4. Determina si el usuario es administrador o asesor para la vista.
     * 5. Retorna vista de detalle del modulo de propiedades.
     *
     * @param Property $property Modelo de propiedad resuelto por route model binding.
     * @return \Illuminate\View\View
     */
    public function show(Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta propiedad.');
        }

        $property->load([
            'images', 'user', 'category',
            'countryRelation', 'stateRelation',
            'municipalityRelation', 'parishRelation', 'cityRelation'
        ]);

        $property->incrementViews();

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        return view('modulos.propiedades.show', compact('property', 'isAdmin', 'isAsesor'));
    }

    /**
     * Elimina una propiedad y todos sus archivos asociados.
     *
     * Realiza la eliminacion fisica de la propiedad junto con todas sus
     * imagenes almacenadas en disco. Antes de eliminar, registra la accion
     * en el log de auditoria para mantenimiento del historial. Aplica los
     * mismos controles de acceso que edit: Super Admin y Administrador
     * pueden eliminar cualquier propiedad, mientras que el Asesor
     * Inmobiliario solo las suyas.
     *
     * Flujo:
     * 1. Verifica que el Asesor Inmobiliario sea dueno de la propiedad.
     * 2. Verifica que el usuario tenga un rol autorizado para eliminar.
     * 3. Registra la eliminacion en el log de auditoria.
     * 4. Elimina todas las imagenes de la propiedad del almacenamiento
     *    fisico a traves de PropertyService.
     * 5. Elimina el registro de la propiedad de la base de datos.
     * 6. Redirige al indice segun el rol del usuario.
     *
     * @param Property $property Modelo de propiedad a eliminar.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            abort(403, 'No tienes permiso para eliminar esta propiedad.');
        }

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para eliminar propiedades.');
        }

        try {
            $this->logDeleted($property, ($user->full_name ?? 'Sistema') . ' ELIMINÓ la propiedad "' . $property->title . '"');

            $this->propertyService->deleteAllImages($property);
            $property->delete();

            $route = $user->hasRole('Asesor Inmobiliario')
                ? route('asesor.properties.index')
                : route('admin.properties.index');

            return redirect($route)->with('success', 'Propiedad eliminada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar propiedad', [
                'property_id' => $property->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Hubo un problema al eliminar la propiedad.']);
        }
    }

    /**
     * Construye la cadena de ubicacion concatenando los datos geograficos.
     *
     * Metodo privado que actua como delegado al PropertyService. Recibe los
     * datos de la propiedad y retorna una cadena formada por la combinacion
     * de pais, estado, municipio, parroquia y ciudad. Util para campos de
     * busqueda de texto completo y mostruo en la interfaz.
     *
     * @param array $data Arreglo con los datos de la propiedad que contienen
     *        los IDs o nombres de los campos geograficos.
     * @return string Cadena concatenada de la ubicacion (ej: "Venezuela, Caracas, Miranda").
     */
    private function buildLocationString($data)
    {
        return $this->propertyService->buildLocationString($data);
    }

    /**
     * Obtiene los estados de un pais para cascada geografica AJAX.
     *
     * Endpoint utilizado por el frontend para cargar dinamicamente los
     * estados al seleccionar un pais en los formularios de filtro o creacion
     * de propiedades. Los resultados se cachean durante 24 horas para
     * optimizar el rendimiento dado que los datos geograficos cambian
     * muy raramente.
     *
     * @param int $countryId Identificador del país del cual se obtienen los estados.
     * @return \Illuminate\Support\Collection Coleccion de objetos con id y name.
     */
    public function getStates($countryId)
    {
        return Cache::remember("states_country_{$countryId}", 86400, function() use ($countryId) {
            return State::where('country_id', $countryId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    /**
     * Obtiene los municipios de un estado para cascada geografica AJAX.
     *
     * Endpoint utilized por el frontend para cargar dinamicamente los
     * municipios al seleccionar un estado. Los resultados se cachean 24
     * horas. Retorna los campos id y name para los selectores del formulario.
     *
     * @param int $stateId Identificador del estado del cual se obtienen los municipios.
     * @return \Illuminate\Support\Collection Coleccion de objetos con id y name.
     */
    public function getMunicipalities($stateId)
    {
        return Cache::remember("municipalities_state_{$stateId}", 86400, function() use ($stateId) {
            return Municipality::where('state_id', $stateId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    /**
     * Obtiene las parroquias de un municipio para cascada geografica AJAX.
     *
     * Endpoint utilizado por el frontend para cargar dinamicamente las
     * parroquias al seleccionar un municipio. Cacheado 24 horas. Retorna
     * los campos id y name para los selectores del formulario.
     *
     * @param int $municipalityId Identificador del municipio del cual se obtienen
     *        las parroquias.
     * @return \Illuminate\Support\Collection Coleccion de objetos con id y name.
     */
    public function getParishes($municipalityId)
    {
        return Cache::remember("parishes_municipality_{$municipalityId}", 86400, function() use ($municipalityId) {
            return Parish::where('municipality_id', $municipalityId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    /**
     * Obtiene las ciudades de una parroquia para cascada geografica AJAX.
     *
     * Endpoint utilizado por el frontend para cargar dinamicamente las
     * ciudades al seleccionar una parroquia. Cacheado 24 horas. Representa
     * el ultimo nivel de la cascada geografica (pais -> estado -> municipio
     * -> parroquia -> ciudad). Retorna los campos id y name.
     *
     * @param int $parishId Identificador de la parroquia de la cual se obtienen
     *        las ciudades.
     * @return \Illuminate\Support\Collection Coleccion de objetos con id y name.
     */
    public function getCities($parishId)
    {
        return Cache::remember("cities_parish_{$parishId}", 86400, function() use ($parishId) {
            return City::where('parish_id', $parishId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }
}
