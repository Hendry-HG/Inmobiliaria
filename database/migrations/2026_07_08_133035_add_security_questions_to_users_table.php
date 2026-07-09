<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Preguntas de seguridad (3 preguntas y sus respuestas)
            $table->json('security_questions')->nullable()->after('address');
            $table->json('security_answers')->nullable()->after('security_questions');

            // Para almacenar respuestas hasheadas individualmente
            $table->string('security_answer_1')->nullable()->after('security_answers');
            $table->string('security_answer_2')->nullable()->after('security_answer_1');
            $table->string('security_answer_3')->nullable()->after('security_answer_2');

            // Timestamp para verificar cuándo se configuraron las preguntas
            $table->timestamp('security_questions_set_at')->nullable()->after('security_answer_3');

            // Índices para búsquedas rápidas (opcional)
            $table->index('security_answer_1');
            $table->index('security_answer_2');
            $table->index('security_answer_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'security_questions',
                'security_answers',
                'security_answer_1',
                'security_answer_2',
                'security_answer_3',
                'security_questions_set_at'
            ]);
        });
    }
};
