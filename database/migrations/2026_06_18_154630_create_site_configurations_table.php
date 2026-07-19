<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_configurations', function (Blueprint $table) {
            $table->id();

            // Hero Section
            $table->string('hero_badge')->default('Exclusividad & Confort');
            $table->string('hero_title_line1')->default('El Arte de');
            $table->string('hero_title_line2')->default('Vivir Bien');
            $table->text('hero_subtitle')->default('Descubre una curaduría exclusiva de propiedades de lujo en las mejores zonas de Venezuela.');
            $table->json('hero_images')->nullable();
            $table->string('hero_image_paths')->nullable();

            // Sección de propiedades destacadas
            $table->string('featured_badge')->default('Colección Exclusiva');
            $table->string('featured_title')->default('Propiedades Destacadas');
            $table->json('featured_properties')->nullable();

            // Soporte / Contacto
            $table->string('support_whatsapp')->nullable();
            $table->string('support_instagram')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('support_email')->nullable();

            // ==========================================
            // FOOTER - PIE DE PÁGINA (AGREGADO)
            // ==========================================
            $table->text('footer_text')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_configurations');
    }
};
