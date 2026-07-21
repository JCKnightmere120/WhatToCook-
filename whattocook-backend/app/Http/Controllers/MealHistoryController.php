<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\MealHistory;
use Illuminate\Http\Request;

class MealHistoryController extends Controller
{
    public function index(Request $request) { return response()->json(MealHistory::with('recipe')->where('user_id', $request->user()->id)->latest('prepared_at')->get()); }
    public function store(Request $request) { $data = $this->data($request); return response()->json(MealHistory::create($data + ['user_id' => $request->user()->id]), 201); }
    public function update(Request $request, MealHistory $mealHistory) { $this->owns($request, $mealHistory); $mealHistory->update($this->data($request, true)); return response()->json($mealHistory); }
    public function destroy(Request $request, MealHistory $mealHistory) { $this->owns($request, $mealHistory); $mealHistory->delete(); return response()->noContent(); }
    private function data(Request $request, bool $partial = false): array { $p = $partial ? 'sometimes|' : 'required|'; $data = $request->validate(['recipe_id' => $p.'exists:recipes,id', 'prepared_at' => $p.'date', 'servings' => 'sometimes|nullable|integer|min:1', 'notes' => 'sometimes|nullable|string|max:5000', 'family_id' => 'sometimes|nullable|exists:families,id']); if (!empty($data['family_id'])) abort_unless(FamilyMember::where(['family_id' => $data['family_id'], 'user_id' => $request->user()->id])->exists(), 403); return $data; }
    private function owns(Request $request, MealHistory $history): void { abort_unless($history->user_id === $request->user()->id, 403); }
}
