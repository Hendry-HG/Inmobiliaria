<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        // Tabla para imágenes publicitarias de servicios
        Schema::create('service_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_galleries');
        Schema::dropIfExists('services');
    }
};
