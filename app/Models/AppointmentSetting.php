<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo que configura la agenda y disponibilidad horaria de un asesor inmobiliario.
 *
 * Permite definir la disponibilidad semanal del asesor: dias activos, horas disponibles
 * por dia, limite de citas diarias, duracion de slots, descansos y excepciones.
 * El sistema utiliza esta configuracion para determinar los horarios disponibles
 * para que los clientes agenden citas.
 *
 * Relaciones:
 * - user: Asesor inmobiliario al que pertenece la configuracion.
 *
 * Campos clave:
 * - daily_config: JSON con la configuracion de cada dia de la semana (max, hours).
 * - is_active: Indica si el asesor esta aceptando citas actualmente.
 * - apply_always: Si es true, la configuracion aplica para siempre (ignora fechas).
 * - valid_from/valid_to: Rango de fechas de validez (si apply_always es false).
 * - slot_duration: Duracion de cada slot en minutos.
 * - break_duration: Duracion del descanso entre slots en minutos.
 * - notify_client: Si es true, envia notificacion al cliente al agendar.
 * - reminder_minutes: Minutos antes de la cita para enviar recordatorio.
 * - exceptions: JSON con fechas excepcionales (dias deshabilitados).
 *
 * @package App\Models
 */
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

    /**
     * Obtiene el asesor inmobiliario al que pertenece la configuracion.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relacion con el modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la configuracion de disponibilidad para un usuario especifico.
     *
     * Si el usuario no tiene configuracion, crea una con valores por defecto:
     * - Lunes a jueves: 6 slots (09:00 a 16:00).
     * - Viernes: 5 slots (09:00 a 15:00).
     * - Sabado y domingo: sin disponibilidad.
     * - Slot de 60 minutos, descanso de 15 minutos.
     * - Notificaciones activadas, recordatorio 60 minutos antes.
     *
     * @param int $userId ID del asesor inmobiliario.
     * @return \App\Models\AppointmentSetting Configuracion de disponibilidad del asesor.
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
     * Verifica si la configuracion es valida para una fecha especifica.
     *
     * Si apply_always es true, siempre retorna true. De lo contrario,
     * verifica que la fecha este dentro del rango valid_from - valid_to.
     *
     * @param string $date Fecha a verificar (formato Y-m-d).
     * @return bool true si la configuracion es valida para la fecha.
     */
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

    /**
     * Obtiene la configuracion completa de un dia especifico.
     *
     * @param string $dayName Nombre del dia en ingles (monday, tuesday, etc.).
     * @return array Configuracion del dia: ['max' => int, 'hours' => array].
     */
    public function getDayConfig($dayName)
    {
        $config = $this->daily_config ?? [];
        return $config[$dayName] ?? ['max' => 0, 'hours' => []];
    }

    /**
     * Obtiene el limite maximo de citas para un dia especifico.
     *
     * @param string $dayName Nombre del dia en ingles.
     * @return int Numero maximo de citas permitidas en el dia.
     */
    public function getMaxForDay($dayName)
    {
        $config = $this->getDayConfig($dayName);
        return (int) ($config['max'] ?? 0);
    }

    /**
     * Obtiene las horas disponibles para un dia especifico.
     *
     * @param string $dayName Nombre del dia en ingles.
     * @return array Lista de horas disponibles en formato 'HH:MM'.
     */
    public function getHoursForDay($dayName)
    {
        $config = $this->getDayConfig($dayName);
        $hours = $config['hours'] ?? [];
        return is_array($hours) ? array_values($hours) : [];
    }

    /**
     * Verifica si un dia esta activo (tiene citas permitidas).
     *
     * @param string $dayName Nombre del dia en ingles.
     * @return bool true si el dia tiene un limite mayor a 0.
     */
    public function isDayActive($dayName)
    {
        return $this->getMaxForDay($dayName) > 0;
    }

    /**
     * Verifica si una hora especifica esta disponible para un dia.
     *
     * @param string $dayName Nombre del dia en ingles.
     * @param string $hour Hora a verificar en formato 'HH:MM'.
     * @return bool true si la hora esta en la lista de horas disponibles del dia.
     */
    public function isHourAvailableForDay($dayName, $hour)
    {
        $hours = $this->getHoursForDay($dayName);
        return in_array($hour, $hours);
    }

    /**
     * Verifica si una fecha es una excepcion (dia deshabilitado).
     *
     * @param string $date Fecha a verificar (formato Y-m-d).
     * @return bool true si la fecha esta marcada como excepcion con active=false.
     */
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

    /**
     * Obtiene las horas disponibles para una fecha especifica.
     *
     * Combina la validacion de fecha, excepciones, limite diario y citas
     * existentes para retornar solo las horas que realmente estan disponibles.
     *
     * @param string $date Fecha a consultar (formato Y-m-d).
     * @return array Lista de horas disponibles ('HH:MM') o array vacio si no hay disponibilidad.
     */
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

    /**
     * Verifica si se puede agendar una cita en una fecha y hora especificas.
     *
     * Realiza todas las validaciones necesarias: estado activo, fecha valida,
     * no es excepcion, dia activo, hora disponible, limite diario no excedido
     * y hora no ocupada.
     *
     * @param string $date Fecha a verificar (formato Y-m-d).
     * @param string $time Hora a verificar en formato 'HH:MM'.
     * @return array Array con clave 'available' (bool) y 'reason' (string) si no esta disponible.
     */
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

    /**
     * Convierte el nombre de un dia en ingles a su equivalente en español.
     *
     * @param string $dayName Nombre del dia en ingles.
     * @return string Nombre del dia en español.
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
     * Obtiene la configuracion completa de todos los dias de la semana.
     *
     * Retorna un array asociativo donde cada clave es el dia en ingles y
     * el valor contiene: nombre en español, maximo de citas, horas disponibles
     * y si el dia esta activo. Util para renderizar formularios de configuracion.
     *
     * @return array<string, array{name: string, max: int, hours: array, is_active: bool}>
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
                'max' => (int) ($config['max'] ?? 0),
                'hours' => $this->getHoursForDay($day),
                'is_active' => ((int) ($config['max'] ?? 0)) > 0,
            ];
        }
        return $result;
    }
}