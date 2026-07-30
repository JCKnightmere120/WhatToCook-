<?php

namespace Tests\Feature;

use App\Models\MealPlan;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealPlanCookingPreflightApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_scales_to_planned_servings_and_keeps_personal_and_family_stock_separate(): void
    {
        $user = User::factory()->create();
        $recipe = $this->recipe($user, 'Rice Meal', 2);
        PantryItem::create([
            'user_id' => $user->id,
            'name' => 'Rice',
            'quantity' => '4',
            'quantity_value' => 4,
            'unit' => 'kg',
            'freshness_status' => 'fresh',
        ]);
        $personalPlan = MealPlan::create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'planned_date' => '2026-08-03',
            'meal_type' => 'dinner',
            'servings' => 4,
        ]);

        $this->actingAs($user, 'sanctum')->getJson("/api/meal-plans/{$personalPlan->id}/preflight")
            ->assertOk()
            ->assertJsonPath('pantry_scope', 'personal')
            ->assertJsonPath('can_cook_from_pantry', true)
            ->assertJsonPath('ingredients_by_status.ready.0.required_quantity', 2)
            ->assertJsonPath('ingredients_by_status.ready.0.pantry_quantity', 4)
            ->assertJsonPath('diners.0.relation', 'Self');

        $this->actingAs($user, 'sanctum')->getJson("/api/meal-plans/{$personalPlan->id}")
            ->assertOk()
            ->assertJsonPath('recipe.ingredients.0.name', 'Rice');

        $family = $this->actingAs($user, 'sanctum')->postJson('/api/families', ['name' => 'Strict Scope'])->assertCreated()->json();
        PantryItem::create([
            'user_id' => $user->id,
            'family_id' => $family['id'],
            'name' => 'Rice',
            'quantity' => '0.5',
            'quantity_value' => .5,
            'unit' => 'kg',
            'freshness_status' => 'fresh',
        ]);
        $familyPlan = MealPlan::create([
            'user_id' => $user->id,
            'family_id' => $family['id'],
            'recipe_id' => $recipe->id,
            'planned_date' => '2026-08-04',
            'meal_type' => 'dinner',
            'servings' => 4,
        ]);

        $this->actingAs($user, 'sanctum')->getJson("/api/meal-plans/{$familyPlan->id}/preflight")
            ->assertOk()
            ->assertJsonPath('pantry_scope', 'family')
            ->assertJsonPath('can_cook_from_pantry', false)
            ->assertJsonPath('ingredients_by_status.low_stock.0.required_quantity', 2)
            ->assertJsonPath('ingredients_by_status.low_stock.0.pantry_quantity', 0.5)
            ->assertJsonPath('ingredients_by_status.low_stock.0.missing_quantity', 1.5);

        $this->actingAs($user, 'sanctum')->postJson("/api/meal-plans/{$familyPlan->id}/shopping-list")
            ->assertCreated()
            ->assertJsonPath('items.0.ingredient_name', 'Rice')
            ->assertJsonPath('items.0.quantity', '1.5');
        $this->assertDatabaseHas('shopping_lists', [
            'user_id' => $user->id,
            'family_id' => $family['id'],
            'ingredient_name' => 'Rice',
            'quantity' => '1.5',
        ]);

        $draft = MealPlan::create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'planned_date' => '2026-08-05',
            'meal_type' => 'dinner',
            'servings' => 1,
            'status' => 'draft',
        ]);
        $this->actingAs($user, 'sanctum')->getJson("/api/meal-plans/{$draft->id}/preflight")
            ->assertUnprocessable();
    }

    public function test_a_meal_can_be_marked_cooked_without_deducting_pantry_stock(): void
    {
        $user = User::factory()->create();
        $recipe = $this->recipe($user, 'Rice Meal', 2);
        $pantryItem = PantryItem::create([
            'user_id' => $user->id,
            'name' => 'Rice',
            'quantity' => '3',
            'quantity_value' => 3,
            'unit' => 'kg',
            'freshness_status' => 'fresh',
        ]);
        $plan = MealPlan::create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'planned_date' => '2026-08-03',
            'meal_type' => 'dinner',
            'servings' => 4,
        ]);

        $this->actingAs($user, 'sanctum')->postJson("/api/meal-plans/{$plan->id}/complete-without-deduction")
            ->assertOk()
            ->assertJsonPath('meal_plan.status', 'completed')
            ->assertJsonPath('meal_plan.completion_method', 'without_pantry_deduction')
            ->assertJsonPath('meal_plan.consumed_items', [])
            ->assertJsonPath('message', 'Meal marked as cooked; pantry stock was not deducted.');

        $this->assertDatabaseHas('pantry_items', [
            'id' => $pantryItem->id,
            'quantity_value' => 3,
            'freshness_status' => 'fresh',
        ]);
        $this->assertNotNull($plan->fresh()->completed_at);
        $this->assertDatabaseHas('meal_history', [
            'meal_plan_id' => $plan->id,
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'servings' => 4,
        ]);

        $this->actingAs($user, 'sanctum')->postJson("/api/meal-plans/{$plan->id}/complete-without-deduction")
            ->assertUnprocessable();
        $this->actingAs($user, 'sanctum')->putJson("/api/meal-plans/{$plan->id}", ['servings' => 2])
            ->assertUnprocessable();
        $this->actingAs($user, 'sanctum')->deleteJson("/api/meal-plans/{$plan->id}")
            ->assertUnprocessable();
        $this->assertDatabaseCount('meal_history', 1);
    }

    public function test_family_cooking_never_deducts_or_counts_the_cooks_private_pantry_stock(): void
    {
        $user = User::factory()->create();
        $recipe = $this->recipe($user, 'Rice Meal', 2);
        $family = $this->actingAs($user, 'sanctum')->postJson('/api/families', ['name' => 'Shared Only'])->assertCreated()->json();
        $personal = PantryItem::create([
            'user_id' => $user->id,
            'name' => 'Rice',
            'quantity' => '4',
            'quantity_value' => 4,
            'unit' => 'kg',
            'freshness_status' => 'fresh',
        ]);
        $shared = PantryItem::create([
            'user_id' => $user->id,
            'family_id' => $family['id'],
            'name' => 'Rice',
            'quantity' => '1',
            'quantity_value' => 1,
            'unit' => 'kg',
            'freshness_status' => 'fresh',
        ]);
        $plan = MealPlan::create([
            'user_id' => $user->id,
            'family_id' => $family['id'],
            'recipe_id' => $recipe->id,
            'planned_date' => '2026-08-03',
            'meal_type' => 'dinner',
            'servings' => 4,
        ]);

        $this->actingAs($user, 'sanctum')->postJson("/api/meal-plans/{$plan->id}/complete")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('pantry');

        $this->assertDatabaseHas('pantry_items', ['id' => $personal->id, 'quantity_value' => 4]);
        $this->assertDatabaseHas('pantry_items', ['id' => $shared->id, 'quantity_value' => 1]);
        $this->assertNull($plan->fresh()->completed_at);
        $this->assertDatabaseCount('meal_history', 0);
    }

    public function test_completed_family_meals_appear_in_each_accepted_members_history(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $family = $this->actingAs($owner, 'sanctum')->postJson('/api/families', ['name' => 'History Household'])
            ->assertCreated()->json();
        $invitation = $this->actingAs($owner, 'sanctum')->postJson("/api/families/{$family['id']}/members", [
            'email' => $member->email,
            'role' => 'member',
        ])->assertCreated()->json('invitation');
        $this->actingAs($member, 'sanctum')->postJson("/api/family-invitations/{$invitation['id']}/accept")
            ->assertOk();

        $recipe = $this->recipe($owner, 'Shared Rice Meal', 2);
        PantryItem::create([
            'user_id' => $owner->id,
            'family_id' => $family['id'],
            'name' => 'Rice',
            'quantity' => '1',
            'quantity_value' => 1,
            'unit' => 'kg',
            'freshness_status' => 'fresh',
        ]);
        $plan = MealPlan::create([
            'user_id' => $owner->id,
            'family_id' => $family['id'],
            'recipe_id' => $recipe->id,
            'planned_date' => '2026-08-03',
            'meal_type' => 'dinner',
            'servings' => 2,
        ]);

        $this->actingAs($owner, 'sanctum')->postJson("/api/meal-plans/{$plan->id}/complete")
            ->assertOk();
        $this->actingAs($member, 'sanctum')->getJson('/api/meal-history')
            ->assertOk()
            ->assertJsonPath('0.recipe_id', $recipe->id)
            ->assertJsonPath('0.family_id', $family['id']);
    }

    private function recipe(User $user, string $name, int $servings): Recipe
    {
        $recipe = Recipe::create([
            'name' => $name,
            'instructions' => 'Cook.',
            'servings' => $servings,
            'created_by' => $user->id,
        ]);
        $recipe->ingredients()->create([
            'name' => 'Rice',
            'quantity' => '1',
            'unit' => 'kg',
        ]);

        return $recipe;
    }
}
