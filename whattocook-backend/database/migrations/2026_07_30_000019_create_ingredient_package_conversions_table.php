<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_package_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ingredient_name');
            $table->string('package_unit', 50);
            $table->decimal('amount_per_package', 12, 3);
            $table->string('amount_unit', 50)->default('g');
            $table->timestamps();
            $table->unique(['user_id', 'family_id', 'ingredient_name', 'package_unit', 'amount_unit'], 'ingredient_package_conversion_scope_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('ingredient_package_conversions'); }
};
