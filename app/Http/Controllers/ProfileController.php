<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Perfil de Usuario
 *
 * Gestiona la visualizacion y actualizacion del perfil del usuario autenticado.
 * Permite modificar datos personales, informacion de contacto, redes sociales,
 * foto de perfil y contrasena. Adapta las estadisticas mostradas segun el
 * rol del usuario (Admin, Asesor, Cliente, Auditor).
 *
 * @package App\Http\Controllers
 */
class ProfileController extends Controller
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
     * Muestra la vista del perfil del usuario autenticado.
     *
     * Flujo de datos:
     * 1. Obtiene el usuario autenticado desde Auth
     * 2. Carga relaciones geograficas (pais, estado, municipio, parroquia, ciudad)
     * 3. Consulta los roles asignados al usuario desde model_has_roles
     * 4. Determina el rol principal segun jerarquia: Super Admin > Admin > Asesor > Auditor > Cliente
     * 5. Carga catalogos geograficos filtrados por la ubicacion actual del usuario
     * 6. Obtiene estadisticas personalizadas segun el rol
     * 7. Retorna la vista del perfil con todos los datos compactados
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $user = User::find($user->id);
        $user->load(['country', 'state', 'municipality', 'parish', 'userCity']);

        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();

        $mainRole = 'Cliente';
        if (in_array('Super Admin', $userRoles)) {
            $mainRole = 'Super Admin';
        } elseif (in_array('Administrador', $userRoles)) {
            $mainRole = 'Administrador';
        } elseif (in_array('Asesor Inmobiliario', $userRoles)) {
            $mainRole = 'Asesor Inmobiliario';
        } elseif (in_array('Auditor', $userRoles)) {
            $mainRole = 'Auditor';
        }

        $countries = Country::orderBy('name')->get();
        $states = State::where('country_id', $user->country_id)->orderBy('name')->get();
        $municipalities = Municipality::where('state_id', $user->state_id)->orderBy('name')->get();
        $parishes = Parish::where('municipality_id', $user->municipality_id)->orderBy('name')->get();
        $cities = City::where('parish_id', $user->parish_id)->orderBy('name')->get();

        $stats = $this->getUserStats($user, $mainRole);
        $socialLinks = $user->social_links ?? [];

        return view('modulos.perfil.perfil', compact(
            'user',
            'mainRole',
            'countries',
            'states',
            'municipalities',
            'parishes',
            'cities',
            'stats',
            'socialLinks'
        ));
    }

    /**
     * Actualiza los datos del perfil del usuario autenticado.
     *
     * Flujo de datos:
     * 1. Valida todos los campos del formulario (nombre, telefono, cedula, ubicacion, etc.)
     * 2. Si se proporciono contrasena nueva, verifica la contrasena actual con Hash::check
     * 3. Si se subio foto de perfil, elimina la anterior del disco y almacena la nueva
     * 4. Procesa redes sociales (whatsapp, instagram, facebook, tiktok, telegram, linkedin, twitter)
     * 5. Asigna todos los campos validados al modelo y guarda en base de datos
     * 6. Redirige al perfil con mensaje de exito
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $user = User::find($user->id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',  //  NUEVO CAMPO
            'phone' => 'nullable|string|max:20',
            'id_type' => 'nullable|string|in:V,E,J,P',
            'id_number' => 'nullable|string|max:20|unique:users,id_number,' . $user->id,
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'city_id' => 'nullable|exists:cities,id',
            'bio' => 'nullable|string|max:500',
            'specialization' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:100',
            'telegram' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:100',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
            }
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $socialLinks = [];
        if ($request->filled('whatsapp')) {
            $whatsapp = preg_replace('/[^0-9]/', '', $request->whatsapp);
            $socialLinks['whatsapp'] = $whatsapp;
        }
        if ($request->filled('instagram')) {
            $instagram = ltrim($request->instagram, '@');
            $socialLinks['instagram'] = $instagram;
        }
        if ($request->filled('facebook')) {
            $socialLinks['facebook'] = $request->facebook;
        }
        if ($request->filled('tiktok')) {
            $tiktok = ltrim($request->tiktok, '@');
            $socialLinks['tiktok'] = $tiktok;
        }
        if ($request->filled('telegram')) {
            $telegram = ltrim($request->telegram, '@');
            $socialLinks['telegram'] = $telegram;
        }
        if ($request->filled('linkedin')) {
            $socialLinks['linkedin'] = $request->linkedin;
        }
        if ($request->filled('twitter')) {
            $twitter = ltrim($request->twitter, '@');
            $socialLinks['twitter'] = $twitter;
        }

        $user->name = $validated['name'];
        $user->last_name = $validated['last_name'] ?? null;  //  NUEVO CAMPO
        $user->phone = $validated['phone'] ?? null;
        $user->id_type = $validated['id_type'] ?? null;
        $user->id_number = $validated['id_number'] ?? null;
        $user->country_id = $validated['country_id'] ?? null;
        $user->state_id = $validated['state_id'] ?? null;
        $user->municipality_id = $validated['municipality_id'] ?? null;
        $user->parish_id = $validated['parish_id'] ?? null;
        $user->city_id = $validated['city_id'] ?? null;
        $user->bio = $validated['bio'] ?? null;
        $user->specialization = $validated['specialization'] ?? null;
        $user->social_links = !empty($socialLinks) ? $socialLinks : null;

        $user->save();

        return redirect()->route('profile.index')
            ->with('success', 'Perfil actualizado exitosamente.');
    }

    /**
     * Calcula estadisticas personalizadas del usuario segun su rol.
     *
     * Flujo de datos:
     * 1. Segun el rol principal, ejecuta consultas especificas:
     *    - Super Admin / Administrador: total usuarios, propiedades, propiedades activas
     *    - Asesor Inmobiliario: propiedades propias, publicadas, citas asignadas, pendientes
     *    - Cliente: favoritos, citas solicitadas, citas pendientes
     *    - Auditor: total de registros de auditoria, auditorias recientes
     * 2. Agrega fecha de membresia y antiguedad de la cuenta en dias
     * 3. Retorna array con todas las estadisticas calculadas
     *
     * @param \App\Models\User $user Usuario del cual se calcularan las estadisticas
     * @param string $mainRole Rol principal del usuario
     * @return array Estadisticas del usuario
     */
    private function getUserStats($user, $mainRole)
    {
        $stats = [];

        switch ($mainRole) {
            case 'Super Admin':
            case 'Administrador':
                $stats = [
                    'total_users' => User::count(),
                    'total_properties' => \App\Models\Property::count(),
                    'active_properties' => \App\Models\Property::where('status', 'publicada')->count(),
                ];
                break;

            case 'Asesor Inmobiliario':
                $stats = [
                    'my_properties' => \App\Models\Property::where('user_id', $user->id)->count(),
                    'published_properties' => \App\Models\Property::where('user_id', $user->id)->where('status', 'publicada')->count(),
                    'total_appointments' => \App\Models\Appointment::where('asesor_id', $user->id)->count(),
                    'pending_appointments' => \App\Models\Appointment::where('asesor_id', $user->id)->where('status', 'pending')->count(),
                ];
                break;

            case 'Cliente':
                $stats = [
                    'favorites' => $user->favorites()->count(),
                    'appointments' => \App\Models\Appointment::where('user_id', $user->id)->count(),
                    'pending_appointments' => \App\Models\Appointment::where('user_id', $user->id)->where('status', 'pending')->count(),
                ];
                break;

            case 'Auditor':
                try {
                    $stats = [
                        'total_audits' => DB::table('audit_logs')->count(),
                        'recent_audits' => DB::table('audit_logs')->latest()->limit(5)->count(),
                    ];
                } catch (\Exception $e) {
                    $stats = [
                        'total_audits' => 0,
                        'recent_audits' => 0,
                    ];
                }
                break;

            default:
                $stats = [];
        }

        $stats['member_since'] = $user->created_at->format('d/m/Y');
        $stats['account_age'] = $user->created_at->diffInDays(now());

        return $stats;
    }
}
