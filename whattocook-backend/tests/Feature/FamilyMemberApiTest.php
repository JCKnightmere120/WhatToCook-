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
        $this->assertDatabaseHas('household_profiles', ['family_id' => $family['id'], 'user_id' => $member->id, 'name' => $member->name]);
    }

    public function test_an_owner_can_remove_a_registered_member_but_not_themself(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $family = $this->actingAs($owner, 'sanctum')
            ->postJson('/api/families', ['name' => 'Santos Household'])
            ->assertCreated()
            ->json();

        $invitation = $this->actingAs($owner, 'sanctum')
            ->postJson("/api/families/{$family['id']}/members", [
                'email' => $member->email,
                'role' => 'member',
            ])
            ->assertCreated()->json('invitation');

        $this->actingAs($member, 'sanctum')
            ->postJson("/api/family-invitations/{$invitation['id']}/accept")
            ->assertOk();
        $this->assertDatabaseHas('household_profiles', [
            'family_id' => $family['id'],
            'user_id' => $member->id,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/families/{$family['id']}/members/{$member->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('family_members', [
            'family_id' => $family['id'],
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseMissing('household_profiles', [
            'family_id' => $family['id'],
            'user_id' => $member->id,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/families/{$family['id']}/members/{$owner->id}")
            ->assertStatus(422);
    }

    public function test_pending_invitees_cannot_access_or_write_family_data_until_acceptance(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create();
        $family = $this->actingAs($owner, 'sanctum')->postJson('/api/families', ['name' => 'Santos Household'])->json();
        $invitation = $this->actingAs($owner, 'sanctum')->postJson("/api/families/{$family['id']}/members", [
            'email' => $invitee->email,
            'role' => 'member',
        ])->assertCreated()->json('invitation');

        $this->actingAs($invitee, 'sanctum')->getJson("/api/families/{$family['id']}")->assertForbidden();
        $this->actingAs($invitee, 'sanctum')->postJson('/api/pantry', [
            'name' => 'Rice', 'quantity' => 1, 'unit' => 'kg', 'family_id' => $family['id'],
        ])->assertForbidden();
        $this->actingAs($invitee, 'sanctum')->postJson('/api/families/join', ['join_code' => $family['join_code']])->assertForbidden();

        $this->actingAs($invitee, 'sanctum')->postJson("/api/family-invitations/{$invitation['id']}/accept")
            ->assertOk();
        $this->actingAs($invitee, 'sanctum')->getJson("/api/families/{$family['id']}")->assertOk();
    }
}
