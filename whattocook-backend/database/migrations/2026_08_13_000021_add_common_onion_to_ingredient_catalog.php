<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('ingredient_catalog')->updateOrInsert(
            ['canonical_name' => 'onion'],
            [
                'aliases' => json_encode(['onions', 'sibuyas']),
                'category' => 'produce',
                'default_units' => json_encode(['pieces', 'g']),
                'is_approved' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
        DB::table('ingredient_catalog')->updateOrInsert(
            ['canonical_name' => 'cheese'],
            [
                'aliases' => json_encode(['cheeses', 'keso']),
                'category' => 'dairy',
                'default_units' => json_encode(['g', 'packs', 'slices']),
                'is_approved' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
        DB::table('ingredient_catalog')->updateOrInsert(
            ['canonical_name' => 'cabbage'],
            [
                'aliases' => json_encode(['repolyo']),
                'category' => 'produce',
                'default_units' => json_encode(['pieces', 'g']),
                'is_approved' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('ingredient_catalog')->whereIn('canonical_name', ['onion', 'cheese', 'cabbage'])->delete();
    }
};
