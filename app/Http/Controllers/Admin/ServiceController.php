<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Models\SiteConfiguration;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Traits\AuditTrait;

/**
 * Controlador para la gestion administrativa de servicios.
 *
 * Este controlador maneja las operaciones CRUD sobre los servicios que ofrece
 * la inmobiliaria, incluyendo la gestion de la imagen principal, el
 * reordenamiento de servicios mediante drag-and-drop (AJAX), y el registro
 * de auditoria para cada operacion realizada.
 *
 * Cada operacion de creacion, actualizacion y eliminacion queda registrada
 * a traves del AuditTrait para mantener un historial de cambios realizados
 * por los usuarios autenticados.
 */
class ServiceController extends Controller
{
    use AuditTrait;

    /**
     * Constructor del controlador.
     *
     * Aplica middlewares de permisos porRol a cada accion del controlador.
     * Solo los usuarios con los permisos correspondientes podran acceder
     * a las distintas rutas del CRUD.
     */
    public function __construct()
    {
        $this->middleware('permission:ver servicios')->only(['index', 'show']);
        $this->middleware('permission:crear servicios')->only(['create', 'store']);
        $this->middleware('permission:editar servicios')->only(['edit', 'update']);
        $this->middleware('permission:eliminar servicios')->only(['destroy']);
    }

    /**
     * Lista todos los servicios registrados en el sistema.
     *
     * Obtiene todos los servicios, ordenados por el campo 'order' para
     * mantener el orden visual definido por el administrador. Tambien carga
     * la lista de asesores inmobiliarios activos y los datos de ubicacion
     * (paises, estados, municipios, parroquias, ciudades) para los filtros
     * del panel.
     *
     * @return \Illuminate\View\View Retorna la vista 'modulos.servicios.index' con los servicios, asesores y datos de ubicacion.
     */
    public function index()
    {
        $services = Service::orderBy('order')
            ->get();

        $asesores = User::role('Asesor Inmobiliario')
            ->where('is_active', true)
            ->get();

        // Datos para los filtros de ubicacion (si los necesitas)
        $countries = Country::orderBy('name')->get();
        $states = collect();
        $municipalities = collect();
        $parishes = collect();
        $cities = collect();

        return view('modulos.servicios.index', compact(
            'services',
            'asesores',
            'countries',
            'states',
            'municipalities',
            'parishes',
            'cities'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo servicio.
     *
     * Renderiza la vista de creacion sin datos precargados,
     * permitiendo al administrador completar los campos del servicio.
     *
     * @return \Illuminate\View\View Retorna la vista 'modulos.servicios.create'.
     */
    public function create()
    {
        return view('modulos.servicios.create');
    }

    /**
     * Almacena un nuevo servicio en la base de datos.
     *
     * Este metodo realiza las siguientes operaciones:
     * 1. Valida los datos del formulario (titulo, descripcion, icono, color, badge, etc.).
     * 2. Genera un slug automaticamente a partir del titulo del servicio.
     * 3. Procesa la imagen principal: la almacena en el disco publico dentro
     *    de la carpeta 'services' y guarda la ruta en el registro.
     * 4. Crea el registro del servicio en la tabla 'services'.
     * 5. Registra la accion de auditoria indicando que usuario creo el servicio.
     *
     * @param  \Illuminate\Http\Request  $request Solicitud HTTP con los datos del formulario.
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de servicios con un mensaje de exito.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'external_url' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $data = $request->except(['image']);

        // Generar slug
        $data['slug'] = \Illuminate\Support\Str::slug($request->title);

        // Procesar imagen principal
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        // Crear el servicio
        $service = Service::create($data);

        $userName = Auth::user()->full_name;
        $this->logCreated($service, "{$userName} creó el servicio '{$service->title}'");

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Muestra los detalles de un servicio especifico.
     *
     * Carga el servicio especificado por su identificador unico.
     *
     * @param  int  $id Identificador unico del servicio a mostrar.
     * @return \Illuminate\View\View Retorna la vista 'modulos.servicios.show' con el servicio cargado.
     */
    public function show($id)
    {
        $service = Service::findOrFail($id);
        return view('modulos.servicios.show', compact('service'));
    }

    /**
     * Muestra el formulario para editar un servicio existente.
     *
     * Carga el servicio especificado para que el administrador
     * pueda modificar sus datos.
     *
     * @param  int  $id Identificador unico del servicio a editar.
     * @return \Illuminate\View\View Retorna la vista 'modulos.servicios.edit' con el servicio cargado.
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('modulos.servicios.edit', compact('service'));
    }

    /**
     * Actualiza un servicio existente en la base de datos.
     *
     * Este metodo realiza las siguientes operaciones:
     * 1. Valida los datos del formulario, excluyendo la imagen principal.
     * 2. Si se proporciona una nueva imagen principal, elimina la imagen anterior
     *    del disco publico (si existe) y almacena la nueva en 'services'.
     * 3. Actualiza el registro del servicio con los nuevos datos.
     * 4. Registra la accion de auditoria con los valores anteriores y la descripcion
     *    de la operacion realizada por el usuario.
     *
     * @param  \Illuminate\Http\Request  $request Solicitud HTTP con los datos actualizados.
     * @param  int  $id Identificador unico del servicio a actualizar.
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de servicios con un mensaje de exito.
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'external_url' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $data = $request->except(['image']);

        // Procesar imagen principal
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        // Actualizar el servicio
        $oldValues = $service->toArray();
        $service->update($data);

        $userName = Auth::user()->full_name;
        $this->logUpdated($service, $oldValues, null, "{$userName} actualizó el servicio '{$service->title}'");

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    /**
     * Elimina un servicio y todos sus recursos asociados.
     *
     * Este metodo realiza las siguientes operaciones:
     * 1. Busca el servicio especificado por su identificador unico.
     * 2. Elimina la imagen principal del disco publico si existe.
     * 3. Registra la accion de auditoria antes de eliminar el registro.
     * 4. Elimina el registro del servicio de la base de datos.
     *
     * @param  int  $id Identificador unico del servicio a eliminar.
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de servicios con un mensaje de exito.
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);

        // Eliminar imagen principal
        if ($service->image && Storage::disk('public')->exists($service->image)) {
            Storage::disk('public')->delete($service->image);
        }

        $userName = Auth::user()->full_name;
        $this->logDeleted($service, "{$userName} eliminó el servicio '{$service->title}'");

        $service->delete();

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio eliminado exitosamente.');
    }

    /**
     * Reordena los servicios mediante una solicitud AJAX.
     *
     * Recibe un array con los IDs de los servicios en el nuevo orden deseado
     * (por ejemplo, resultante de un drag-and-drop en la interfaz) y actualiza
     * el campo 'order' de cada servicio segun su posicion en el array.
     *
     * El indice del array corresponde al nuevo valor de orden:
     * - Primer elemento (indice 0) -> order = 0
     * - Segundo elemento (indice 1) -> order = 1
     * - Y asi sucesivamente...
     *
     * @param  \Illuminate\Http\Request  $request Solicitud HTTP con el array 'order' que contiene los IDs en el nuevo orden.
     * @return \Illuminate\Http\JsonResponse Retorna JSON con { success: true } si la operacion fue exitosa.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:services,id',
        ]);

        foreach ($request->order as $index => $id) {
            Service::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Muestra la vista publica de servicios para el frontend del sitio.
     *
     * Este metodo esta destinado a ser consumido por los visitantes del sitio
     * web. Solo carga los servicios activos (is_active = true), ordenados por
     * su campo 'order'. Tambien carga los asesores inmobiliarios activos con
     * sus propiedades publicadas y la configuracion del sitio.
     *
     * A diferencia del metodo index(), este metodo:
     * - No requiere autenticacion ni permisos especiales.
     * - Filtra solo servicios activos.
     * - No incluye datos de filtros de ubicacion.
     *
     * @return \Illuminate\View\View Retorna la vista 'modulos.servicios.public' con los servicios activos y asesores.
     */
    public function publicIndex()
    {
        $services = Service::where('is_active', true)
            ->orderBy('order')
            ->get();

        $asesores = User::role('Asesor Inmobiliario')
            ->with(['properties' => function($query) {
                $query->where('status', 'publicada');
            }])
            ->where('is_active', true)
            ->get();

        $config = SiteConfiguration::getConfig();

        return view('modulos.servicios.public', compact('services', 'asesores', 'config'));
    }
}
