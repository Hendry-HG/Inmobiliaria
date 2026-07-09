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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Relación con el usuario que realizó la acción
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Acción realizada (ej. 'created_property', 'login', 'deleted_user')

            // Polimorfismo para saber qué entidad fue afectada (Opcional pero recomendado)
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // Datos del cambio (JSON)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Descripción legible para humanos (ej. "Juan creó la propiedad Casa Lomas")
            $table->string('description')->nullable();

            // Metadatos técnicos
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
