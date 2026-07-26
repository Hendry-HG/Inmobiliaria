<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Notificaciones
 *
 * Gestiona las notificaciones del usuario autenticado. Permite listar,
 * marcar como leidas, eliminar y obtener el conteo de notificaciones
 * no leidas. Soporta respuestas JSON para integracion con frontend
 * y respuestas de redireccion para vistas tradicionales.
 *
 * @package App\Http\Controllers
 */
class NotificationController extends Controller
{
    /**
     * Constructor del controlador.
     * Aplica middleware de autenticacion para todas las rutas.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Obtiene todas las notificaciones del usuario autenticado con paginacion.
     *
     * Flujo de datos:
     * 1. Filtra notificaciones por el id del usuario autenticado
     * 2. Ordena de mas reciente a mas antigua
     * 3. Pagina resultados en bloques de 15 registros
     * 4. Retorna JSON si la peticion es AJAX, o vista en caso contrario
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
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
     * Obtiene unicamente las notificaciones no leidas del usuario.
     *
     * Flujo de datos:
     * 1. Filtra notificaciones del usuario con read_at nulo (no leidas)
     * 2. Ordena de mas reciente a mas antigua
     * 3. Retorna JSON con el conteo total y la coleccion de notificaciones
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Marca una notificacion especifica como leida.
     *
     * Flujo de datos:
     * 1. Busca la notificacion por id y verifica que pertenezca al usuario
     * 2. Actualiza el campo read_at con la fecha y hora actuales
     * 3. Retorna JSON si la peticion es AJAX, o redirige con mensaje en caso contrario
     * 4. Retorna 404 si la notificacion no existe o no pertenece al usuario
     *
     * @param int $id Identificador de la notificacion a marcar
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
     * Marca todas las notificaciones no leidas del usuario como leidas.
     *
     * Flujo de datos:
     * 1. Filtra todas las notificaciones del usuario con read_at nulo
     * 2. Actualiza el campo read_at con la fecha y hora actuales en lote
     * 3. Retorna JSON o redirige con mensaje de exito
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
     * Obtiene el conteo de notificaciones no leidas del usuario.
     *
     * Flujo de datos:
     * 1. Cuenta las notificaciones del usuario con read_at nulo
     * 2. Retorna JSON con el campo 'count' para usar en badges del frontend
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Elimina una notificacion especifica del usuario.
     *
     * Flujo de datos:
     * 1. Busca la notificacion por id y verifica que pertenezca al usuario
     * 2. Elimina el registro de la base de datos
     * 3. Retorna JSON con resultado de la operacion
     * 4. Retorna 404 si la notificacion no existe o no pertenece al usuario
     *
     * @param int $id Identificador de la notificacion a eliminar
     * @return \Illuminate\Http\JsonResponse
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
     * Elimina todas las notificaciones leidas del usuario.
     *
     * Flujo de datos:
     * 1. Filtra las notificaciones del usuario con read_at no nulo (leidas)
     * 2. Elimina todos los registros que coincidan en lote
     * 3. Retorna JSON con resultado de la operacion
     *
     * @return \Illuminate\Http\JsonResponse
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
