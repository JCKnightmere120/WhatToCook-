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
        // Seed a default admin user for testing and demo purposes.
        // Change the email/password or remove this in production.
        $this->call([
            AdminUserSeeder::class,
            RecipeSeeder::class,
            IngredientCatalogSeeder::class,
        ]);
    }
}
