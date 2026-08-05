<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use App\Models\AppointmentSetting;
use App\Traits\AuditTrait;
use App\Traits\SendsNotifications;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AppointmentController
 *
 * Controlador encargado de gestionar el ciclo de vida completo de las citas
 * inmobiliarias: creacion, consulta, actualizacion de estado y reprogramacion.
 *
 * Ciclo de vida del estado de una cita:
 *   pending -> confirmed -> completed
 *   pending -> cancelled
 *   confirmed -> cancelled
 *   confirmed -> reprogrammed -> pending (al reprogramar se cambia a reprogrammed,
 *                y el asesor debe volver a confirmar la nueva fecha)
 *   completed / cancelled son estados finales (no se pueden modificar).
 *
 * Flujo de datos:
 *   1. El cliente selecciona una propiedad y un horario disponible.
 *   2. Se verifica la disponibilidad dentro de una transaccion con bloqueo
 *      (lockForUpdate) para evitar condiciones de carrera entre usuarios
 *      que intenten reservar el mismo slot simultaneamente.
 *   3. Se crea la cita con estado "pending".
 *   4. Se envian notificaciones al asesor asignado y al cliente.
 *   5. El asesor o un administrador pueden confirmar, cancelar o reprogramar.
 *
 * Proteccion contra condiciones de carrera:
 *   El metodo store utiliza DB::transaction junto con lockForUpdate sobre
 *   las filas de citas existentes del mismo asesor en el mismo dia. Esto
 *   garantiza que dos procesos concurrentes no puedan crear una cita que
 *   exceda el limite diario o que duplique un slot ya reservado.
 *
 * Notificaciones:
 *   - Se usa el trait SendsNotifications para enviar notificaciones internas
 *     al asesor y al cliente en cada cambio relevante (creacion, cambio
 *     de estado, reprogramacion).
 *   - Cada notificacion incluye un enlace directo al panel de citas.
 *
 * Auditoria:
 *   - Se usa el trait AuditTrait para registrar cada accion critica
 *     (creacion, cambio de estado, reprogramacion) con los valores
 *     anteriores y nuevos, ademas de una descripcion legible.
 *
 * @package App\Http\Controllers
 */
class AppointmentController extends Controller
{
    use AuditTrait, SendsNotifications;

    /**
     * Servicio de permisos inyectado via constructor.
     * Se utiliza para verificar si el usuario autenticado tiene el
     * permiso "crear cita" antes de permitir el registro de una nueva cita.
     *
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * Constructor del controlador.
     *
     * Aplica el middleware 'auth' a todos los metodos excepto createPublic,
     * que permite a usuarios no autenticados ver el formulario de agendamiento
     * (y ser redirigidos al login si no tienen sesion activa).
     *
     * Inyecta el PermissionService para verificacion de permisos
     * en las operaciones de escritura.
     *
     * @param  PermissionService  $permissionService  Servicio de verificacion de permisos
     * @return void
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->middleware('auth')->except(['createPublic']);
        $this->permissionService = $permissionService;
    }

    /**
     * Obtiene los nombres de los roles asignados a un usuario.
     *
     * Consulta directamente las tablas del paquete Spatie Laravel-Permission
     * (model_has_roles y roles) para obtener una lista de cadenas con los
     * nombres de todos los roles activos del usuario.
     *
     * Flujo:
     *   1. Se consulta la tabla pivote model_has_roles filtrando por el ID
     *      y el tipo del modelo (App\Models\User).
     *   2. Se hace JOIN con la tabla roles para obtener el nombre de cada rol.
     *   3. Se devuelve un array simple de cadenas, ej: ["Super Admin", "Asesor Inmobiliario"].
     *
     * Se utiliza en los metodos index, store, updateStatus y reschedule
     * para implementar la logica de control de acceso basada en roles.
     *
     * @param  int  $userId  ID del usuario cuyos roles se desean consultar
     * @return array          Array de cadenas con los nombres de los roles
     */
    private function getUserRoles($userId)
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $userId)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();
    }

    /**
     * Muestra la lista paginada de citas con filtros, busqueda y estadisticas.
     *
     * Flujo de datos:
     *   1. Se obtiene el usuario autenticado y sus roles.
     *   2. Segun el rol, se aplica un filtro base sobre las citas:
     *      - Super Admin / Administrador / Auditor: ven todas las citas (con
     *        opcion de filtrar por un asesor especifico o "todos").
     *      - Asesor Inmobiliario: solo ve sus propias citas (donde es asesor).
     *      - Cliente: solo ve las citas que el mismo ha agendado.
     *   3. Se aplican filtros opcionales adicionales: estado, rango de fechas
     *      y busqueda libre por nombre, email, telefono o titulo de propiedad.
     *   4. Se ejecuta la consulta con paginacion (15 registros por pagina)
     *      ordenadas por fecha programada descendente.
     *   5. Se calculan estadisticas (total, pendientes, confirmadas, completadas,
     *      canceladas) usando la misma base de filtros para mantener coherencia
     *      entre la tabla y los contadores.
     *   6. Se retorna la vista modulos.citas.index con todos los datos necesarios.
     *
     * @param  Request  $request  Objeto de peticion HTTP con parametros opcionales:
     *                            asesor_id, status, date_from, date_to, search
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $userRoles = $this->getUserRoles($user->id);
        $primaryRole = $userRoles[0] ?? 'Cliente';

        $asesores = collect();
        if (in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles) || in_array('Auditor', $userRoles)) {
            $asesores = User::whereHas('roles', function($q) {
                $q->where('name', 'Asesor Inmobiliario');
            })->where('is_active', true)->get();
        }

        $query = Appointment::with(['property.images', 'property.user', 'asesor', 'user']);

        if (in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles) || in_array('Auditor', $userRoles)) {
            if ($request->filled('asesor_id') && $request->asesor_id !== 'todos') {
                $query->where('asesor_id', $request->asesor_id);
            }
        } elseif (in_array('Asesor Inmobiliario', $userRoles)) {
            $query->where('asesor_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhereHas('property', function($sub) use ($search) {
                      $sub->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $appointments = $query->orderBy('scheduled_date', 'desc')->paginate(15);

        $statsBase = Appointment::query();
        if (in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles) || in_array('Auditor', $userRoles)) {
            if ($request->filled('asesor_id') && $request->asesor_id !== 'todos') $statsBase->where('asesor_id', $request->asesor_id);
        } elseif (in_array('Asesor Inmobiliario', $userRoles)) {
            $statsBase->where('asesor_id', $user->id);
        } else {
            $statsBase->where('user_id', $user->id);
        }
        if ($request->filled('status') && $request->status !== 'todos') $statsBase->where('status', $request->status);
        if ($request->filled('date_from')) $statsBase->whereDate('scheduled_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $statsBase->whereDate('scheduled_date', '<=', $request->date_to);
        if ($request->filled('search')) {
            $search = $request->search;
            $statsBase->where(function($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                  ->orWhereHas('property', function($sub) use ($search) {
                      $sub->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->where('status', 'pending')->count(),
            'confirmed' => (clone $statsBase)->where('status', 'confirmed')->count(),
            'completed' => (clone $statsBase)->where('status', 'completed')->count(),
            'cancelled' => (clone $statsBase)->where('status', 'cancelled')->count(),
        ];

        return view('modulos.citas.index', compact(
            'appointments', 'user', 'asesores', 'userRoles', 'primaryRole', 'stats'
        ));
    }

    /**
     * Registra una nueva cita inmobiliaria en el sistema.
     *
     * Este metodo implementa la logica central del agendamiento, incluyendo
     * verificacion de permisos, validacion de datos, proteccion contra
     * condiciones de carrera y envio de notificaciones.
     *
     * Flujo completo:
     *   1. Se verifica que el usuario este autenticado.
     *   2. Se configura el PermissionService con el usuario actual y se
     *      verifica que tenga el permiso "crear cita". Si no lo tiene,
     *      se registra un warning en el log y se retorna 403.
     *   3. Se validan los campos del formulario (nombre, email, telefono,
     *      fecha, propiedad, mensaje opcional). La fecha debe ser futura.
     *   4. Se obtiene la propiedad y se verifica que exista.
     *   5. Se cargan las configuraciones de citas del asesor propietario
     *      de la propiedad. Si el asesor no esta activo, se rechaza.
     *   6. Se ejecuta un DB::transaction con las siguientes validaciones
     *      atomicas (ver Proteccion contra condiciones de carrera abajo).
     *   7. Si se creo la cita exitosamente, se registra en la auditoria.
     *   8. Se envian notificaciones al asesor y al cliente.
     *   9. Se retorna una respuesta JSON con el resultado.
     *
     * Proteccion contra condiciones de carrera (DB::transaction + lockForUpdate):
     *
     *   El problema:
     *     Dos usuarios pueden intentar reservar el ultimo slot disponible
     *     de un asesor para el mismo dia al mismo tiempo. Sin proteccion,
     *     ambos pasarian la verificacion de disponibilidad y se crearian
     *     dos citas, superando el limite diario (max_appointments_per_day).
     *
     *   La solucion:
     *     DB::transaction inicia una transaccion de base de datos que
     *     garantiza atomicidad (commit o rollback completo).
     *
     *     Dentro de la transaccion, lockForUpdate aplica un bloqueo
     *     EXCLUSIVE LOCK sobre las filas que coinciden con la consulta
     *     (citas del mismo asesor en la misma fecha, excluyendo las
     *     canceladas). Esto bloquea cualquier otra transaccion que
     *     intente leer o modificar esas mismas filas hasta que la
     *     transaccion actual haga commit o rollback.
     *
     *     Flujo del bloqueo:
     *       a) La transaccion T1 consulta las citas del asesor para el dia D
     *          con lockForUpdate -> obtiene el bloqueo exclusivo.
     *       b) Si la transaccion T2 intenta hacer la misma consulta para el
     *          mismo dia D, queda en espera (bloqueada) hasta que T1 libere
     *          el bloqueo (commit/rollback).
     *       c) T1 verifica el conteo de citas existentes contra el maximo
     *          permitido (max_appointments_per_day, default 5).
     *       d) Si el conteo es menor al maximo, T1 crea la cita y hace commit.
     *       e) T2 desbloquea, ejecuta su consulta (ahora ve la cita de T1),
     *          y repite la validacion. Si ya no hay espacio, retorna null.
     *
     *     Resultado: solo una de las dos transacciones puede crear la cita,
     *     y la otra recibira un mensaje indicando que el horario ya fue
     *     ocupado.
     *
     *   Validacion adicional de slots individuales:
     *     Despues de verificar el limite diario, se revisa si el asesor
     *     tiene horarios configurados (available_hours). Si solo tiene un
     *     horario configurado y ya hay una cita en ese exacto horario, se
     *     rechaza para evitar duplicidad de slot.
     *
     * @param  Request  $request  Peticion HTTP con campos: name, email, phone,
     *                            date, property_id, message (opcional)
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con success/message
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para agendar una cita.'
                ], 401);
            }

            $this->permissionService->setUser($user);

            if (!$this->permissionService->hasPermission('crear cita')) {
                $userRoles = $this->getUserRoles($user->id);

                Log::warning('Intento de crear cita sin permiso', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'roles' => $userRoles
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para agendar citas. Contacta al administrador.'
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'date' => 'required|date|after:now',
                'property_id' => 'required|exists:properties,id',
                'message' => 'nullable|string|max:500'
            ]);

            $property = Property::find($request->property_id);
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propiedad no encontrada.'
                ], 404);
            }

            $settings = AppointmentSetting::getForUser($property->user_id);

            if (!$settings || !$settings->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El asesor no está recibiendo citas en este momento.'
                ], 400);
            }

            $dateTime = new \DateTime($request->date);
            $time = $dateTime->format('H:i');
            $dateStr = $dateTime->format('Y-m-d');

            // Bloqueo atómico para prevenir race condition
            $rejectionReason = null;
            $appointment = DB::transaction(function () use ($settings, $dateStr, $time, $request, $user, $property, &$rejectionReason) {
                // Validar según la configuración real del asesor: día activo,
                // horas disponibles, excepciones, límite diario y hora no ocupada.
                $scheduleCheck = $settings->canSchedule($dateStr, $time);
                if (!$scheduleCheck['available']) {
                    $rejectionReason = $scheduleCheck['reason'];
                    return null;
                }

                // Bloquear las filas de citas del asesor para esa fecha y verificar
                // disponibilidad dentro de la transacción. En PostgreSQL, FOR UPDATE
                // no está permitido sobre funciones de agregación (count/exists), por
                // eso primero se traen las filas con el bloqueo exclusivo y luego se
                // calculan los totales en PHP.
                $existingAppointments = Appointment::where('asesor_id', $property->user_id)
                    ->whereDate('scheduled_date', $dateStr)
                    ->where('status', '!=', 'cancelled')
                    ->lockForUpdate()
                    ->get();

                $dayName = strtolower(date('l', strtotime($dateStr)));
                $maxPerDay = $settings->getMaxForDay($dayName);

                if ($existingAppointments->count() >= $maxPerDay) {
                    $rejectionReason = 'Límite de citas diarias alcanzado (máximo ' . $maxPerDay . ').';
                    return null;
                }

                $existingSlots = $existingAppointments
                    ->map(fn($appt) => \Carbon\Carbon::parse($appt->scheduled_date)->format('H:i'))
                    ->toArray();

                if (in_array($time, $existingSlots)) {
                    $rejectionReason = 'Este horario acaba de ser ocupado por otro usuario. Por favor, selecciona otro horario.';
                    return null;
                }

                return Appointment::create([
                    'user_id'       => $user->id,
                    'property_id'   => $property->id,
                    'asesor_id'     => $property->user_id,
                    'scheduled_date'=> $request->date,
                    'contact_name'  => $request->name,
                    'contact_email' => $request->email,
                    'contact_phone' => $request->phone,
                    'message'       => $request->message,
                    'status'        => 'pending',
                    'notes'         => null
                ]);
            });

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => $rejectionReason ?? 'Este horario no está disponible.'
                ], 400);
            }

            $this->logCreated(
                $appointment,
                ($user->full_name ?? $user->name) . ' solicitó una cita para "' . ($property->title ?? 'Propiedad #' . $property->id) . '" el ' . $request->date
            );

            // NOTIFICACIÓN AL ASESOR DE LA PROPIEDAD
            $asesor = User::find($property->user_id);
            if ($asesor) {
                $userName = $user->full_name ?? $user->name;
                $this->notifyUser(
                    $asesor,
                    'Nueva Cita Agendada',
                    "{$userName} agendó una cita para \"{$property->title}\" el {$request->date}.",
                    'info',
                    route('citas.index'),
                    ['appointment_id' => $appointment->id]
                );
            }

            // NOTIFICACIÓN AL CLIENTE (solo si tiene cuenta)
            if ($appointment->user_id && $appointment->user) {
                $this->notifyUser(
                    $appointment->user,
                    'Cita Agendada',
                    "Tu cita para \"{$property->title}\" el {$request->date} ha sido registrada. Pendiente de confirmación.",
                    'success',
                    route('citas.index'),
                    ['appointment_id' => $appointment->id]
                );
            }

            Log::info('Nueva cita creada', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'property_id' => $property->id,
                'date' => $request->date
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Cita agendada correctamente! Te enviaremos la confirmación a tu correo.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creando cita: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al agendar la cita. Por favor, intenta de nuevo más tarde.'
            ], 500);
        }
    }

    /**
     * Actualiza el estado de una cita existente.
     *
     * Permite cambiar el estado de una cita entre los valores validos:
     * pending, confirmed, completed, cancelled y reprogrammed.
     *
     * Flujo:
     *   1. Se verifica que el usuario este autenticado.
     *   2. Se configura el PermissionService y se obtienen los roles.
     *   3. Se verifica la autorizacion:
     *      - Super Admin y Administrador pueden actualizar cualquier cita.
     *      - Asesor Inmobiliario solo puede actualizar citas donde el es
     *        el asesor asignado (asesor_id coincide con su ID).
     *   4. Se valida que el nuevo estado sea uno de los valores permitidos.
     *   5. Se captura el estado anterior (oldStatus) y los valores completos
     *      anteriores (oldValues) para auditoria.
     *   6. Se actualiza el campo status y se persiste en la base de datos.
     *   7. Se registra la accion en la auditoria con una descripcion legible
     *      que incluye el estado anterior y el nuevo.
     *   8. Se envia notificacion al cliente (si tiene cuenta en el sistema)
     *      indicando el cambio de estado.
     *   9. Se envia notificacion al asesor (si es diferente del usuario que
     *      realiza la accion) indicando el cambio de estado.
     *  10. Se retorna respuesta JSON con la cita actualizada.
     *
     * Estados validos y su significado:
     *   - pending:       Cita recien creada, esperando confirmacion del asesor.
     *   - confirmed:     Asesor ha aceptado la cita.
     *   - completed:     Cita finalizada exitosamente.
     *   - cancelled:     Cita cancelada por el cliente o el asesor.
     *   - reprogrammed:  Cita fue reprogramada a una nueva fecha/hora.
     *
     * @param  Request     $request     Peticion HTTP con campo 'status'
     * @param  Appointment $appointment Instancia de la cita a actualizar (route model binding)
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con success/message/appointment
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            //  ACTUALIZAR EL PERMISSION SERVICE CON EL USUARIO ACTUAL
            $this->permissionService->setUser($user);

            $userRoles = $this->getUserRoles($user->id);

            $canUpdate = in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles) ||
                         (in_array('Asesor Inmobiliario', $userRoles) && $user->id === $appointment->asesor_id);

            if (!$canUpdate) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $request->validate([
                'status' => 'required|in:pending,confirmed,cancelled,completed,reprogrammed'
            ]);

            $oldStatus = $appointment->status;
            $oldValues = $appointment->toArray();
            $appointment->status = $request->status;
            $appointment->save();

            // AUDITORÍA
            $statusLabels = [
                'pending' => 'Pendiente',
                'confirmed' => 'Confirmada',
                'completed' => 'Completada',
                'cancelled' => 'Cancelada',
                'reprogrammed' => 'Reprogramada'
            ];

            $this->logAudit('updated', $appointment, $oldValues, $appointment->toArray(),
                ($user->full_name ?? $user->name) . ' cambió el estado de la cita de "' . ($statusLabels[$oldStatus] ?? $oldStatus) . '" a "' . ($statusLabels[$request->status] ?? $request->status) . '"'
            );

            // NOTIFICACIÓN AL CLIENTE
            if ($appointment->user) {
                try {
                    $appointment->user->createNotification(
                        'Estado de Cita Actualizado',
                        "Tu cita para {$appointment->property->title} cambió de " . ucfirst($oldStatus) . " a " . ucfirst($appointment->status),
                        'info',
                        route('citas.index')
                    );
                } catch (\Exception $e) {
                    Log::warning('Error enviando notificación: ' . $e->getMessage());
                }
            }

            // NOTIFICACIÓN AL ASESOR
            $asesor = User::find($appointment->asesor_id);
            if ($asesor && $asesor->id !== $user->id) {
                $clientName = $appointment->contact_name ?? ($appointment->user?->full_name ?? 'Cliente');
                $this->notifyUser(
                    $asesor,
                    'Estado de Cita Actualizado',
                    "La cita de {$clientName} para \"{$appointment->property->title}\" cambió de " . ucfirst($oldStatus) . " a " . ucfirst($appointment->status),
                    'info',
                    route('citas.index'),
                    ['appointment_id' => $appointment->id]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'appointment' => $appointment
            ]);

        } catch (\Exception $e) {
            Log::error('Error en updateStatus: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reprograma una cita existente a una nueva fecha/hora y/o actualiza sus notas.
     *
     * Este metodo permite modificar la fecha programada de una cita y/o
     * agregar/modificar notas internas. Al reprogramar, el estado de la
     * cita cambia automaticamente a "reprogrammed", indicando que el asesor
     * debe revisar y confirmar la nueva fecha.
     *
     * Flujo:
     *   1. Se verifica que el usuario este autenticado.
     *   2. Se configura el PermissionService y se obtienen los roles.
     *   3. Se verifica la autorizacion (mismas reglas que updateStatus):
     *      - Super Admin y Administrador pueden reprogramar cualquier cita.
     *      - Asesor Inmobiliario solo puede reprogramar sus propias citas.
     *   4. Si se proporciona una nueva scheduled_date:
     *      a. Se valida que sea una fecha futura.
     *      b. Se cargan las configuraciones de citas del asesor.
     *      c. Se extrae la hora y la fecha de la nueva fecha programada.
     *      d. Se llama a AppointmentSetting::canSchedule() para verificar
     *         que la nueva fecha/hora este disponible segun las reglas del
     *         asesor (horarios configurados, limite diario, etc.).
     *      e. Si no esta disponible, se retorna error con la razon.
     *      f. Se actualiza scheduled_date y se cambia estado a "reprogrammed".
     *   5. Si se proporcionan notas, se actualiza el campo notes.
     *   6. Si no se realizaron cambios, se retorna error 400.
     *   7. Se persiste la cita actualizada.
     *   8. Se registra en auditoria con descripcion de la reprogramacion.
     *   9. Se notifica al cliente si la fecha fue cambiada.
     *  10. Se notifica al asesor si la fecha fue cambiada y el asesor
     *       es diferente del usuario que realiza la accion.
     *  11. Se retorna respuesta JSON con la cita actualizada y sus relaciones.
     *
     * Diferencia clave con updateStatus:
     *   - updateStatus solo cambia el campo status.
     *   - reschedule puede cambiar scheduled_date, status y notes.
     *   - reschedule valida disponibilidad del nuevo horario antes de aplicar.
     *   - El estado se fuerza a "reprogrammed" cuando se cambia la fecha.
     *
     * @param  Request     $request     Peticion HTTP con campos opcionales:
     *                                  scheduled_date (date|after:now) y notes (string)
     * @param  Appointment $appointment Instancia de la cita a reprogramar (route model binding)
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con success/message/appointment
     */
    public function reschedule(Request $request, Appointment $appointment)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            // ACTUALIZAR EL PERMISSION SERVICE CON EL USUARIO ACTUAL
            $this->permissionService->setUser($user);

            $userRoles = $this->getUserRoles($user->id);

            $canReschedule = in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles) ||
                             (in_array('Asesor Inmobiliario', $userRoles) && $user->id === $appointment->asesor_id);

            if (!$canReschedule) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            $changes = false;
            $oldValues = $appointment->toArray();

            if ($request->has('scheduled_date') && $request->filled('scheduled_date')) {
                $request->validate([
                    'scheduled_date' => 'required|date|after:now'
                ]);

                $settings = AppointmentSetting::getForUser($appointment->asesor_id);
                $dateTime = new \DateTime($request->scheduled_date);
                $time = $dateTime->format('H:i');
                $dateStr = $dateTime->format('Y-m-d');

                $canSchedule = $settings->canSchedule($dateStr, $time);
                if (!$canSchedule['available']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La nueva fecha no está disponible: ' . $canSchedule['reason']
                    ], 400);
                }

                $appointment->scheduled_date = $request->scheduled_date;
                $appointment->status = 'reprogrammed';
                $changes = true;
            }

            if ($request->has('notes') && $request->filled('notes')) {
                $appointment->notes = $request->notes;
                $changes = true;
            }

            if (!$changes) {
                return response()->json(['success' => false, 'message' => 'No se realizaron cambios'], 400);
            }

            $appointment->save();

            // AUDITORÍA
            $this->logAudit('updated', $appointment, $oldValues, $appointment->toArray(),
                ($user->full_name ?? $user->name) . ' reprogramó la cita para el ' . ($appointment->scheduled_date ? $appointment->scheduled_date->format('d/m/Y H:i') : '')
            );

            // NOTIFICACIÓN AL CLIENTE
            if ($appointment->user && $request->has('scheduled_date')) {
                try {
                    $appointment->user->createNotification(
                        'Cita Reprogramada',
                        "Tu visita ha sido reprogramada para el {$appointment->scheduled_date->format('d/m/Y H:i')}.",
                        'warning',
                        route('citas.index')
                    );
                } catch (\Exception $e) {
                    Log::warning('Error enviando notificación: ' . $e->getMessage());
                }
            }

            // NOTIFICACIÓN AL ASESOR
            $asesor = User::find($appointment->asesor_id);
            if ($asesor && $asesor->id !== $user->id && $request->has('scheduled_date')) {
                $clientName = $appointment->contact_name ?? ($appointment->user?->full_name ?? 'Cliente');
                $this->notifyUser(
                    $asesor,
                    'Cita Reprogramada',
                    "La cita de {$clientName} ha sido reprogramada para el {$appointment->scheduled_date->format('d/m/Y H:i')}.",
                    'warning',
                    route('citas.index'),
                    ['appointment_id' => $appointment->id]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $request->has('scheduled_date') ? 'Cita reprogramada correctamente' : 'Notas actualizadas correctamente',
                'appointment' => $appointment->load(['property', 'asesor'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en reschedule: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al reprogramar: ' . $e->getMessage()], 500);
        }
    }
}
