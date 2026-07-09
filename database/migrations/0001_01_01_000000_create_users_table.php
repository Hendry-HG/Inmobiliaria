<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // CAMPO NUEVO: apellido
            $table->string('last_name')->nullable();
            $table->string('name');

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Campos adicionales
            $table->string('phone')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('specialization')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();

            // Campos de ubicación
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('municipality_id')->nullable();
            $table->unsignedBigInteger('parish_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();

            // CAMPO NUEVO: dirección
            $table->text('address')->nullable()->after('city_id');

            // Estado en línea
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['country_id']);
            $table->index(['state_id']);
            $table->index(['municipality_id']);
            $table->index(['parish_id']);
            $table->index(['city_id']);
            $table->index(['is_active']);
            $table->index(['is_online']);
            $table->index(['id_type', 'id_number']);
            $table->index(['last_name']);

            // 👇 Índice opcional para búsqueda por dirección
            $table->index(['address']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
