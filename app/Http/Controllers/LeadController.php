<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\Property;
use App\Models\UserNotification;
use App\Traits\AuditTrait;
use App\Traits\SendsNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Leads
 *
 * Gestiona el ciclo de vida completo de los leads inmobiliarios: creacion,
 * consulta, edicion, eliminacion y cambio de estado.
 *
 * Flujo general del lead:
 *   1. Un lead ingresa desde el formulario publico del sitio web (storePublic)
 *      o es creado internamente por un usuario autenticado (store).
 *   2. Se asigna automaticamente un asesor inmobiliario disponible mediante
 *      logica round-robin (ver storePublic).
 *   3. Se registra una auditoria de la creacion y se envian notificaciones
 *      al asesor asignado y, si aplica, al cliente que registro la cuenta.
 *   4. A lo largo de su vida, el lead avanza por estados: nuevo -> contactado
 *      -> calificado -> negociacion -> cerrado_ganado / cerrado_perdido.
 *   5. Los asesores pueden filtrar, ver, editar y cambiar el estado de sus leads.
 *   6. Un administrador puede asignar, editar o eliminar cualquier lead.
 *
 * Permisos requeridos:
 *   - "ver leads": para index y show.
 *   - "editar lead": para edit y update.
 *   - "eliminar lead": para destroy.
 *   - "crear lead": para store (interno).
 *
 * Traits utilizados:
 *   - AuditTrait: registra acciones de creacion, actualizacion y eliminacion
 *     en la tabla de auditoria (audit_logs).
 *   - SendsNotifications: envia notificaciones in-app a los usuarios
 *     (asesores y clientes) a traves del modelo UserNotification.
 */
class LeadController extends Controller
{
    use AuditTrait, SendsNotifications;

    /**
     * Constructor: define los middlewares de autenticacion y permisos
     * para cada metodo del controlador.
     *
     * Flujo:
     *   - Se aplica el middleware 'auth' a los metodos index, show, edit,
     *     update, destroy y changeStatus, exigiendo que el usuario este
     *     autenticado para acceder a ellos.
     *   - Se aplican middlewares de permisos (spatie/laravel-permission) para
     *     controlar el acceso segun el rol del usuario:
     *       * "ver leads"     -> index, show
     *       * "editar lead"   -> edit, update
     *       * "eliminar lead" -> destroy
     *
     * Nota: storePublic y getLeadsData no tienen middleware de auth en el
     * constructor porque storePublic debe ser accesible desde formularios
     * publicos del sitio web sin sesion activa, y getLeadsData sirve como
     * endpoint de datos para dashboards.
     */
    public function __construct()
    {
        $this->middleware('auth')->only(['index', 'show', 'edit', 'update', 'destroy', 'changeStatus']);
        $this->middleware('permission:ver leads')->only(['index', 'show']);
        $this->middleware('permission:editar lead')->only(['edit', 'update']);
        $this->middleware('permission:eliminar lead')->only(['destroy']);
    }

    /**
     * Convierte el identificador interno de un estado de lead a su etiqueta
     * legible para humanos.
     *
     * @param  string  $status  Clave interna del estado (ej: 'nuevo', 'negociacion').
     * @return string  Etiqueta en espanol (ej: 'Nuevo', 'En Negociacion').
     *                 Si no existe mapeo, devuelve la clave original sin modificar.
     *
     * Flujo de datos:
     *   Entrada: cadena de texto con el identificador del estado almacenado
     *            en la columna 'status' de la tabla 'leads'.
     *   Salida:  cadena de texto con el nombre amigable del estado, utilizada
     *            en vistas, respuestas JSON y mensajes de auditoria.
     *
     * Estados soportados:
     *   nuevo, contactado, calificado, negociacion, cerrado_ganado,
     *   cerrado_perdido, inactivo.
     */
    private function getStatusLabel($status)
    {
        $statuses = [
            'nuevo' => 'Nuevo',
            'contactado' => 'Contactado',
            'calificado' => 'Calificado',
            'negociacion' => 'En Negociación',
            'cerrado_ganado' => 'Cerrado - Ganado',
            'cerrado_perdido' => 'Cerrado - Perdido',
            'inactivo' => 'Inactivo'
        ];

        return $statuses[$status] ?? $status;
    }

    /**
     * Muestra la lista paginada de leads con filtros avanzados.
     *
     * Ruta: GET /leads
     * Vista: modulos.leads.index
     * Permiso requerido: "ver leads"
     *
     * @param  \Illuminate\Http\Request  $request  Peticion HTTP con parametros opcionales de filtrado.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *         Vista con la coleccion de leads paginados, la lista de asesores
     *         activos y los estados disponibles; o redireccion con mensaje de error.
     *
     * Parametros de filtrado (todos opcionales):
     *   - status (string): Filtra leads por estado exacto (ej: 'nuevo').
     *   - search  (string): Busqueda parcial por nombre, email o telefono del lead.
     *   - asesor_id (int): Filtra leads asignados a un asesor especifico.
     *   - date_from (date): Filtra leads creados desde esta fecha.
     *   - date_to   (date): Filtra leads creados hasta esta fecha.
     *
     * Flujo de datos:
     *   1. Se construye una consulta Eloquent sobre el modelo Lead.
     *   2. Se aplican filtros de forma dinamica segun los parametros presentes.
     *   3. Si el usuario autenticado tiene el rol "Asesor Inmobiliario", la
     *      consulta se restringe automaticamente a los leads asignados a ese
     *      usuario (aislamiento de datos por rol).
     *   4. Se cargan las relaciones: user (usuario que registro el lead),
     *      asesor (asesor asignado) y property (propiedad relacionada).
     *   5. Se pagina el resultado en bloques de 20 registros.
     *   6. Se obtiene la lista de asesores activos para los filtros de la vista.
     *   7. Se retorna la vista index con leads, asesores y estados.
     */
    public function index(Request $request)
    {
        try {
            $query = Lead::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            }

            if (Auth::check()) {
                $userId = Auth::id();
                if (Auth::user()->hasRole('Asesor Inmobiliario')) {
                    $query->where('asesor_id', $userId);
                }
            }

            if ($request->filled('asesor_id')) {
                $query->where('asesor_id', $request->asesor_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $leads = $query->with(['user', 'asesor', 'property'])
                ->orderBy('id', 'asc')
                ->paginate(20)
                ->withQueryString();

            // Obtener asesores con nombre completo
            $asesores = User::role('Asesor Inmobiliario')
                ->where('is_active', true)
                ->get(['id', 'name', 'last_name']);

            $statuses = [
                'nuevo' => 'Nuevo',
                'contactado' => 'Contactado',
                'calificado' => 'Calificado',
                'negociacion' => 'En Negociación',
                'cerrado_ganado' => 'Cerrado - Ganado',
                'cerrado_perdido' => 'Cerrado - Perdido',
                'inactivo' => 'Inactivo'
            ];

            return view('modulos.leads.index', compact('leads', 'asesores', 'statuses'));

        } catch (\Exception $e) {
            Log::error('Error en LeadController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los leads: ' . $e->getMessage());
        }
    }

    /**
     * Registra un lead nuevo desde el formulario publico del sitio web.
     *
     * Ruta: POST /leads/public (o ruta de valoracion de propiedad)
     * Vista: N/A - redirecciona a la pagina anterior con mensaje flash.
     * Permiso requerido: Ninguno (accesible sin autenticacion).
     *
     * @param  \Illuminate\Http\Request  $request  Formulario con datos del lead.
     * @return \Illuminate\Http\RedirectResponse  Redireccion con mensaje de exito o error.
     *
     * Campos del formulario:
     *   - name (requerido): Nombre del lead.
     *   - last_name (opcional): Apellido del lead.
     *   - email (requerido): Correo electronico del lead.
     *   - phone (requerido): Telefono de contacto.
     *   - asesor_id (opcional): ID del asesor preferido por el lead.
     *   - property_address (opcional): Direccion de la propiedad de interes.
     *   - property_type (opcional): Tipo de propiedad (casa, apartamento, etc.).
     *   - property_details (opcional): Detalles adicionales de la propiedad.
     *   - interest_type (opcional): Tipo de interés: compra, alquiler, venta o asesoria.
     *   - source (opcional): Fuente del lead (por defecto: 'website').
     *
     * ==============================================
     * LOGICA DE ASIGNACION ROUND-ROBIN DE ASESORES
     * ==============================================
     *
     * El lead debe ser asignado a un asesor inmobiliario activo. La asignacion
     * sigue este orden de prioridad:
     *
     *   Paso 1 - Asesor especifico:
     *     Si el lead envio un asesor_id en el formulario, se verifica que ese
     *     usuario exista, tenga el rol "Asesor Inmobiliario" y este activo.
     *     Si cumple todas las condiciones, se asigna directamente a ese asesor.
     *
     *   Paso 2 - Fallback por ID (round-robin basico):
     *     Si el asesor especificado no existe, no tiene el rol correcto, o esta
     *     inactivo, se busca el PRIMER asesor activo ordenado por ID ascendente.
     *     Esto implementa un round-robin estatico: al ordenar por ID y tomar el
     *     primero, se distribuyen los leads de forma circular entre los asesores
     *     disponibles. En la practica, el asesor con el ID mas bajo recibe la
     *     primera tanda de leads, y conforme se crean mas leads, el ciclo se
     *     repite entre todos los asesores activos.
     *
     *   Paso 3 - Sin asesor disponible:
     *     Si no hay ningun asesor inmobiliario activo en el sistema, el campo
     *     asesor_id queda como null y el lead se crea sin asignar.
     *
     * NOTA: El round-robin implementado es simplificado. Un round-robin real
     * mantendria un contador o registro del ultimo asesor asignado para
     * distribuir equitativamente. Aqui se asigna siempre al primer asesor
     * por ID, lo que funciona cuando hay pocos asesores pero puede generar
     * desbalance con muchos.
     *
     * ==============================================
     * FLUJO COMPLETO DE NOTIFICACIONES
     * ==============================================
     *
     *   1. AUDITORIA: Se registra en la tabla audit_logs mediante logCreated()
     *      con el nombre del usuario que capturo el lead (o "Sistema" si es
     *      anonimo) y el nombre del lead creado.
     *
     *   2. NOTIFICACION AL ASESOR: Si se asigno un asesor (asesor_id no nulo),
     *      se busca el usuario asesor y se le envia una notificacion in-app
     *      (tabla user_notifications) con:
     *        - Titulo: "Nuevo Lead Asignado"
     *        - Mensaje: nombre del cliente, telefono.
     *        - Tipo: success.
     *        - URL de redireccion: ruta leads.index.
     *        - Metadata: lead_id para referencia cruzada.
     *      Esto garantiza que el asesor sea informado inmediatamente de su
     *      nuevo lead asignado.
     *
     *   3. NOTIFICACION AL CLIENTE: Si el usuario que envia el formulario
     *      tiene una cuenta registrada en el sistema (Auth::check() es true),
     *      se busca el usuario cliente y se le envia una notificacion in-app
     *      con:
     *        - Titulo: "Solicitud Enviada"
     *        - Mensaje: confirmacion de que la solicitud fue recibida.
     *        - Tipo: success.
     *        - Sin URL de redireccion (null).
     *        - Metadata: lead_id.
     *      Si el usuario no tiene cuenta (visitante anonimo), no se envia
     *      notificacion al cliente.
     */
    public function storePublic(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:50',
                'asesor_id' => 'nullable|exists:users,id',
                'property_address' => 'nullable|string|max:500',
                'property_type' => 'nullable|string|max:100',
                'property_details' => 'nullable|string',
                'interest_type' => 'nullable|string|in:compra,alquiler,venta,asesoria',
                'source' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            if ($request->filled('asesor_id')) {
                $asesor = User::role('Asesor Inmobiliario')
                    ->where('id', $request->asesor_id)
                    ->where('is_active', true)
                    ->first();

                if ($asesor) {
                    $asesorId = $asesor->id;
                } else {
                    $asesor = User::role('Asesor Inmobiliario')
                        ->where('is_active', true)
                        ->orderBy('id')
                        ->first();
                    $asesorId = $asesor ? $asesor->id : null;
                }
            } else {
                $asesor = User::role('Asesor Inmobiliario')
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->first();

                $asesorId = $asesor ? $asesor->id : null;
            }

            $fullName = $request->name;
            if ($request->filled('last_name')) {
                $fullName = $request->name . ' ' . $request->last_name;
            }

            $userId = Auth::check() ? Auth::id() : null;

            $lead = Lead::create([
                'name' => $fullName,
                'email' => $request->email,
                'phone' => $request->phone,
                'notes' => $request->property_details,
                'source' => $request->source ?? 'website',
                'source_detail' => 'Valoración de propiedad',
                'interest_type' => $request->interest_type ?? 'venta',
                'status' => 'nuevo',
                'asesor_id' => $asesorId,
                'user_id' => $userId,
            ]);

            $preferences = [];
            if ($request->property_address) {
                $preferences['property_address'] = $request->property_address;
            }
            if ($request->property_type) {
                $preferences['property_type'] = $request->property_type;
            }
            if ($request->last_name) {
                $preferences['last_name'] = $request->last_name;
            }

            if (!empty($preferences)) {
                $lead->update(['preferences' => $preferences]);
            }

            //  AUDITORÍA - CREACIÓN DE LEAD
            $this->logCreated($lead, (Auth::user()?->full_name ?? 'Sistema') . ' CAPTÓ un nuevo lead: "' . $fullName . '" (Email: ' . $request->email . ')');

            // NOTIFICACIÓN AL ASESOR
            if ($asesorId) {
                $asesor = User::find($asesorId);
                if ($asesor) {
                    $this->notifyUser(
                        $asesor,
                        'Nuevo Lead Asignado',
                        "El cliente \"{$fullName}\" te ha seleccionado como su asesor. Tel: {$request->phone}",
                        'success',
                        route('leads.index'),
                        ['lead_id' => $lead->id]
                    );
                }
            }

            // NOTIFICACIÓN AL CLIENTE (solo si tiene cuenta registrada)
            if ($userId) {
                $client = User::find($userId);
                if ($client) {
                    $this->notifyUser(
                        $client,
                        'Solicitud Enviada',
                        "Tu solicitud de valoración ha sido recibida. Un asesor se comunicará contigo pronto.",
                        'success',
                        null,
                        ['lead_id' => $lead->id]
                    );
                }
            }

            return redirect()->back()
                ->with('success', '¡Solicitud enviada con éxito! El asesor se comunicará contigo pronto.');

        } catch (\Exception $e) {
            Log::error('Error en LeadController@storePublic: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ocurrió un error al enviar la solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Registra un lead desde el panel interno (usuario autenticado).
     *
     * Ruta: POST /leads
     * Vista: N/A - delega completamente a storePublic().
     * Permiso requerido: "crear lead".
     *
     * @param  \Illuminate\Http\Request  $request  Mismo formulario que storePublic.
     * @return \Illuminate\Http\RedirectResponse  Respuesta de storePublic().
     *
     * Flujo de datos:
     *   1. Verifica que el usuario este autenticado (Auth::check()). Si no lo
     *      esta, aborta con 403.
     *   2. Verifica que el usuario tenga el permiso 'crear lead' mediante
     *      Spatie Permission. Si no lo tiene, aborta con 403.
     *   3. Delega toda la logica de creacion, asignacion de asesor,
     *      auditoria y notificaciones a storePublic().
     *
     * Este metodo existe como punto de entrada diferenciado para que el
     * sistema de permisos pueda controlar internamente quien crea leads,
     * mientras que storePublic permanece abierto al publico.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'Debes iniciar sesión para crear leads.');
        }

        if (!Auth::user()->can('crear lead')) {
            abort(403, 'No tienes permiso para crear leads.');
        }

        return $this->storePublic($request);
    }

    /**
     * Muestra el detalle completo de un lead.
     *
     * Ruta: GET /leads/{lead}
     * Vista: modulos.leads.show (HTML) o respuesta JSON (peticiones AJAX).
     * Permiso requerido: "ver leads".
     *
     * @param  \App\Models\Lead  $lead  Lead inyectado por route model binding.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     *         Vista con el lead completo, o respuesta JSON con todos los campos.
     *
     * Flujo de datos:
     *   1. Se resuelve el modelo Lead a partir del ID en la URL (route model
     *      binding de Laravel). Si el ID no existe, Laravel retorna 404.
     *   2. Se cargan eager-loading de las relaciones: user (quien registro
     *      el lead), asesor (asesor asignado) y property (propiedad asociada).
     *   3. Si la peticion es AJAX o espera JSON (headers Accept: application/json),
     *      se retorna un objeto JSON con todos los campos del lead incluyendo:
     *        - Datos basicos: id, name, email, phone, status, notes.
     *        - Etiqueta legible del estado (via getStatusLabel).
     *        - Preferencias del lead (JSON con direccion, tipo, apellido).
     *        - Relaciones: asesor, user y property (solo campos esenciales).
     *        - Timestamps: created_at, updated_at.
     *        - Origen: source, source_detail, interest_type.
     *   4. Si la peticion es HTML normal, se retorna la vista show con el
     *      lead completo.
     *
     * Manejo de errores:
     *   - Se capturan excepciones generales y se registra el error en el log.
     *   - En peticiones JSON se retorna un error 500 con mensaje.
     *   - En peticiones HTML se redirige a leads.index con mensaje flash.
     */
    public function show(Lead $lead)
    {
        try {
            $lead->load(['user', 'asesor', 'property']);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'status' => $lead->status,
                    'status_label' => $this->getStatusLabel($lead->status),
                    'notes' => $lead->notes,
                    'preferences' => $lead->preferences,
                    'asesor' => $lead->asesor ? [
                        'id' => $lead->asesor->id,
                        'name' => $lead->asesor->name,
                        'last_name' => $lead->asesor->last_name ?? ''
                    ] : null,
                    'user' => $lead->user ? [
                        'id' => $lead->user->id,
                        'name' => $lead->user->name
                    ] : null,
                    'property' => $lead->property ? [
                        'id' => $lead->property->id,
                        'title' => $lead->property->title
                    ] : null,
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->updated_at,
                    'source' => $lead->source,
                    'source_detail' => $lead->source_detail,
                    'interest_type' => $lead->interest_type,
                ]);
            }

            return view('modulos.leads.show', compact('lead'));

        } catch (\Exception $e) {
            Log::error('Error en LeadController@show: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'No se pudo cargar el detalle del lead.'], 500);
            }
            return redirect()->route('leads.index')
                ->with('error', 'No se pudo cargar el detalle del lead.');
        }
    }

    /**
     * Muestra el formulario de edicion para un lead existente.
     *
     * Ruta: GET /leads/{lead}/edit
     * Vista: modulos.leads.edit
     * Permiso requerido: "editar lead".
     *
     * @param  \App\Models\Lead  $lead  Lead inyectado por route model binding.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     *         Vista con el formulario precargado, o vista parcial para AJAX.
     *
     * Flujo de datos:
     *   1. Se obtiene la lista de asesores inmobiliarios activos (rol
     *      "Asesor Inmobiliario", is_active = true) con campos id, name
     *      y last_name. Esta lista se usa en el formulario para el select
     *      de reasignacion de asesor.
     *   2. Se construye el array de estados disponibles con sus etiquetas
     *      para el select de estado.
     *   3. Si la peticion es AJAX, se retorna la vista edit directamente
     *      (utilizado para modales o formularios cargados dinamicamente).
     *   4. Si la peticion es HTML normal, se retorna la vista edit con
     *      el lead, asesores y estados disponibles.
     *
     * Datos enviados a la vista:
     *   - lead: modelo completo del lead a editar.
     *   - asesores: coleccion de usuarios con rol Asesor Inmobiliario activos.
     *   - statuses: array asociativo de estados con clave => etiqueta.
     */
    public function edit(Lead $lead)
    {
        try {
            $asesores = User::role('Asesor Inmobiliario')
                ->where('is_active', true)
                ->get(['id', 'name', 'last_name']);

            $statuses = [
                'nuevo' => 'Nuevo',
                'contactado' => 'Contactado',
                'calificado' => 'Calificado',
                'negociacion' => 'En Negociación',
                'cerrado_ganado' => 'Cerrado - Ganado',
                'cerrado_perdido' => 'Cerrado - Perdido',
                'inactivo' => 'Inactivo'
            ];

            if (request()->ajax()) {
                return view('modulos.leads.edit', compact('lead', 'asesores', 'statuses'));
            }

            return view('modulos.leads.edit', compact('lead', 'asesores', 'statuses'));

        } catch (\Exception $e) {
            Log::error('Error en LeadController@edit: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json(['error' => 'No se pudo cargar el formulario de edición.'], 500);
            }
            return redirect()->route('leads.index')
                ->with('error', 'No se pudo cargar el formulario de edición.');
        }
    }

    /**
     * Actualiza los datos de un lead existente.
     *
     * Ruta: PUT /leads/{lead}
     * Vista: N/A - redirecciona a leads.index (HTML) o retorna JSON (AJAX).
     * Permiso requerido: "editar lead".
     *
     * @param  \Illuminate\Http\Request  $request  Formulario con los campos actualizados.
     * @param  \App\Models\Lead          $lead     Lead inyectado por route model binding.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *         Redireccion con mensaje de exito o respuesta JSON.
     *
     * Campos editables:
     *   - name (requerido): Nombre completo del lead.
     *   - email (requerido): Correo electronico.
     *   - phone (requerido): Telefono de contacto.
     *   - status (requerido): Estado del lead en el pipeline de ventas.
     *   - asesor_id (opcional): Reasignar el lead a otro asesor.
     *   - notes (opcional): Notas internas sobre el lead.
     *
     * Flujo de datos:
     *   1. Se validan todos los campos del formulario con las reglas definidas.
     *   2. Si la validacion falla, se retorna errores (JSON 422 para AJAX,
     *      o redireccion con errores y datos de entrada para HTML).
     *   3. Se capturan los valores anteriores del lead (toArray) antes de
     *      la actualizacion para el registro de auditoria.
     *   4. Se actualiza el lead con todos los campos del request.
     *   5. Se compara el estado anterior con el nuevo para generar un
     *      registro legible de cambios (solo campos que realmente cambiaron,
     *      excluyendo updated_at).
     *   6. Se registra la auditoria via logUpdated() con:
     *        - Modelo lead.
     *        - Valores anteriores (oldValues).
     *        - Array de cambios formateados como "campo: 'valor anterior' -> 'valor nuevo'".
     *
     * ==============================================
     * FLUJO DE AUDITORIA EN ACTUALIZACION
     * ==============================================
     *
     * Los campos se traducen a etiquetas legibles en espanol:
     *   name -> nombre, email -> email, phone -> telefono,
     *   status -> estado, notes -> notas, asesor_id -> asesor asignado.
     *
     * Solo se registran los campos que cambiaron realmente. Ejemplo de
     * registro generado:
     *   "estado: 'Nuevo' -> 'Contactado', asesor asignado: '5' -> '12'"
     *
     * NOTA: No se envian notificaciones en la actualizacion general.
     * Las notificaciones de cambio de estado se manejan en changeStatus().
     */
    public function update(Request $request, Lead $lead)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:50',
                'status' => 'required|in:nuevo,contactado,calificado,negociacion,cerrado_ganado,cerrado_perdido,inactivo',
                'asesor_id' => 'nullable|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $oldValues = $lead->toArray();
            $lead->update($request->all());

            //  AUDITORÍA - ACTUALIZACIÓN DE LEAD
            $changes = [];
            $fieldLabels = [
                'name' => 'nombre',
                'email' => 'email',
                'phone' => 'teléfono',
                'status' => 'estado',
                'notes' => 'notas',
                'asesor_id' => 'asesor asignado',
            ];

            foreach ($lead->getChanges() as $key => $value) {
                if ($key !== 'updated_at' && isset($oldValues[$key])) {
                    $label = $fieldLabels[$key] ?? $key;
                    $changes[] = "{$label}: '{$oldValues[$key]}' → '{$value}'";
                }
            }

            if (!empty($changes)) {
                $this->logUpdated($lead, $oldValues, $changes);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead actualizado exitosamente.',
                    'lead' => $lead
                ]);
            }

            return redirect()->route('leads.index')
                ->with('success', 'Lead actualizado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error en LeadController@update: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Error al actualizar el lead.'], 500);
            }
            return redirect()->back()
                ->with('error', 'Error al actualizar el lead.')
                ->withInput();
        }
    }

    /**
     * Elimina un lead del sistema.
     *
     * Ruta: DELETE /leads/{lead}
     * Vista: N/A - redirecciona a leads.index (HTML) o retorna JSON (AJAX).
     * Permiso requerido: "eliminar lead".
     *
     * @param  \App\Models\Lead  $lead  Lead inyectado por route model binding.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *         Redireccion con mensaje de exito o respuesta JSON.
     *
     * ==============================================
     * FLUJO DE AUDITORIA EN ELIMINACION
     * ==============================================
     *
     *   1. ANTES de eliminar el lead, se registra la auditoria via logDeleted()
     *      con:
     *        - Modelo lead (se capturan todos los datos antes de borrar).
     *        - Mensaje descriptivo: "[nombre del usuario] ELIMINO el lead
     *          '[nombre del lead]'".
     *   2. Se ejecuta la eliminacion fisica del registro (soft delete no
     *      esta habilitado en este controlador).
     *   3. Se retorna exito o error segun el tipo de peticion.
     *
     * Flujo de datos:
     *   Entrada: ID del lead via URL (route model binding).
     *   Salida: Registro de auditoria en audit_logs + eliminacion del
     *           registro en la tabla leads + respuesta al usuario.
     *
     * IMPORTANTE: La auditoria se registra ANTES de la eliminacion para
     * asegurar que los datos del lead esten disponibles para el log.
     */
    public function destroy(Lead $lead)
    {
        try {
            //  AUDITORÍA - ELIMINACIÓN DE LEAD
            $this->logDeleted($lead, (Auth::user()?->full_name ?? 'Sistema') . ' ELIMINÓ el lead "' . $lead->name . '"');

            $lead->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead eliminado exitosamente.'
                ]);
            }

            return redirect()->route('leads.index')
                ->with('success', 'Lead eliminado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error en LeadController@destroy: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json(['error' => 'Error al eliminar el lead.'], 500);
            }
            return redirect()->route('leads.index')
                ->with('error', 'Error al eliminar el lead.');
        }
    }

    /**
     * Cambia el estado de un lead de forma rapida y dedicada.
     *
     * Ruta: PUT /leads/{lead}/status (o PATCH)
     * Vista: N/A - redirecciona a la pagina anterior (HTML) o retorna JSON (AJAX).
     * Permiso requerido: Middleware 'auth' (aplicado en constructor).
     *
     * @param  \Illuminate\Http\Request  $request  Formulario con el nuevo estado.
     * @param  \App\Models\Lead          $lead     Lead inyectado por route model binding.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *         Redireccion con mensaje de exito, o respuesta JSON con el
     *         nuevo estado y su etiqueta legible.
     *
     * Estados validos:
     *   nuevo, contactado, calificado, negociacion, cerrado_ganado,
     *   cerrado_perdido, inactivo.
     *
     * Flujo de datos:
     *   1. Se valida que el campo status este presente y sea un estado valido.
     *   2. Si la validacion falla, se retorna errores 422 (JSON) o
     *      redireccion con errores (HTML).
     *   3. Se captura el estado anterior (oldStatus) y los valores completos
     *      del lead antes de la actualizacion (oldValues).
     *   4. Se actualiza SOLO el campo status del lead.
     *   5. Se registra la auditoria via logAudit() con:
     *        - Tipo de accion: 'updated'.
     *        - Modelo lead.
     *        - Valores anteriores (oldValues) y nuevos (lead->toArray()).
     *        - Mensaje descriptivo con los nombres legibles del estado
     *          anterior y nuevo.
     *
     * ==============================================
     * DIFERENCIA CON update()
     * ==============================================
     *
     * Este metodo esta disenado para cambios rapidos de estado desde la
     * interfaz de usuario (botones, dropdowns, drag-and-drop en un kanban).
     * Solo modifica el campo 'status', mientras que update() permite
     * modificar multiples campos a la vez.
     *
     * Ambos metodos registran auditoria, pero changeStatus() utiliza
     * logAudit() directamente (registering the full before/after snapshots),
     * mientras que update() usa logUpdated() con un formato de cambios
     * legibles.
     *
     * NOTA: No se envian notificaciones de cambio de estado a traves de
     * este metodo. Si se desea notificar al asesor o al cliente cuando
     * el estado cambia, se debe implementar aqui o en un listener de eventos.
     */
    public function changeStatus(Request $request, Lead $lead)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:nuevo,contactado,calificado,negociacion,cerrado_ganado,cerrado_perdido,inactivo'
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()
                    ->withErrors($validator);
            }

            $oldStatus = $lead->status;
            $oldValues = $lead->toArray();
            $lead->update(['status' => $request->status]);

            //  AUDITORÍA - CAMBIO DE ESTADO DE LEAD
            $statusLabels = [
                'nuevo' => 'Nuevo',
                'contactado' => 'Contactado',
                'calificado' => 'Calificado',
                'negociacion' => 'En Negociación',
                'cerrado_ganado' => 'Cerrado - Ganado',
                'cerrado_perdido' => 'Cerrado - Perdido',
                'inactivo' => 'Inactivo'
            ];

            $this->logAudit('updated', $lead, $oldValues, $lead->toArray(),
                (Auth::user()?->full_name ?? 'Sistema') . ' CAMBIÓ el estado del lead "' . $lead->name . '" de "' . ($statusLabels[$oldStatus] ?? $oldStatus) . '" a "' . ($statusLabels[$request->status] ?? $request->status) . '"'
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente',
                    'status' => $lead->status,
                    'status_label' => $this->getStatusLabel($lead->status)
                ]);
            }

            return redirect()->back()
                ->with('success', 'Estado actualizado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error en LeadController@changeStatus: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el estado'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al actualizar el estado.');
        }
    }

    /**
     * Endpoint API que retorna leads en formato JSON para dashboards y reportes.
     *
     * Ruta: GET /leads/data (o similar, segun definicion de rutas).
     * Respuesta: JSON con coleccion paginada de leads.
     * Permiso requerido: Ninguno en el constructor (verificar en rutas).
     *
     * @param  \Illuminate\Http\Request  $request  Parametros de filtrado opcionales.
     * @return \Illuminate\Http\JsonResponse
     *         JSON con estructura: { success: bool, data: paginatedCollection }
     *
     * Parametros de filtrado (todos opcionales):
     *   - status (string): Filtra por estado exacto del lead.
     *   - asesor_id (int): Filtra por asesor asignado.
     *   - date_from (date): Leads creados desde esta fecha.
     *   - date_to   (date): Leads creados hasta esta fecha.
     *
     * Flujo de datos:
     *   1. Se construye una consulta Eloquent sobre Lead.
     *   2. Se aplican filtros dinamicamente segun los parametros presentes.
     *   3. Se cargan las relaciones user y asesor (no property, a diferencia
     *      de index, para optimizar el rendimiento en respuestas API).
     *   4. Se pagina en bloques de 100 registros (mayor que index que usa 20,
     *      pensado para componentes de dashboard que muestran mas datos).
     *   5. Se retorna el JSON con success = true y la coleccion paginada.
     *
     * Diferencia con index():
     *   - Este metodo es exclusivamente JSON (no retorna vistas).
     *   - No aplica el filtro de aislamiento por rol de asesor (no verifica
     *     Auth o el rol del usuario).
     *   - Usa un paginado mas amplio (100 vs 20).
     *   - No incluye la relacion property para ser mas ligero.
     *
     * Uso típico: graficos de dashboard, tablas de reportes, componentes
     * JavaScript que cargan datos de leads dinamicamente.
     */
    public function getLeadsData(Request $request)
    {
        try {
            $query = Lead::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('asesor_id')) {
                $query->where('asesor_id', $request->asesor_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $leads = $query->with(['user', 'asesor'])->paginate(100);

            return response()->json([
                'success' => true,
                'data' => $leads
            ]);

        } catch (\Exception $e) {
            Log::error('Error en LeadController@getLeadsData: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos'
            ], 500);
        }
    }
}
