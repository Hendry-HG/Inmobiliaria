<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();

            // Datos del lead
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();

            // Origen
            $table->string('source')->default('website');
            $table->string('source_detail')->nullable();

            // Interés
            $table->enum('interest_type', ['compra', 'alquiler', 'venta', 'asesoria'])->default('compra');
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->json('preferences')->nullable();

            // Estado
            $table->enum('status', [
                'nuevo', 'contactado', 'calificado', 'negociacion',
                'cerrado_ganado', 'cerrado_perdido', 'inactivo'
            ])->default('nuevo');

            $table->text('notes')->nullable();
            $table->dateTime('last_contact')->nullable();
            $table->integer('contact_count')->default(0);

            $table->timestamps();

            // ============================================================
            //  ÍNDICES OPTIMIZADOS PARA RENDIMIENTO
            // ============================================================
            $table->index('email');
            $table->index('phone');
            $table->index('status');
            $table->index('asesor_id');
            $table->index('user_id');
            $table->index('property_id');
            $table->index('created_at');
            $table->index(['status', 'asesor_id']);
            $table->index(['email', 'status']);
            $table->index(['phone', 'status']);
            $table->index(['asesor_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
