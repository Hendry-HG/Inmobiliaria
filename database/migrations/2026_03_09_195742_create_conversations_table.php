<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('users')->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // ============================================================
            //  ÍNDICES OPTIMIZADOS PARA RENDIMIENTO
            // ============================================================
            $table->unique(['client_id', 'asesor_id']);
            $table->index('client_id');
            $table->index('asesor_id');
            $table->index('is_active');
            $table->index('last_message_at');
            $table->index(['client_id', 'is_active']);
            $table->index(['asesor_id', 'is_active']);
            $table->index(['last_message_at', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};
