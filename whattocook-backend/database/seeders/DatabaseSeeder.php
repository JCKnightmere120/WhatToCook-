<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
<<<<<<< HEAD
        // No demo account seeded. Use registration to create a new user.
=======
        // User::factory(10)->create();

        $this->call([RecipeSeeder::class, IngredientCatalogSeeder::class]);
>>>>>>> e959e466 (feat: expand household meal planning and pantry workflow)
    }
}
