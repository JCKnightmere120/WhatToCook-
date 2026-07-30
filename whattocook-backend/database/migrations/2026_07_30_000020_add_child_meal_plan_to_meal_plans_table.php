<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meal_plans', function (Blueprint $table) {
            $table->decimal('serving_equivalents', 5, 2)->nullable()->after('servings');
            $table->json('child_meal_plan')->nullable()->after('diner_profile_ids');
        });
    }
    public function down(): void
    {
        Schema::table('meal_plans', fn (Blueprint $table) => $table->dropColumn(['serving_equivalents', 'child_meal_plan']));
    }
};
