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

class RegisterController extends Controller
{
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

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $questions = $this->securityQuestions;
        return view('auth.register', compact('questions'));
    }

    public function register(Request $request)
    {
        Log::info(' Registro iniciado', [
            'email' => $request->email,
            'has_phone' => $request->has('phone'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'id_type' => ['nullable', 'string', 'in:V,E,J'],
            'id_number' => ['nullable', 'string', 'max:20', Rule::unique('users', 'id_number')->whereNull('deleted_at')],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'municipality_id' => ['nullable', 'integer', 'exists:municipalities,id'],
            'parish_id' => ['nullable', 'integer', 'exists:parishes,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'terms' => ['required', 'accepted'],
            'security_question_1' => ['required', 'string', 'in:' . implode(',', $this->securityQuestions)],
            'security_answer_1' => ['required', 'string', 'min:2', 'max:255'],
            'security_question_2' => ['required', 'string', 'in:' . implode(',', $this->securityQuestions)],
            'security_answer_2' => ['required', 'string', 'min:2', 'max:255'],
            'security_question_3' => ['required', 'string', 'in:' . implode(',', $this->securityQuestions)],
            'security_answer_3' => ['required', 'string', 'min:2', 'max:255'],
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
            $phone = $validated['phone'] ?? null;
            if ($phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
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
                'phone' => $phone,
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

    public function getSecurityQuestions()
    {
        return response()->json($this->securityQuestions);
    }
}
