<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User']
        );

<<<<<<< HEAD
        $this->call(RecipeSeeder::class);
        $this->call(AdminUserSeeder::class);
=======
        $this->call([RecipeSeeder::class, IngredientCatalogSeeder::class]);
>>>>>>> origin
    }
}