<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('meal_plans', fn (Blueprint $table) => $table->json('selection_reason')->nullable()->after('child_meal_plan')); } public function down(): void { Schema::table('meal_plans', fn (Blueprint $table) => $table->dropColumn('selection_reason')); } };
