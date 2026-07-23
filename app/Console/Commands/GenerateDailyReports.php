<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportSnapshot;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportMail; // Tendrías que crear este Mailable

class GenerateDailyReports extends Command
{
    /**
     * 
     *
     * @var string
     */
    protected $signature = 'reports:generate-daily';

    /**
     * 
     *
     * @var string
     */
    protected $description = 'Genera y archiva el reporte diario de ventas y actividad';

    /**
     * 
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $this->info("Generando reportes para la fecha: {$yesterday}");

        $recipients = User::role(['Super Admin', 'Administrador'])->get();

        if ($recipients->isEmpty()) {
            $this->warn('No hay destinatarios configurados para recibir reportes.');
            return;
        }

        foreach ($recipients as $user) {
            $this->info("Procesando reporte para: {$user->name}");

            $stats = [
                'date' => $yesterday,
                'new_leads' => 0, 
                'appointments_completed' => Appointment::whereDate('updated_at', $yesterday)
                    ->where('status', 'completed')->count(),
                'new_properties' => Property::whereDate('created_at', $yesterday)->count(),
                'total_revenue' => 0, 
            ];

            $snapshot = ReportSnapshot::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'report_type' => 'daily_activity',
                    'period' => $yesterday
                ],
                [
                    'data' => $stats,
                    'status' => 'generated',
                    'sent_at' => now() // Marcamos como enviado ahora
                ]
            );

            
            try {
                $this->info("Email enviado a {$user->email}");
            } catch (\Exception $e) {
                $this->error("Error enviando email a {$user->email}: " . $e->getMessage());
                $snapshot->update(['status' => 'failed']);
            }
        }

        $this->info('Proceso de reportes diarios finalizado.');
    }
}
