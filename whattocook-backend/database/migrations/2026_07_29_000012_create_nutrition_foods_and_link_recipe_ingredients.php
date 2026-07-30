<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition_foods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fdc_id')->nullable()->unique();
            $table->string('description');
            $table->string('normalized_name')->index();
            $table->string('source')->default('usda');
            $table->json('nutrients'); // values per 100 g
            $table->json('raw_data')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
            $table->unique(['normalized_name', 'source']);
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('nutrition_food_id')->nullable()->after('recipe_id')->constrained('nutrition_foods')->nullOnDelete();
            $table->decimal('nutrition_grams', 10, 3)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nutrition_food_id');
            $table->dropColumn('nutrition_grams');
        });
        Schema::dropIfExists('nutrition_foods');
    }
};
