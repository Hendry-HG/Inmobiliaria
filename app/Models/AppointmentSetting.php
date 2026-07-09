<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_active',
        'daily_config',
        'valid_from',
        'valid_to',
        'apply_always',
        'slot_duration',
        'break_duration',
        'notify_client',
        'reminder_minutes',
        'exceptions',
    ];

    protected $casts = [
        'daily_config' => 'array',
        'exceptions' => 'array',
        'is_active' => 'boolean',
        'apply_always' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'notify_client' => 'boolean',
    ];

    /**
     * Relación con el usuario (asesor)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la configuración de un asesor
     */
    public static function getForUser($userId)
    {
        $setting = self::where('user_id', $userId)->first();

        if (!$setting) {
            $defaultDays = [
                'monday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']],
                'tuesday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']],
                'wednesday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']],
                'thursday' => ['max' => 5, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']],
                'friday' => ['max' => 4, 'hours' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                'saturday' => ['max' => 0, 'hours' => []],
                'sunday' => ['max' => 0, 'hours' => []],
            ];

            $setting = self::create([
                'user_id' => $userId,
                'is_active' => true,
                'daily_config' => $defaultDays,
                'valid_from' => null,
                'valid_to' => null,
                'apply_always' => true,
                'slot_duration' => 60,
                'break_duration' => 15,
                'notify_client' => true,
                'reminder_minutes' => 60,
                'exceptions' => [],
            ]);
        }

        return $setting;
    }

    /**
     * Verificar si la configuración es válida para una fecha específica
     */
    public function isValidForDate($date)
    {
        // Si apply_always es true, siempre válido
        if ($this->apply_always) {
            return true;
        }

        // Verificar rango de fechas
        if ($this->valid_from && $date < $this->valid_from) {
            return false;
        }

        if ($this->valid_to && $date > $this->valid_to) {
            return false;
        }

        return true;
    }

    /**
     * Obtener la configuración de un día específico
     */
    public function getDayConfig($dayName)
    {
        $config = $this->daily_config ?? [];
        return $config[$dayName] ?? ['max' => 0, 'hours' => []];
    }

    /**
     * Obtener el máximo de citas para un día
     */
    public function getMaxForDay($dayName)
    {
        $config = $this->getDayConfig($dayName);
        return $config['max'] ?? 0;
    }

    /**
     * Obtener las horas disponibles para un día
     */
    public function getHoursForDay($dayName)
    {
        $config = $this->getDayConfig($dayName);
        return $config['hours'] ?? [];
    }

    /**
     * Verificar si un día está activo (tiene citas disponibles)
     */
    public function isDayActive($dayName)
    {
        return $this->getMaxForDay($dayName) > 0;
    }

    /**
     * Verificar si una hora está disponible para un día
     */
    public function isHourAvailableForDay($dayName, $hour)
    {
        $hours = $this->getHoursForDay($dayName);
        return in_array($hour, $hours);
    }

    /**
     * Verificar si una fecha es una excepción
     */
    public function isException($date)
    {
        $exceptions = $this->exceptions ?? [];
        foreach ($exceptions as $exception) {
            if ($exception['date'] === $date && !$exception['active']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtener slots disponibles para una fecha específica
     */
    public function getAvailableSlotsForDate($date)
    {
        // Verificar si la configuración es válida para esta fecha
        if (!$this->isValidForDate($date)) {
            return [];
        }

        $dayName = strtolower(date('l', strtotime($date)));
        $hours = $this->getHoursForDay($dayName);
        $maxPerDay = $this->getMaxForDay($dayName);

        // Verificar excepción
        if ($this->isException($date)) {
            return [];
        }

        if ($maxPerDay == 0 || empty($hours)) {
            return [];
        }

        // Obtener citas ya agendadas
        $appointments = Appointment::where('asesor_id', $this->user_id)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $bookedSlots = $appointments->map(function($app) {
            return $app->scheduled_date->format('H:i');
        })->toArray();

        // Verificar si ya se alcanzó el límite
        if (count($bookedSlots) >= $maxPerDay) {
            return [];
        }

        // Filtrar horas disponibles
        $availableSlots = [];
        foreach ($hours as $hour) {
            if (!in_array($hour, $bookedSlots)) {
                $availableSlots[] = $hour;
            }
        }

        return $availableSlots;
    }

    /**
     * Verificar si se puede agendar una cita
     */
    public function canSchedule($date, $time)
    {
        $dayName = strtolower(date('l', strtotime($date)));

        if (!$this->is_active) {
            return ['available' => false, 'reason' => 'El asesor no está disponible para citas en este momento.'];
        }

        // Verificar si la configuración es válida para esta fecha
        if (!$this->isValidForDate($date)) {
            return ['available' => false, 'reason' => 'El asesor no tiene agenda disponible para esta fecha.'];
        }

        // Verificar excepción
        if ($this->isException($date)) {
            return ['available' => false, 'reason' => 'El asesor no está disponible en esta fecha.'];
        }

        $maxPerDay = $this->getMaxForDay($dayName);
        if ($maxPerDay == 0) {
            return ['available' => false, 'reason' => 'El asesor no atiende los ' . $this->getDayNameSpanish($dayName) . '.'];
        }

        $hours = $this->getHoursForDay($dayName);
        if (empty($hours)) {
            return ['available' => false, 'reason' => 'No hay horas disponibles para este día.'];
        }

        if (!in_array($time, $hours)) {
            return ['available' => false, 'reason' => 'Hora no disponible para este día.'];
        }

        // Contar citas existentes
        $appointmentsCount = Appointment::where('asesor_id', $this->user_id)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        if ($appointmentsCount >= $maxPerDay) {
            return ['available' => false, 'reason' => 'Límite de citas diarias alcanzado (máximo ' . $maxPerDay . ').'];
        }

        // Verificar si la hora ya está ocupada
        $existingAppointment = Appointment::where('asesor_id', $this->user_id)
            ->whereDate('scheduled_date', $date)
            ->whereTime('scheduled_date', $time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existingAppointment) {
            return ['available' => false, 'reason' => 'Hora ya ocupada.'];
        }

        return ['available' => true];
    }

    /**
     * Obtener nombre del día en español
     */
    private function getDayNameSpanish($dayName)
    {
        $days = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sábado',
            'sunday' => 'domingo'
        ];
        return $days[$dayName] ?? $dayName;
    }

    /**
     * Obtener todos los días configurados
     */
    public function getConfiguredDays()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayNames = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo'
        ];

        $result = [];
        foreach ($days as $day) {
            $config = $this->getDayConfig($day);
            $result[$day] = [
                'name' => $dayNames[$day],
                'max' => $config['max'] ?? 0,
                'hours' => $config['hours'] ?? [],
                'is_active' => ($config['max'] ?? 0) > 0,
            ];
        }
        return $result;
    }
}
