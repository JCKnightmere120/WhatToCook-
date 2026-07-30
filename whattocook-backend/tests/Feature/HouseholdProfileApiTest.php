<?php

namespace Tests\Feature;

use App\Models\HouseholdProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_family_owner_can_create_read_update_and_delete_a_dependent_profile(): void
    {
        $owner = User::factory()->create();
        $family = $this->createFamily($owner);

        $created = $this->actingAs($owner, 'sanctum')->postJson("/api/families/{$family['id']}/household-profiles", [
            'name' => 'Mika Santos',
            'relation' => 'child',
            'sex' => 'female',
            'birth_date' => '2018-05-10',
            'height_cm' => 117.5,
            'weight_kg' => 21.3,
            'activity_level' => 'active',
            'goal' => 'healthy growth',
            'health_conditions' => ['asthma'],
            'allergies' => ['peanuts'],
            'dietary_restrictions' => ['halal'],
            'likes' => ['chicken adobo'],
            'dislikes' => ['ampalaya'],
            'visible_to_family' => ['allergies' => false],
        ]);

        $created->assertCreated()
            ->assertJsonPath('household_profile.name', 'Mika Santos')
            ->assertJsonPath('household_profile.relation', 'child')
            ->assertJsonPath('household_profile.allergies.0', 'peanuts');

        $profileId = $created->json('household_profile.id');

        $this->assertDatabaseHas('household_profiles', [
            'id' => $profileId,
            'family_id' => $family['id'],
            'name' => 'Mika Santos',
            'relation' => 'child',
        ]);

        $this->actingAs($owner, 'sanctum')->getJson("/api/families/{$family['id']}/household-profiles/{$profileId}")
            ->assertOk()
            ->assertJsonPath('household_profile.health_conditions.0', 'asthma');

        $this->actingAs($owner, 'sanctum')->patchJson("/api/families/{$family['id']}/household-profiles/{$profileId}", [
            'goal' => 'maintain healthy growth',
            'weight_kg' => 22.1,
        ])->assertOk()
            ->assertJsonPath('household_profile.goal', 'maintain healthy growth')
            ->assertJsonPath('household_profile.weight_kg', '22.10');

        $this->actingAs($owner, 'sanctum')->deleteJson("/api/families/{$family['id']}/household-profiles/{$profileId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('household_profiles', ['id' => $profileId]);
    }

    public function test_a_family_member_only_sees_the_profile_fields_shared_with_the_family(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $family = $this->createFamily($owner);

        $this->actingAs($owner, 'sanctum')->postJson("/api/families/{$family['id']}/members", [
            'email' => $member->email,
            'role' => 'member',
        ])->assertCreated();

        $invitation = $this->actingAs($member, 'sanctum')->getJson('/api/family-invitations')
            ->assertOk()->json('invitations.0');
        $this->actingAs($member, 'sanctum')->postJson("/api/family-invitations/{$invitation['id']}/accept")
            ->assertOk();

        $profile = HouseholdProfile::create([
            'family_id' => $family['id'],
            'name' => 'Lolo Ben',
            'relation' => 'grandparent',
            'allergies' => ['shellfish'],
            'dietary_restrictions' => ['low sodium'],
            'likes' => ['sinigang'],
            'visible_to_family' => [
                'allergies' => false,
                'likes' => false,
            ],
        ]);

        $this->actingAs($member, 'sanctum')->getJson("/api/families/{$family['id']}/household-profiles/{$profile->id}")
            ->assertOk()
            ->assertJsonPath('household_profile.name', 'Lolo Ben')
            ->assertJsonPath('household_profile.dietary_restrictions.0', 'low sodium')
            ->assertJsonMissingPath('household_profile.allergies')
            ->assertJsonMissingPath('household_profile.likes')
            ->assertJsonMissingPath('household_profile.visible_to_family');

        $this->actingAs($member, 'sanctum')->getJson("/api/families/{$family['id']}/household-profiles")
            ->assertOk()
            ->assertJsonFragment(['id' => $profile->id, 'name' => 'Lolo Ben']);
    }

    public function test_a_linked_member_can_update_its_own_profile_but_cannot_relink_it(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $family = $this->createFamily($owner);

        $this->actingAs($owner, 'sanctum')->postJson("/api/families/{$family['id']}/members", [
            'email' => $member->email,
            'role' => 'member',
        ])->assertCreated();

        $invitation = $this->actingAs($member, 'sanctum')->getJson('/api/family-invitations')
            ->assertOk()->json('invitations.0');
        $this->actingAs($member, 'sanctum')->postJson("/api/family-invitations/{$invitation['id']}/accept")
            ->assertOk();

        // Acceptance creates the member's linked diner profile automatically.
        $profile = $this->actingAs($member, 'sanctum')->getJson("/api/families/{$family['id']}/household-profiles")
            ->assertOk()->json('household_profiles');
        $profile = collect($profile)->firstWhere('user_id', $member->id);
        $this->assertNotNull($profile);

        $this->actingAs($member, 'sanctum')->patchJson("/api/families/{$family['id']}/household-profiles/{$profile['id']}", [
            'allergies' => ['shrimp'],
            'user_id' => $owner->id,
        ])->assertOk()
            ->assertJsonPath('household_profile.allergies.0', 'shrimp')
            ->assertJsonPath('household_profile.user_id', $member->id);

        $this->assertDatabaseHas('household_profiles', [
            'id' => $profile['id'],
            'user_id' => $member->id,
        ]);

        $this->actingAs($member, 'sanctum')->postJson("/api/families/{$family['id']}/household-profiles", [
            'name' => 'Unauthorized dependent',
        ])->assertForbidden();
    }

    public function test_only_family_members_can_be_linked_to_a_household_profile(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $family = $this->createFamily($owner);

        $this->actingAs($owner, 'sanctum')->postJson("/api/families/{$family['id']}/household-profiles", [
            'name' => 'Not in this household',
            'user_id' => $outsider->id,
        ])->assertUnprocessable()
            ->assertJsonPath('errors.user_id.0', 'The linked user must be a member of this family.');

        $this->actingAs($outsider, 'sanctum')->getJson("/api/families/{$family['id']}/household-profiles")
            ->assertForbidden();
    }

    private function createFamily(User $owner): array
    {
        return $this->actingAs($owner, 'sanctum')
            ->postJson('/api/families', ['name' => 'Santos Household'])
            ->assertCreated()
            ->json();
    }
}
