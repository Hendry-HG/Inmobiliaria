<?php

namespace App\Http\Controllers;

use App\Models\AppointmentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Super Admin|Administrador|Asesor Inmobiliario');
    }

    /**
     * Mostrar formulario de configuración
     */
    public function index()
    {
        $settings = AppointmentSetting::getForUser(Auth::id());
        $configuredDays = $settings->getConfiguredDays();

        return view('modulos.citas.configuracion', compact('settings', 'configuredDays'));
    }

    /**
     * Actualizar configuración
     */
    public function update(Request $request)
    {
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

        $settings = AppointmentSetting::firstOrNew(['user_id' => Auth::id()]);
        $settings->user_id = Auth::id();
        $settings->is_active = $request->has('is_active');
        $settings->apply_always = $request->has('apply_always');
        $settings->valid_from = $request->valid_from;
        $settings->valid_to = $request->valid_to;
        $settings->slot_duration = $request->slot_duration;
        $settings->break_duration = $request->break_duration;
        $settings->notify_client = $request->has('notify_client');
        $settings->reminder_minutes = $request->reminder_minutes;

        // Procesar daily_config
        $dailyConfig = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            $max = (int) $request->input("daily_config.{$day}.max", 0);
            $hours = $request->input("daily_config.{$day}.hours", []);

            // Si el día está activo pero no tiene horas, asignar horas por defecto
            if ($max > 0 && empty($hours)) {
                $hours = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
            }

            $dailyConfig[$day] = [
                'max' => $max,
                'hours' => $hours,
            ];
        }

        $settings->daily_config = $dailyConfig;

        // Procesar excepciones
        $exceptions = [];
        if ($request->has('exceptions')) {
            foreach ($request->exceptions as $exception) {
                if (!empty($exception['date']) && !empty($exception['reason'])) {
                    $exceptions[] = [
                        'date' => $exception['date'],
                        'reason' => $exception['reason'],
                        'active' => false,
                    ];
                }
            }
        }
        $settings->exceptions = $exceptions;

        $settings->save();

        return redirect()->route('citas.configuracion')
            ->with('success', ' Configuración de citas actualizada correctamente.');
    }

    /**
     * Obtener slots disponibles para una fecha (API)
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'asesor_id' => 'required|exists:users,id',
        ]);

        $settings = AppointmentSetting::getForUser($request->asesor_id);
        $slots = $settings->getAvailableSlotsForDate($request->date);
        $dayName = strtolower(date('l', strtotime($request->date)));

        return response()->json([
            'success' => true,
            'slots' => $slots,
            'max_per_day' => $settings->getMaxForDay($dayName),
            'is_active' => $settings->is_active,
            'is_valid' => $settings->isValidForDate($request->date),
        ]);
    }

    /**
     * Agregar excepción (día no laborable)
     */
    public function addException(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after:today',
            'reason' => 'required|string|max:255',
        ]);

        $settings = AppointmentSetting::getForUser(Auth::id());
        $exceptions = $settings->exceptions ?? [];

        // Verificar si ya existe
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
            'reason' => $request->reason,
            'active' => false,
        ];

        $settings->exceptions = $exceptions;
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Excepción agregada correctamente.',
        ]);
    }

    /**
     * Eliminar excepción
     */
    public function removeException(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $settings = AppointmentSetting::getForUser(Auth::id());
        $exceptions = array_filter($settings->exceptions ?? [], function($exception) use ($request) {
            return $exception['date'] !== $request->date;
        });

        $settings->exceptions = array_values($exceptions);
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Excepción eliminada correctamente.'
        ]);
    }
}
