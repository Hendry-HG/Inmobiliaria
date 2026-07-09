<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 14, 2);
            $table->string('price_currency')->default('USD');

            // Sistema de ubicación jerárquico
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities');
            $table->foreignId('parish_id')->nullable()->constrained('parishes');
            $table->foreignId('city_id')->nullable()->constrained('cities');

            $table->string('address')->nullable();
            $table->string('location')->nullable();

            // Campos antiguos (compatibilidad)
            $table->string('sector')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();

            // Características
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('parking_spaces')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('land_area', 10, 2)->nullable();
            $table->integer('floors')->nullable();
            $table->integer('year_built')->nullable();

            // Tipo y Estado
            $table->enum('type', ['venta', 'alquiler', 'venta/alquiler'])->default('venta');
            $table->enum('status', ['borrador', 'pendiente', 'publicada', 'vendida', 'alquilada', 'inactiva'])->default('borrador');

            // Características adicionales (JSON)
            $table->json('features')->nullable();

            // Geolocalización
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            // Relaciones
            $table->foreignId('user_id')->constrained();
            $table->foreignId('category_id')->nullable()->constrained();

            // Métricas
            $table->integer('views')->default(0);
            $table->integer('inquiries')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->date('featured_until')->nullable();

            // Metadatos
            $table->json('meta_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar búsquedas
            $table->index(['status', 'type', 'price']);
            $table->index('user_id');
            $table->index('category_id');
            $table->index(['country_id', 'state_id', 'municipality_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
