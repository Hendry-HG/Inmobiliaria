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

class ChatController extends Controller
{
    private const MAX_MESSAGE_LENGTH = 1000;
    private const TYPING_TIMEOUT = 30; // segundos

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:60,1')->only(['sendMessage', 'startConversation', 'startConversationWithCliente']);
        $this->middleware('throttle:100,1')->only(['getMessages', 'getConversations']);
    }

    /**
     * Obtiene los roles de un usuario con caché.
     */
    private function getUserRoles($userId): array
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
     * Limpia la caché de roles de un usuario.
     */
    private function clearUserRolesCache($userId): void
    {
        Cache::forget("user_roles_{$userId}");
    }

    /**
     * Muestra la vista principal del chat.
     */
    public function index()
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user->id);

        $isAsesor = in_array('Asesor Inmobiliario', $userRoles);
        $isCliente = in_array('Cliente', $userRoles);
        $isAdmin = in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles);

        Log::info('Chat Index', [
            'user_id' => $user->id,
            'roles' => $userRoles,
            'is_asesor' => $isAsesor,
            'is_cliente' => $isCliente
        ]);

        $conversations = $this->getUserConversations($user);

        // Obtener asesores disponibles (para Clientes)
        $asesoresDisponibles = collect();
        if ($isCliente) {
            $asesorIds = $this->getUsersByRole('Asesor Inmobiliario');
            $asesoresDisponibles = $this->formatAsesoresForClient($user, $asesorIds);
        }

        // Obtener clientes con citas (para Asesores)
        $clientesDisponibles = collect();
        if ($isAsesor) {
            $clientesDisponibles = $this->formatClientesForAsesor($user);
        }

        return view('modulos.chat.index', compact(
            'conversations',
            'asesoresDisponibles',
            'clientesDisponibles',
            'isAsesor',
            'isCliente',
            'isAdmin'
        ));
    }

    /**
     * Obtiene IDs de usuarios por rol.
     */
    private function getUsersByRole(string $roleName): array
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', $roleName)
            ->pluck('model_has_roles.model_id')
            ->toArray();
    }

    /**
     * Formatea asesores para clientes.
     */
    private function formatAsesoresForClient($user, array $asesorIds): \Illuminate\Support\Collection
    {
        if (empty($asesorIds)) {
            Log::warning('No se encontraron asesores con el rol "Asesor Inmobiliario"');
            return collect();
        }

        $asesores = User::whereIn('id', $asesorIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        Log::info('Asesores activos encontrados', ['count' => $asesores->count()]);

        return $asesores->map(function ($asesor) use ($user) {
            $existingConv = Conversation::where('client_id', $user->id)
                ->where('asesor_id', $asesor->id)
                ->first();

            $asesor->has_conversation = !is_null($existingConv);
            $asesor->conversation_id = $existingConv?->id;
            $asesor->avatar = $asesor->profile_photo_url;

            $hasAppointment = Appointment::where('user_id', $user->id)
                ->where('asesor_id', $asesor->id)
                ->exists();
            $asesor->has_appointment = $hasAppointment;
            $asesor->specialization = $asesor->specialization ?? 'Asesor inmobiliario';

            return $asesor;
        });
    }

    /**
     * Formatea clientes para asesores.
     */
    private function formatClientesForAsesor($user): \Illuminate\Support\Collection
    {
        $clienteIds = Appointment::where('asesor_id', $user->id)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if (empty($clienteIds)) {
            Log::warning('No se encontraron citas para este asesor', ['asesor_id' => $user->id]);
            return collect();
        }

        $clientes = User::whereIn('id', $clienteIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        Log::info('Clientes activos encontrados', [
            'asesor_id' => $user->id,
            'count' => $clientes->count()
        ]);

        return $clientes->map(function ($cliente) use ($user) {
            $cliente->avatar = $cliente->profile_photo_url;
            $cliente->has_conversation = Conversation::where('client_id', $cliente->id)
                ->where('asesor_id', $user->id)
                ->exists();
            $cliente->has_appointment = true;
            return $cliente;
        });
    }

    /**
     * Obtiene las conversaciones del usuario formateadas para la vista.
     */
    private function getUserConversations($user): \Illuminate\Support\Collection
    {
        $userRoles = $this->getUserRoles($user->id);
        $isCliente = in_array('Cliente', $userRoles);
        $isAsesor = in_array('Asesor Inmobiliario', $userRoles);

        $query = Conversation::query();

        if ($isCliente) {
            $query->where('client_id', $user->id);
        } elseif ($isAsesor) {
            $query->where('asesor_id', $user->id);
        } else {
            return collect();
        }

        $conversations = $query->with(['client', 'asesor', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return $conversations->map(function ($conv) use ($user, $isCliente) {
            $otherUser = $isCliente ? $conv->asesor : $conv->client;

            $hasAppointment = Appointment::where(function ($q) use ($user, $otherUser, $isCliente) {
                if ($isCliente) {
                    $q->where('user_id', $user->id)->where('asesor_id', $otherUser->id);
                } else {
                    $q->where('user_id', $otherUser->id)->where('asesor_id', $user->id);
                }
            })->exists();

            return [
                'id' => $conv->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->profile_photo_url,
                    'is_online' => $otherUser->is_online ?? false,
                    'last_seen' => $otherUser->last_seen_at ? $otherUser->last_seen_at->diffForHumans() : null,
                    'email' => $otherUser->email,
                    'phone' => $otherUser->phone,
                    'has_appointment' => $hasAppointment,
                    'specialization' => $otherUser->specialization,
                ],
                'unread_count' => Message::where('conversation_id', $conv->id)
                    ->where('user_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count(),
                'last_message' => $conv->lastMessage?->content,
                'last_message_time' => $conv->lastMessage?->created_at?->diffForHumans(),
            ];
        });
    }

    /**
     * Obtiene los asesores disponibles para iniciar chat.
     */
    public function getAvailableAsesores()
    {
        try {
            $asesorIds = $this->getUsersByRole('Asesor Inmobiliario');

            $asesores = User::whereIn('id', $asesorIds)
                ->where('is_active', true)
                ->select('id', 'name', 'email', 'profile_photo_url as avatar', 'specialization')
                ->get();

            return response()->json([
                'success' => true,
                'asesores' => $asesores
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getAvailableAsesores', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener asesores'
            ], 500);
        }
    }

    /**
     * Obtiene los mensajes de una conversación específica.
     */
    public function getMessages($conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::with(['client', 'asesor'])->findOrFail($conversationId);

            // Verificar autorización
            if ($user->id !== $conversation->client_id && $user->id !== $conversation->asesor_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Marcar mensajes como leídos
            $updated = Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            if ($updated > 0) {
                Log::info('Mensajes marcados como leídos', [
                    'conversation_id' => $conversationId,
                    'user_id' => $user->id,
                    'count' => $updated
                ]);
            }

            // Obtener mensajes
            $messages = $conversation->messages()
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'content' => $msg->content,
                        'user_id' => $msg->user_id,
                        'user_name' => $msg->user->name,
                        'user_avatar' => $msg->user->profile_photo_url,
                        'created_at' => $msg->created_at->toISOString(),
                        'formatted_time' => $msg->created_at->format('H:i'),
                        'is_read' => $msg->is_read,
                    ];
                });

            // Obtener el otro usuario
            $userRoles = $this->getUserRoles($user->id);
            $isCliente = in_array('Cliente', $userRoles);
            $otherUser = $user->id === $conversation->client_id ? $conversation->asesor : $conversation->client;

            $hasAppointment = Appointment::where(function ($q) use ($user, $otherUser, $isCliente) {
                if ($isCliente) {
                    $q->where('user_id', $user->id)->where('asesor_id', $otherUser->id);
                } else {
                    $q->where('user_id', $otherUser->id)->where('asesor_id', $user->id);
                }
            })->exists();

            $conversationData = [
                'id' => $conversation->id,
                'client_id' => $conversation->client_id,
                'asesor_id' => $conversation->asesor_id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->profile_photo_url,
                    'specialization' => $otherUser->specialization,
                    'is_online' => $otherUser->is_online ?? false,
                    'last_seen' => $otherUser->last_seen_at ? $otherUser->last_seen_at->diffForHumans() : null,
                    'email' => $otherUser->email,
                    'phone' => $otherUser->phone,
                    'has_appointment' => $hasAppointment
                ]
            ];

            return response()->json([
                'success' => true,
                'messages' => $messages,
                'conversation' => $conversationData
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Conversación no encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error en getMessages', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener mensajes'
            ], 500);
        }
    }

    /**
     * Endpoint para refrescar la lista de conversaciones.
     */
    public function getConversations()
    {
        try {
            $user = Auth::user();
            $conversations = $this->getUserConversations($user);
            return response()->json([
                'success' => true,
                'conversations' => $conversations
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getConversations', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener conversaciones'
            ], 500);
        }
    }

    /**
     * Inicia una nueva conversación (Como Cliente).
     */
    public function startConversation(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'asesor_id' => ['required', 'exists:users,id', Rule::notIn([$user->id])]
            ]);

            $asesor = User::findOrFail($validated['asesor_id']);
            $asesorRoles = $this->getUserRoles($asesor->id);

            if (!in_array('Asesor Inmobiliario', $asesorRoles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'El usuario seleccionado no es un asesor'
                ], 400);
            }

            $conversation = Conversation::firstOrCreate(
                ['client_id' => $user->id, 'asesor_id' => $asesor->id],
                [
                    'subject' => 'Chat con ' . $asesor->name,
                    'last_message_at' => now(),
                    'is_active' => true
                ]
            );

            Log::info('Conversación iniciada', [
                'client_id' => $user->id,
                'asesor_id' => $asesor->id,
                'conversation_id' => $conversation->id
            ]);

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'asesor' => [
                    'id' => $asesor->id,
                    'name' => $asesor->name,
                    'avatar' => $asesor->profile_photo_url,
                    'specialization' => $asesor->specialization,
                    'is_online' => $asesor->is_online ?? false,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en startConversation', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al iniciar conversación'
            ], 500);
        }
    }

    /**
     * Inicia una nueva conversación (Como Asesor).
     */
    public function startConversationWithCliente(Request $request)
    {
        try {
            $user = Auth::user();
            $userRoles = $this->getUserRoles($user->id);

            if (!in_array('Asesor Inmobiliario', $userRoles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No autorizado'
                ], 403);
            }

            $validated = $request->validate([
                'cliente_id' => ['required', 'exists:users,id', Rule::notIn([$user->id])]
            ]);

            $cliente = User::findOrFail($validated['cliente_id']);

            $conversation = Conversation::firstOrCreate(
                ['client_id' => $cliente->id, 'asesor_id' => $user->id],
                [
                    'subject' => 'Chat con ' . $cliente->name,
                    'last_message_at' => now(),
                    'is_active' => true
                ]
            );

            Log::info('Conversación iniciada por asesor', [
                'asesor_id' => $user->id,
                'cliente_id' => $cliente->id,
                'conversation_id' => $conversation->id
            ]);

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'cliente' => [
                    'id' => $cliente->id,
                    'name' => $cliente->name,
                    'avatar' => $cliente->profile_photo_url,
                    'is_online' => $cliente->is_online ?? false,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en startConversationWithCliente', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al iniciar conversación'
            ], 500);
        }
    }

    /**
     * Envía un mensaje nuevo.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            // Verificar autorización
            if ($user->id !== $conversation->client_id && $user->id !== $conversation->asesor_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'No autorizado'
                ], 403);
            }

            // Validar contenido
            $validated = $request->validate([
                'content' => ['required', 'string', 'min:1', 'max:' . self::MAX_MESSAGE_LENGTH]
            ]);

            // Sanitizar contenido (prevenir XSS)
            $content = strip_tags($validated['content']);
            $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            if (empty(trim($content))) {
                return response()->json([
                    'success' => false,
                    'error' => 'El mensaje no puede estar vacío'
                ], 422);
            }

            // Crear mensaje
            $message = Message::create([
                'conversation_id' => $conversationId,
                'user_id' => $user->id,
                'content' => $content,
            ]);

            // Actualizar conversación
            $conversation->update(['last_message_at' => now()]);

            // Cargar relación user
            $message->load('user');

            // Determinar receptor
            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;

            // Broadcast del mensaje
            broadcast(new NewMessage($message, $conversationId, $receiverId))->toOthers();

            Log::info('Mensaje enviado', [
                'conversation_id' => $conversationId,
                'user_id' => $user->id,
                'message_id' => $message->id
            ]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user->name,
                    'user_avatar' => $message->user->profile_photo_url,
                    'created_at' => $message->created_at->toISOString(),
                    'formatted_time' => $message->created_at->format('H:i'),
                    'is_read' => $message->is_read,
                ],
                'conversation_id' => $conversationId
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Conversación no encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error en sendMessage', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar mensaje'
            ], 500);
        }
    }

    /**
     * Marca mensajes como leídos.
     */
    public function markAsRead($conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if ($user->id !== $conversation->client_id && $user->id !== $conversation->asesor_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $updated = Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'updated' => $updated
            ]);
        } catch (\Exception $e) {
            Log::error('Error en markAsRead', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al marcar como leído'
            ], 500);
        }
    }

    /**
     * Obtiene el conteo total de no leídos.
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            $conversations = $this->getUserConversations($user);
            $totalUnread = $conversations->sum('unread_count');

            return response()->json([
                'success' => true,
                'unread_count' => $totalUnread
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'unread_count' => 0
            ]);
        }
    }

    /**
     * Elimina una conversación (solo para el usuario actual).
     */
    public function deleteConversation($conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if ($user->id !== $conversation->client_id && $user->id !== $conversation->asesor_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Eliminar mensajes del usuario actual
            Message::where('conversation_id', $conversationId)
                ->where('user_id', $user->id)
                ->delete();

            // Verificar si quedan mensajes
            $remainingMessages = Message::where('conversation_id', $conversationId)->count();

            if ($remainingMessages === 0) {
                $conversation->delete();
                Log::info('Conversación eliminada', [
                    'conversation_id' => $conversationId,
                    'user_id' => $user->id
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en deleteConversation', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar conversación'
            ], 500);
        }
    }

    /**
     * Elimina un mensaje específico con broadcasting.
     */
    public function deleteMessage($messageId)
    {
        try {
            $user = Auth::user();
            $message = Message::with('conversation')->findOrFail($messageId);

            if ($message->user_id !== $user->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $conversation = $message->conversation;
            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;

            // Broadcast antes de eliminar
            broadcast(new MessageDeleted($message->id, $conversation->id, $receiverId))->toOthers();

            $message->delete();

            // Verificar si quedan mensajes
            $remainingMessages = Message::where('conversation_id', $conversation->id)->count();

            if ($remainingMessages === 0) {
                $conversation->delete();
                Log::info('Conversación eliminada al eliminar último mensaje', [
                    'conversation_id' => $conversation->id
                ]);
            }

            Log::info('Mensaje eliminado', [
                'message_id' => $messageId,
                'user_id' => $user->id
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en deleteMessage', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar mensaje'
            ], 500);
        }
    }

    /**
     * Edita un mensaje con broadcasting.
     */
    public function editMessage(Request $request, $messageId)
    {
        try {
            $user = Auth::user();
            $message = Message::with('conversation')->findOrFail($messageId);

            if ($message->user_id !== $user->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $validated = $request->validate([
                'content' => ['required', 'string', 'min:1', 'max:' . self::MAX_MESSAGE_LENGTH]
            ]);

            // Sanitizar contenido
            $content = strip_tags($validated['content']);
            $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            if (empty(trim($content))) {
                return response()->json([
                    'success' => false,
                    'error' => 'El mensaje no puede estar vacío'
                ], 422);
            }

            $message->content = $content;
            $message->save();

            $conversation = $message->conversation;
            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;

            // Broadcast de edición
            broadcast(new MessageEdited($message->id, $conversation->id, $message->content, $receiverId))->toOthers();

            Log::info('Mensaje editado', [
                'message_id' => $messageId,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'updated_at' => $message->updated_at->toISOString()
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en editMessage', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al editar mensaje'
            ], 500);
        }
    }

    /**
     * Actualiza el estado de presencia (Online/Offline).
     */
    public function updatePresence(Request $request)
    {
        try {
            $user = Auth::user();
            $isOnline = filter_var($request->is_online, FILTER_VALIDATE_BOOLEAN);

            $user->is_online = $isOnline;
            $user->last_seen_at = $isOnline ? null : now();
            $user->save();

            // Limpiar caché de roles
            $this->clearUserRolesCache($user->id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en updatePresence', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar presencia'
            ], 500);
        }
    }

    /**
     * Envía el estado de "Escribiendo..." al otro usuario.
     */
    public function setTypingStatus(Request $request, $conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if ($user->id !== $conversation->client_id && $user->id !== $conversation->asesor_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $isTyping = filter_var($request->is_typing, FILTER_VALIDATE_BOOLEAN);
            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;

            broadcast(new UserTyping($user, $conversationId, $isTyping, $receiverId))->toOthers();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en setTypingStatus', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar estado de escritura'
            ], 500);
        }
    }
}
