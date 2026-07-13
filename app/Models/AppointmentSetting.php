<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentSetting extends Model
{
    use HasFactory;

    protected $table = 'appointment_settings';

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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

    public function isValidForDate($date)
    {
        if ($this->apply_always) {
            return true;
        }

        if ($this->valid_from && $date < $this->valid_from) {
            return false;
        }

        if ($this->valid_to && $date > $this->valid_to) {
            return false;
        }

        return true;
    }

    public function getDayConfig($dayName)
    {
        $config = $this->daily_config ?? [];
        return $config[$dayName] ?? ['max' => 0, 'hours' => []];
    }

    public function getMaxForDay($dayName)
    {
        $config = $this->getDayConfig($dayName);
        return (int) ($config['max'] ?? 0);
    }

    public function getHoursForDay($dayName)
    {
        $config = $this->getDayConfig($dayName);
        $hours = $config['hours'] ?? [];
        return is_array($hours) ? array_values($hours) : [];
    }

    public function isDayActive($dayName)
    {
        return $this->getMaxForDay($dayName) > 0;
    }

    public function isHourAvailableForDay($dayName, $hour)
    {
        $hours = $this->getHoursForDay($dayName);
        return in_array($hour, $hours);
    }

    public function isException($date)
    {
        $exceptions = $this->exceptions ?? [];
        foreach ($exceptions as $exception) {
            if (isset($exception['date']) && $exception['date'] === $date && !($exception['active'] ?? false)) {
                return true;
            }
        }
        return false;
    }

    public function getAvailableSlotsForDate($date)
    {
        if (!$this->isValidForDate($date)) {
            return [];
        }

        $dayName = strtolower(date('l', strtotime($date)));
        $hours = $this->getHoursForDay($dayName);
        $maxPerDay = $this->getMaxForDay($dayName);

        if ($this->isException($date)) {
            return [];
        }

        if ($maxPerDay == 0 || empty($hours)) {
            return [];
        }

        $appointments = Appointment::where('asesor_id', $this->user_id)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $bookedSlots = $appointments->map(function($app) {
            return $app->scheduled_date->format('H:i');
        })->toArray();

        if (count($bookedSlots) >= $maxPerDay) {
            return [];
        }

        $availableSlots = [];
        foreach ($hours as $hour) {
            if (!in_array($hour, $bookedSlots)) {
                $availableSlots[] = $hour;
            }
        }

        return array_values($availableSlots);
    }

    public function canSchedule($date, $time)
    {
        $dayName = strtolower(date('l', strtotime($date)));

        if (!$this->is_active) {
            return ['available' => false, 'reason' => 'El asesor no está disponible para citas en este momento.'];
        }

        if (!$this->isValidForDate($date)) {
            return ['available' => false, 'reason' => 'El asesor no tiene agenda disponible para esta fecha.'];
        }

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

        $appointmentsCount = Appointment::where('asesor_id', $this->user_id)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        if ($appointmentsCount >= $maxPerDay) {
            return ['available' => false, 'reason' => 'Límite de citas diarias alcanzado (máximo ' . $maxPerDay . ').'];
        }

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
                'max' => (int) ($config['max'] ?? 0),
                'hours' => $this->getHoursForDay($day),
                'is_active' => ((int) ($config['max'] ?? 0)) > 0,
            ];
        }
        return $result;
    }
}