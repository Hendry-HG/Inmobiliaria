<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // ===== COLUMNAS QUE FALTAN PARA EL SEEDER =====
            
            // Para el campo 'event' (ya debería existir, pero lo verificamos)
            if (!Schema::hasColumn('audit_logs', 'event')) {
                $table->string('event')->nullable();
            }
            
            // Para el campo 'subject_type' (polimorfismo)
            if (!Schema::hasColumn('audit_logs', 'subject_type')) {
                $table->string('subject_type')->nullable();
            }
            
            // Para el campo 'subject_id' (polimorfismo)
            if (!Schema::hasColumn('audit_logs', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable();
            }
            
            // Para el campo 'description' (descripción legible)
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->string('description')->nullable();
            }
            
            // Para el campo 'url' (la que está causando el error)
            if (!Schema::hasColumn('audit_logs', 'url')) {
                $table->text('url')->nullable();
            }
            
            // Para el campo 'user_type'
            if (!Schema::hasColumn('audit_logs', 'user_type')) {
                $table->string('user_type')->nullable();
            }
            
            // Para el campo 'tags'
            if (!Schema::hasColumn('audit_logs', 'tags')) {
                $table->string('tags')->nullable();
            }
            
            // Para el campo 'auditable_type'
            if (!Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->string('auditable_type')->nullable();
            }
            
            // Para el campo 'auditable_id'
            if (!Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')->nullable();
            }
            
            // ===== MODIFICAR COLUMNAS EXISTENTES =====
            
            // Cambiar 'old_values' a JSON si es necesario
            if (Schema::hasColumn('audit_logs', 'old_values')) {
                // Verificar si es text y cambiarlo a json
                $table->json('old_values')->nullable()->change();
            }
            
            // Cambiar 'new_values' a JSON si es necesario
            if (Schema::hasColumn('audit_logs', 'new_values')) {
                $table->json('new_values')->nullable()->change();
            }
            
            // ===== ÍNDICES ADICIONALES =====
            
            // Índices para búsquedas rápidas (evitar duplicados)
            if (!Schema::hasIndex('audit_logs', ['subject_type', 'subject_id'])) {
                $table->index(['subject_type', 'subject_id']);
            }
            
            if (!Schema::hasIndex('audit_logs', ['auditable_type', 'auditable_id'])) {
                $table->index(['auditable_type', 'auditable_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Lista de columnas a eliminar
            $columns = [
                'event', 'subject_type', 'subject_id', 'description',
                'url', 'user_type', 'tags', 'auditable_type', 'auditable_id'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('audit_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};