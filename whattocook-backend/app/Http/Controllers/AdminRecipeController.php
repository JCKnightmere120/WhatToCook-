<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class AdminRecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::orderBy('name')->paginate(20);

        return view('admin.recipes.index', ['recipes' => $recipes]);
    }

    public function create()
    {
        return view('admin.recipes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // Accept ingredients as JSON string (from simple admin form) or array
        if (isset($data['ingredients']) && is_string($data['ingredients'])) {
            $decoded = json_decode($data['ingredients'], true);
            $data['ingredients'] = is_array($decoded) ? $decoded : [];
        }

        $recipe = Recipe::create($data);

        if (! empty($data['ingredients'] ?? [])) {
            $recipe->ingredients()->createMany($data['ingredients']);
        }

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe created.');
    }

    public function edit(Recipe $recipe)
    {
        return view('admin.recipes.edit', ['recipe' => $recipe]);
    }

    public function update(Request $request, Recipe $recipe)
    {
        $data = $this->validated($request, true);
        if (isset($data['ingredients']) && is_string($data['ingredients'])) {
            $decoded = json_decode($data['ingredients'], true);
            $data['ingredients'] = is_array($decoded) ? $decoded : [];
        }
        $recipe->update(collect($data)->except('ingredients')->all());
        if (array_key_exists('ingredients', $data)) {
            $recipe->ingredients()->delete();
            $recipe->ingredients()->createMany($data['ingredients']);
        }

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe updated.');
    }

    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe deleted.');
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $prefix = $partial ? 'sometimes|' : 'required|';

        return $request->validate([
            'name' => $prefix.'string|max:255', 'description' => 'nullable|string', 'instructions' => ($partial ? 'sometimes|' : 'required|').'string', 'cooking_tips' => 'nullable|string', 'region' => 'nullable|string|max:255',
            'prep_time' => 'nullable|integer|min:0', 'cook_time' => 'nullable|integer|min:0', 'servings' => 'nullable|integer|min:1',
            'meal_type' => 'nullable|string|max:255', 'difficulty' => 'nullable|string|max:255', 'image' => 'nullable|string|max:2048',
            'calories' => 'nullable|numeric|min:0', 'protein' => 'nullable|numeric|min:0', 'carbs' => 'nullable|numeric|min:0', 'fat' => 'nullable|numeric|min:0',
            'ingredients' => ($partial ? 'sometimes|' : 'required|').'array|min:1', 'ingredients.*.name' => 'required_with:ingredients|string|max:255',
            'ingredients.*.quantity' => 'nullable|string|max:255', 'ingredients.*.unit' => 'nullable|string|max:255',
        ]);
    }
}
