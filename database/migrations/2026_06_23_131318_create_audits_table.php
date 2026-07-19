<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();

                // ==========================================
                // CAMPOS DE USUARIO
                // ==========================================
                $table->text('user_type')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();

                // ==========================================
                // CAMPOS DE EVENTO
                // ==========================================
                $table->text('event');
                $table->text('action')->nullable();

                // ==========================================
                // CAMPOS DE SUJETO (POLIMÓRFICO)
                // ==========================================
                $table->text('subject_type');
                $table->unsignedBigInteger('subject_id');
                $table->text('auditable_type');
                $table->unsignedBigInteger('auditable_id');

                // ==========================================
                // CAMPOS DE VALORES (JSON)
                // ==========================================
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();

                // ==========================================
                // CAMPOS DE DESCRIPCIÓN Y URL (TEXT)
                // ==========================================
                $table->text('description')->nullable();
                $table->text('url')->nullable();

                // ==========================================
                // CAMPOS DE METADATOS
                // ==========================================
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->text('tags')->nullable();

                // ==========================================
                // TIMESTAMPS
                // ==========================================
                $table->timestamps();

                // ==========================================
                // ÍNDICES
                // ==========================================
                $table->index(['user_id', 'user_type']);
                $table->index(['auditable_id', 'auditable_type']);
                $table->index(['subject_id', 'subject_type']);
                $table->index(['event', 'action']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::dropIfExists('audit_logs');
        }
    }
};
