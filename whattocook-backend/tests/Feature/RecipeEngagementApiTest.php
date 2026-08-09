<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeEngagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_persist_favorites_and_submit_a_review_comment(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['name' => 'Saved Adobo', 'instructions' => 'Simmer.']);

        $this->actingAs($user, 'sanctum')->postJson("/api/recipes/{$recipe->id}/favorite")->assertNoContent();
        $this->actingAs($user, 'sanctum')->getJson('/api/favorites')->assertOk()->assertJsonPath('0.id', $recipe->id);
        $this->actingAs($user, 'sanctum')->putJson("/api/recipes/{$recipe->id}/review", ['rating' => 4, 'review' => 'Great for weeknights.'])
            ->assertCreated()->assertJsonPath('review', 'Great for weeknights.');
        $this->actingAs($user, 'sanctum')->getJson("/api/recipes/{$recipe->id}/reviews")
            ->assertOk()->assertJsonPath('0.review', 'Great for weeknights.');
    }

    public function test_recipe_discovery_filters_and_paginates_results(): void
    {
        $user = User::factory()->create();
        Recipe::create(['name' => 'Quick Soup', 'instructions' => 'Cook.', 'meal_type' => 'dinner', 'difficulty' => 'easy', 'prep_time' => 10, 'cook_time' => 10]);
        Recipe::create(['name' => 'Slow Soup', 'instructions' => 'Cook.', 'meal_type' => 'dinner', 'difficulty' => 'hard', 'prep_time' => 40, 'cook_time' => 50]);

        $this->actingAs($user, 'sanctum')->getJson('/api/recipes?include_match=1&meal_type=dinner&difficulty=easy&max_time=30&per_page=1')
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.recipe.name', 'Quick Soup');
    }
}
