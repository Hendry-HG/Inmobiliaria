<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Appointment;
use App\Events\NewMessage;
use App\Events\UserTyping;
use App\Events\MessageEdited;
use App\Events\MessageDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use App\Traits\SendsNotifications;

class ChatController extends Controller
{
    use SendsNotifications;

    private const MAX_MESSAGE_LENGTH = 1000;
    private const TYPING_THROTTLE = 3;
    private const TYPING_CACHE_KEY_PREFIX = 'typing_';
    private const MAX_MESSAGES_PER_MINUTE = 30;

    /**
     * Matriz de comunicación interna.
     *
     * Define los roles con los que cada rol puede INICIAR una conversación.
     * Los roles que no aparecen como objetivo (o cuyo objetivo no se lista)
     * solo pueden responder una vez que el otro usuario les escribe primero.
     *
     * - Super Admin: inicia con Asesores, Administradores y Auditores.
     * - Administrador: inicia con Super Admin y Asesores.
     * - Auditor: inicia con Super Admin.
     * - Asesor Inmobiliario: solo responde a Super Admin/Administrador/Auditor
     *   (no los inicia); conserva el flujo con clientes vía citas.
     * - Cliente: inicia con Asesores (flujo público).
     */
    private const COMMUNICATION_MATRIX = [
        'Super Admin' => ['Asesor Inmobiliario', 'Administrador', 'Auditor'],
        'Administrador' => ['Super Admin', 'Asesor Inmobiliario'],
        'Auditor' => ['Super Admin'],
        'Asesor Inmobiliario' => [],
        'Cliente' => ['Asesor Inmobiliario'],
    ];

    /**
     * Constructor del controlador de chat en tiempo real.
     *
     * Configura los middleware para todas las rutas del controlador:
     * - Todas las rutas requieren autenticacion.
     * - Throttling estricto (60/min) para sendMessage, startConversation y
     *   startConversationWithCliente para prevenir spam de mensajes.
     * - Throttling mas amplio (100/min) para consultas de lectura
     *   (getMessages, getConversations).
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:60,1')->only(['sendMessage', 'startConversation', 'startConversationWithCliente', 'startConversationInternal']);
        $this->middleware('throttle:100,1')->only(['getMessages', 'getConversations']);
    }

    // ==========================================
    // HELPERS PRIVADOS
    // ==========================================

    /**
     * Obtiene los roles de un usuario con cache de 5 minutos.
     *
     * Consulta la tabla model_has_roles uniendo con roles para obtener
     * los nombres de los roles asignados a un usuario. Los resultados se
     * cachean durante 300 segundos para reducir consultas repetitivas en
     * el contexto de sesiones de chat donde se verifican roles con
     * frecuencia.
     *
     * @param int $userId Identificador del usuario cuyos roles se desean obtener.
     * @return array Arreglo de strings con los nombres de los roles asignados.
     */
    private function getUserRoles(int $userId): array
    {
        return Cache::remember("user_roles_{$userId}", 300, function () use ($userId) {
            return DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $userId)
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->pluck('roles.name')
                ->toArray();
        });
    }

    /**
     * Limpia los caches de roles y permisos de un usuario.
     *
     * Invalida las entradas de cache de roles y permisos del usuario
     * identificado. Se invoca cuando cambia el estado de presencia del
     * usuario para garantizar que los datos en cache se reflejen con
     * exactitud en operaciones posteriores.
     *
     * @param int $userId Identificador del usuario cuyo cache se va a limpiar.
     * @return void
     */
    private function clearUserCache(int $userId): void
    {
        Cache::forget("user_roles_{$userId}");
        Cache::forget("user_permissions_{$userId}");
    }

    /**
     * Determina el ID del usuario receptor en una conversacion.
     *
     * Dado un usuario emisor, retorna el ID del otro participante de la
     * conversacion. Si el usuario autenticado es el cliente, retorna el ID
     * del asesor, y viceversa. Util para dirigir broadcasts y notificaciones
     * al destinatario correcto.
     *
     * @param Conversation $conversation Modelo de la conversacion activa.
     * @param int $userId Identificador del usuario emisor.
     * @return int Identificador del usuario receptor.
     */
    private function getReceiverId(Conversation $conversation, int $userId): int
    {
        return $userId === $conversation->client_id
            ? $conversation->asesor_id
            : $conversation->client_id;
    }

    /**
     * Verifica si un usuario tiene acceso a una conversacion.
     *
     * Control de acceso que valida que el usuario sea participante de la
     * conversacion (ya sea cliente o asesor). Retorna true solo si el ID
     * del usuario coincide con client_id o asores_id de la conversacion.
     * Se aplica en todas las operaciones de lectura, escritura y eliminacion
     * de mensajes para garantizar que solo los participantes accedan al
     * contenido.
     *
     * @param int $userId Identificador del usuario que solicita acceso.
     * @param Conversation $conversation Modelo de la conversacion a verificar.
     * @return bool true si el usuario es participante, false de lo contrario.
     */
    private function canUserAccessConversation(int $userId, Conversation $conversation): bool
    {
        return in_array($userId, [$conversation->client_id, $conversation->asesor_id]);
    }

    /**
     * Formatea un modelo Message a un arreglo asociativo para la API/JSON.
     *
     * Transforma el modelo Eloquent de Message en una estructura plana
     * adecuada para enviar como respuesta JSON al frontend. Incluye el
     * contenido, datos del usuario remitente (nombre, avatar), hora
     * formateada (HH:MM), estado de lectura y timestamp ISO 8601.
     *
     * @param Message $message Modelo de mensaje a formatear.
     * @return array Arreglo asociativo con los campos: id, content, user_id,
     *         user_name, user_avatar, formatted_time, is_read, created_at.
     */
    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'content' => $message->content,
            'user_id' => $message->user_id,
            'user_name' => $message->user->full_name ?? $message->user->name ?? 'Usuario',
            'user_avatar' => $message->user->profile_photo_url ?? null,
            'formatted_time' => $message->created_at->format('H:i'),
            'is_read' => $message->is_read,
            'created_at' => $message->created_at->toISOString(),
        ];
    }

    /**
     * Formatea un modelo Conversation con datos del otro participante.
     *
     * Transforma el modelo de conversacion en una estructura para el panel
     * lateral de chat. Determina automaticamente cual es el "otro usuario"
     * segun el rol del usuario autenticado (si es cliente, muestra el asesor;
     * si es asesor, muestra el cliente). Incluye informacion de presencia,
     * cantidad de mensajes no leidos, ultimo mensaje y tiempo relativo.
     *
     * El conteo de mensajes no leidos se realiza con una consulta adicional
     * para garantizar precision en tiempo real.
     *
     * @param Conversation $conversation Modelo de la conversacion.
     * @param User $user Usuario autenticado que consulta la conversacion.
     * @param bool $isCliente true si el usuario autenticado tiene rol de Cliente.
     * @return array Arreglo con los datos formateados de la conversacion.
     */
    private function formatConversation(Conversation $conversation, User $user, bool $isCliente): array
    {
        $otherUser = $user->id === $conversation->client_id
            ? $conversation->asesor
            : $conversation->client;

        $otherRole = $otherUser->roles->first()->name ?? '';

        return [
            'id' => $conversation->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->full_name ?? $otherUser->name,
                'avatar' => $otherUser->profile_photo_url,
                'is_online' => $otherUser->is_online ?? false,
                'email' => $otherUser->email,
                'specialization' => $otherUser->specialization,
                'role' => $otherRole,
                'last_seen' => $otherUser->last_seen_at?->diffForHumans(),
            ],
            'unread_count' => Message::where('conversation_id', $conversation->id)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->count(),
            'last_message' => $conversation->lastMessage?->content,
            'last_message_time' => $conversation->lastMessage?->created_at?->diffForHumans(),
        ];
    }

    // ==========================================
    // VISTA PRINCIPAL
    // ==========================================

    /**
     * Muestra la vista principal del modulo de chat.
     *
     * Punto de entrada al sistema de mensajeria. Carga todas las conversaciones
     * activas del usuario autenticado y las contactos disponibles para iniciar
     * nuevas conversaciones. El comportamiento varia segun el rol:
     *
     * - Cliente: ve sus conversaciones existentes y una lista de asesores
     *   inmobiliarios activos disponibles para iniciar chat.
     * - Asesor Inmobiliario: ve sus conversaciones y una lista de clientes
     *   que tienen citas agendadas con el, habilitando el chat.
     *
     * Las conversaciones se ordenan por el timestamp del ultimo mensaje
     * (de mas reciente a mas antiguo) y se formatean incluyendo el
     * conteo de mensajes no leidos.
     *
     * Flujo:
     * 1. Obtiene el usuario autenticado y sus roles.
     * 2. Carga conversaciones con relaciones (client, asesor, lastMessage).
     * 3. Formatea cada conversacion con datos del otro participante.
     * 4. Carga contactos disponibles segun el rol del usuario.
     * 5. Retorna la vista del chat con todos los datos necesarios.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $roles = $this->getUserRoles($user->id);

        $isAsesor = in_array('Asesor Inmobiliario', $roles);
        $isCliente = in_array('Cliente', $roles);
        $isStaff = array_intersect($roles, ['Super Admin', 'Administrador', 'Auditor']) !== [];

        $conversations = Conversation::query()
            ->with(['client', 'asesor', 'lastMessage'])
            ->where(function ($q) use ($user) {
                $q->where('client_id', $user->id)
                    ->orWhere('asesor_id', $user->id);
            })
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(fn($conv) => $this->formatConversation($conv, $user, $isCliente));

        $contactos = $this->getAvailableContactos($user, $isCliente, $isAsesor);

        //  Pasar contactos con el nombre correcto para la vista
        $asesoresDisponibles = $isCliente ? $contactos : collect();
        $clientesDisponibles = $isAsesor ? $contactos : collect();
        $usuariosDisponibles = $isStaff ? $this->getStaffContacts($user) : collect();

        return view('modulos.chat.index', compact(
            'conversations',
            'asesoresDisponibles',
            'clientesDisponibles',
            'usuariosDisponibles',
            'isAsesor',
            'isCliente',
            'isStaff'
        ));
    }

    /**
     * Obtiene la lista de contactos disponibles segun el rol del usuario.
     *
     * Metodo de despacho que delega a la funcion correspondiente segun el
     * rol: para clientes retorna asesores disponibles, para asesores retorna
     * clientes con citas. Si el usuario no tiene ninguno de estos roles,
     * retorna una coleccion vacia.
     *
     * @param User $user Usuario autenticado.
     * @param bool $isCliente true si el usuario tiene rol de Cliente.
     * @param bool $isAsesor true si el usuario tiene rol de Asesor Inmobiliario.
     * @return \Illuminate\Support\Collection Coleccion de contactos disponibles
     *         formateados como objetos planos.
     */
    private function getAvailableContactos(User $user, bool $isCliente, bool $isAsesor): \Illuminate\Support\Collection
    {
        if ($isCliente) {
            return $this->getAvailableAsesoresForClient($user);
        }

        if ($isAsesor) {
            return $this->getAvailableClientesForAsesor($user);
        }

        return collect();
    }

    /**
     * Obtiene los asesores inmobiliarios disponibles para un cliente.
     *
     * Consulta directamente la tabla model_has_roles para encontrar todos
     * los usuarios con el rol "Asesor Inmobiliario", filtra solo los activos
     * y formatea cada uno con datos basicos (id, nombre, email, avatar,
     * especializacion). Incluye un indicador has_conversation que indica
     * si ya existe una conversacion activa entre el cliente y cada asesor,
     * permitiendo al frontend diferenciar entre crear una nueva conversacion
     * o reabrir una existente.
     *
     * @param User $user Cliente autenticado.
     * @return \Illuminate\Support\Collection Coleccion de objetos con los datos
     *         de cada asesor disponible.
     */
    private function getAvailableAsesoresForClient(User $user): \Illuminate\Support\Collection
    {
        $asesorIds = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Asesor Inmobiliario')
            ->pluck('model_has_roles.model_id')
            ->toArray();

        if (empty($asesorIds)) {
            return collect();
        }

        return User::whereIn('id', $asesorIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($asesor) => (object) [
                'id' => $asesor->id,
                'name' => $asesor->full_name ?? $asesor->name,
                'email' => $asesor->email,
                'avatar' => $asesor->profile_photo_url,
                'specialization' => $asesor->specialization ?? 'Asesor inmobiliario',
                'has_conversation' => Conversation::where('client_id', $user->id)
                    ->where('asesor_id', $asesor->id)
                    ->exists(),
            ]);
    }

    /**
     * Devuelve los asesores disponibles para el cliente autenticado en formato JSON.
     *
     * Ruta: GET /chat/asesores/disponibles
     * Permiso requerido: "chat access" (aplicado por la ruta).
     *
     * @return \Illuminate\Http\JsonResponse Lista de asesores disponibles o error 403 si no es cliente.
     */
    public function getAvailableAsesores()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);
        }

        $roles = $this->getUserRoles($user->id);

        if (!in_array('Cliente', $roles)) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->getAvailableAsesoresForClient($user),
        ]);
    }

    /**
     * Obtiene los clientes disponibles para un asesor inmobiliario.
     *
     * La lista de clientes se deriva de las citas (Appointment) agendadas
     * con el asesor. Solo los usuarios que tienen una cita con el asesor
     * aparecen como contactos disponibles para iniciar una conversacion.
     * Filtra clientes activos y los formatea con datos basicos.
     *
     * Este enfoque garantiza que el asesor solo pueda iniciar chats con
     * clientes que han tenido interaccion real a traves del sistema de
     * citas, manteniendo la integridad del flujo de negocio.
     *
     * @param User $user Asesor inmobiliario autenticado.
     * @return \Illuminate\Support\Collection Coleccion de objetos con los datos
     *         de cada cliente disponible.
     */
    private function getAvailableClientesForAsesor(User $user): \Illuminate\Support\Collection
    {
        $clienteIds = Appointment::where('asesor_id', $user->id)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if (empty($clienteIds)) {
            return collect();
        }

        return User::whereIn('id', $clienteIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($cliente) => (object) [
                'id' => $cliente->id,
                'name' => $cliente->full_name ?? $cliente->name,
                'email' => $cliente->email,
                'avatar' => $cliente->profile_photo_url,
            ]);
    }

    /**
     * Obtiene los contactos internos disponibles para el usuario staff.
     *
     * Retorna los usuarios con los que el usuario autenticado puede iniciar
     * una conversacion segun la matriz de comunicacion interna. Super Admin
     * puede contactar asesores, administradores y auditores; Administrador
     * puede contactar super admins y asesores; Auditor solo super admins.
     *
     * @param User $user Usuario staff autenticado.
     * @return \Illuminate\Support\Collection Coleccion de contactos con role.
     */
    private function getStaffContacts(User $user): \Illuminate\Support\Collection
    {
        $userRoles = $this->getUserRoles($user->id);

        $targetRoles = [];
        foreach ($userRoles as $role) {
            foreach (self::COMMUNICATION_MATRIX[$role] ?? [] as $targetRole) {
                $targetRoles[] = $targetRole;
            }
        }
        $targetRoles = array_values(array_unique($targetRoles));

        if (empty($targetRoles)) {
            return collect();
        }

        $targetIds = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', $targetRoles)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('model_has_roles.model_id', '!=', $user->id)
            ->pluck('model_has_roles.model_id')
            ->unique()
            ->toArray();

        if (empty($targetIds)) {
            return collect();
        }

        return User::whereIn('id', $targetIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($target) => (object) [
                'id' => $target->id,
                'name' => $target->full_name ?? $target->name,
                'email' => $target->email,
                'avatar' => $target->profile_photo_url,
                'specialization' => $target->specialization,
                'role' => $target->roles->first()->name ?? '',
            ]);
    }

    /**
     * Emite un evento de broadcast de forma segura (best-effort).
     *
     * Envuelve la transmision en tiempo real en un try/catch para que un
     * fallo del servidor de WebSockets (p. ej. Reverb/soketi apagado o
     * inalcanzable) NO rompa la operacion principal en base de datos.
     * La persistencia de los mensajes, ediciones y eliminaciones se
     * garantiza aunque el canal en tiempo real este temporalmente fuera
     * de servicio; la sincronizacion posterior via polling (fetchConversations)
     * y la reconexion de Echo mantienen la interfaz consistente.
     *
     * @param mixed $event Evento de broadcast a emitir.
     * @return void
     */
    private function safeBroadcast($event): void
    {
        try {
            broadcast($event)->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Broadcast no enviado (servidor de tiempo real no disponible): ' . $e->getMessage());
        }
    }

    /**
     * Verifica si el usuario puede iniciar una conversacion con otro usuario.
     *
     * Consulta la matriz de comunicacion interna (COMMUNICATION_MATRIX)
     * comparando los roles del iniciador con los roles del objetivo. Si el
     * rol del objetivo aparece en la lista de roles permitidos para el rol
     * del iniciador, retorna true. Los asesores no pueden iniciar con staff
     * (solo responden); solo los clientes pueden iniciar con asesores y los
     * roles staff con los roles definidos en la matriz.
     *
     * @param User $initiator Usuario que intenta iniciar la conversacion.
     * @param User $target Usuario al que se desea contactar.
     * @return bool true si puede iniciar, false en caso contrario.
     */
    private function canUserStartConversation(User $initiator, User $target): bool
    {
        $initiatorRoles = $this->getUserRoles($initiator->id);
        $targetRoles = $this->getUserRoles($target->id);

        foreach ($initiatorRoles as $initiatorRole) {
            foreach (self::COMMUNICATION_MATRIX[$initiatorRole] ?? [] as $allowedRole) {
                if (in_array($allowedRole, $targetRoles)) {
                    return true;
                }
            }
        }

        return false;
    }

    // ==========================================
    // API: CONVERSACIONES
    // ==========================================

    /**
     * API JSON: retorna todas las conversaciones del usuario autenticado.
     *
     * Endpoint AJAX utilizado para recargar la lista de conversaciones del
     * panel lateral sin recargar la pagina completa. Determina el rol del
     * usuario para filtrar conversaciones por el campo correcto (client_id
     * o asores_id). Carga las relaciones de client, asesor y lastMessage,
     * y formatea cada conversacion con el conteo de no leidos y datos del
     * otro participante.
     *
     * Las conversaciones se ordenan por last_message_at descendente para
     * que las mas recientes aparezcan primero.
     *
     * @return \Illuminate\Http\JsonResponse JSON con success=true y el
     *         arreglo de conversaciones formateadas, o error 500 en caso
     *         de excepcion.
     */
    public function getConversations()
    {
        try {
            $user = Auth::user();
            $roles = $this->getUserRoles($user->id);
            $isCliente = in_array('Cliente', $roles);

            $conversations = Conversation::query()
                ->with(['client', 'asesor', 'lastMessage'])
                ->where(function ($q) use ($user) {
                    $q->where('client_id', $user->id)
                        ->orWhere('asesor_id', $user->id);
                })
                ->orderBy('last_message_at', 'desc')
                ->get()
                ->map(fn($conv) => $this->formatConversation($conv, $user, $isCliente));

            return response()->json([
                'success' => true,
                'conversations' => $conversations
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getConversations', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al obtener conversaciones'], 500);
        }
    }

    // ==========================================
    // API: MENSAJES
    // ==========================================

    /**
     * API JSON: retorna todos los mensajes de una conversacion especifica.
     *
     * Endpoint AJAX que carga el historial completo de mensajes de una
     * conversacion, ordenados cronologicamente (ascendente). Incluye
     * logica de marcado automatico de mensajes como leidos: todos los
     * mensajes no leidos del otro participante se marcan como leidos
     * con timestamp de lectura al momento de abrir la conversacion.
     *
     * Flujo:
     * 1. Valida que la conversacion exista.
     * 2. Verifica que el usuario autenticado sea participante.
     * 3. Marca como leidos los mensajes no leidos del otro usuario.
     * 4. Obtiene todos los mensajes ordenados por fecha de creacion.
     * 5. Formatea cada mensaje con datos del remitente.
     * 6. Retorna los mensajes junto con datos del otro participante
     *    (nombre, avatar, especializacion, estado de presencia, email).
     *
     * @param int $conversationId Identificador de la conversacion.
     * @return \Illuminate\Http\JsonResponse JSON con success=true, messages
     *         y conversation, o error 403/500.
     */
    public function getMessages($conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::with(['client', 'asesor'])->findOrFail($conversationId);

            if (!$this->canUserAccessConversation($user->id, $conversation)) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }

            Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            $messages = $conversation->messages()
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($msg) => $this->formatMessage($msg));

            $roles = $this->getUserRoles($user->id);
            $isCliente = in_array('Cliente', $roles);
            $otherUser = $user->id === $conversation->client_id ? $conversation->asesor : $conversation->client;
            $otherRole = $otherUser->roles->first()->name ?? '';

            return response()->json([
                'success' => true,
                'messages' => $messages,
                'conversation' => [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->full_name ?? $otherUser->name,
                        'avatar' => $otherUser->profile_photo_url,
                        'specialization' => $otherUser->specialization,
                        'role' => $otherRole,
                        'is_online' => $otherUser->is_online ?? false,
                        'email' => $otherUser->email,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getMessages', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al obtener mensajes'], 500);
        }
    }

    // ==========================================
    // API: INICIAR CONVERSACIÓN
    // ==========================================

    /**
     * API JSON: inicia o reabre una conversacion cliente-asesor.
     *
     * Endpoint utilizado por los clientes para iniciar una conversacion con
     * un asesor inmobiliario. Delega a startConversationBase con el campo
     * asores_id y validacion de rol "Asesor Inmobiliario" en el usuario
     * objetivo. Si ya existe una conversacion entre ambos, retorna el ID
     * de la existente (firstOrCreate).
     *
     * @param Request $request Solicitud HTTP con el campo asores_id (required).
     * @return \Illuminate\Http\JsonResponse JSON con success=true y
     *         conversation_id, o error de validacion/autorizacion.
     */
    public function startConversation(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'asesor_id' => ['required', 'exists:users,id', Rule::notIn([$user->id])]
        ]);

        $target = User::findOrFail($validated['asesor_id']);

        if (!$this->canUserStartConversation($user, $target)) {
            return response()->json([
                'success' => false,
                'error' => 'No puedes iniciar una conversación con este usuario'
            ], 403);
        }

        return $this->startConversationBase($request, 'asesor_id', 'Asesor Inmobiliario');
    }

    /**
     * API JSON: inicia una conversacion desde el asor hacia un cliente.
     *
     * Endpoint utilizado por los asesores inmobiliarios para iniciar una
     * conversacion con un cliente. Verifica que el usuario autenticado tenga
     * el rol "Asesor Inmobiliario" antes de delegar a startConversationBase.
     * Utiliza el campo cliente_id en lugar de asores_id ya que el asesor
     * es quien inicia el contacto.
     *
     * @param Request $request Solicitud HTTP con el campo cliente_id (required).
     * @return \Illuminate\Http\JsonResponse JSON con success=true y
     *         conversation_id, o error de validacion/autorizacion.
     */
    public function startConversationWithCliente(Request $request)
    {
        $user = Auth::user();
        $roles = $this->getUserRoles($user->id);

        if (!in_array('Asesor Inmobiliario', $roles)) {
            return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
        }

        return $this->startConversationBase($request, 'cliente_id', null);
    }

    /**
     * API JSON: inicia o reabre una conversacion entre usuarios internos.
     *
     * Endpoint utilizado por el personal (Super Admin, Administrador, Auditor)
     * para iniciar conversaciones con otros usuarios internos segun la matriz
     * de comunicacion (COMMUNICATION_MATRIX). Valida que el usuario autenticado
     * pueda iniciar con el usuario objetivo; si ya existe una conversacion entre
     * ambos (en cualquier direccion), retorna la existente para evitar duplicados.
     *
     * @param Request $request Solicitud HTTP con el campo user_id (required).
     * @return \Illuminate\Http\JsonResponse JSON con success=true y
     *         conversation_id, o error de validacion/autorizacion.
     */
    public function startConversationInternal(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'user_id' => ['required', 'exists:users,id', Rule::notIn([$user->id])]
            ]);

            $target = User::findOrFail($validated['user_id']);

            if (!$this->canUserStartConversation($user, $target)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No puedes iniciar una conversación con este usuario'
                ], 403);
            }

            $conversation = Conversation::where(function ($q) use ($user, $target) {
                $q->where('client_id', $user->id)->where('asesor_id', $target->id);
            })->orWhere(function ($q) use ($user, $target) {
                $q->where('client_id', $target->id)->where('asesor_id', $user->id);
            })->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'client_id' => $user->id,
                    'asesor_id' => $target->id,
                    'subject' => 'Chat con ' . ($target->full_name ?? $target->name),
                    'last_message_at' => now(),
                    'is_active' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error al iniciar conversación interna', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al iniciar conversación'], 500);
        }
    }

    /**
     * Logica base para crear o recuperar una conversacion existente.
     *
     * Metodo privado que encapsula la creacion de conversaciones entre dos
     * usuarios. Utiliza firstOrCreate para evitar duplicados: si ya existe
     * una conversacion entre el usuario autenticado y el usuario objetivo,
     * retorna la existente. La determinacion de quien es cliente y quien
     * es asesor se realiza segun el contexto (si se pasa rol de validacion
     * "Asesor Inmobiliario", el otro usuario es el asesor; de lo contrario
     * es el cliente).
     *
     * Flujo:
     * 1. Valida que el ID del otro usuario exista en la tabla users.
     * 2. Valida que el usuario no intente crearse una conversacion consigo mismo.
     * 3. Si se especifica roleCheck, verifica que el otro usuario tenga ese rol.
     * 4. Determina el orden correcto de client_id/asesores_id segun contexto.
     * 5. Busca o crea la conversacion con subject generado y timestamps.
     * 6. Retorna el ID de la conversacion (nueva o existente).
     *
     * @param Request $request Solicitud HTTP con el ID del otro usuario.
     * @param string $idField Nombre del campo que contiene el ID del otro
     *        usuario (asesor_id o cliente_id).
     * @param ?string $roleCheck Nombre del rol que debe tener el otro usuario,
     *        o null para omitir la validacion de rol.
     * @return \Illuminate\Http\JsonResponse JSON con success=true y
     *         conversation_id, o error de validacion.
     */
    private function startConversationBase(Request $request, string $idField, ?string $roleCheck)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                $idField => ['required', 'exists:users,id', Rule::notIn([$user->id])]
            ]);

            $otherUser = User::findOrFail($validated[$idField]);

            if ($roleCheck && !in_array($roleCheck, $this->getUserRoles($otherUser->id))) {
                return response()->json(['success' => false, 'error' => 'Usuario no válido para esta acción'], 400);
            }

            $isCliente = $roleCheck === 'Asesor Inmobiliario';
            $conversation = Conversation::firstOrCreate(
                [
                    'client_id' => $isCliente ? $user->id : $otherUser->id,
                    'asesor_id' => $isCliente ? $otherUser->id : $user->id,
                ],
                [
                    'subject' => 'Chat con ' . ($otherUser->full_name ?? $otherUser->name),
                    'last_message_at' => now(),
                    'is_active' => true
                ]
            );

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error al iniciar conversación', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al iniciar conversación'], 500);
        }
    }

    // ==========================================
    // API: ENVIAR MENSAJE
    // ==========================================

    /**
     * API JSON: envia un mensaje de texto en una conversacion.
     *
     * Endpoint principal del sistema de mensajeria en tiempo real. Recibe
     * el contenido del mensaje, lo sanitiza, lo almacena en base de datos
     * y emite un evento de broadcast para que el destinatario lo reciba
     * instantaneamente via WebSocket. Tambien envia una notificacion push
     * al receptor.
     *
     * Flujo:
     * 1. Valida que la conversacion exista y el usuario sea participante.
     * 2. Aplica rate limiting por usuario (max 30 mensajes/minuto).
     * 3. Valida y sanitiza el contenido (strip_tags, htmlspecialchars) para
     *    prevenir inyeccion de scripts (XSS).
     * 4. Valida que el contenido no este vacio despues de la sanitizacion.
     * 5. Crea el registro del mensaje en base de datos.
     * 6. Actualiza last_message_at de la conversacion para el ordenamiento.
     * 7. Carga la relacion del usuario para el formato.
     * 8. Emite evento NewMessage via broadcast a traves del canal privado
     *    del receptor (toOthers para no duplicar al emisor).
     * 9. Envia notificacion in-app al receptor con preview del mensaje.
     * 10. Retorna el mensaje formateado como JSON.
     *
     * @param Request $request Solicitud HTTP con el campo content (required,
     *        max 1000 caracteres).
     * @param int $conversationId Identificador de la conversacion destino.
     * @return \Illuminate\Http\JsonResponse JSON con success=true y el
     *         mensaje formateado, o error de validacion/autorizacion.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if (!$this->canUserAccessConversation($user->id, $conversation)) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }

            $key = "send_message_{$user->id}";
            if (RateLimiter::tooManyAttempts($key, self::MAX_MESSAGES_PER_MINUTE)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Demasiados mensajes. Espera un momento.'
                ], 429);
            }
            RateLimiter::hit($key, 60);

            $validated = $request->validate([
                'content' => ['required', 'string', 'min:1', 'max:' . self::MAX_MESSAGE_LENGTH]
            ]);

            $content = htmlspecialchars(strip_tags($validated['content']), ENT_QUOTES, 'UTF-8');

            if (empty(trim($content))) {
                return response()->json(['success' => false, 'error' => 'El mensaje no puede estar vacío'], 422);
            }

            $message = Message::create([
                'conversation_id' => $conversationId,
                'user_id' => $user->id,
                'content' => $content,
            ]);

            $conversation->update(['last_message_at' => now()]);
            $message->load('user');

            $receiverId = $this->getReceiverId($conversation, $user->id);
            $this->safeBroadcast(new NewMessage($message, $conversationId, $receiverId));

            // NOTIFICACIÓN AL RECEPTOR
            $receiver = User::find($receiverId);
            if ($receiver) {
                $preview = Str::limit(strip_tags($content), 80);
                $senderName = $user->full_name ?? $user->name;
                $this->notifyUser(
                    $receiver,
                    'Nuevo Mensaje',
                    "{$senderName}: \"{$preview}\"",
                    'info',
                    route('chat.index'),
                    ['conversation_id' => $conversationId]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $this->formatMessage($message)
            ]);
        } catch (\Exception $e) {
            Log::error('Error en sendMessage', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al enviar mensaje'], 500);
        }
    }

    // ==========================================
    // API: EDITAR MENSAJE
    // ==========================================

    /**
     * API JSON: edita el contenido de un mensaje existente.
     *
     * Permite al autor de un mensaje modificar su contenido. Solo el usuario
     * que envio el mensaje puede editarlo. El contenido se sanitiza de la
     * misma manera que en sendMessage. Despues de actualizar, emite un
     * evento MessageEdited via broadcast para que el otro participante
     * vea el cambio en tiempo real.
     *
     * Flujo:
     * 1. Busca el mensaje y carga su conversacion.
     * 2. Verifica que el usuario autenticado sea el autor del mensaje.
     * 3. Valida y sanitiza el nuevo contenido.
     * 4. Actualiza el contenido del mensaje en la base de datos.
     * 5. Emite evento MessageEdited via broadcast al otro participante.
     *
     * @param Request $request Solicitud HTTP con el campo content (required,
     *        max 1000 caracteres).
     * @param int $messageId Identificador del mensaje a editar.
     * @return \Illuminate\Http\JsonResponse JSON con success=true, o error
     *         de autorizacion/validacion.
     */
    public function editMessage(Request $request, $messageId)
    {
        try {
            $user = Auth::user();
            $message = Message::with('conversation')->findOrFail($messageId);

            if ($message->user_id !== $user->id) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }

            $validated = $request->validate([
                'content' => ['required', 'string', 'min:1', 'max:' . self::MAX_MESSAGE_LENGTH]
            ]);

            $content = htmlspecialchars(strip_tags($validated['content']), ENT_QUOTES, 'UTF-8');

            if (empty(trim($content))) {
                return response()->json(['success' => false, 'error' => 'El mensaje no puede estar vacío'], 422);
            }

            $message->update(['content' => $content]);

            $receiverId = $this->getReceiverId($message->conversation, $user->id);
            $this->safeBroadcast(new MessageEdited($messageId, $message->conversation_id, $content, $receiverId));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en editMessage', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al editar mensaje'], 500);
        }
    }

    // ==========================================
    // API: ELIMINAR MENSAJE
    // ==========================================

    /**
     * API JSON: elimina un mensaje y emite evento de eliminacion.
     *
     * Permite al autor de un mensaje eliminarlo permanentemente. Solo el
     * usuario que envio el mensaje puede eliminarlo. Antes de eliminar,
     * emite el evento MessageDeleted via broadcast para que el otro
     * participante actualice su interfaz en tiempo real.
     *
     * Incluye limpieza automatica de conversaciones: si despues de eliminar
     * el mensaje la conversacion queda sin mensajes, se elimina tambien
     * la conversacion para evitar registros vacios en la base de datos.
     *
     * Flujo:
     * 1. Busca el mensaje y carga su conversacion.
     * 2. Verifica que el usuario autenticado sea el autor.
     * 3. Elimina el mensaje de la base de datos.
     * 4. Si la conversacion queda sin mensajes, la elimina tambien.
     * 5. Emite evento MessageDeleted via broadcast al otro participante.
     *
     * @param int $messageId Identificador del mensaje a eliminar.
     * @return \Illuminate\Http\JsonResponse JSON con success=true, o error
     *         de autorizacion.
     */
    public function deleteMessage($messageId)
    {
        try {
            $user = Auth::user();
            $message = Message::with('conversation')->findOrFail($messageId);

            if ($message->user_id !== $user->id) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }

            $conversationId = $message->conversation_id;
            $receiverId = $this->getReceiverId($message->conversation, $user->id);

            $message->delete();

            if (Message::where('conversation_id', $conversationId)->count() === 0) {
                Conversation::find($conversationId)?->delete();
            }

            $this->safeBroadcast(new MessageDeleted($messageId, $conversationId, $receiverId));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en deleteMessage', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al eliminar mensaje'], 500);
        }
    }

    // ==========================================
    // API: ELIMINAR CONVERSACIÓN
    // ==========================================

    /**
     * API JSON: elimina los mensajes propios de una conversacion.
     *
     * Metodo de eliminacion parcial: solo elimina los mensajes que el usuario
     * autenticado envio en la conversacion, no los del otro participante.
     * Esto preserva la integridad del historial para el otro usuario. Si
     * despues de eliminar los mensajes propios la conversacion queda sin
     * mensajes, se elimina la conversacion completa.
     *
     * Flujo:
     * 1. Verifica que la conversacion exista y el usuario sea participante.
     * 2. Elimina unicamente los mensajes del usuario autenticado.
     * 3. Si no quedan mensajes en la conversacion, la elimina.
     *
     * @param int $conversationId Identificador de la conversacion.
     * @return \Illuminate\Http\JsonResponse JSON con success=true, o error
     *         de autorizacion.
     */
    public function deleteConversation($conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if (!$this->canUserAccessConversation($user->id, $conversation)) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }

            Message::where('conversation_id', $conversationId)
                ->where('user_id', $user->id)
                ->delete();

            if (Message::where('conversation_id', $conversationId)->count() === 0) {
                $conversation->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en deleteConversation', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al eliminar conversación'], 500);
        }
    }

    // ==========================================
    // API: MARCAR COMO LEÍDO
    // ==========================================

    /**
     * API JSON: marca todos los mensajes no leidos de una conversacion como leidos.
     *
     * Endpoint utilizado para sincronizar el estado de lectura cuando el
     * usuario abre una conversacion o cuando el frontend detecta que los
     * mensajes estan visibles. Actualiza el campo is_read y read_at en
     * todos los mensajes que no fueron enviados por el usuario autenticado
     * y que aun no han sido marcados como leidos.
     *
     * Retorna la cantidad de mensajes que fueron actualizados, lo cual el
     * frontend puede utilzar para actualizar el badge de no leidos.
     *
     * Flujo:
     * 1. Verifica que la conversacion exista y el usuario sea participante.
     * 2. Actualiza mensajes no leidos del otro usuario (is_read=true, read_at=now).
     * 3. Retorna el conteo de mensajes actualizados.
     *
     * @param int $conversationId Identificador de la conversacion.
     * @return \Illuminate\Http\JsonResponse JSON con success=true y updated
     *         (cantidad de mensajes marcados), o error.
     */
    public function markAsRead($conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if (!$this->canUserAccessConversation($user->id, $conversation)) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }

            $updated = Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            return response()->json(['success' => true, 'updated' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Error al marcar como leído'], 500);
        }
    }

    // ==========================================
    // API: CONTADOR DE NO LEÍDOS
    // ==========================================

    /**
     * API JSON: retorna el total de mensajes no leidos del usuario.
     *
     * Endpoint utilizado por el frontend para mostrar un badge global con
     * el numero total de mensajes no leidos en todas las conversaciones.
     * Realiza una unica consulta que cuenta los mensajes no leidos por
     * conversacion y luego suma todos los conteos. Determina el campo
     * correcto del usuario (client_id o asores_id) segun su rol.
     *
     * En caso de error, retorna 0 como valor por defecto para evitar
     * que la interfaz muestre errores.
     *
     * @return \Illuminate\Http\JsonResponse JSON con success=true y
     *         unread_count (entero).
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();

            $totalUnread = Conversation::query()
                ->where(function ($q) use ($user) {
                    $q->where('client_id', $user->id)
                        ->orWhere('asesor_id', $user->id);
                })
                ->withCount(['messages as unread_count' => function ($q) use ($user) {
                    $q->where('user_id', '!=', $user->id)->where('is_read', false);
                }])
                ->get()
                ->sum('unread_count');

            return response()->json(['success' => true, 'unread_count' => $totalUnread]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'unread_count' => 0]);
        }
    }

    // ==========================================
    // API: ESTADO DE PRESENCIA
    // ==========================================

    /**
     * API JSON: actualiza el estado de presencia del usuario (online/offline).
     *
     * Endpoint invocado periodicamente por el frontend via JavaScript para
     * indicar que el usuario esta activo, o al detectar cierre de ventana/
     * pestaña para marcar como offline. Actualiza los campos is_online y
     * last_seen_at en la tabla users. Cuando el usuario se desconecta,
     * registra la fecha/hora actual como ultima conexion vista.
     *
     * Tambien invalida el cache de roles y permisos del usuario para
     * mantener consistencia con el nuevo estado.
     *
     * Flujo:
     * 1. Obtiene el ID del usuario autenticado.
     * 2. Convierte el parametro is_online a booleano.
     * 3. Actualiza is_online y last_seen_at en la tabla users.
     * 4. Limpia el cache de roles y permisos del usuario.
     *
     * @param Request $request Solicitud HTTP con el campo is_online (boolean).
     * @return \Illuminate\Http\JsonResponse JSON con success=true, o error
     *         de autenticacion.
     */
    public function updatePresence(Request $request)
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json(['success' => false, 'error' => 'No autenticado'], 401);
            }

            $isOnline = filter_var($request->is_online, FILTER_VALIDATE_BOOLEAN);

            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'is_online' => $isOnline,
                    'last_seen_at' => $isOnline ? null : now(),
                ]);

            $this->clearUserCache($userId);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en updatePresence', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al actualizar presencia'], 500);
        }
    }

    // ==========================================
    // API: ESCRIBIENDO (OPTIMIZADO SIN BUCLES)
    // ==========================================

    /**
     * API JSON: gestiona el indicador de "escribiendo..." en tiempo real.
     *
     * Endpoint utilizado por el frontend cuando el usuario esta escribiendo
     * un mensaje o deja de escribir. Emite un evento UserTyping via broadcast
     * al otro participante de la conversacion para mostrar/ocultar el
     * indicador de escritura.
     *
     * Incluye un mecanismo anti-bucles mediante cache: si el usuario ya
     * envio un evento de "escribiendo" hace menos de TYPING_THROTTLE (3)
     * segundos, no se emite otro broadcast. Esto previene saturacion de
     * eventos cuando el frontend envia eventos de escritura continuamente.
     *
     * Flujo:
     * 1. Verifica que la conversacion exista y el usuario sea participante.
     * 2. Determina si el usuario esta escribiendo (true) o dejo de escribir.
     * 3. Si esta escribiendo y ya existe en cache (throttle activo), retorna
     *    sin emitir broadcast para evitar bucles.
     * 4. Si esta escribiendo sin throttle activo, guarda en cache por 3 segundos.
     * 5. Si dejo de escribir, limpia la entrada de cache.
     * 6. Emite evento UserTyping via broadcast al otro participante.
     *
     * @param Request $request Solicitud HTTP con el campo is_typing (boolean).
     * @param int $conversationId Identificador de la conversacion.
     * @return \Illuminate\Http\JsonResponse JSON con success=true, o error.
     */
    public function setTypingStatus(Request $request, $conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if (!$this->canUserAccessConversation($user->id, $conversation)) {
                return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
            }

            $isTyping = filter_var($request->is_typing, FILTER_VALIDATE_BOOLEAN);
            $receiverId = $this->getReceiverId($conversation, $user->id);

            $cacheKey = self::TYPING_CACHE_KEY_PREFIX . $conversationId . '_' . $user->id;

            if ($isTyping) {
                // Si ya existe en caché, NO enviar broadcast (evita bucles)
                if (Cache::has($cacheKey)) {
                    return response()->json(['success' => true]);
                }
                // Guardar en caché por TYPING_THROTTLE segundos
                Cache::put($cacheKey, true, self::TYPING_THROTTLE);
            } else {
                // Si dejó de escribir, limpiar caché
                Cache::forget($cacheKey);
            }

            // Broadcast SOLO si cambia el estado o después del throttle
            $this->safeBroadcast(new UserTyping($user, $conversationId, $isTyping, $receiverId));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en setTypingStatus', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al actualizar estado de escritura'], 500);
        }
    }
}
