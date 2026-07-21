<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->longText('instructions')->nullable()->after('description');
            $table->text('cooking_tips')->nullable()->after('instructions');
            $table->decimal('calories', 10, 2)->nullable()->after('image');
            $table->decimal('protein', 10, 2)->nullable()->after('calories');
            $table->decimal('carbs', 10, 2)->nullable()->after('protein');
            $table->decimal('fat', 10, 2)->nullable()->after('carbs');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['instructions', 'cooking_tips', 'calories', 'protein', 'carbs', 'fat']);
        });
    }
};
