<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\MealPlan;
use App\Models\PantryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MealPlanController extends Controller
{
    public function index(Request $request) { return response()->json(MealPlan::with('recipe')->where('user_id', $request->user()->id)->orderBy('planned_date')->get()); }
    public function store(Request $request) { $data = $this->data($request); return response()->json(MealPlan::create($data + ['user_id' => $request->user()->id]), 201); }
    public function update(Request $request, MealPlan $mealPlan) { $this->owns($request, $mealPlan); $mealPlan->update($this->data($request, true)); return response()->json($mealPlan); }
    public function destroy(Request $request, MealPlan $mealPlan) { $this->owns($request, $mealPlan); $mealPlan->delete(); return response()->noContent(); }
    public function complete(Request $request, MealPlan $mealPlan)
    {
        $this->owns($request, $mealPlan);
        abort_if($mealPlan->completed_at, 422, 'This meal has already been cooked.');
        $plan = DB::transaction(function () use ($mealPlan, $request) {
            $mealPlan->load('recipe.ingredients');
            $consumed = [];
            foreach ($mealPlan->recipe->ingredients as $ingredient) {
                if (!is_numeric($ingredient->quantity)) continue;
                $query = PantryItem::whereRaw('lower(name) = ?', [strtolower($ingredient->name)])->whereIn('freshness_status', ['fresh', 'review'])->lockForUpdate();
                if ($mealPlan->family_id) $query->where('family_id', $mealPlan->family_id); else $query->where('user_id', $request->user()->id)->whereNull('family_id');
                $item = $query->first();
                $needed = (float) $ingredient->quantity;
                if (!$item || (float) $item->quantity_value < $needed) throw ValidationException::withMessages(['pantry' => ["Not enough {$ingredient->name} to cook this meal."]]);
                $remaining = round((float) $item->quantity_value - $needed, 3);
                $consumed[] = ['pantry_item_id' => $item->id, 'quantity' => $needed, 'unit' => $ingredient->unit];
                $item->update(['quantity_value' => $remaining, 'quantity' => (string) $remaining, 'last_used_quantity' => $needed, 'previous_freshness_status' => $item->freshness_status, 'freshness_status' => $remaining <= 0 ? 'used' : $item->freshness_status]);
            }
            $mealPlan->update(['completed_at' => now(), 'consumed_items' => $consumed]);
            return $mealPlan->fresh()->load('recipe');
        });
        return response()->json(['meal_plan' => $plan, 'message' => 'Meal cooked and pantry stock deducted.']);
    }
    private function data(Request $request, bool $partial = false): array { $p = $partial ? 'sometimes|' : 'required|'; $data = $request->validate(['recipe_id' => $p.'exists:recipes,id', 'planned_date' => $p.'date', 'meal_type' => $p.'string|max:255', 'family_id' => 'nullable|exists:families,id']); if (!empty($data['family_id'])) abort_unless(FamilyMember::where(['family_id' => $data['family_id'], 'user_id' => $request->user()->id])->exists(), 403); return $data; }
    private function owns(Request $request, MealPlan $plan): void { abort_unless($plan->user_id === $request->user()->id, 403); }
}
