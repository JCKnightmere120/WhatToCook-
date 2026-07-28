<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyMemberApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_registered_user_can_join_a_household_with_its_invite_code(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $family = $this->actingAs($owner, 'sanctum')->postJson('/api/families', ['name' => 'Santos Family'])
            ->assertCreated()->json();

        $this->actingAs($member, 'sanctum')->postJson('/api/families/join', ['join_code' => $family['join_code']])
            ->assertCreated()->assertJsonPath('family.id', $family['id'])->assertJsonPath('joined', true);
        $this->assertDatabaseHas('family_members', ['family_id' => $family['id'], 'user_id' => $member->id, 'role' => 'member']);
    }

    public function test_an_owner_can_remove_a_registered_member_but_not_themself(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $family = $this->actingAs($owner, 'sanctum')
            ->postJson('/api/families', ['name' => 'Santos Household'])
            ->assertCreated()
            ->json();

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/families/{$family['id']}/members", [
                'email' => $member->email,
                'role' => 'member',
            ])
            ->assertCreated();

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/families/{$family['id']}/members/{$member->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('family_members', [
            'family_id' => $family['id'],
            'user_id' => $member->id,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/families/{$family['id']}/members/{$owner->id}")
            ->assertStatus(422);
    }
}
