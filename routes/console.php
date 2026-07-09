<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <--- AGREGA ESTA LÍNEA

// Comando por defecto de Laravel (puedes dejarlo)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =================================================================
// PROGRAMACIÓN DE TAREAS AUTOMÁTICAS (CRON JOBS)
// =================================================================

// Ejecuta el comando de reportes diarios todos los días a las 8:00 AM
Schedule::command('reports:generate-daily')
         ->dailyAt('08:00')
         ->withoutOverlapping()
         ->runInBackground(); // Ejecuta en segundo plano para no bloquear el sistema
