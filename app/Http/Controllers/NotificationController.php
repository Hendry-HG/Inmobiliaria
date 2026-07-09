<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Obtener todas las notificaciones del usuario
     */
    public function index()
    {
        try {
            $notifications = UserNotification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            if (request()->wantsJson()) {
                return response()->json($notifications);
            }

            return view('profile.notifications', compact('notifications'));
        } catch (\Exception $e) {
            Log::error('Error en index: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener solo notificaciones no leídas
     */
    public function unread()
    {
        try {
            $notifications = UserNotification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'count' => $notifications->count(),
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            Log::error('Error en unread: ' . $e->getMessage());
            return response()->json(['count' => 0, 'notifications' => []]);
        }
    }

    /**
     * Marcar una notificación específica como leída
     */
    public function markAsRead($id)
    {
        try {
            $notification = UserNotification::where('user_id', Auth::id())
                ->where('id', $id)
                ->first();

            if ($notification) {
                $notification->update(['read_at' => now()]);

                if (request()->wantsJson()) {
                    return response()->json(['success' => true]);
                }

                return back()->with('success', 'Notificación marcada como leída');
            }

            return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);

        } catch (\Exception $e) {
            Log::error('Error al marcar notificación: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Marcar TODAS las notificaciones como leídas
     */
    public function markAllAsRead()
    {
        try {
            UserNotification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            if (request()->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Todas las notificaciones marcadas como leídas');

        } catch (\Exception $e) {
            Log::error('Error al marcar todas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener el conteo de notificaciones no leídas
     */
    public function unreadCount()
    {
        try {
            $count = UserNotification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->count();

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error en unreadCount: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    /**
     * Eliminar una notificación
     */
    public function destroy($id)
    {
        try {
            $notification = UserNotification::where('user_id', Auth::id())
                ->where('id', $id)
                ->first();

            if ($notification) {
                $notification->delete();
                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);

        } catch (\Exception $e) {
            Log::error('Error al eliminar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar todas las notificaciones leídas
     */
    public function clearRead()
    {
        try {
            UserNotification::where('user_id', Auth::id())
                ->whereNotNull('read_at')
                ->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error al limpiar leídas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
