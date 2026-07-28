<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\FamilyMember;
use App\Models\HouseholdProfile;
use App\Models\PantryItem;
use App\Models\Profile;
use App\Services\RecipeMatcher;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function recommendations(Request $request, RecipeMatcher $matcher)
    {
        $familyId = $request->validate(['family_id' => 'nullable|integer|exists:families,id'])['family_id'] ?? null;
        if ($familyId !== null) {
            abort_unless(FamilyMember::where(['family_id' => $familyId, 'user_id' => $request->user()->id, 'status' => 'accepted'])->exists(), 403);
        }

        $pantry = PantryItem::where(fn ($query) => $familyId === null
            ? $query->where('user_id', $request->user()->id)
            : $query->where(fn ($items) => $items->where('user_id', $request->user()->id)->whereNull('family_id'))->orWhere('family_id', $familyId))
            ->whereIn('freshness_status', ['fresh', 'review'])
            ->get();
        $blockedIngredients = $familyId === null
            ? $this->profileBlockedIngredients(Profile::where('user_id', $request->user()->id)->first())
            : $this->familyBlockedIngredients($familyId);
        $results = Recipe::with('ingredients')->get()
            ->filter(fn (Recipe $recipe) => ! $this->containsBlockedIngredient($recipe, $blockedIngredients))
            ->map(fn (Recipe $recipe) => $matcher->match($recipe, $pantry))
            ->sortByDesc('match_percentage')
            ->values();

        return response()->json(['recommendations' => $results]);
    }

    private function familyBlockedIngredients(int $familyId): array
    {
        return HouseholdProfile::where('family_id', $familyId)->get()
            ->flatMap(function (HouseholdProfile $profile) {
                $allergies = $profile->allergies ?? [];
                $restrictions = $profile->dietary_restrictions ?? [];
                $blocked = array_map('strtolower', $allergies);
                if (in_array('vegetarian', array_map('strtolower', $restrictions), true)) {
                    $blocked = [...$blocked, 'chicken', 'pork', 'beef', 'fish', 'seafood', 'meat'];
                }
                if (in_array('vegan', array_map('strtolower', $restrictions), true)) {
                    $blocked = [...$blocked, 'chicken', 'pork', 'beef', 'fish', 'seafood', 'meat', 'egg', 'milk', 'dairy'];
                }
                return $blocked;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function profileBlockedIngredients(?Profile $profile): array
    {
        if (! $profile) return [];
        $blocked = array_map('strtolower', $profile->allergies ?? []);
        $restrictions = array_map('strtolower', $profile->dietary_restrictions ?? []);
        if (in_array('vegetarian', $restrictions, true)) $blocked = [...$blocked, 'chicken', 'pork', 'beef', 'fish', 'seafood', 'meat'];
        if (in_array('vegan', $restrictions, true)) $blocked = [...$blocked, 'chicken', 'pork', 'beef', 'fish', 'seafood', 'meat', 'egg', 'milk', 'dairy'];
        return array_values(array_unique($blocked));
    }

    private function containsBlockedIngredient(Recipe $recipe, array $blockedIngredients): bool
    {
        return $recipe->ingredients->contains(function ($ingredient) use ($blockedIngredients) {
            $name = strtolower($ingredient->name);
            foreach ($blockedIngredients as $blocked) {
                if (str_contains($name, $blocked)) return true;
            }
            return false;
        });
    }

    public function index(Request $request)
    {
        return response()->json(Recipe::with('ingredients')->when($request->region, fn ($q, $region) => $q->where('region', $region))->paginate(20));
    }

    public function show(Recipe $recipe) { return response()->json($recipe->load('ingredients')); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $recipe = Recipe::create($data + ['created_by' => $request->user()->id]);
        $recipe->ingredients()->createMany($data['ingredients'] ?? []);
        return response()->json($recipe->load('ingredients'), 201);
    }

    public function update(Request $request, Recipe $recipe)
    {
        abort_unless($recipe->created_by === $request->user()->id, 403);
        $data = $this->validated($request, true);
        $recipe->update(collect($data)->except('ingredients')->all());
        if (array_key_exists('ingredients', $data)) { $recipe->ingredients()->delete(); $recipe->ingredients()->createMany($data['ingredients']); }
        return response()->json($recipe->load('ingredients'));
    }

    public function destroy(Request $request, Recipe $recipe) { abort_unless($recipe->created_by === $request->user()->id, 403); $recipe->delete(); return response()->noContent(); }

    private function validated(Request $request, bool $partial = false): array
    {
        $prefix = $partial ? 'sometimes|' : 'required|';
        return $request->validate([
            'name' => $prefix.'string|max:255', 'description' => 'nullable|string', 'instructions' => ($partial ? 'sometimes|' : 'required|').'string', 'cooking_tips' => 'nullable|string', 'region' => 'nullable|string|max:255',
            'prep_time' => 'nullable|integer|min:0', 'cook_time' => 'nullable|integer|min:0', 'servings' => 'nullable|integer|min:1',
            'meal_type' => 'nullable|string|max:255', 'difficulty' => 'nullable|string|max:255', 'image' => 'nullable|string|max:2048',
            'calories' => 'nullable|numeric|min:0', 'protein' => 'nullable|numeric|min:0', 'carbs' => 'nullable|numeric|min:0', 'fat' => 'nullable|numeric|min:0',
            'ingredients' => ($partial ? 'sometimes|' : 'required|').'array|min:1', 'ingredients.*.name' => 'required_with:ingredients|string|max:255',
            'ingredients.*.quantity' => 'nullable|string|max:255', 'ingredients.*.unit' => 'nullable|string|max:255', 'ingredients.*.is_substitute' => 'nullable|boolean',
        ]);
    }
}
