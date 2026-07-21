<?php

namespace App\Http\Controllers;

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
        return response()->json($request->user()->profile()->updateOrCreate([], $data));
    }
}
