<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
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
