<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\Property;
use App\Models\UserNotification;
use App\Traits\AuditTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    use AuditTrait;

    public function __construct()
    {
        $this->middleware('auth')->only(['index', 'show', 'edit', 'update', 'destroy', 'changeStatus']);
        $this->middleware('permission:ver leads')->only(['index', 'show']);
        $this->middleware('permission:editar lead')->only(['edit', 'update']);
        $this->middleware('permission:eliminar lead')->only(['destroy']);
    }

    private function userHasRole($userId, $roleName)
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $userId)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('roles.name', $roleName)
            ->exists();
    }

    private function userHasPermission($userId, $permissionName)
    {
        $hasDirect = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $userId)
            ->where('model_has_permissions.model_type', 'App\\Models\\User')
            ->where('permissions.name', $permissionName)
            ->exists();

        if ($hasDirect) {
            return true;
        }

        return DB::table('model_has_roles')
            ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_roles.model_id', $userId)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('permissions.name', $permissionName)
            ->exists();
    }

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
                if ($this->userHasRole($userId, 'Asesor Inmobiliario')) {
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

            return redirect()->back()
                ->with('success', '¡Solicitud enviada con éxito! El asesor se comunicará contigo pronto.');

        } catch (\Exception $e) {
            Log::error('Error en LeadController@storePublic: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ocurrió un error al enviar la solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'Debes iniciar sesión para crear leads.');
        }

        $userId = Auth::id();
        if (!$this->userHasPermission($userId, 'crear lead')) {
            abort(403, 'No tienes permiso para crear leads.');
        }

        return $this->storePublic($request);
    }

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

            $leads = $query->with(['user', 'asesor'])->get();

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
