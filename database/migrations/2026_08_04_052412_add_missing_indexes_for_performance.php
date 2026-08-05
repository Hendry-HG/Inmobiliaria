<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega índices faltantes para optimizar consultas frecuentes.
     *
     * Prioridad alta: claves foráneas y consultas polimórficas sin índice.
     * Prioridad media: columnas de filtrado común en dashboards y catálogos.
     */
    public function up(): void
    {
        // Consultas polimórficas del paquete de auditoría
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index(['auditable_type', 'auditable_id']);
        });

        // Jerarquía geográfica (carga de dropdowns y cascadas)
        Schema::table('states', function (Blueprint $table) {
            $table->index('country_id');
        });

        Schema::table('municipalities', function (Blueprint $table) {
            $table->index('state_id');
        });

        Schema::table('parishes', function (Blueprint $table) {
            $table->index('municipality_id');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->index('parish_id');
        });

        // Propiedades: parroquia es la única FK sin índice propio
        Schema::table('properties', function (Blueprint $table) {
            $table->index('parish_id');
        });

        // Usuarios: FKs geográficas que solo existen en un índice compuesto
        Schema::table('users', function (Blueprint $table) {
            $table->index('state_id');
            $table->index('city_id');
            $table->index('municipality_id');
            $table->index('parish_id');
        });

        // Filtros de dashboards
        Schema::table('report_snapshots', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('interest_type');
            $table->index('source');
            $table->index('last_contact');
        });

        // Catálogo público
        Schema::table('services', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('order');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->index('code');
        });

        // Spatie: consultas por role_id (la PK es permission_id + role_id)
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->index('role_id');
        });
    }

    /**
     * Revierte la migración eliminando los índices agregados.
     */
    public function down(): void
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['code']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['order']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['last_contact']);
            $table->dropIndex(['source']);
            $table->dropIndex(['interest_type']);
        });

        Schema::table('report_snapshots', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['parish_id']);
            $table->dropIndex(['municipality_id']);
            $table->dropIndex(['city_id']);
            $table->dropIndex(['state_id']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['parish_id']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['parish_id']);
        });

        Schema::table('parishes', function (Blueprint $table) {
            $table->dropIndex(['municipality_id']);
        });

        Schema::table('municipalities', function (Blueprint $table) {
            $table->dropIndex(['state_id']);
        });

        Schema::table('states', function (Blueprint $table) {
            $table->dropIndex(['country_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['auditable_type', 'auditable_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
