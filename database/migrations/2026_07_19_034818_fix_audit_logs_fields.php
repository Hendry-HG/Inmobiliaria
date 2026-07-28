<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Corrige y completa campos faltantes en la tabla audit_logs,
 * cambiando tipos a TEXT y convirtiendo valores JSON a JSONB.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // AGREGAR SOLO LAS COLUMNAS QUE FALTAN
        // ==========================================
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'action')) {
                $table->text('action')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'subject_type')) {
                $table->text('subject_type')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->text('auditable_type')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'tags')) {
                $table->text('tags')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'url')) {
                $table->text('url')->nullable()->after('ip_address');
            }
            // ==========================================
            // AGREGAR user_type SI NO EXISTE
            // ==========================================
            if (!Schema::hasColumn('audit_logs', 'user_type')) {
                $table->text('user_type')->nullable()->after('id');
            }
        });

        // ==========================================
        // CAMBIAR CAMPOS A TEXT (SOLO SI EXISTEN)
        // ==========================================
        $columns = ['action', 'auditable_type', 'description', 'event', 'subject_type', 'tags', 'user_type'];
        foreach ($columns as $column) {
            if (Schema::hasColumn('audit_logs', $column)) {
                try {
                    DB::statement("ALTER TABLE audit_logs ALTER COLUMN {$column} TYPE TEXT;");
                } catch (\Exception $e) {
                    // Si la columna no existe o no se puede cambiar, ignorar
                }
            }
        }

        // ==========================================
        // CAMBIAR old_values Y new_values A JSONB
        // ==========================================
        if (Schema::hasColumn('audit_logs', 'old_values')) {
            try {
                DB::statement('ALTER TABLE audit_logs ALTER COLUMN old_values TYPE JSONB USING old_values::jsonb;');
            } catch (\Exception $e) {
                // Ignorar
            }
        }
        if (Schema::hasColumn('audit_logs', 'new_values')) {
            try {
                DB::statement('ALTER TABLE audit_logs ALTER COLUMN new_values TYPE JSONB USING new_values::jsonb;');
            } catch (\Exception $e) {
                // Ignorar
            }
        }
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $columns = ['action', 'subject_type', 'subject_id', 'description', 'tags', 'auditable_type', 'auditable_id', 'url'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('audit_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
