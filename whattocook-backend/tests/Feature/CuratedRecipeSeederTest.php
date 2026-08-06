<?php

namespace Tests\Feature;

use App\Models\IngredientCatalog;
use App\Models\Recipe;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuratedRecipeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_curated_manifest_can_be_rerun_to_sync_recipe_content_and_pantry_catalogue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $recipe = Recipe::where('name', 'Adobong Manok')->firstOrFail();
        $recipe->update(['description' => 'Stale description']);
        $recipe->ingredients()->delete();
        $recipe->ingredients()->create(['name' => 'stale ingredient']);

        $this->seed(DatabaseSeeder::class);

        $recipe->refresh();
        $this->assertSame('Classic Filipino chicken adobo braised in soy sauce, vinegar, and garlic.', $recipe->description);
        $this->assertDatabaseHas('ingredients', ['recipe_id' => $recipe->id, 'name' => 'chicken thighs']);
        $this->assertDatabaseMissing('ingredients', ['recipe_id' => $recipe->id, 'name' => 'stale ingredient']);
        $this->assertTrue(IngredientCatalog::where('canonical_name', 'chicken thighs')->where('is_approved', true)->exists());
    }
}
