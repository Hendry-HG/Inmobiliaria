<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use App\Models\AppointmentSetting;
use App\Traits\AuditTrait;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    use AuditTrait;

    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->middleware('auth')->except(['createPublic']);
        $this->permissionService = $permissionService;
    }

    private function getUserRoles($userId)
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $userId)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();
    }

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

    public function createPublic(Request $request)
    {
        if (!Auth::check()) {
            $currentUrl = route('citas.create', ['property_id' => $request->property_id]);
            session(['url.intended' => $currentUrl]);
            return redirect()->route('login')->with('info', 'Por favor inicia sesión para agendar una cita.');
        }

        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta está desactivada.');
        }

        $property = null;
        if ($request->has('property_id')) {
            $property = Property::find($request->property_id);
            if (!$property) {
                return redirect()->route('catalogo.index')->with('error', 'Propiedad no encontrada.');
            }
        }

        session()->forget('url.intended');
        return view('modulos.citas.create', compact('property', 'user'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // 1. VERIFICAR PERMISO USANDO PERMISSIONSERVICE
            if (!$this->permissionService->hasPermission('crear cita')) {
                Log::warning('Intento de crear cita sin permiso', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para agendar citas. Contacta al administrador.'
                ], 403);
            }

            // 2. VALIDAR DATOS
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'date' => 'required|date|after:now',
                'property_id' => 'required|exists:properties,id',
                'message' => 'nullable|string|max:500'
            ]);

            // 3. BUSCAR PROPIEDAD
            $property = Property::find($request->property_id);
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propiedad no encontrada.'
                ], 404);
            }

            // 4. VERIFICAR DISPONIBILIDAD DEL ASESOR
            $settings = AppointmentSetting::getForUser($property->user_id);

            if (!$settings->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El asesor no está recibiendo citas en este momento.'
                ], 400);
            }

            // 5. VERIFICAR HORARIO DISPONIBLE
            $dateTime = new \DateTime($request->date);
            $time = $dateTime->format('H:i');
            $dateStr = $dateTime->format('Y-m-d');

            $canSchedule = $settings->canSchedule($dateStr, $time);

            if (!$canSchedule['available']) {
                return response()->json([
                    'success' => false,
                    'message' => $canSchedule['reason'] ?? 'Horario no disponible para esta fecha.'
                ], 400);
            }

            // 6. CREAR LA CITA
            $appointment = Appointment::create([
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

            // 7. LOG DE AUDITORÍA
            $this->logCreated(
                $appointment,
                ($user->full_name ?? $user->name) . ' solicitó una cita para "' . ($property->title ?? 'Propiedad #' . $property->id) . '" el ' . $request->date
            );

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

    public function updateStatus(Request $request, Appointment $appointment)
    {
        try {
            $user = Auth::user();
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

    public function reschedule(Request $request, Appointment $appointment)
    {
        try {
            $user = Auth::user();
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

            // NOTIFICACIÓN
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
