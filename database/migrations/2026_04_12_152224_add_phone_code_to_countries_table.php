<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('phone_code', 5)->nullable()->after('code'); // Ej: +58
            $table->string('phone_format', 50)->nullable()->after('phone_code'); // Ej: 000-0000000
            $table->integer('phone_min_length')->default(7)->after('phone_format');
            $table->integer('phone_max_length')->default(15)->after('phone_min_length');
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['phone_code', 'phone_format', 'phone_min_length', 'phone_max_length']);
        });
    }
};
