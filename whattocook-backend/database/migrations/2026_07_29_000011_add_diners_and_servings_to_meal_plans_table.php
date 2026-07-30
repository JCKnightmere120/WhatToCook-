<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('servings')->default(1)->after('meal_type');
            // Profiles represent diners, including dependents who do not have accounts.
            $table->json('diner_profile_ids')->nullable()->after('servings');
        });
    }

    public function down(): void
    {
        Schema::table('meal_plans', function (Blueprint $table) {
            $table->dropColumn(['servings', 'diner_profile_ids']);
        });
    }
};
