<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // ============================================================
            //  ÍNDICES OPTIMIZADOS PARA RENDIMIENTO
            // ============================================================
            $table->index('conversation_id');
            $table->index('user_id');
            $table->index('is_read');
            $table->index('created_at');
            $table->index(['conversation_id', 'is_read']);
            $table->index(['user_id', 'is_read']);
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
