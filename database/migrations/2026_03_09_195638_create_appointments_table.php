<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminamos la tabla si existe para asegurar una estructura limpia
        Schema::dropIfExists('appointments');

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cliente que agenda
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('users')->onDelete('cascade'); // Asesor asignado

            // Fechas
            $table->dateTime('scheduled_date'); // Inicio de la cita
            $table->dateTime('end_date')->nullable(); // Fin estimado (opcional)

            // Estado
            $table->enum('status', [
                'pending', 'confirmed', 'cancelled', 'completed',
                'reprogrammed', 'no_show'
            ])->default('pending');

            // Datos de contacto (Capturados en el formulario)
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            // Mensajes y Notas
            $table->text('message')->nullable(); // Mensaje del cliente al agendar
            $table->text('notes')->nullable();   // Notas internas (Solo manual)
            $table->text('result_notes')->nullable(); // Resultado post-cita

            // Indicadores de resultado
            $table->boolean('client_attended')->default(false);
            $table->boolean('property_sold')->default(false);

            $table->timestamps();

            // Índices para búsquedas rápidas en calendario
            $table->index(['asesor_id', 'scheduled_date']);
            $table->index(['status', 'scheduled_date']);
            $table->index('user_id');

            // IMPORTANTE: No hay unique(['user_id', 'property_id'])
            // Esto permite múltiples visitas del mismo cliente a la misma propiedad.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
