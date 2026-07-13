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
            $table->foreignId('country_id')->nullable()->constrained('countries')->cascadeOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->cascadeOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities')->cascadeOnDelete();
            $table->foreignId('parish_id')->nullable()->constrained('parishes')->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->cascadeOnDelete();

            $table->string('address')->nullable();
            $table->string('location')->nullable();

            // Campos antiguos (compatibilidad) - marcados para deprecación
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
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            // Métricas
            $table->integer('views')->default(0);
            $table->integer('inquiries')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->date('featured_until')->nullable();

            // Metadatos
            $table->json('meta_data')->nullable();

            // Timestamps y SoftDeletes
            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar búsquedas (incorporados directamente)
            $table->index(['status', 'type', 'price']);
            $table->index('user_id');
            $table->index('category_id');
            $table->index(['country_id', 'state_id', 'municipality_id']);
            $table->index(['title', 'description']); // Full-text search podría ser mejor
            $table->index(['is_featured', 'featured_until']);
            $table->index(['created_at', 'status']);
            $table->index('price');
            $table->index('status');
            $table->index('type');

            // Índice compuesto para búsquedas comunes
            $table->index(['status', 'type', 'price', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};