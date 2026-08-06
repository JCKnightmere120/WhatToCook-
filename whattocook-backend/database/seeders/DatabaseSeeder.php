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
        // User::factory(10)->create();

        // RecipeSeeder is the reviewed Filipino recipe manifest. The catalogue
        // is then synchronized from that manifest plus its explicit pantry base.
        $this->call([RecipeSeeder::class, IngredientCatalogSeeder::class]);
    }
}
