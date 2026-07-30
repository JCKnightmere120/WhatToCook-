<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void {
        Schema::create('ingredient_catalog', function (Blueprint $table) {
            $table->id(); $table->string('canonical_name')->unique(); $table->json('aliases')->nullable();
            $table->string('category')->default('other'); $table->boolean('is_approved')->default(true);
            $table->json('default_units')->nullable(); $table->timestamps();
        });
        foreach (['rice', 'brown rice', 'milk', 'eggs', 'chicken', 'chicken breast', 'chicken thighs', 'tomatoes', 'carrots', 'bihon noodles', 'canned sardines', 'canned tuna', 'spicy canton', 'coconut milk', 'tuna', 'salt', 'yogurt', 'pechay', 'soy sauce', 'pasta', 'pork', 'peanut', 'beef', 'potatoes'] as $name) {
            DB::table('ingredient_catalog')->insert(['canonical_name' => $name, 'aliases' => json_encode(match ($name) { 'eggs' => ['egg'], 'tomatoes' => ['tomato'], 'carrots' => ['carrot'], 'bihon noodles' => ['bihon'], 'canned sardines' => ['sardines', 'sardine', 'sardines tuna'], default => [] }), 'category' => 'food', 'is_approved' => true, 'default_units' => json_encode([]), 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach (DB::table('ingredients')->select('name', 'unit')->get() as $ingredient) {
            $name = trim((string) $ingredient->name); if ($name === '') continue;
            DB::table('ingredient_catalog')->updateOrInsert(['canonical_name' => Str::lower($name)], ['aliases' => json_encode([]), 'category' => 'recipe ingredient', 'is_approved' => true, 'default_units' => json_encode(array_values(array_filter([$ingredient->unit]))), 'updated_at' => now(), 'created_at' => now()]);
        }
    }
    public function down(): void { Schema::dropIfExists('ingredient_catalog'); }
};
