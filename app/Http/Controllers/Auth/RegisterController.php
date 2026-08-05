<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Controlador de registro de nuevos usuarios.
 *
 * Flujo de registro:
 * 1. Se muestra el formulario con las 20 preguntas de seguridad disponibles.
 * 2. El usuario completa sus datos personales, credenciales y selecciona
 *    3 preguntas de seguridad con sus respectivas respuestas.
 * 3. Se valida que las 3 preguntas seleccionadas sean diferentes entre si.
 * 4. Las respuestas de seguridad se almacenan hasheadas con Hash::make()
 *    (mismo algoritmo que las contrasenas) para protegerlas en base de datos.
 * 5. Las preguntas de seguridad se guardan en formato JSON en el campo
 *    'security_questions' del usuario.
 * 6. Se crea el usuario con estado activo (is_active = true).
 * 7. Se asigna el rol 'Cliente' al usuario recien creado.
 * 8. Se inicia sesion automaticamente con Auth::login() y se redirige al
 *    dashboard de cliente.
 *
 * Preguntas de seguridad:
 * - Se utilizan para la recuperacion de cuenta y verificacion de identidad.
 * - El usuario debe responder exactamente 3 preguntas diferentes.
 * - Las respuestas se almacenan hasheadas, por lo que no se pueden recuperar
 *   en texto plano, solo pueden ser verificadas con Hash::check().
 * - La fecha de configuracion de las preguntas se registra en 'security_questions_set_at'.
 *
 * Gestion de sesiones:
 * - Antes del login automatico post-registro, se regenera el ID de sesion y
 *   el token CSRF para prevenir session fixation.
 * - Despues del login, se regenera la sesion nuevamente para garantizar un
 *   identificador de sesion unico y seguro.
 *
 * Manejo de errores:
 * - Los errores de base de datos (QueryException) se analizan para detectar
 *   entradas duplicadas (email, numero de identificacion) y mostrar mensajes
 *   especificos al usuario.
 * - Los errores generales se registran en el log de Laravel con nivel error
 *   incluyendo archivo, linea y mensaje del error.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterController extends Controller
{
    /**
     * Lista de preguntas de seguridad disponibles para el registro.
     *
     * Contiene 20 preguntas personales que el usuario puede seleccionar.
     * Estas preguntas se utilizan para la verificacion de identidad en
     * procesos de recuperacion de cuenta. Cada pregunta es una cadena
     * en espanol que representa una pregunta personal comun.
     *
     * @var array<string>
     */
    private $securityQuestions = [
        '¿Cuál es el nombre de tu primera mascota?',
        '¿Cuál es el apellido de soltera de tu madre?',
        '¿En qué ciudad naciste?',
        '¿Cuál es tu comida favorita?',
        '¿Cuál es el nombre de tu mejor amigo de la infancia?',
        '¿Cuál es el título de tu libro favorito?',
        '¿Cuál es el nombre de tu profesor favorito?',
        '¿En qué año te graduaste de la escuela?',
        '¿Cuál es el nombre de tu primer amor?',
        '¿Cuál es el nombre de tu abuelo favorito?',
        '¿Cuál es el nombre de tu hijo/a?',
        '¿Cuál es el nombre de tu padre?',
        '¿Cuál es el modelo de tu primer auto?',
        '¿Cuál es el nombre de tu mejor amigo?',
        '¿Cuál es tu color favorito?',
        '¿Cuál es tu deporte favorito?',
        '¿Cuál es el nombre de tu primera escuela?',
        '¿Cuál es el nombre de tu primer jefe?',
        '¿Cuál es tu lugar favorito para vacacionar?',
        '¿Cuál es el nombre de tu tío favorito?',
    ];

    /**
     * Crea una nueva instancia del controlador.
     *
     * Aplica el middleware 'guest' a todas las rutas, lo que garantiza que
     * solo los usuarios no autenticados puedan acceder al formulario y
     * proceso de registro. Los usuarios que ya tienen sesion activa seran
     * redirigidos automaticamente a su dashboard.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Muestra el formulario de registro de nuevo usuario.
     *
     * Pasa la lista completa de preguntas de seguridad a la vista para que
     * el usuario pueda seleccionar 3 de ellas. La vista renderiza un formulario
     * con campos para datos personales, credenciales y las preguntas de seguridad.
     *
     * @return \Illuminate\View\View Vista del formulario de registro con las preguntas de seguridad.
     */
    public function showRegistrationForm()
    {
        $questions = $this->securityQuestions;
        $phoneCountries = \App\Models\Country::whereNotNull('phone_code')
            ->where('phone_code', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'phone_code', 'phone_format', 'phone_min_length', 'phone_max_length']);

        return view('auth.register', compact('questions', 'phoneCountries'));
    }

    /**
     * Procesa el registro de un nuevo usuario en el sistema.
     *
     * Flujo completo del registro:
     *
     * Fase 1 - Validacion:
     * 1. Valida todos los campos del formulario incluyendo datos personales
     *    (name, last_name, email, password, phone, address), datos de ubicacion
     *    (country, state, municipality, parish, city), documento de identidad
     *    (id_type, id_number), aceptacion de terminos, y las 3 preguntas de
     *    seguridad con sus respuestas.
     * 2. Valida unicidad de email e id_number (excluyendo registros soft-deleted).
     * 3. Verifica que las 3 preguntas de seguridad seleccionadas sean diferentes.
     *
     * Fase 2 - Creacion:
     * 1. Limpia el telefono eliminando caracteres no numericos.
     * 2. Hashea las 3 respuestas de seguridad con Hash::make().
     * 3. Crea el usuario en base de datos con:
     *    - Todos los campos validados.
     *    - Contrasena hasheada.
     *    - Preguntas de seguridad en formato JSON.
     *    - Respuestas de seguridad hasheadas.
     *    - Estado activo (is_active = true).
     *    - Fecha de configuracion de preguntas de seguridad.
     *
     * Fase 3 - Asignacion de rol:
     * 1. Busca o crea el rol 'Cliente' con guard 'web'.
     * 2. Asigna el rol al usuario recien creado.
     *
     * Fase 4 - Login automatico:
     * 1. Regenera el ID de sesion y token CSRF (prevencion de session fixation).
     * 2. Inicia sesion con Auth::login().
     * 3. Regenera la sesion nuevamente post-login.
     * 4. Redirige al dashboard de cliente con mensaje de bienvenida.
     *
     * Manejo de errores:
     * - QueryException: Analiza errores de duplicidad (email, id_number) y
     *   muestra mensajes especificos. Otros errores de BD muestran mensaje generico.
     * - Exception: Registra error completo en log (archivo, linea, mensaje)
     *   y muestra mensaje generico al usuario.
     *
     * @param  \Illuminate\Http\Request $request Solicitud HTTP con todos los campos del formulario.
     * @return \Illuminate\Http\RedirectResponse Redireccion al dashboard de cliente o atras con errores.
     */
    public function register(Request $request)
    {
        Log::info(' Registro iniciado', [
            'email' => $request->email,
            'has_phone' => $request->has('phone'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/', 'max:20'],
            'phone_code' => ['required', 'string', 'regex:/^\+\d{1,4}$/', 'max:6'],
            'address' => ['required', 'string', 'max:500'],
            'id_type' => ['required', 'string', 'in:V,E,J'],
            'id_number' => ['required', 'string', 'max:20', Rule::unique('users', 'id_number')->whereNull('deleted_at')],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'municipality_id' => ['required', 'integer', 'exists:municipalities,id'],
            'parish_id' => ['required', 'integer', 'exists:parishes,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'terms' => ['required', 'accepted'],
            'security_question_1' => ['required', 'string', 'in:' . implode(',', $this->securityQuestions)],
            'security_answer_1' => ['required', 'string', 'min:2', 'max:255'],
            'security_question_2' => ['required', 'string', 'in:' . implode(',', $this->securityQuestions)],
            'security_answer_2' => ['required', 'string', 'min:2', 'max:255'],
            'security_question_3' => ['required', 'string', 'in:' . implode(',', $this->securityQuestions)],
            'security_answer_3' => ['required', 'string', 'min:2', 'max:255'],
        ], [
            'email.unique' => 'Este correo ya está en uso por otro usuario.',
            'id_number.unique' => 'Este número de identificación ya está en uso por otro usuario.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.regex' => 'El teléfono debe contener entre 7 y 15 dígitos.',
            'phone_code.regex' => 'El código de país no es válido.',
            'last_name.required' => 'El apellido es obligatorio.',
            'id_type.required' => 'Selecciona el tipo de identificación.',
            'id_number.required' => 'El número de identificación es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'country_id.required' => 'Selecciona un país.',
            'state_id.required' => 'Selecciona un estado.',
            'municipality_id.required' => 'Selecciona un municipio.',
            'parish_id.required' => 'Selecciona una parroquia.',
            'city_id.required' => 'Selecciona una ciudad.',
        ]);

        $questions = [
            $validated['security_question_1'],
            $validated['security_question_2'],
            $validated['security_question_3']
        ];

        if (count(array_unique($questions)) < 3) {
            Log::warning(' Preguntas de seguridad duplicadas', ['email' => $validated['email']]);
            return redirect()->back()
                ->withInput()
                ->withErrors(['security_question' => 'Debes seleccionar 3 preguntas de seguridad diferentes.']);
        }

        try {
            $phone = preg_replace('/[^0-9]/', '', $validated['phone_code']) . $validated['phone'];

            $phoneExists = User::whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', ''), '.', ''), '(', ''), ')', '') = ?", [$phone])
                ->exists();

            if ($phoneExists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['phone' => 'Este teléfono ya está en uso por otro usuario.']);
            }

            $hashedAnswers = [
                Hash::make($validated['security_answer_1']),
                Hash::make($validated['security_answer_2']),
                Hash::make($validated['security_answer_3'])
            ];

            $user = User::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => '+' . $phone,
                'address' => $validated['address'] ?? null,
                'id_type' => $validated['id_type'] ?? null,
                'id_number' => $validated['id_number'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
                'state_id' => $validated['state_id'] ?? null,
                'municipality_id' => $validated['municipality_id'] ?? null,
                'parish_id' => $validated['parish_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'is_active' => true,
                'security_questions' => json_encode($questions, JSON_UNESCAPED_UNICODE),
                'security_answer_1' => $hashedAnswers[0],
                'security_answer_2' => $hashedAnswers[1],
                'security_answer_3' => $hashedAnswers[2],
                'security_questions_set_at' => now(),
            ]);

            Log::info(' Usuario creado', ['user_id' => $user->id, 'email' => $user->email]);

            $clienteRole = Role::firstOrCreate(
                ['name' => 'Cliente', 'guard_name' => 'web']
            );
            $user->assignRole($clienteRole);

            //  REGENERAR SESIÓN Y TOKEN ANTES DE LOGIN
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            Auth::login($user);

            //  REGENERAR SESIÓN DESPUÉS DE LOGIN
            $request->session()->regenerate();

            Log::info(' Registro completado', ['user_id' => $user->id]);

            return redirect()->route('cliente.dashboard')
                ->with('success', '¡Bienvenido ' . $user->full_name . '!');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error(' Error DB en registro', [
                'email' => $validated['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            $errorMessage = 'Hubo un problema al crear la cuenta. ';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                if (str_contains($e->getMessage(), 'id_number')) {
                    $errorMessage = 'El número de identificación ya está registrado.';
                } elseif (str_contains($e->getMessage(), 'email')) {
                    $errorMessage = 'El correo electrónico ya está registrado.';
                } else {
                    $errorMessage .= 'Datos duplicados.';
                }
            } else {
                $errorMessage .= 'Por favor, intenta nuevamente.';
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);

        } catch (\Exception $e) {
            Log::error(' Error general en registro', [
                'email' => $validated['email'] ?? 'unknown',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al crear la cuenta. Por favor, intenta nuevamente.']);
        }
    }

    /**
     * Devuelve la lista de preguntas de seguridad en formato JSON.
     *
     * Endpoint util para solicitudes AJAX que necesiten obtener las preguntas
     * de seguridad disponibles sin recargar la pagina completa. Retorna el
     * array completo de 20 preguntas como una respuesta JSON.
     *
     * @return \Illuminate\Http\JsonResponse Lista de preguntas de seguridad en formato JSON.
     */
    public function getSecurityQuestions()
    {
        return response()->json($this->securityQuestions);
    }

    /**
     * Verifica en vivo si un campo del formulario de registro ya está en uso.
     *
     * Endpoint público usado por el formulario de registro para avisar al
     * usuario (sin recargar la página) cuando el correo, el número de
     * identificación o el teléfono ya pertenecen a otra cuenta. Acepta
     * 'field' (email|id_number|phone) y 'value'.
     *
     * @param  \Illuminate\Http\Request $request Solicitud con 'field' y 'value'.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la disponibilidad del valor.
     */
    public function checkUnique(Request $request)
    {
        $field = $request->input('field');
        $value = trim((string) $request->input('value'));

        if (!in_array($field, ['email', 'id_number', 'phone'], true)) {
            return response()->json(['available' => true]);
        }

        if ($value === '') {
            return response()->json(['available' => true]);
        }

        $query = User::query();

        if ($field === 'email') {
            $query->whereRaw('LOWER(email) = ?', [mb_strtolower($value)]);
        } elseif ($field === 'id_number') {
            $query->whereRaw('LOWER(id_number) = ?', [mb_strtolower($value)]);
        } else {
            $query->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', ''), '.', ''), '(', ''), ')', '') = ?", [preg_replace('/[^0-9]/', '', $value)]);
        }

        return response()->json(['available' => !$query->exists()]);
    }
}
