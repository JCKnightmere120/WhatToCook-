<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\MealPlan;
use App\Models\PantryItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_the_bearer_token_used_for_the_request(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->flushHeaders()->getJson('/api/user', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create(['email' => 'rate-limit@example.test']);

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/login', ['email' => $user->email, 'password' => 'incorrect-password'])->assertUnprocessable();
        }

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'incorrect-password'])
            ->assertStatus(429);
    }

    public function test_removed_members_cannot_read_or_modify_their_previous_family_meal_plans(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $family = $this->actingAs($owner, 'sanctum')->postJson('/api/families', ['name' => 'Scoped'])->json();
        FamilyMember::create(['family_id' => $family['id'], 'user_id' => $member->id, 'role' => 'member', 'status' => 'accepted']);
        $plan = MealPlan::create([
            'user_id' => $member->id,
            'family_id' => $family['id'],
            'recipe_id' => $this->recipeId($owner),
            'planned_date' => '2026-08-01',
            'meal_type' => 'dinner',
        ]);
        FamilyMember::where(['family_id' => $family['id'], 'user_id' => $member->id])->delete();

        $this->actingAs($member, 'sanctum')->getJson('/api/meal-plans')->assertOk()->assertJsonCount(0);
        $this->actingAs($member, 'sanctum')->deleteJson("/api/meal-plans/{$plan->id}")->assertForbidden();
    }

    public function test_removed_members_cannot_access_previous_household_pantry_or_shopping_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $family = $this->actingAs($owner, 'sanctum')->postJson('/api/families', ['name' => 'Scoped'])->json();
        FamilyMember::create(['family_id' => $family['id'], 'user_id' => $member->id, 'role' => 'member', 'status' => 'accepted']);
        $pantry = PantryItem::create([
            'user_id' => $owner->id, 'family_id' => $family['id'], 'name' => 'Rice',
            'quantity' => '1', 'quantity_value' => 1, 'unit' => 'kg', 'freshness_status' => 'fresh',
        ]);
        $shopping = ShoppingList::create([
            'user_id' => $owner->id, 'family_id' => $family['id'], 'ingredient_name' => 'Rice', 'quantity' => '1', 'unit' => 'kg',
        ]);
        FamilyMember::where(['family_id' => $family['id'], 'user_id' => $member->id])->delete();

        $this->actingAs($member, 'sanctum')->getJson("/api/pantry?family_id={$family['id']}")->assertForbidden();
        $this->actingAs($member, 'sanctum')->deleteJson("/api/pantry/{$pantry->id}")->assertNotFound();
        $this->actingAs($member, 'sanctum')->getJson('/api/shopping-list')->assertOk()->assertJsonCount(0);
        $this->actingAs($member, 'sanctum')->deleteJson("/api/shopping-list/{$shopping->id}")->assertForbidden();
    }

    private function recipeId(User $user): int
    {
        return $this->actingAs($user, 'sanctum')->postJson('/api/recipes', [
            'name' => 'Scoped Recipe', 'instructions' => 'Cook.',
            'ingredients' => [['name' => 'Rice', 'quantity' => '1', 'unit' => 'cup']],
        ])->assertCreated()->json('id');
    }
}
