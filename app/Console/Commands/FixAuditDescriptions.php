<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Property;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Lead;
use Illuminate\Console\Command;

/**
 * Comando artisan para reparar descripciones de logs de auditoria.
 *
 * Busca registros en audit_logs con descripcion vacia, nula o generica
 * y las reconstruye a partir de los datos del sujeto (Property, User,
 * Appointment, Lead). Genera descripciones legibles como:
 * "Juan CREO la propiedad 'Casa en venta'".
 *
 * Util para corregir registros creados antes de implementar el
 * sistema de descripciones automaticas.
 *
 * Uso: php artisan audit:fix-descriptions
 *
 * @package App\Console\Commands
 */
class FixAuditDescriptions extends Command
{
    /**
     * Firma del comando artisan.
     *
     * @var string
     */
    protected $signature = 'audit:fix-descriptions';
    protected $description = 'Actualiza las descripciones de los logs de auditoría existentes';

    /**
     * Ejecuta la reparacion de descripciones de auditoria.
     *
     * Orquesta la llamada a metodos privados que reparan cada tipo de log:
     * propiedades, usuarios, citas y leads.
     *
     * @return int 0 en exito.
     */
    public function handle()
    {
        $this->info('Actualizando descripciones de logs...');

        // 1. Actualizar logs de propiedades
        $this->fixPropertyLogs();

        // 2. Actualizar logs de usuarios
        $this->fixUserLogs();

        // 3. Actualizar logs de citas
        $this->fixAppointmentLogs();

        // 4. Actualizar logs de leads
        $this->fixLeadLogs();

        $this->info(' ¡Descripciones actualizadas!');
    }

    /**
     * Reconstruye las descripciones de logs de propiedades.
     *
     * Busca logs de tipo Property con descripcion vacia y genera
     * descripciones con el nombre del usuario, la accion y el titulo
     * de la propiedad. Para actualizaciones, incluye los campos modificados.
     *
     * @return void
     */
    private function fixPropertyLogs()
    {
        $logs = AuditLog::where('subject_type', 'like', '%Property%')
            ->where(function($q) {
                $q->whereNull('description')
                  ->orWhere('description', '')
                  ->orWhere('description', '—');
            })
            ->get();

        $this->info("Encontrados {$logs->count()} logs de propiedad sin descripción.");

        foreach ($logs as $log) {
            try {
                $property = Property::withTrashed()->find($log->subject_id);
                if ($property) {
                    $userName = $log->user ? $log->user->full_name : 'Sistema';
                    $eventLabels = [
                        'created' => 'CREÓ',
                        'updated' => 'ACTUALIZÓ',
                        'deleted' => 'ELIMINÓ',
                        'restored' => 'RESTAURÓ',
                    ];
                    $eventLabel = $eventLabels[$log->event] ?? strtoupper($log->event ?? 'ACCION');

                    // Detectar cambios si es una actualización
                    $changes = '';
                    if ($log->event === 'updated' && $log->old_values && $log->new_values) {
                        $old = json_decode($log->old_values, true) ?? [];
                        $new = json_decode($log->new_values, true) ?? [];
                        $changeList = [];
                        foreach ($new as $key => $value) {
                            if (isset($old[$key]) && $old[$key] != $value && $key !== 'updated_at') {
                                $changeList[] = "{$key}: '{$old[$key]}' → '{$value}'";
                            }
                        }
                        if (!empty($changeList)) {
                            $changes = '. Cambios: ' . implode(', ', $changeList);
                        }
                    }

                    $log->description = "{$userName} {$eventLabel} la propiedad '{$property->title}'{$changes}";
                    $log->save();
                    $this->line(" Log ID {$log->id} actualizado");
                }
            } catch (\Exception $e) {
                $this->error("Error en log ID {$log->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Reconstruye las descripciones de logs de usuarios.
     *
     * Genera descripciones para eventos de login, logout, created,
     * updated y deleted sobre el modelo User.
     *
     * @return void
     */
    private function fixUserLogs()
    {
        $logs = AuditLog::where('subject_type', 'like', '%User%')
            ->where(function($q) {
                $q->whereNull('description')
                  ->orWhere('description', '')
                  ->orWhere('description', '—');
            })
            ->get();

        $this->info("Encontrados {$logs->count()} logs de usuario sin descripción.");

        foreach ($logs as $log) {
            try {
                $user = User::withTrashed()->find($log->subject_id);
                if ($user) {
                    $userName = $log->user ? $log->user->full_name : 'Sistema';

                    if ($log->event === 'login') {
                        $log->description = "Inicio de sesión de {$user->full_name}";
                    } elseif ($log->event === 'logout') {
                        $log->description = "Cierre de sesión de {$user->full_name}";
                    } elseif ($log->event === 'created') {
                        $log->description = "{$userName} CREÓ al usuario {$user->full_name}";
                    } elseif ($log->event === 'updated') {
                        $log->description = "{$userName} ACTUALIZÓ al usuario {$user->full_name}";
                    } elseif ($log->event === 'deleted') {
                        $log->description = "{$userName} ELIMINÓ al usuario {$user->full_name}";
                    } else {
                        $log->description = "{$userName} realizó una acción sobre el usuario {$user->full_name}";
                    }
                    $log->save();
                    $this->line(" Log ID {$log->id} actualizado");
                }
            } catch (\Exception $e) {
                $this->error("Error en log ID {$log->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Reconstruye las descripciones de logs de citas.
     *
     * Genera descripciones para eventos de created, updated y deleted
     * sobre Appointment. Incluye el titulo de la propiedad relacionada
     * y el estado actualizado en caso de modificacion.
     *
     * @return void
     */
    private function fixAppointmentLogs()
    {
        $logs = AuditLog::where('subject_type', 'like', '%Appointment%')
            ->where(function($q) {
                $q->whereNull('description')
                  ->orWhere('description', '')
                  ->orWhere('description', '—');
            })
            ->get();

        $this->info("Encontrados {$logs->count()} logs de cita sin descripción.");

        foreach ($logs as $log) {
            try {
                $appointment = Appointment::find($log->subject_id);
                if ($appointment && $appointment->property) {
                    $userName = $log->user ? $log->user->full_name : 'Sistema';

                    if ($log->event === 'created') {
                        $log->description = "{$userName} SOLICITÓ una cita para '{$appointment->property->title}'";
                    } elseif ($log->event === 'updated') {
                        $statusLabels = [
                            'pending' => 'Pendiente',
                            'confirmed' => 'Confirmada',
                            'completed' => 'Completada',
                            'cancelled' => 'Cancelada',
                            'rescheduled' => 'Reprogramada'
                        ];
                        $status = $statusLabels[$appointment->status] ?? $appointment->status;
                        $log->description = "{$userName} ACTUALIZÓ el estado de la cita para '{$appointment->property->title}' a '{$status}'";
                    } elseif ($log->event === 'deleted') {
                        $log->description = "{$userName} CANCELÓ la cita para '{$appointment->property->title}'";
                    } else {
                        $log->description = "{$userName} realizó una acción sobre la cita para '{$appointment->property->title}'";
                    }
                    $log->save();
                    $this->line(" Log ID {$log->id} actualizado");
                }
            } catch (\Exception $e) {
                $this->error("Error en log ID {$log->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Reconstruye las descripciones de logs de leads.
     *
     * Genera descripciones para eventos de created, updated y deleted
     * sobre Lead. Incluye el nombre del lead y el estado actualizado
     * en caso de modificacion.
     *
     * @return void
     */
    private function fixLeadLogs()
    {
        $logs = AuditLog::where('subject_type', 'like', '%Lead%')
            ->where(function($q) {
                $q->whereNull('description')
                  ->orWhere('description', '')
                  ->orWhere('description', '—');
            })
            ->get();

        $this->info("Encontrados {$logs->count()} logs de lead sin descripción.");

        foreach ($logs as $log) {
            try {
                $lead = Lead::find($log->subject_id);
                if ($lead) {
                    $userName = $log->user ? $log->user->full_name : 'Sistema';

                    if ($log->event === 'created') {
                        $log->description = "{$userName} CAPTÓ un nuevo lead: '{$lead->name}'";
                    } elseif ($log->event === 'updated') {
                        $statusLabels = [
                            'nuevo' => 'Nuevo',
                            'contactado' => 'Contactado',
                            'calificado' => 'Calificado',
                            'negociacion' => 'En Negociación',
                            'cerrado_ganado' => 'Ganado',
                            'cerrado_perdido' => 'Perdido',
                            'inactivo' => 'Inactivo'
                        ];
                        $status = $statusLabels[$lead->status] ?? $lead->status;
                        $log->description = "{$userName} ACTUALIZÓ el lead '{$lead->name}' a '{$status}'";
                    } elseif ($log->event === 'deleted') {
                        $log->description = "{$userName} ELIMINÓ el lead '{$lead->name}'";
                    } else {
                        $log->description = "{$userName} realizó una acción sobre el lead '{$lead->name}'";
                    }
                    $log->save();
                    $this->line(" Log ID {$log->id} actualizado");
                }
            } catch (\Exception $e) {
                $this->error("Error en log ID {$log->id}: " . $e->getMessage());
            }
        }
    }
}
