<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega las columnas faltantes 'action' y 'event' a la tabla
 * de audit_logs si no existen previamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                // Agregar action
                if (!Schema::hasColumn('audit_logs', 'action')) {
                    $table->string('action')->nullable()->index()->after('user_id');
                }

                // Agregar event
                if (!Schema::hasColumn('audit_logs', 'event')) {
                    $table->string('event')->nullable()->index()->after('action');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'action')) {
                    $table->dropColumn('action');
                }
                if (Schema::hasColumn('audit_logs', 'event')) {
                    $table->dropColumn('event');
                }
            });
        }
    }
};
