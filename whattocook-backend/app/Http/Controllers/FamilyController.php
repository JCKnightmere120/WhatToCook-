<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\HouseholdProfile;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Family::whereHas('members', fn ($q) => $q->where('user_id', $request->user()->id)->where('status', 'accepted'))->with(['members' => fn ($q) => $q->where('status', 'accepted')->with('user')])->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $family = Family::create(['name' => $data['name'], 'owner_id' => $request->user()->id]);
        FamilyMember::create(['family_id' => $family->id, 'user_id' => $request->user()->id, 'role' => 'owner', 'status' => 'accepted']);
        $this->ensureDinerProfile($family, $request->user());

        return response()->json($family->load('members.user'), 201);
    }

    public function show(Request $request, Family $family)
    {
        $this->member($request, $family);

        return response()->json($family->load(['members' => fn ($query) => $query->where('status', 'accepted')->with('user')]));
    }

    public function join(Request $request)
    {
        $code = strtoupper(trim($request->validate(['join_code' => 'required|string|size:8'])['join_code']));
        $family = Family::where('join_code', $code)->first();
        abort_unless($family, 404, 'No household was found with that invite code.');
        $membership = FamilyMember::firstOrCreate(['family_id' => $family->id, 'user_id' => $request->user()->id], ['role' => 'member', 'status' => 'accepted']);
        abort_if($membership->status !== 'accepted', 403, 'Accept the existing invitation before accessing this household.');
        $this->ensureDinerProfile($family, $request->user());

        return response()->json(['family' => $family->load('members.user'), 'joined' => $membership->wasRecentlyCreated], $membership->wasRecentlyCreated ? 201 : 200);
    }

    public function addMember(Request $request, Family $family)
    {
        $this->owner($request, $family);
        $data = $request->validate(['email' => 'required|email|exists:users,email', 'role' => 'required|in:member']);
        $user = User::where('email', $data['email'])->firstOrFail();
        $membership = FamilyMember::firstOrCreate(['family_id' => $family->id, 'user_id' => $user->id], ['role' => $data['role'], 'status' => 'pending', 'invited_by_user_id' => $request->user()->id]);

        return response()->json(['invitation' => $membership->load('user'), 'message' => $membership->wasRecentlyCreated ? 'Invitation sent. The account must accept before joining.' : 'This account already has a household membership or pending invitation.'], $membership->wasRecentlyCreated ? 201 : 200);
    }

    public function invitations(Request $request)
    {
        return response()->json(['invitations' => FamilyMember::where('user_id', $request->user()->id)->where('status', 'pending')->with('family.owner')->get()]);
    }

    public function acceptInvitation(Request $request, FamilyMember $familyMember)
    {
        abort_unless($familyMember->user_id === $request->user()->id && $familyMember->status === 'pending', 403);
        $familyMember->update(['status' => 'accepted']);
        $this->ensureDinerProfile($familyMember->family, $request->user());

        return response()->json(['family' => $familyMember->family->load('members.user')]);
    }

    public function removeMember(Request $request, Family $family, User $user)
    {
        $this->owner($request, $family);
        abort_if($user->id === $family->owner_id, 422, 'The family owner cannot be removed.');
        FamilyMember::where(['family_id' => $family->id, 'user_id' => $user->id])->firstOrFail()->delete();

        return response()->noContent();
    }

    private function member(Request $request, Family $family): void
    {
        abort_unless(FamilyMember::where(['family_id' => $family->id, 'user_id' => $request->user()->id, 'status' => 'accepted'])->exists(), 403);
    }

    private function owner(Request $request, Family $family): void
    {
        abort_unless($family->owner_id === $request->user()->id, 403);
    }

    /** Every accepted account is automatically a selectable diner. Dependents
     * without an account remain optional, owner-created household profiles. */
    private function ensureDinerProfile(Family $family, User $user): void
    {
        if (HouseholdProfile::where('family_id', $family->id)->where('user_id', $user->id)->exists()) {
            return;
        }

        $profile = Profile::where('user_id', $user->id)->first();
        HouseholdProfile::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'relation' => $family->owner_id === $user->id ? 'Household owner' : 'Family account member',
            'health_conditions' => $profile?->health_conditions,
            'allergies' => $profile?->allergies,
            'dietary_restrictions' => $profile?->dietary_restrictions,
            'likes' => $profile?->likes,
            'dislikes' => $profile?->dislikes,
            'visible_to_family' => $profile?->visible_to_family,
        ]);
    }
}
