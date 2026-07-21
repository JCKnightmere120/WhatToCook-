<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::table('meal_plans', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shopping_lists', fn (Blueprint $table) => $table->dropConstrainedForeignId('family_id'));
        Schema::table('meal_plans', fn (Blueprint $table) => $table->dropConstrainedForeignId('family_id'));
        Schema::table('recipes', fn (Blueprint $table) => $table->dropConstrainedForeignId('created_by'));
        Schema::table('pantry_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('family_id'));
    }
};
