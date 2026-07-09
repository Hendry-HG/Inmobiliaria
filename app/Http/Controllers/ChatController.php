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

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Obtiene los roles de un usuario dado.
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
     * Muestra la vista principal del chat.
     */
    public function index()
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user->id);

        $isAsesor = in_array('Asesor Inmobiliario', $userRoles);
        $isCliente = in_array('Cliente', $userRoles);
        $isAdmin = in_array('Super Admin', $userRoles) || in_array('Administrador', $userRoles);

        Log::info('Chat Index - Usuario: ' . $user->id . ' - Roles: ' . json_encode($userRoles));
        Log::info('isAsesor: ' . ($isAsesor ? 'true' : 'false') . ' - isCliente: ' . ($isCliente ? 'true' : 'false'));

        $conversations = $this->getUserConversations($user);

        // Obtener asesores disponibles (para Clientes)
        $asesoresDisponibles = collect();
        if ($isCliente) {
            Log::info('Cliente detectado, buscando asesores...');

            $asesorIds = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'Asesor Inmobiliario')
                ->pluck('model_has_roles.model_id')
                ->toArray();

            Log::info('IDs de asesores encontrados: ' . json_encode($asesorIds));

            if (!empty($asesorIds)) {
                $asesores = User::whereIn('id', $asesorIds)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                Log::info('Cantidad de asesores activos: ' . $asesores->count());

                $asesoresDisponibles = $asesores->map(function($asesor) use ($user) {
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
            } else {
                Log::warning('No se encontraron asesores con el rol "Asesor Inmobiliario"');
            }
        }

        // Obtener clientes con citas (para Asesores)
        $clientesDisponibles = collect();
        if ($isAsesor) {
            Log::info('Asesor detectado, buscando clientes con citas...');

            $clienteIds = Appointment::where('asesor_id', $user->id)
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id')
                ->toArray();

            Log::info('IDs de clientes con citas encontrados: ' . json_encode($clienteIds));

            if (!empty($clienteIds)) {
                $clientes = User::whereIn('id', $clienteIds)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                Log::info('Cantidad de clientes activos: ' . $clientes->count());

                $clientesDisponibles = $clientes->map(function($cliente) use ($user) {
                    $cliente->avatar = $cliente->profile_photo_url;
                    $cliente->has_conversation = Conversation::where('client_id', $cliente->id)
                        ->where('asesor_id', $user->id)
                        ->exists();
                    $cliente->has_appointment = true;
                    return $cliente;
                });
            } else {
                Log::warning('No se encontraron citas para este asesor');
            }
        }

        Log::info('Asesores disponibles count: ' . $asesoresDisponibles->count());
        Log::info('Clientes disponibles count: ' . $clientesDisponibles->count());

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
     * Obtiene las conversaciones del usuario formateadas para la vista.
     */
    private function getUserConversations($user)
    {
        $userRoles = $this->getUserRoles($user->id);
        $isCliente = in_array('Cliente', $userRoles);
        $isAsesor = in_array('Asesor Inmobiliario', $userRoles);

        if ($isCliente) {
            $conversations = Conversation::where('client_id', $user->id)
                ->with(['asesor', 'lastMessage'])
                ->orderBy('last_message_at', 'desc')
                ->get();

            return $conversations->map(function($conv) use ($user) {
                $hasAppointment = Appointment::where('user_id', $user->id)
                    ->where('asesor_id', $conv->asesor->id)
                    ->exists();

                return [
                    'id' => $conv->id,
                    'other_user' => [
                        'id' => $conv->asesor->id,
                        'name' => $conv->asesor->name,
                        'avatar' => $conv->asesor->profile_photo_url,
                        'is_online' => $conv->asesor->is_online ?? false,
                        'last_seen' => $conv->asesor->last_seen_at ? $conv->asesor->last_seen_at->diffForHumans() : null,
                        'email' => $conv->asesor->email,
                        'phone' => $conv->asesor->phone,
                        'has_appointment' => $hasAppointment
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

        if ($isAsesor) {
            $conversations = Conversation::where('asesor_id', $user->id)
                ->with(['client', 'lastMessage'])
                ->orderBy('last_message_at', 'desc')
                ->get();

            return $conversations->map(function($conv) use ($user) {
                $hasAppointment = Appointment::where('user_id', $conv->client->id)
                    ->where('asesor_id', $user->id)
                    ->exists();

                return [
                    'id' => $conv->id,
                    'other_user' => [
                        'id' => $conv->client->id,
                        'name' => $conv->client->name,
                        'avatar' => $conv->client->profile_photo_url,
                        'is_online' => $conv->client->is_online ?? false,
                        'last_seen' => $conv->client->last_seen_at ? $conv->client->last_seen_at->diffForHumans() : null,
                        'email' => $conv->client->email,
                        'phone' => $conv->client->phone,
                        'has_appointment' => $hasAppointment
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

        return collect();
    }

    /**
     * Obtiene los asesores disponibles para iniciar chat.
     */
    public function getAvailableAsesores()
    {
        try {
            $asesorIds = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'Asesor Inmobiliario')
                ->pluck('model_has_roles.model_id')
                ->toArray();

            $asesores = User::whereIn('id', $asesorIds)
                ->where('is_active', true)
                ->select('id', 'name', 'email', 'profile_photo_url as avatar', 'specialization')
                ->get();

            return response()->json(['success' => true, 'asesores' => $asesores]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene los mensajes de una conversación específica.
     */
    public function getMessages($conversationId)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);

            if ($user->id !== $conversation->client_id && $user->id !== $conversation->asesor_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            $messages = $conversation->messages()
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($msg) {
                    return [
                        'id' => $msg->id,
                        'content' => $msg->content,
                        'user_id' => $msg->user_id,
                        'user_name' => $msg->user->name,
                        'user_avatar' => $msg->user->profile_photo_url,
                        'created_at' => $msg->created_at,
                        'formatted_time' => $msg->created_at->format('H:i'),
                        'is_read' => $msg->is_read,
                    ];
                });

            $otherUser = $user->id === $conversation->client_id ? $conversation->asesor : $conversation->client;
            $userRoles = $this->getUserRoles($user->id);
            $isCliente = in_array('Cliente', $userRoles);

            $hasAppointment = Appointment::where(function($q) use ($user, $otherUser, $isCliente) {
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
        } catch (\Exception $e) {
            Log::error('Error en getMessages: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint para refrescar la lista de conversaciones (Sidebar).
     */
    public function getConversations()
    {
        try {
            $user = Auth::user();
            $conversations = $this->getUserConversations($user);
            return response()->json(['success' => true, 'conversations' => $conversations]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Inicia una nueva conversación (Como Cliente).
     */
    public function startConversation(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate(['asesor_id' => 'required|exists:users,id']);

            $asesor = User::findOrFail($request->asesor_id);
            $asesorRoles = $this->getUserRoles($asesor->id);

            if (!in_array('Asesor Inmobiliario', $asesorRoles)) {
                return response()->json(['error' => 'El usuario seleccionado no es un asesor'], 400);
            }

            $conversation = Conversation::firstOrCreate(
                ['client_id' => $user->id, 'asesor_id' => $asesor->id],
                ['subject' => 'Chat con ' . $asesor->name, 'last_message_at' => now(), 'is_active' => true]
            );

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
        } catch (\Exception $e) {
            Log::error('Error en startConversation: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Inicia una nueva conversación (Como Asesor).
     */
    public function startConversationWithCliente(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate(['cliente_id' => 'required|exists:users,id']);

            $cliente = User::findOrFail($request->cliente_id);
            $userRoles = $this->getUserRoles($user->id);

            if (!in_array('Asesor Inmobiliario', $userRoles)) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $conversation = Conversation::firstOrCreate(
                ['client_id' => $cliente->id, 'asesor_id' => $user->id],
                ['subject' => 'Chat con ' . $cliente->name, 'last_message_at' => now(), 'is_active' => true]
            );

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
        } catch (\Exception $e) {
            Log::error('Error en startConversationWithCliente: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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

            if ($user->id !== $conversation->client_id && $user->id !== $conversation->asesor_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $request->validate(['content' => 'required|string|max:1000']);

            $message = Message::create([
                'conversation_id' => $conversationId,
                'user_id' => $user->id,
                'content' => $request->content,
            ]);

            $conversation->update(['last_message_at' => now()]);

            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;
            $message->load('user');

            broadcast(new NewMessage($message, $conversationId, $receiverId));

            $formattedMessage = [
                'id' => $message->id,
                'content' => $message->content,
                'user_id' => $message->user_id,
                'user_name' => $message->user->name,
                'user_avatar' => $message->user->profile_photo_url,
                'created_at' => $message->created_at,
                'formatted_time' => $message->created_at->format('H:i'),
                'is_read' => $message->is_read,
            ];

            return response()->json(['success' => true, 'message' => $formattedMessage, 'conversation_id' => $conversationId]);
        } catch (\Exception $e) {
            Log::error('Error en sendMessage: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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

            Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
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
            return response()->json(['success' => true, 'unread_count' => $totalUnread]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'unread_count' => 0]);
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
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un mensaje específico con broadcasting.
     */
    public function deleteMessage($messageId)
    {
        try {
            $user = Auth::user();
            $message = Message::findOrFail($messageId);
            $conversation = Conversation::findOrFail($message->conversation_id);

            if ($message->user_id !== $user->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Determinar el receptor
            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;

            // Broadcast antes de eliminar
            broadcast(new MessageDeleted($message->id, $conversation->id, $receiverId));

            $message->delete();

            // Verificar si quedan mensajes
            $remainingMessages = Message::where('conversation_id', $conversation->id)->count();

            if ($remainingMessages === 0) {
                $conversation->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Edita un mensaje con broadcasting.
     */
    public function editMessage(Request $request, $messageId)
    {
        try {
            $user = Auth::user();
            $message = Message::findOrFail($messageId);
            $conversation = Conversation::findOrFail($message->conversation_id);

            if ($message->user_id !== $user->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $request->validate(['content' => 'required|string|max:1000']);

            $oldContent = $message->content;
            $message->content = $request->content;
            $message->save();

            // Determinar el receptor
            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;

            // Broadcast de edición
            broadcast(new MessageEdited($message->id, $conversation->id, $message->content, $receiverId));

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza el estado de presencia (Online/Offline).
     */
    public function updatePresence(Request $request)
    {
        try {
            $userId = Auth::id();
            $isOnline = $request->is_online;

            DB::table('users')->where('id', $userId)->update([
                'is_online' => $isOnline,
                'last_seen_at' => $isOnline ? null : now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
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

            $receiverId = $user->id === $conversation->client_id ? $conversation->asesor_id : $conversation->client_id;

            broadcast(new UserTyping($user, $conversationId, $request->is_typing, $receiverId));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
