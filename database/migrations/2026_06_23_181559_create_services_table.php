<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla de servicios para gestionar el catalogo de
 * servicios inmobiliarios del sitio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // Información del servicio
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('icon')->nullable(); // Clase del icono (FontAwesome, Phosphor, etc.)
            $table->string('color')->default('#c5a059'); // Color del servicio
            $table->string('badge')->nullable(); // Badge "Destacado", "Nuevo", etc.

            // Imagen principal
            $table->string('image')->nullable();

            // Características del servicio (JSON)
            $table->json('features')->nullable();

            // URL externa (si aplica)
            $table->string('external_url')->nullable();

            // Orden de visualización
            $table->integer('order')->default(0);

            // Estado
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
