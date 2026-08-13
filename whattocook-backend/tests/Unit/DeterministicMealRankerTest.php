<?php

namespace Tests\Unit;

use App\Models\PantryItem;
use App\Models\Profile;
use App\Models\Recipe;
use App\Models\User;
use App\Services\DeterministicMealRanker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeterministicMealRankerTest extends TestCase
{
    use RefreshDatabase;

    public function test_safety_is_an_exclusion_before_all_other_scores(): void
    {
        $user = User::factory()->create(); $safe = $this->recipe($user, 'Vegetable Soup', 'squash', 20); $unsafe = $this->recipe($user, 'Peanut Stew', 'peanut', 10);
        $profile = new Profile(['allergies' => ['peanut'], 'likes' => ['peanut']]);
        $ranked = app(DeterministicMealRanker::class)->rank(Recipe::with('ingredients')->get(), collect([$profile]), collect(), collect(), ['cooking_time_budget' => '30'], []);
        $this->assertSame([$safe->id], $ranked->pluck('id')->all()); $this->assertNotContains($unsafe->id, $ranked->pluck('id'));
    }

    public function test_strict_time_budget_excludes_over_budget_recipes(): void
    {
        $user = User::factory()->create(); $fast = $this->recipe($user, 'Fast', 'egg', 15); $this->recipe($user, 'Slow', 'beef', 45);
        $ranked = app(DeterministicMealRanker::class)->rank(Recipe::with('ingredients')->get(), collect(), collect(), collect(), ['cooking_time_budget' => '15', 'time_preference' => 'strict'], []);
        $this->assertSame([$fast->id], $ranked->pluck('id')->all());
    }

    public function test_expiring_pantry_ingredient_is_prioritized_then_variety_avoids_repeat(): void
    {
        $user = User::factory()->create(); $expiry = $this->recipe($user, 'Use Tomato', 'tomato', 30); $other = $this->recipe($user, 'Use Rice', 'rice', 30);
        PantryItem::create(['user_id' => $user->id, 'name' => 'tomato', 'quantity' => '1', 'quantity_value' => 1, 'unit' => 'pc', 'freshness_status' => 'fresh', 'expiry_date' => now()->addDay()]);
        $ranker = app(DeterministicMealRanker::class); $first = $ranker->rank(Recipe::with('ingredients')->get(), collect(), PantryItem::all(), collect(), ['cooking_time_budget' => '30'], []);
        $second = $ranker->rank(Recipe::with('ingredients')->get(), collect(), collect(), collect(), ['cooking_time_budget' => '30'], [$expiry->id]);
        $this->assertSame($expiry->id, $first->first()->id); $this->assertSame($other->id, $second->first()->id);
    }

    public function test_halal_taxonomy_excludes_pork_and_alcohol_when_generating(): void
    {
        $user = User::factory()->create();
        $safe = $this->recipe($user, 'Vegetable Soup', 'squash', 20);
        $this->recipe($user, 'Pork Stew', 'pork belly', 20);
        $this->recipe($user, 'Wine Sauce', 'cooking wine', 20);
        $profile = new Profile(['dietary_restrictions' => ['halal']]);

        $ranked = app(DeterministicMealRanker::class)->rank(Recipe::with('ingredients')->get(), collect([$profile]), collect(), collect(), ['cooking_time_budget' => '30'], []);
        $this->assertSame([$safe->id], $ranked->pluck('id')->all());
    }

    private function recipe(User $user, string $name, string $ingredient, int $minutes): Recipe
    {
        $recipe = Recipe::create(['name' => $name, 'instructions' => 'Cook.', 'prep_time' => 0, 'cook_time' => $minutes, 'servings' => 1, 'protein' => 20, 'created_by' => $user->id]);
        $recipe->ingredients()->create(['name' => $ingredient, 'quantity' => '1', 'unit' => 'pc']); return $recipe;
    }
}
