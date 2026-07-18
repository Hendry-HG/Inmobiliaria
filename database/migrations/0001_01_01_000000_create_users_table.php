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

            // ============ DATOS PERSONALES ============
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('phone')->nullable();

            // ============ PERFIL ============
            $table->string('profile_photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('specialization')->nullable();
            $table->json('social_links')->nullable();

            // ============ IDENTIFICACIÓN ============
            $table->string('id_type')->nullable()->comment('V, E, J, P');
            $table->string('id_number')->nullable();

            // ============ UBICACIÓN ============
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('municipality_id')->nullable();
            $table->unsignedBigInteger('parish_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->text('address')->nullable();

            // ============ SEGURIDAD ============
            $table->json('security_questions')->nullable();
            $table->string('security_answer_1')->nullable();
            $table->string('security_answer_2')->nullable();
            $table->string('security_answer_3')->nullable();
            $table->timestamp('security_questions_set_at')->nullable();

            // ============ ESTADO ============
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();

            // ============ TIMESTAMPS ============
            $table->timestamps();
            $table->softDeletes();

            // ============================================================
            //  ÍNDICES OPTIMIZADOS PARA RENDIMIENTO
            // ============================================================
            // Índices simples
            $table->index('email');
            $table->index('phone');
            $table->index('is_active');
            $table->index('is_online');
            $table->index('last_seen_at');
            $table->index('created_at');
            $table->index('deleted_at');
            $table->index('id_number');
            $table->index('security_answer_1');
            $table->index('security_answer_2');
            $table->index('security_answer_3');

            // Índices compuestos
            $table->index(['name', 'last_name']);
            $table->index(['country_id', 'state_id', 'city_id']);
            $table->index(['is_active', 'created_at']);
            $table->index(['id_type', 'id_number']);
            $table->index(['email', 'is_active']);
            $table->index(['phone', 'is_active']);
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
