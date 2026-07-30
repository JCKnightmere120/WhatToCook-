<?php

namespace Tests\Feature;

use App\Models\MealPlan;
use App\Models\HouseholdProfile;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealPlanBatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_generation_creates_a_reviewable_draft_without_changing_the_saved_schedule(): void
    {
        $user = User::factory()->create();
        $recipe = $this->recipe($user, 'Chicken Tinola', 'Chicken', '1', 'kg');
        PantryItem::create(['user_id' => $user->id, 'name' => 'Chicken', 'quantity' => '3', 'quantity_value' => 3, 'unit' => 'kg', 'freshness_status' => 'fresh']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/meal-plan-batches/generate', [
            'start_date' => '2026-08-03', 'end_date' => '2026-08-04', 'meal_types' => ['dinner'], 'servings' => 2,
        ])->assertCreated()->assertJsonPath('summary.meal_count', 2)->assertJsonPath('meal_plans.0.status', 'draft');

        $batchId = $response->json('batch.id');
        $this->assertDatabaseCount('meal_plan_batches', 1);
        $this->assertDatabaseCount('meal_plans', 2);
        $this->actingAs($user, 'sanctum')->getJson('/api/meal-plans')->assertOk()->assertJsonCount(0);

        $this->actingAs($user, 'sanctum')->postJson("/api/meal-plan-batches/{$batchId}/save")
            ->assertOk()->assertJsonPath('batch.status', 'saved');
        $this->actingAs($user, 'sanctum')->getJson('/api/meal-plans')->assertOk()->assertJsonCount(2);
        $this->assertDatabaseHas('meal_plans', ['recipe_id' => $recipe->id, 'status' => 'scheduled']);
    }

    public function test_discarding_a_draft_has_no_side_effects_on_saved_meals(): void
    {
        $user = User::factory()->create();
        $recipe = $this->recipe($user, 'Munggo', 'Mung beans', '1', 'cup');
        $saved = MealPlan::create(['user_id' => $user->id, 'recipe_id' => $recipe->id, 'planned_date' => '2026-08-03', 'meal_type' => 'dinner', 'servings' => 1, 'status' => 'scheduled']);

        $batch = $this->actingAs($user, 'sanctum')->postJson('/api/meal-plan-batches/generate', [
            'start_date' => '2026-08-03', 'end_date' => '2026-08-03', 'meal_types' => ['dinner'], 'servings' => 1,
        ])->assertCreated()->json('batch');

        $this->actingAs($user, 'sanctum')->deleteJson("/api/meal-plan-batches/{$batch['id']}")->assertNoContent();
        $this->assertDatabaseHas('meal_plans', ['id' => $saved->id, 'status' => 'scheduled']);
        $this->assertDatabaseMissing('meal_plans', ['meal_plan_batch_id' => $batch['id']]);
    }

    public function test_draft_ingredient_summary_aggregates_fractional_and_convertible_units(): void
    {
        $user = User::factory()->create();
        $kilogramRecipe = Recipe::create(['name' => 'Half Kilo Rice', 'instructions' => 'Cook.', 'servings' => 2, 'created_by' => $user->id]);
        $kilogramRecipe->ingredients()->create(['name' => 'Rice', 'quantity' => '1/2', 'unit' => 'kg']);
        $gramRecipe = Recipe::create(['name' => 'Five Hundred Gram Rice', 'instructions' => 'Cook.', 'servings' => 2, 'created_by' => $user->id]);
        $gramRecipe->ingredients()->create(['name' => 'Rice', 'quantity' => '500', 'unit' => 'g']);
        PantryItem::create(['user_id' => $user->id, 'name' => 'Rice', 'quantity' => '0.5', 'quantity_value' => .5, 'unit' => 'kg', 'freshness_status' => 'fresh']);

        $this->actingAs($user, 'sanctum')->postJson('/api/meal-plan-batches/generate', [
            'start_date' => '2026-08-03', 'end_date' => '2026-08-04', 'meal_types' => ['dinner'], 'servings' => 2,
        ])->assertCreated()
            ->assertJsonPath('summary.ingredients.0.unit', 'g')
            ->assertJsonPath('summary.ingredients.0.required_quantity', 1000)
            ->assertJsonPath('summary.ingredients.0.pantry_quantity', 500)
            ->assertJsonPath('summary.ingredients.0.missing_quantity', 500);
    }

    public function test_child_exclusions_portions_and_allergies_are_applied_to_each_day(): void
    {
        $owner = User::factory()->create();
        $family = $this->actingAs($owner, 'sanctum')->postJson('/api/families', ['name' => 'Child-safe household'])->json();
        $child = HouseholdProfile::create([
            'family_id' => $family['id'], 'name' => 'Ari', 'relation' => 'child',
            'birth_date' => '2024-08-01', 'allergies' => ['peanut'],
        ]);
        $safe = $this->recipe($owner, 'Chicken Soup', 'Chicken', '1', 'kg');
        $unsafe = $this->recipe($owner, 'Peanut Stew', 'Peanut butter', '1', 'cup');

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/meal-plan-batches/generate', [
            'family_id' => $family['id'], 'start_date' => '2026-07-30', 'end_date' => '2026-07-31',
            'meal_types' => ['dinner'], 'diner_profile_ids' => [$child->id],
            'attendance_by_date' => ['2026-07-30' => [$child->id], '2026-07-31' => []],
            'child_meal_modes' => [$child->id => 'family_meal_with_adaptation'],
        ])->assertCreated();

        $meal = $response->json('meal_plans.0');
        $this->assertSame($safe->id, $meal['recipe_id']);
        $this->assertNotSame($unsafe->id, $meal['recipe_id']);
        $this->assertSame('2-5_years', $meal['child_meal_plan']['children'][0]['age_band']);
        $this->assertSame(0.65, (float) $meal['serving_equivalents']);
        $this->assertNull($response->json('meal_plans.1.diner_profile_ids'));
    }

    public function test_one_two_and_three_requested_meal_slots_are_generated(): void
    {
        $user = User::factory()->create();
        $this->recipe($user, 'Quick Ulam', 'Rice', '1', 'cup');
        foreach ([['breakfast'], ['breakfast', 'lunch'], ['breakfast', 'lunch', 'dinner']] as $types) {
            $response = $this->actingAs($user, 'sanctum')->postJson('/api/meal-plan-batches/generate', [
                'start_date' => '2026-08-03', 'end_date' => '2026-08-03', 'meal_types' => $types, 'servings' => 1,
            ])->assertCreated();
            $this->assertCount(count($types), $response->json('meal_plans'));
        }
    }

    public function test_reuse_ulam_reuses_one_main_and_explains_the_choice(): void
    {
        $user = User::factory()->create(); $this->recipe($user, 'Adobo', 'Chicken', '1', 'kg'); $this->recipe($user, 'Munggo', 'Mung beans', '1', 'cup');
        $meals = $this->actingAs($user, 'sanctum')->postJson('/api/meal-plan-batches/generate', [
            'start_date' => '2026-08-03', 'end_date' => '2026-08-03', 'meal_types' => ['lunch', 'dinner'], 'servings' => 2,
            'leftover_strategy' => 'reuse_ulam', 'cooking_time_budget' => '60',
        ])->assertCreated()->json('meal_plans');
        $this->assertSame($meals[0]['recipe_id'], $meals[1]['recipe_id']);
        $this->assertTrue(collect($meals)->contains(fn (array $meal) => str_contains(implode(' ', $meal['selection_reason']), 'Reuses today')));
    }

    private function recipe(User $user, string $name, string $ingredient, string $quantity, string $unit): Recipe
    {
        $recipe = Recipe::create(['name' => $name, 'instructions' => 'Cook.', 'servings' => 2, 'created_by' => $user->id]);
        $recipe->ingredients()->create(['name' => $ingredient, 'quantity' => $quantity, 'unit' => $unit]);

        return $recipe;
    }
}
