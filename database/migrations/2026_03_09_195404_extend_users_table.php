<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Verificar y agregar solo las columnas que NO existen
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('profile_photo');
            }
            
            if (!Schema::hasColumn('users', 'specialization')) {
                $table->string('specialization')->nullable()->after('bio');
            }
            
            if (!Schema::hasColumn('users', 'social_links')) {
                $table->json('social_links')->nullable()->after('specialization');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('social_links');
            }
            
            if (!Schema::hasColumn('users', 'id_type')) {
                $table->string('id_type')->nullable()->after('is_active');
            }
            
            if (!Schema::hasColumn('users', 'id_number')) {
                $table->string('id_number')->nullable()->after('id_type');
            }
            
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('id_number');
            }
            
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->default('Venezuela')->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['phone', 'profile_photo', 'bio', 'specialization',
                       'social_links', 'is_active', 'id_type', 'id_number',
                       'address', 'city', 'country'];
            
            // Solo eliminar columnas que existen
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};