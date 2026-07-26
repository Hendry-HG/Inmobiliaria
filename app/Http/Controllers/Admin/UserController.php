<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use App\Services\PermissionService;
use App\Traits\AuditTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Controlador de administracion de usuarios.
 *
 * Gestiona el ciclo de vida completo de los usuarios del sistema:
 * creacion, lectura, actualizacion y eliminacion (CRUD).
 *
 * Incluye funcionalidades adicionales como:
 * - Manejo de fotos de perfil con almacenamiento en disco publico.
 * - Asignacion y sincronizacion de roles usando Spatie Permission.
 * - Preguntas de seguridad obligatorias con respuestas hasheadas.
 * - Activacion/desactivacion de cuentas con notificacion por correo.
 * - Exportacion de usuarios a formato CSV.
 * - Modales de vista, edicion y eliminacion para interaccion AJAX.
 *
 * Todas las operaciones requieren autenticacion y verificacion de
 * permisos a traves del PermissionService (roles Super Admin,
 * Administrador, o permisos 'ver usuarios' / 'gestionar usuarios').
 * Utiliza AuditTrait para el registro de auditoria de cada accion.
 */
class UserController extends Controller
{
    use AuditTrait;

    /**
     * Constructor del controlador.
     *
     * Aplica middleware de autenticacion a todos los metodos del controlador.
     * No hay metodos publicos sin autenticacion en este controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Obtener una instancia del PermissionService con el usuario autenticado
     */
    private function getPermissionService()
    {
        return new PermissionService(Auth::user());
    }

    /**
     * Verificar si el usuario tiene acceso a la gestión de usuarios
     */
    private function checkAccess()
    {
        if (!Auth::check()) {
            abort(403, 'No autenticado.');
        }

        $permission = $this->getPermissionService();

        // Verificar si es Super Admin
        if ($permission->hasRole('Super Admin')) {
            return true;
        }

        // Verificar si es Administrador
        if ($permission->hasRole('Administrador')) {
            return true;
        }

        // Verificar permisos específicos
        if ($permission->hasAnyPermission(['ver usuarios', 'gestionar usuarios'])) {
            return true;
        }

        abort(403, 'No tienes permiso para acceder a esta página.');
    }

    /**
     * Lista todos los usuarios con filtros de busqueda, rol y estado.
     *
     * Requiere permisos de acceso. Permite buscar usuarios por nombre,
     * apellido, email, cedula o telefono. Filtra por rol y estado
     * (activo/inactivo). Retorna los usuarios con sus relaciones
     * geograficas (pais, estado, ciudad) paginados de 8 en 8.
     *
     * @param \Illuminate\Http\Request $request Solicitud con parametros opcionales 'search', 'role', 'status'.
     * @return \Illuminate\View\View Vista 'dashboard.admin.users.index' con los usuarios y roles.
     */
    public function index(Request $request)
    {
        $this->checkAccess();

        $query = User::query();

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                  ->orWhere('last_name', 'LIKE', $search)
                  ->orWhere('email', 'LIKE', $search)
                  ->orWhere('id_number', 'LIKE', $search)
                  ->orWhere('phone', 'LIKE', $search);
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->select([
                'id', 'name', 'last_name', 'email', 'phone', 'address',
                'id_type', 'id_number', 'country_id', 'state_id', 'city_id',
                'is_active', 'profile_photo', 'created_at'
            ])
            ->with(['country:id,name', 'state:id,name', 'userCity:id,name'])
            ->orderBy('id', 'asc')
            ->paginate(8)
            ->withQueryString();

        $roles = Role::all();

        return view('dashboard.admin.users.index', compact('users', 'roles'));
    }

    /**
     * Muestra el formulario de creacion de un nuevo usuario.
     *
     * Requiere permisos de acceso. Carga los roles disponibles
     * (excluyendo el rol 'Cliente') y la lista de paises para
     * el formulario de registro.
     *
     * @return \Illuminate\View\View Vista 'dashboard.admin.users.create' con roles y paises.
     */
    public function create()
    {
        $this->checkAccess();
        $roles = Role::where('name', '!=', 'Cliente')->get();
        $countries = Country::orderBy('name')->get();
        return view('dashboard.admin.users.create', compact('roles', 'countries'));
    }

    /**
     * Almacena un nuevo usuario en el sistema.
     *
     * Requiere permisos de acceso. Valida todos los campos obligatorios
     * incluyendo nombre, email, contrasena (minimo 10 caracteres con
     * confirmacion), tres preguntas de seguridad con respuestas, y un rol.
     * Maneja la subida de foto de perfil al disco 'public'. Almacena las
     * respuestas de seguridad hasheadas con Hash::make(). Asigna el rol
     * proporcionado usando Spatie Permission. Registra la creacion
     * en el log de auditoria con el nombre del usuario que creo la cuenta.
     *
     * @param \Illuminate\Http\Request $request Solicitud con todos los campos del usuario y archivos.
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de usuarios con mensaje de exito o error.
     */
    public function store(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:10|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|in:V,E,J,P',
            'id_number' => 'nullable|string|max:20|unique:users,id_number',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'city_id' => 'nullable|exists:cities,id',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'security_question_1' => 'required|string|max:255',
            'security_answer_1' => 'required|string|max:255',
            'security_question_2' => 'required|string|max:255',
            'security_answer_2' => 'required|string|max:255',
            'security_question_3' => 'required|string|max:255',
            'security_answer_3' => 'required|string|max:255',
        ]);

        try {
            $profilePhotoPath = null;
            if ($request->hasFile('profile_photo')) {
                $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            $user = User::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'id_type' => $validated['id_type'] ?? null,
                'id_number' => $validated['id_number'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
                'state_id' => $validated['state_id'] ?? null,
                'municipality_id' => $validated['municipality_id'] ?? null,
                'parish_id' => $validated['parish_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'profile_photo' => $profilePhotoPath,
                'security_questions' => [
                    $validated['security_question_1'],
                    $validated['security_question_2'],
                    $validated['security_question_3'],
                ],
                'security_answer_1' => Hash::make($validated['security_answer_1']),
                'security_answer_2' => Hash::make($validated['security_answer_2']),
                'security_answer_3' => Hash::make($validated['security_answer_3']),
                'security_questions_set_at' => now(),
            ]);

            $user->assignRole($validated['role']);

            $currentUser = Auth::user();
            $userName = $currentUser ? $currentUser->full_name : 'Sistema';

            $this->logCreated($user, $userName . ' CREÓ al usuario ' . $user->full_name . ' con el rol "' . $validated['role'] . '"');

            return redirect()->route('admin.users.index')
                ->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear usuario', [
                'email' => $validated['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al crear el usuario.']);
        }
    }

    /**
     * Muestra los detalles completos de un usuario.
     *
     * Requiere permisos de acceso. Carga todas las relaciones del usuario
     * (roles, ubicacion geografica) y calcula estadisticas: propiedades,
     * citas como cliente, favoritos y leads asociados.
     *
     * @param \App\Models\User $user Usuario a mostrar (resuelto por route model binding).
     * @return \Illuminate\View\View Vista 'dashboard.admin.users.show' con el usuario y sus estadisticas.
     */
    public function show(User $user)
    {
        $this->checkAccess();
        $user->load(['roles', 'country', 'state', 'municipality', 'parish', 'userCity']);

        $stats = [
            'properties' => $user->properties()->count(),
            'appointments' => $user->appointmentsAsClient()->count(),
            'favorites' => $user->favorites()->count(),
            'leads' => $user->leads()->count(),
        ];

        return view('dashboard.admin.users.show', compact('user', 'stats'));
    }

    /**
     * Muestra el formulario de edicion de un usuario existente.
     *
     * Requiere permisos de acceso. Carga todos los roles, la lista de
     * paises y los registros geograficos (estados, municipios, parroquias,
     * ciudades) filtrados segun la ubicacion actual del usuario para
     * mantener la coherencia de los selects encadenados.
     *
     * @param \App\Models\User $user Usuario a editar (resuelto por route model binding).
     * @return \Illuminate\View\View Vista 'dashboard.admin.users.edit' con todos los datos para el formulario.
     */
    public function edit(User $user)
    {
        $this->checkAccess();
        $roles = Role::all();
        $countries = Country::orderBy('name')->get();
        $states = State::where('country_id', $user->country_id)->orderBy('name')->get();
        $municipalities = Municipality::where('state_id', $user->state_id)->orderBy('name')->get();
        $parishes = Parish::where('municipality_id', $user->municipality_id)->orderBy('name')->get();
        $cities = City::where('parish_id', $user->parish_id)->orderBy('name')->get();
        $userRole = $user->roles->first()->name ?? null;

        $isClient = $user->hasRole('Cliente');

        return view('dashboard.admin.users.edit', compact('user', 'roles', 'userRole', 'countries', 'states', 'municipalities', 'parishes', 'cities', 'isClient'));
    }

    /**
     * Actualiza los datos de un usuario existente.
     *
     * Requiere permisos de acceso. Valida los campos actualizados,
     * permite cambiar la contrasena opcionalmente (minimo 10 caracteres
     * con confirmacion). Maneja la reemplazo de la foto de perfil,
     * eliminando la anterior del disco si existe una nueva. Sincroniza
     * el rol asignado. Registra los cambios en el log de auditoria
     * comparando valores anteriores con los nuevos, mostrando solo
     * los campos que efectivamente cambiaron.
     *
     * @param \Illuminate\Http\Request $request Solicitud con los campos actualizados del usuario.
     * @param \App\Models\User $user Usuario a actualizar (resuelto por route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de usuarios con mensaje de exito o error.
     */
    public function update(Request $request, User $user)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|in:V,E,J,P',
            'id_number' => 'nullable|string|max:20|unique:users,id_number,' . $user->id,
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'city_id' => 'nullable|exists:cities,id',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $oldValues = $user->toArray();

            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'required|string|min:10|confirmed'
                ]);
                $user->password = Hash::make($request->password);
            }

            if ($request->hasFile('profile_photo')) {
                if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                $user->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            $user->update([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'id_type' => $validated['id_type'] ?? null,
                'id_number' => $validated['id_number'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
                'state_id' => $validated['state_id'] ?? null,
                'municipality_id' => $validated['municipality_id'] ?? null,
                'parish_id' => $validated['parish_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $user->syncRoles([$validated['role']]);

            $changes = [];
            $fieldLabels = [
                'name' => 'nombre',
                'last_name' => 'apellido',
                'email' => 'email',
                'phone' => 'teléfono',
                'address' => 'dirección',
                'id_type' => 'tipo de identificación',
                'id_number' => 'número de identificación',
                'is_active' => 'estado de la cuenta',
            ];

            $dirty = $user->getDirty();
            foreach ($dirty as $key => $value) {
                if ($key !== 'updated_at' && isset($oldValues[$key])) {
                    $label = $fieldLabels[$key] ?? $key;
                    $oldVal = $oldValues[$key] ?? 'vacío';
                    $newVal = $value ?? 'vacío';
                    $changes[] = "{$label}: '{$oldVal}' → '{$newVal}'";
                }
            }

            if (!empty($changes)) {
                $this->logUpdated($user, $oldValues, $changes);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al actualizar el usuario.']);
        }
    }

    /**
     * Elimina un usuario del sistema.
     *
     * Requiere permisos de acceso. Impide que un usuario se elimine
     * a si mismo para prevenir bloqueos de sesion. Antes de eliminar,
     * registra la accion en auditoria y elimina la foto de perfil
     * del disco de almacenamiento publico si existe.
     *
     * @param \App\Models\User $user Usuario a eliminar (resuelto por route model binding).
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de usuarios con mensaje de exito o error.
     */
    public function destroy(User $user)
    {
        $this->checkAccess();

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes eliminarte a ti mismo.');
        }

        try {
            $currentUser = Auth::user();
            $userName = $currentUser ? $currentUser->full_name : 'Sistema';

            $this->logDeleted($user, $userName . ' ELIMINÓ al usuario ' . $user->full_name);

            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Hubo un problema al eliminar el usuario.']);
        }
    }

    /**
     * Cambia el estado de activacion de un usuario (activo/inactivo).
     *
     * Requiere permisos de acceso. Impide que un usuario se desactive
     * a si mismo. Invierte el valor actual del campo 'is_active'. Si
     * el usuario pasa de activo a desactivado, envia un correo electronico
     * de notificacion a la direccion del usuario. Registra la accion
     * en auditoria. Soporta respuestas tanto AJAX (JSON) como tradicionales
     * (redirect) para funcionar con switches en la interfaz.
     *
     * @param \App\Models\User $user Usuario cuyo estado sera cambiado (resuelto por route model binding).
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Respuesta JSON o redirect segun el tipo de peticion.
     */
    public function toggleStatus(User $user)
    {
        $this->checkAccess();

        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'No puedes desactivarte a ti mismo.'], 403);
        }

        try {
            $wasActive = $user->is_active;
            $oldValues = $user->toArray();
            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'activado' : 'desactivado';

            $currentUser = Auth::user();
            $userName = $currentUser ? $currentUser->full_name : 'Sistema';

            $this->logAudit('updated', $user, $oldValues, $user->toArray(),
                $userName . ' ' . ($user->is_active ? 'ACTIVÓ' : 'DESACTIVÓ') . ' al usuario ' . $user->full_name
            );

            if (!$user->is_active && $wasActive) {
                try {
                    Mail::send('emails.account-deactivated', ['user' => $user], function($message) use ($user) {
                        $message->to($user->email, $user->full_name)
                                ->subject('Tu cuenta ha sido desactivada - ' . config('app.name'));
                    });
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de desactivación: ' . $e->getMessage());
                }
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Usuario {$status} exitosamente.",
                    'data' => [
                        'id' => $user->id,
                        'is_active' => $user->is_active,
                        'full_name' => $user->full_name
                    ]
                ]);
            }

            return redirect()->back()->with('success', "Usuario {$status} exitosamente.");
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            if (request()->ajax()) {
                return response()->json(['error' => 'Error al cambiar el estado del usuario.'], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Error al cambiar el estado del usuario.']);
        }
    }

    /**
     * Exporta los usuarios a un archivo CSV.
     *
     * Requiere permisos de acceso. Aplica los mismos filtros de busqueda
     * y rol que el metodo index. Limita la exportacion a 1000 registros
     * para evitar problemas de memoria. Genera un archivo CSV con columnas:
     * ID, Nombre, Apellido, Email, Telefono, Direccion, Cedula, Rol,
     * Pais, Estado, Ciudad, Estado de Cuenta y Fecha de registro.
     *
     * @param \Illuminate\Http\Request $request Solicitud con parametros opcionales de filtro ('search', 'role').
     * @return \Symfony\Component\HttpFoundation\StreamedResponse Archivo CSV como descarga.
     */
    public function export(Request $request)
    {
        $this->checkAccess();

        $query = User::query()->with(['country', 'state', 'userCity']);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                  ->orWhere('last_name', 'LIKE', $search)
                  ->orWhere('email', 'LIKE', $search)
                  ->orWhere('id_number', 'LIKE', $search)
                  ->orWhere('address', 'LIKE', $search);
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->limit(1000)->get();

        $filename = "usuarios_" . date('Y-m-d_His') . ".csv";
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['ID', 'Nombre', 'Apellido', 'Email', 'Teléfono', 'Dirección', 'Cédula', 'Rol', 'País', 'Estado', 'Ciudad', 'Estado Cuenta', 'Registrado']);

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->last_name ?? '',
                $user->email,
                $user->phone ?? '',
                $user->address ?? '',
                $user->full_id ?? '',
                $user->main_role,
                $user->country_name,
                $user->state_name,
                $user->city_name,
                $user->is_active ? 'Activo' : 'Inactivo',
                $user->created_at->format('d/m/Y H:i')
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Muestra los detalles de un usuario en un modal (vista AJAX).
     *
     * Requiere permisos de acceso. Carga las relaciones geograficas
     * y los roles del usuario para renderizar la vista parcial del modal.
     *
     * @param \App\Models\User $user Usuario a mostrar en el modal (resuelto por route model binding).
     * @return \Illuminate\View\View Vista parcial 'dashboard.admin.users.modal-show' con el usuario.
     */
    public function modalShow(User $user)
    {
        $this->checkAccess();
        $user->load(['country', 'state', 'municipality', 'parish', 'userCity', 'roles']);
        return view('dashboard.admin.users.modal-show', compact('user'));
    }

    /**
     * Muestra el formulario de edicion de un usuario en un modal (vista AJAX).
     *
     * Requiere permisos de acceso. Carga todos los roles, la lista de
     * paises y los registros geograficos filtrados segun la ubicacion
     * actual del usuario, igual que el metodo edit pero para modal.
     *
     * @param \App\Models\User $user Usuario a editar en el modal (resuelto por route model binding).
     * @return \Illuminate\View\View Vista parcial 'dashboard.admin.users.modal-edit' con los datos del formulario.
     */
    public function modalEdit(User $user)
    {
        $this->checkAccess();
        $roles = Role::all();
        $countries = Country::orderBy('name')->get();
        $states = State::where('country_id', $user->country_id)->orderBy('name')->get();
        $municipalities = Municipality::where('state_id', $user->state_id)->orderBy('name')->get();
        $parishes = Parish::where('municipality_id', $user->municipality_id)->orderBy('name')->get();
        $cities = City::where('parish_id', $user->parish_id)->orderBy('name')->get();

        $isClient = $user->hasRole('Cliente');

        return view('dashboard.admin.users.modal-edit', compact('user', 'roles', 'countries', 'states', 'municipalities', 'parishes', 'cities', 'isClient'));
    }

    /**
     * Muestra el formulario de confirmacion de eliminacion en un modal (vista AJAX).
     *
     * Requiere permisos de acceso. Renderiza la vista parcial de
     * confirmacion de eliminacion del usuario para interaccion AJAX.
     *
     * @param \App\Models\User $user Usuario a confirmar eliminacion (resuelto por route model binding).
     * @return \Illuminate\View\View Vista parcial 'dashboard.admin.users.modal-delete' con el usuario.
     */
    public function modalDelete(User $user)
    {
        $this->checkAccess();
        return view('dashboard.admin.users.modal-delete', compact('user'));
    }
}
