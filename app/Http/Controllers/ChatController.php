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
use Illuminate\Support\Facades\RateLimiter;

class ChatController extends Controller
{
    private const MAX_MESSAGE_LENGTH = 1000;
    private const TYPING_THROTTLE = 3;
    private const TYPING_CACHE_KEY_PREFIX = 'typing_';
    private const MAX_MESSAGES_PER_MINUTE = 30;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:60,1')->only(['sendMessage', 'startConversation', 'startConversationWithCliente']);
        $this->middleware('throttle:100,1')->only(['getMessages', 'getConversations']);
    }

    // ==========================================
    // HELPERS PRIVADOS
    // ==========================================

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

    private function clearUserCache(int $userId): void
    {
        Cache::forget("user_roles_{$userId}");
        Cache::forget("user_permissions_{$userId}");
    }

    private function getReceiverId(Conversation $conversation, int $userId): int
    {
        return $userId === $conversation->client_id
            ? $conversation->asesor_id
            : $conversation->client_id;
    }

    private function canUserAccessConversation(int $userId, Conversation $conversation): bool
    {
        return in_array($userId, [$conversation->client_id, $conversation->asesor_id]);
    }

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

    private function formatConversation(Conversation $conversation, User $user, bool $isCliente): array
    {
        $otherUser = $isCliente ? $conversation->asesor : $conversation->client;

        return [
            'id' => $conversation->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->full_name ?? $otherUser->name,
                'avatar' => $otherUser->profile_photo_url,
                'is_online' => $otherUser->is_online ?? false,
                'email' => $otherUser->email,
                'specialization' => $otherUser->specialization,
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

    public function index()
    {
        $user = Auth::user();
        $roles = $this->getUserRoles($user->id);

        $isAsesor = in_array('Asesor Inmobiliario', $roles);
        $isCliente = in_array('Cliente', $roles);

        $conversations = Conversation::query()
            ->with(['client', 'asesor', 'lastMessage'])
            ->where($isCliente ? 'client_id' : 'asesor_id', $user->id)
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(fn($conv) => $this->formatConversation($conv, $user, $isCliente));

        $contactos = $this->getAvailableContactos($user, $isCliente, $isAsesor);

        // ✅ Pasar contactos con el nombre correcto para la vista
        $asesoresDisponibles = $isCliente ? $contactos : collect();
        $clientesDisponibles = $isAsesor ? $contactos : collect();

        return view('modulos.chat.index', compact(
            'conversations',
            'asesoresDisponibles',
            'clientesDisponibles',
            'isAsesor',
            'isCliente'
        ));
    }

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

    // ==========================================
    // API: CONVERSACIONES
    // ==========================================

    public function getConversations()
    {
        try {
            $user = Auth::user();
            $roles = $this->getUserRoles($user->id);
            $isCliente = in_array('Cliente', $roles);

            $conversations = Conversation::query()
                ->with(['client', 'asesor', 'lastMessage'])
                ->where($isCliente ? 'client_id' : 'asesor_id', $user->id)
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

    public function startConversation(Request $request)
    {
        return $this->startConversationBase($request, 'asesor_id', 'Asesor Inmobiliario');
    }

    public function startConversationWithCliente(Request $request)
    {
        $user = Auth::user();
        $roles = $this->getUserRoles($user->id);

        if (!in_array('Asesor Inmobiliario', $roles)) {
            return response()->json(['success' => false, 'error' => 'No autorizado'], 403);
        }

        return $this->startConversationBase($request, 'cliente_id', null);
    }

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
            broadcast(new NewMessage($message, $conversationId, $receiverId))->toOthers();

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

            DB::table('messages')
                ->where('id', $messageId)
                ->update(['content' => $content]);

            $receiverId = $this->getReceiverId($message->conversation, $user->id);
            broadcast(new MessageEdited($messageId, $message->conversation_id, $content, $receiverId))->toOthers();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en editMessage', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al editar mensaje'], 500);
        }
    }

    // ==========================================
    // API: ELIMINAR MENSAJE
    // ==========================================

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

            broadcast(new MessageDeleted($messageId, $conversationId, $receiverId))->toOthers();
            $message->delete();

            if (Message::where('conversation_id', $conversationId)->count() === 0) {
                Conversation::find($conversationId)?->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en deleteMessage', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al eliminar mensaje'], 500);
        }
    }

    // ==========================================
    // API: ELIMINAR CONVERSACIÓN
    // ==========================================

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

    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            $roles = $this->getUserRoles($user->id);
            $isCliente = in_array('Cliente', $roles);

            $totalUnread = Conversation::query()
                ->where($isCliente ? 'client_id' : 'asesor_id', $user->id)
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
            broadcast(new UserTyping($user, $conversationId, $isTyping, $receiverId))->toOthers();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en setTypingStatus', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al actualizar estado de escritura'], 500);
        }
    }
}
