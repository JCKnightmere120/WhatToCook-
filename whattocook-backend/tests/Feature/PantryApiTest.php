<?php

namespace Tests\Feature;

use App\Models\PantryItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PantryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_api_requests_return_json_401_instead_of_a_login_redirect(): void
    {
        $this->getJson('/api/nutrition/search?query=chicken')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_a_user_can_register_and_receive_an_access_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'test@example.com')
            ->assertJsonStructure(['token']);
    }

    public function test_a_user_can_store_an_expiring_pantry_item(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pantry', [
            'name' => 'Milk',
            'quantity' => '1',
            'unit' => 'litre',
            'purchase_date' => '2026-07-20',
            'expiry_date' => '2026-07-25',
        ]);

        $response->assertCreated()->assertJsonPath('item.expiry_date', '2026-07-25T00:00:00.000000Z');
        $this->assertDatabaseHas('pantry_items', ['name' => 'Milk', 'expiry_date' => '2026-07-25 00:00:00']);
    }

    public function test_pantry_updates_cannot_change_an_items_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = PantryItem::create(['user_id' => $owner->id, 'name' => 'Rice']);

        $this->actingAs($owner, 'sanctum')->putJson("/api/pantry/{$item->id}", [
            'name' => 'Brown rice',
            'user_id' => $otherUser->id,
        ])->assertOk();

        $this->assertDatabaseHas('pantry_items', [
            'id' => $item->id,
            'user_id' => $owner->id,
            'name' => 'Brown rice',
        ]);
    }

    public function test_profiles_are_saved_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'allergies' => ['peanuts'],
            'dietary_restrictions' => ['vegetarian'],
            'visible_to_family' => ['allergies' => false],
        ])->assertOk()->assertJsonPath('allergies.0', 'peanuts');

        $this->assertDatabaseHas('profiles', ['user_id' => $user->id]);
    }

    public function test_family_members_can_view_the_shared_pantry_with_a_default_expiry_date(): void
    {
        Carbon::setTestNow('2026-07-21 12:00:00');
        try {
            $owner = User::factory()->create();
            $member = User::factory()->create();
            $family = $this->actingAs($owner, 'sanctum')->postJson('/api/families', ['name' => 'Santos Family'])->json();

            $this->actingAs($owner, 'sanctum')->postJson("/api/families/{$family['id']}/members", ['email' => $member->email, 'role' => 'member'])->assertCreated();
            $this->actingAs($owner, 'sanctum')->postJson('/api/pantry', ['name' => 'Eggs', 'family_id' => $family['id']])->assertCreated();

            $this->actingAs($member, 'sanctum')->getJson('/api/pantry')
                ->assertOk()->assertJsonPath('0.name', 'Eggs')->assertJsonPath('0.expiry_date', '2026-07-22T00:00:00.000000Z');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_a_user_can_create_a_recipe_with_instructions_and_nutrition(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson('/api/recipes', [
            'name' => 'Chicken Adobo',
            'region' => 'Filipino',
            'instructions' => 'Simmer the chicken in the sauce until tender.',
            'cooking_tips' => 'Rest before serving.',
            'calories' => 420.5,
            'protein' => 31,
            'carbs' => 8,
            'fat' => 29,
            'ingredients' => [['name' => 'Chicken', 'quantity' => '1', 'unit' => 'kg']],
        ])->assertCreated()->assertJsonPath('instructions', 'Simmer the chicken in the sauce until tender.');

        $this->assertDatabaseHas('recipes', ['name' => 'Chicken Adobo', 'region' => 'Filipino']);
    }

    public function test_an_authenticated_user_can_search_usda_food_data(): void
    {
        $user = User::factory()->create();
        config()->set('services.usda.key', 'test-key');
        config()->set('services.usda.base_url', 'https://api.nal.usda.gov/fdc/v1');
        Http::fake(['https://api.nal.usda.gov/fdc/v1/foods/search*' => Http::response(['foods' => [['fdcId' => 123, 'description' => 'Chicken breast']]], 200)]);

        $this->actingAs($user, 'sanctum')->getJson('/api/nutrition/search?query=chicken')
            ->assertOk()->assertJsonPath('foods.0.fdcId', 123);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'query=chicken') && str_contains($request->url(), 'api_key=test-key'));
    }
}
