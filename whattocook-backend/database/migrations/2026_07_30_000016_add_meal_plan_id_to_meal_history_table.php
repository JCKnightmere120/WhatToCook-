<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_history', function (Blueprint $table) {
            $table->foreignId('meal_plan_id')->nullable()->after('family_id')->constrained('meal_plans')->nullOnDelete();
            $table->unique('meal_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('meal_history', function (Blueprint $table) {
            $table->dropUnique(['meal_plan_id']);
            $table->dropConstrainedForeignId('meal_plan_id');
        });
    }
};
