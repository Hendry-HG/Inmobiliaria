<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSetting;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AppointmentSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Super Admin|Administrador|Asesor Inmobiliario');
    }

    public function index()
    {
        $settings = AppointmentSetting::getForUser(Auth::id());
        $configuredDays = $settings->getConfiguredDays();

        return view('modulos.citas.configuracion', compact('settings', 'configuredDays'));
    }

    public function update(Request $request)
    {
        Log::info('📥 Configuración recibida', [
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
            $settings->is_active = $request->has('is_active');
            $settings->apply_always = $request->has('apply_always');
            $settings->valid_from = $request->valid_from;
            $settings->valid_to = $request->valid_to;
            $settings->slot_duration = (int) $request->slot_duration;
            $settings->break_duration = (int) $request->break_duration;
            $settings->notify_client = $request->has('notify_client');
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

            Log::info('✅ Configuración guardada', [
                'user_id' => Auth::id(),
                'daily_config' => $dailyConfig,
                'hours_saved' => $dailyConfig['monday']['hours'] ?? []
            ]);

            Cache::forget("appointment_settings_{$settings->user_id}");

            return redirect()->route('citas.configuracion')
                ->with('success', '✅ Configuración de citas actualizada correctamente.');

        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar configuración de citas', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar la configuración: ' . $e->getMessage());
        }
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'asesor_id' => 'required|exists:users,id',
        ]);

        try {
            $settings = AppointmentSetting::getForUser($request->asesor_id);
            
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

    public function getAvailableDays(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2024|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'asesor_id' => 'required|exists:users,id',
        ]);

        try {
            $settings = AppointmentSetting::getForUser($request->asesor_id);
            
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

    public function addException(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after:today',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $settings = AppointmentSetting::getForUser(Auth::id());
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

    public function removeException(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        try {
            $settings = AppointmentSetting::getForUser(Auth::id());
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
}