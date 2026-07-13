<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->index('idx_appointment_settings_user_id');

            $table->boolean('is_active')->default(true)
                ->index('idx_appointment_settings_is_active');

            $table->json('daily_config')->nullable();

            $table->date('valid_from')->nullable()
                ->index('idx_appointment_settings_valid_from');
            $table->date('valid_to')->nullable()
                ->index('idx_appointment_settings_valid_to');
            $table->boolean('apply_always')->default(true)
                ->index('idx_appointment_settings_apply_always');

            $table->integer('slot_duration')->default(60);
            $table->integer('break_duration')->default(15);
            $table->boolean('notify_client')->default(true);
            $table->integer('reminder_minutes')->default(60);
            
            $table->json('exceptions')->nullable();

            $table->timestamps();

            // Índices compuestos
            $table->index(['user_id', 'is_active'], 'idx_appointment_settings_user_active');
            $table->index(['is_active', 'apply_always'], 'idx_appointment_settings_active_always');
            $table->index(['valid_from', 'valid_to'], 'idx_appointment_settings_valid_range');
            $table->index(['user_id', 'valid_from', 'valid_to'], 'idx_appointment_settings_user_valid');

            // Índice único
            $table->unique('user_id', 'idx_appointment_settings_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_settings');
    }
};