<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la tabla NO existe antes de crearla
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('user_type')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('event');
                $table->string('auditable_type');
                $table->unsignedBigInteger('auditable_id');
                $table->text('old_values')->nullable();
                $table->text('new_values')->nullable();
                $table->text('url')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent', 1023)->nullable();
                $table->string('tags')->nullable();
                $table->timestamps();

                // Índices
                $table->index(['user_id', 'user_type']);
                $table->index(['auditable_id', 'auditable_type']);
            });
        }
    }

    public function down(): void
    {
        // Solo eliminar si la tabla existe
        if (Schema::hasTable('audit_logs')) {
            Schema::dropIfExists('audit_logs');
        }
    }
};