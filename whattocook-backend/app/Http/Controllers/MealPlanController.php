<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\MealPlan;
use Illuminate\Http\Request;

class MealPlanController extends Controller
{
    public function index(Request $request) { return response()->json(MealPlan::with('recipe')->where('user_id', $request->user()->id)->orderBy('planned_date')->get()); }
    public function store(Request $request) { $data = $this->data($request); return response()->json(MealPlan::create($data + ['user_id' => $request->user()->id]), 201); }
    public function update(Request $request, MealPlan $mealPlan) { $this->owns($request, $mealPlan); $mealPlan->update($this->data($request, true)); return response()->json($mealPlan); }
    public function destroy(Request $request, MealPlan $mealPlan) { $this->owns($request, $mealPlan); $mealPlan->delete(); return response()->noContent(); }
    private function data(Request $request, bool $partial = false): array { $p = $partial ? 'sometimes|' : 'required|'; $data = $request->validate(['recipe_id' => $p.'exists:recipes,id', 'planned_date' => $p.'date', 'meal_type' => $p.'string|max:255', 'family_id' => 'nullable|exists:families,id']); if (!empty($data['family_id'])) abort_unless(FamilyMember::where(['family_id' => $data['family_id'], 'user_id' => $request->user()->id])->exists(), 403); return $data; }
    private function owns(Request $request, MealPlan $plan): void { abort_unless($plan->user_id === $request->user()->id, 403); }
}
