<?php

namespace App\Http\Controllers;

use App\Models\HouseholdProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request) { return response()->json($request->user()->profile()->firstOrCreate([])); }

    public function update(Request $request)
    {
        $data = $request->validate([
            'health_conditions' => 'sometimes|array', 'allergies' => 'sometimes|array',
            'dietary_restrictions' => 'sometimes|array', 'likes' => 'sometimes|array',
            'dislikes' => 'sometimes|array', 'visible_to_family' => 'sometimes|array',
        ]);
        $profile = $request->user()->profile()->updateOrCreate([], $data);

        // A signed-in person's health and food rules have one source of truth.
        // Mirror changes into every linked household diner profile so family
        // recommendations cannot become less strict than personal planning.
        HouseholdProfile::where('user_id', $request->user()->id)->update($data);

        return response()->json($profile);
    }
}
