<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\HouseholdProfile;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeDiscoveryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_menu_search_matches_ingredients_and_excludes_unsafe_recipes(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['allergies' => ['peanut']]);
        PantryItem::create([
            'user_id' => $user->id,
            'name' => 'ginger',
            'quantity' => '1',
            'quantity_value' => 1,
            'unit' => 'thumb',
            'freshness_status' => 'fresh',
        ]);

        $safe = $this->recipe('Ginger Vegetable Stew', [
            ['name' => 'ginger', 'quantity' => '1', 'unit' => 'thumb'],
        ]);
        $unsafe = $this->recipe('Peanut Ginger Stew', [
            ['name' => 'ginger', 'quantity' => '1', 'unit' => 'thumb'],
            ['name' => 'peanut butter', 'quantity' => '1', 'unit' => 'tbsp'],
        ]);

        $this->actingAs($user, 'sanctum')->getJson('/api/recipes?q=ginger&include_match=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.recipe.id', $safe->id)
            ->assertJsonPath('data.0.match_percentage', 100)
            ->assertJsonMissing(['id' => $unsafe->id]);
    }

    public function test_family_menu_search_uses_only_shared_stock_and_household_safety_rules(): void
    {
        $owner = User::factory()->create();
        $family = Family::create(['name' => 'Discovery Household', 'owner_id' => $owner->id]);
        FamilyMember::create(['family_id' => $family->id, 'user_id' => $owner->id, 'role' => 'owner', 'status' => 'accepted']);
        HouseholdProfile::create(['family_id' => $family->id, 'name' => 'Mika', 'allergies' => ['shrimp']]);

        // This personal item must not make a family search look stocked.
        PantryItem::create([
            'user_id' => $owner->id,
            'name' => 'coconut milk',
            'quantity' => '1',
            'quantity_value' => 1,
            'unit' => 'cup',
            'freshness_status' => 'fresh',
        ]);

        $safe = $this->recipe('Coconut Vegetable Soup', [
            ['name' => 'coconut milk', 'quantity' => '1', 'unit' => 'cup'],
        ]);
        $unsafe = $this->recipe('Shrimp Coconut Soup', [
            ['name' => 'shrimp', 'quantity' => '1', 'unit' => 'kg'],
        ]);

        $this->actingAs($owner, 'sanctum')->getJson("/api/recipes?q=soup&family_id={$family->id}&include_match=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.recipe.id', $safe->id)
            ->assertJsonPath('data.0.match_percentage', 0)
            ->assertJsonMissing(['id' => $unsafe->id]);
    }

    private function recipe(string $name, array $ingredients): Recipe
    {
        $recipe = Recipe::create(['name' => $name, 'instructions' => 'Cook and serve.']);
        $recipe->ingredients()->createMany($ingredients);

        return $recipe;
    }
}
