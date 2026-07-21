<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index(Request $request) { return response()->json(Family::whereHas('members', fn ($q) => $q->where('user_id', $request->user()->id))->with('members.user')->get()); }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $family = Family::create(['name' => $data['name'], 'owner_id' => $request->user()->id]);
        FamilyMember::create(['family_id' => $family->id, 'user_id' => $request->user()->id, 'role' => 'owner']);
        return response()->json($family->load('members.user'), 201);
    }

    public function show(Request $request, Family $family) { $this->member($request, $family); return response()->json($family->load('members.user')); }

    public function addMember(Request $request, Family $family)
    {
        $this->owner($request, $family);
        $data = $request->validate(['email' => 'required|email|exists:users,email', 'role' => 'required|in:member']);
        $user = User::where('email', $data['email'])->firstOrFail();
        $membership = FamilyMember::firstOrCreate(['family_id' => $family->id, 'user_id' => $user->id], ['role' => $data['role']]);
        return response()->json($membership->load('user'), $membership->wasRecentlyCreated ? 201 : 200);
    }

    public function removeMember(Request $request, Family $family, User $user)
    {
        $this->owner($request, $family);
        abort_if($user->id === $family->owner_id, 422, 'The family owner cannot be removed.');
        FamilyMember::where(['family_id' => $family->id, 'user_id' => $user->id])->firstOrFail()->delete();
        return response()->noContent();
    }

    private function member(Request $request, Family $family): void { abort_unless(FamilyMember::where(['family_id' => $family->id, 'user_id' => $request->user()->id])->exists(), 403); }
    private function owner(Request $request, Family $family): void { abort_unless($family->owner_id === $request->user()->id, 403); }
}
