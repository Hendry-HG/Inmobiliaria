
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_snapshots', function (Blueprint $table) {
            $table->id();

            // Quién generó o solicitó el reporte
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Tipo de reporte (ej: 'monthly_sales', 'daily_leads', 'performance_summary')
            $table->string('report_type')->index();

            // Período que cubre el reporte (ej: '2024-01' para Enero, '2024-01-15' para diario)
            $table->string('period')->index();

            // Los datos "congelados" en formato JSON
            $table->json('data');

            // Ruta del archivo PDF generado (si aplica)
            $table->string('file_path')->nullable();

            // Estado del reporte
            $table->enum('status', ['generated', 'sent', 'failed'])->default('generated');

            // Fecha en que se envió por correo (si aplica)
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Evitar duplicados: Un usuario no puede generar dos veces el reporte del mismo tipo para el mismo periodo
            $table->unique(['user_id', 'report_type', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_snapshots');
    }
};
