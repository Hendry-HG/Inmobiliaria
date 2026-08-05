<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSetting;
use App\Models\Appointment;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Controlador API de Configuracion de Citas
 *
 * Gestiona la configuracion de disponibilidad horaria para asesores
 * inmobiliarios. Permite definir dias laborables, horarios por dia,
 * duracion de slots, break entre citas, recordatorios y excepciones.
 * Expone endpoints para consultar slots y dias disponibles.
 *
 * @package App\Http\Controllers\Api
 */
class AppointmentSettingController extends Controller
{
    /**
     * Servicio de permisos inyectado para validacion de accesos.
     * @var \App\Services\PermissionService
     */
    protected $permissionService;

    /**
     * Constructor del controlador.
     * Aplica middleware de autenticacion e inyecta PermissionService.
     *
     * @param \App\Services\PermissionService $permissionService Servicio de verificacion de permisos
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->middleware('auth');
        $this->permissionService = $permissionService;
    }

    /**
     * Muestra la vista de configuracion de citas del asesor.
     *
     * Flujo de datos:
     * 1. Obtiene el usuario autenticado y verifica que exista
     * 2. Configura PermissionService con el usuario actual
     * 3. Verifica el permiso 'ver configuracion'
     * 4. Obtiene la configuracion de citas del usuario y sus dias configurados
     * 5. Retorna la vista de configuracion con los datos
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'No autenticado');
        }


        $this->permissionService->setUser($user);

        // VERIFICAR PERMISO CON PERMISSIONSERVICE
        if (!$this->permissionService->hasPermission('ver configuración')) {
            abort(403, 'No tienes permiso para ver la configuración de citas.');
        }

        $settings = AppointmentSetting::getForUser(Auth::id());
        $configuredDays = $settings->getConfiguredDays();

        return view('modulos.citas.configuracion', compact('settings', 'configuredDays'));
    }

    /**
     * Actualiza la configuracion de disponibilidad horaria del asesor.
     *
     * Flujo de datos:
     * 1. Valida autenticacion y permiso 'editar configuracion'
     * 2. Registra en log los datos recibidos del formulario
     * 3. Valida todos los campos: estado activo, fecha de validez, config diaria,
     *    duracion de slots, break, notificaciones y excepciones
     * 4. Crea o actualiza el registro AppointmentSetting del usuario
     * 5. Procesa la configuracion diaria (lunes a domingo) con horas y maximo
     * 6. Procesa las excepciones de fechas no disponibles
     * 7. Invalida el cache de configuracion del usuario
     * 8. Redirige con mensaje de exito o error
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }


        $this->permissionService->setUser($user);

        // VERIFICAR PERMISO CON PERMISSIONSERVICE
        if (!$this->permissionService->hasPermission('editar configuración')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar la configuración de citas.'
            ], 403);
        }

        Log::info('Configuración recibida', [
            'user_id' => Auth::id(),
            'daily_config' => $request->input('daily_config'),
            'all_data' => $request->all()
        ]);

        $request->validate([
            'is_active' => 'nullable|boolean',
            'apply_always' => 'nullable|boolean',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'daily_config' => 'nullable|array',
            'daily_config.*.max' => 'nullable|integer|min:0|max:50',
            'daily_config.*.hours' => 'nullable|array',
            'daily_config.*.hours.*' => 'string|date_format:H:i',
            'slot_duration' => 'required|integer|min:15|max:180',
            'break_duration' => 'required|integer|min:0|max:60',
            'notify_client' => 'nullable|boolean',
            'reminder_minutes' => 'required|integer|min:15|max:1440',
            'exceptions' => 'nullable|array',
            'exceptions.*.date' => 'nullable|date',
            'exceptions.*.reason' => 'nullable|string|max:255',
        ]);

        try {
            $settings = AppointmentSetting::firstOrNew(['user_id' => Auth::id()]);
            $settings->user_id = Auth::id();
            $settings->is_active = $request->boolean('is_active');
            $settings->apply_always = $request->boolean('apply_always');
            $settings->valid_from = $request->valid_from;
            $settings->valid_to = $request->valid_to;
            $settings->slot_duration = (int) $request->slot_duration;
            $settings->break_duration = (int) $request->break_duration;
            $settings->notify_client = $request->boolean('notify_client');
            $settings->reminder_minutes = (int) $request->reminder_minutes;

            $dailyConfig = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            foreach ($days as $day) {
                $max = (int) $request->input("daily_config.{$day}.max", 0);
                $hours = $request->input("daily_config.{$day}.hours", []);

                if (is_string($hours)) {
                    $hours = explode(',', $hours);
                }

                if (is_array($hours)) {
                    $hours = array_filter($hours, function($h) {
                        return !empty($h) && is_string($h);
                    });
                    sort($hours);
                } else {
                    $hours = [];
                }

                $dailyConfig[$day] = [
                    'max' => $max,
                    'hours' => array_values($hours),
                ];
            }

            $settings->daily_config = $dailyConfig;

            $exceptions = [];
            if ($request->has('exceptions')) {
                foreach ($request->exceptions as $exception) {
                    if (!empty($exception['date']) && !empty($exception['reason'])) {
                        $exceptions[] = [
                            'date' => $exception['date'],
                            'reason' => strip_tags(trim($exception['reason'])),
                            'active' => false,
                        ];
                    }
                }
            }
            $settings->exceptions = $exceptions;

            $settings->save();

            Log::info('Configuración guardada', [
                'user_id' => Auth::id(),
                'daily_config' => $dailyConfig,
                'hours_saved' => $dailyConfig['monday']['hours'] ?? []
            ]);

            Cache::forget("appointment_settings_{$settings->user_id}");

            return redirect()->route('citas.configuracion')
                ->with('success', 'Configuración de citas actualizada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración de citas', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar la configuración: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los slots de horarios disponibles para una fecha especifica.
     *
     * Flujo de datos:
     * 1. Valida que la fecha sea hoy o futura y que el asesor exista
     * 2. Obtiene la configuracion del asesor (crea defaults si no existe)
     * 3. Verifica que la configuracion este activa
     * 4. Valida que la fecha este dentro del rango de validez
     * 5. Verifica que la fecha no sea una excepcion (dia no laborable)
     * 6. Calcula los slots disponibles segun horarios y duracion configurada
     * 7. Retorna JSON con slots, maximo por dia y estado de disponibilidad
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableSlots(Request $request)
    {
        //  PÚBLICO PARA USUARIOS AUTENTICADOS
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'asesor_id' => 'required|exists:users,id',
        ]);

        try {
            $settings = AppointmentSetting::getForUser($request->asesor_id);

            if (!$settings) {
                $settings = $this->createDefaultSettings($request->asesor_id);
            }

            if (!$settings->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El asesor no está disponible para citas en este momento.',
                    'slots' => [],
                    'max_per_day' => 0,
                    'is_active' => false
                ]);
            }

            if (!$settings->isValidForDate($request->date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El asesor no tiene agenda disponible para esta fecha.',
                    'slots' => [],
                    'max_per_day' => 0,
                    'is_valid' => false
                ]);
            }

            if ($settings->isException($request->date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El asesor no está disponible en esta fecha (día no laborable).',
                    'slots' => [],
                    'is_exception' => true
                ]);
            }

            $slots = $settings->getAvailableSlotsForDate($request->date);
            $dayName = strtolower(date('l', strtotime($request->date)));
            $maxPerDay = $settings->getMaxForDay($dayName);

            return response()->json([
                'success' => true,
                'slots' => $slots,
                'max_per_day' => $maxPerDay,
                'is_active' => $settings->is_active,
                'is_valid' => $settings->isValidForDate($request->date),
                'is_exception' => $settings->isException($request->date),
                'day_name' => $dayName
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener slots disponibles', [
                'user_id' => $request->asesor_id,
                'date' => $request->date,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los horarios disponibles.',
                'slots' => []
            ], 500);
        }
    }

    /**
     * Obtiene el calendario de dias disponibles para un mes y anio especifico.
     *
     * Flujo de datos:
     * 1. Valida year, month y asesor_id
     * 2. Verifica que el asesor exista y este activo
     * 3. Obtiene la configuracion del asesor (crea defaults si no existe)
     * 4. Itera cada dia del mes verificando disponibilidad:
     *    - Fecha futura o actual
     *    - Configuracion activa
     *    - Fecha dentro del rango de validez
     *    - No es excepcion
     * 5. Para cada dia disponible calcula los slots y el maximo
     * 6. Retorna JSON con el calendario completo del mes
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableDays(Request $request)
    {
        //  PÚBLICO PARA USUARIOS AUTENTICADOS
        $request->validate([
            'year' => 'required|integer|min:2024|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'asesor_id' => 'required|exists:users,id',
        ]);

        try {
            // Verificar que el asesor existe
            $asesor = User::find($request->asesor_id);
            if (!$asesor || !$asesor->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El asesor no está disponible.'
                ], 404);
            }

            $settings = AppointmentSetting::getForUser($request->asesor_id);

            // Si no tiene configuración, crear una por defecto
            if (!$settings) {
                $settings = $this->createDefaultSettings($request->asesor_id);
            }

            $year = $request->year;
            $month = $request->month;
            $today = date('Y-m-d');
            $daysInMonth = date('t', strtotime("{$year}-{$month}-01"));

            $availableDays = [];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = date('Y-m-d', strtotime("{$year}-{$month}-{$day}"));
                $dayName = strtolower(date('l', strtotime($date)));

                $isAvailable = false;
                $availableHours = [];
                $maxPerDay = 0;

                if ($date >= $today && $settings->is_active && $settings->isValidForDate($date) && !$settings->isException($date)) {
                    $maxPerDay = $settings->getMaxForDay($dayName);
                    if ($maxPerDay > 0) {
                        $slots = $settings->getAvailableSlotsForDate($date);
                        if (!empty($slots)) {
                            $isAvailable = true;
                            $availableHours = $slots;
                        }
                    }
                }

                $availableDays[] = [
                    'date' => $date,
                    'day' => $day,
                    'day_name' => $dayName,
                    'is_available' => $isAvailable,
                    'slots' => $availableHours,
                    'max_per_day' => $maxPerDay,
                    'is_exception' => $settings->isException($date),
                    'is_past' => $date < $today,
                ];
            }

            return response()->json([
                'success' => true,
                'days' => $availableDays,
                'year' => $year,
                'month' => $month,
                'total_days' => $daysInMonth,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener días disponibles', [
                'user_id' => $request->asesor_id,
                'year' => $request->year,
                'month' => $request->month,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los días disponibles.',
                'days' => []
            ], 500);
        }
    }

    /**
     * Agrega una excepcion de fecha no disponible a la configuracion.
     *
     * Flujo de datos:
     * 1. Valida autenticacion y permiso 'editar configuracion'
     * 2. Valida que la fecha sea futura y que la razon sea obligatoria
     * 3. Obtiene la configuracion del asesor (crea defaults si no existe)
     * 4. Verifica que no exista ya una excepcion para esa fecha
     * 5. Agrega la excepcion con estado inactivo por defecto
     * 6. Guarda la configuracion y invalida el cache
     * 7. Retorna JSON con resultado de la operacion
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addException(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }


        $this->permissionService->setUser($user);

        // VERIFICAR PERMISO CON PERMISSIONSERVICE
        if (!$this->permissionService->hasPermission('editar configuración')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para agregar excepciones.'
            ], 403);
        }

        $request->validate([
            'date' => 'required|date|after:today',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $settings = AppointmentSetting::getForUser(Auth::id());

            if (!$settings) {
                $settings = $this->createDefaultSettings(Auth::id());
            }

            $exceptions = $settings->exceptions ?? [];

            foreach ($exceptions as $key => $exception) {
                if ($exception['date'] === $request->date) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una excepción para esta fecha.'
                    ], 400);
                }
            }

            $exceptions[] = [
                'date' => $request->date,
                'reason' => strip_tags(trim($request->reason)),
                'active' => false,
            ];

            $settings->exceptions = $exceptions;
            $settings->save();

            Cache::forget("appointment_settings_{$settings->user_id}");

            return response()->json([
                'success' => true,
                'message' => 'Excepción agregada correctamente.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al agregar excepción', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar la excepción.'
            ], 500);
        }
    }

    /**
     * Elimina una excepcion de fecha de la configuracion del asesor.
     *
     * Flujo de datos:
     * 1. Valida autenticacion y permiso 'editar configuracion'
     * 2. Valida que el campo date sea obligatorio
     * 3. Obtiene la configuracion del asesor
     * 4. Filtra las excepciones eliminando la que coincida con la fecha
     * 5. Guarda la configuracion actualizada y invalida el cache
     * 6. Retorna JSON con resultado de la operacion
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeException(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        //  ACTUALIZAR EL PERMISSION SERVICE CON EL USUARIO ACTUAL
        $this->permissionService->setUser($user);

        // VERIFICAR PERMISO CON PERMISSIONSERVICE
        if (!$this->permissionService->hasPermission('editar configuración')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar excepciones.'
            ], 403);
        }

        $request->validate([
            'date' => 'required|date',
        ]);

        try {
            $settings = AppointmentSetting::getForUser(Auth::id());

            if (!$settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay configuración para este usuario.'
                ], 404);
            }

            $exceptions = array_filter($settings->exceptions ?? [], function($exception) use ($request) {
                return $exception['date'] !== $request->date;
            });

            $settings->exceptions = array_values($exceptions);
            $settings->save();

            Cache::forget("appointment_settings_{$settings->user_id}");

            return response()->json([
                'success' => true,
                'message' => 'Excepción eliminada correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar excepción', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la excepción.'
            ], 500);
        }
    }

    /**
     * Crea una configuracion por defecto para un asesor sin configuracion previa.
     *
     * Flujo de datos:
     * 1. Utiliza firstOrCreate para evitar duplicados
     * 2. Establece valores por defecto: activo, slot de 30min, break de 5min,
     *    recordatorio de 60min, lunes a viernes de 9:00 a 15:00 (5 slots por dia)
     * 3. Fines de semana sin disponibilidad (max: 0)
     * 4. Invalida el cache de configuracion del usuario
     *
     * @param int $userId ID del usuario asesor
     * @return \App\Models\AppointmentSetting Configuracion creada o existente
     */
    private function createDefaultSettings($userId)
    {
        $settings = AppointmentSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'is_active' => true,
                'apply_always' => true,
                'slot_duration' => 30,
                'break_duration' => 5,
                'notify_client' => true,
                'reminder_minutes' => 60,
                'daily_config' => [
                    'monday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'tuesday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'wednesday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'thursday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'friday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'saturday' => ['max' => 0, 'hours' => []],
                    'sunday' => ['max' => 0, 'hours' => []],
                ],
                'exceptions' => []
            ]
        );

        Cache::forget("appointment_settings_{$userId}");

        return $settings;
    }
}
