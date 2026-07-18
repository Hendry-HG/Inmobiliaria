<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('appointments');

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('users')->onDelete('cascade');

            // Fechas
            $table->dateTime('scheduled_date');
            $table->dateTime('end_date')->nullable();

            // Estado
            $table->enum('status', [
                'pending', 'confirmed', 'cancelled', 'completed',
                'reprogrammed', 'no_show'
            ])->default('pending');

            // Datos de contacto
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            // Mensajes y Notas
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->text('result_notes')->nullable();

            // Indicadores de resultado
            $table->boolean('client_attended')->default(false);
            $table->boolean('property_sold')->default(false);

            $table->timestamps();

            // ============================================================
            //  ÍNDICES OPTIMIZADOS PARA RENDIMIENTO
            // ============================================================
            $table->index('user_id');
            $table->index('property_id');
            $table->index('asesor_id');
            $table->index('status');
            $table->index('scheduled_date');
            $table->index('created_at');
            $table->index(['asesor_id', 'scheduled_date']);
            $table->index(['status', 'scheduled_date']);
            $table->index(['user_id', 'scheduled_date']);
            $table->index(['property_id', 'status']);
            $table->index(['asesor_id', 'status', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
