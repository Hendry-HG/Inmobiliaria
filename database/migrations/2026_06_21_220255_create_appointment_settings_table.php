<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('appointment_settings');

        Schema::create('appointment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);

            //  CONFIGURACIÓN POR DÍA
            $table->json('daily_config')->nullable();

            //  PERÍODO DE VALIDEZ
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('apply_always')->default(true);

            // Configuración global
            $table->integer('slot_duration')->default(60);
            $table->integer('break_duration')->default(15);
            $table->boolean('notify_client')->default(true);
            $table->integer('reminder_minutes')->default(60);
            $table->json('exceptions')->nullable();

            $table->timestamps();

            $table->unique('user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_settings');
    }
};
