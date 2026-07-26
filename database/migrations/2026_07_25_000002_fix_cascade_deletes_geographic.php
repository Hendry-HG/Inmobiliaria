<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Properties: cambiar cascade a restrict en columnas geográficas
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->foreign('country_id')->references('id')->on('countries')->restrictOnDelete();

            $table->dropForeign(['state_id']);
            $table->foreign('state_id')->references('id')->on('states')->restrictOnDelete();

            $table->dropForeign(['municipality_id']);
            $table->foreign('municipality_id')->references('id')->on('municipalities')->restrictOnDelete();

            if (Schema::hasColumn('properties', 'parish_id')) {
                $table->dropForeign(['parish_id']);
                $table->foreign('parish_id')->references('id')->on('parishes')->restrictOnDelete();
            }

            $table->dropForeign(['city_id']);
            $table->foreign('city_id')->references('id')->on('cities')->restrictOnDelete();
        });

        // Appointments: cambiar cascade a restrict en user y property
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();

            $table->dropForeign(['property_id']);
            $table->foreign('property_id')->references('id')->on('properties')->restrictOnDelete();

            $table->dropForeign(['asesor_id']);
            $table->foreign('asesor_id')->references('id')->on('users')->restrictOnDelete();
        });

        // Conversations: cambiar cascade a restrict
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'property_id')) {
                $table->dropForeign(['property_id']);
                $table->foreign('property_id')->references('id')->on('properties')->restrictOnDelete();
            }

            $table->dropForeign(['client_id']);
            $table->foreign('client_id')->references('id')->on('users')->restrictOnDelete();

            $table->dropForeign(['asesor_id']);
            $table->foreign('asesor_id')->references('id')->on('users')->restrictOnDelete();
        });

        // Leads: cambiar cascade a restrict en user y asesor
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();

            if (Schema::hasColumn('leads', 'asesor_id')) {
                $table->dropForeign(['asesor_id']);
                $table->foreign('asesor_id')->references('id')->on('users')->restrictOnDelete();
            }

            if (Schema::hasColumn('leads', 'property_id')) {
                $table->dropForeign(['property_id']);
                $table->foreign('property_id')->references('id')->on('properties')->restrictOnDelete();
            }
        });

        // Favorites: cambiar cascade a restrict
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();

            $table->dropForeign(['property_id']);
            $table->foreign('property_id')->references('id')->on('properties')->restrictOnDelete();
        });

        // Messages: cambiar cascade a restrict en user
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Revertir a cascade (el estado original)
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();

            $table->dropForeign(['state_id']);
            $table->foreign('state_id')->references('id')->on('states')->cascadeOnDelete();

            $table->dropForeign(['municipality_id']);
            $table->foreign('municipality_id')->references('id')->on('municipalities')->cascadeOnDelete();

            if (Schema::hasColumn('properties', 'parish_id')) {
                $table->dropForeign(['parish_id']);
                $table->foreign('parish_id')->references('id')->on('parishes')->cascadeOnDelete();
            }

            $table->dropForeign(['city_id']);
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->dropForeign(['property_id']);
            $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();

            $table->dropForeign(['asesor_id']);
            $table->foreign('asesor_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'property_id')) {
                $table->dropForeign(['property_id']);
                $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
            }
            $table->dropForeign(['client_id']);
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropForeign(['asesor_id']);
            $table->foreign('asesor_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            if (Schema::hasColumn('leads', 'asesor_id')) {
                $table->dropForeign(['asesor_id']);
                $table->foreign('asesor_id')->references('id')->on('users')->cascadeOnDelete();
            }

            if (Schema::hasColumn('leads', 'property_id')) {
                $table->dropForeign(['property_id']);
                $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
            }
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropForeign(['property_id']);
            $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
