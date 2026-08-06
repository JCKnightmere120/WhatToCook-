<?php

namespace App\Http\Controllers;

use App\Models\NutritionFood;
use App\Services\UsdaFoodDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NutritionController extends Controller
{
    public function search(Request $request, UsdaFoodDataService $usda)
    {
        $data = $request->validate(['query' => 'required|string|min:2|max:255', 'page_size' => 'nullable|integer|min:1|max:50']);

        $raw = $usda->search($data['query'], $data['page_size'] ?? 10);

        return response()->json($raw + ['normalized_foods' => $usda->normalizedFoods($raw)]);
    }

    public function show(int $fdcId, UsdaFoodDataService $usda)
    {
        return response()->json($usda->cacheFood($fdcId));
    }

    public function cache(int $fdcId, UsdaFoodDataService $usda)
    {
        return response()->json($usda->cacheFood($fdcId), 201);
    }

    /** A verified fallback for Filipino ingredients or brands absent from USDA. */
    public function storeLocal(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'nutrients' => 'required|array',
            'nutrients.calories' => 'nullable|numeric|min:0',
            'nutrients.protein' => 'nullable|numeric|min:0',
            'nutrients.carbs' => 'nullable|numeric|min:0',
            'nutrients.fat' => 'nullable|numeric|min:0',
            'nutrients.fiber' => 'nullable|numeric|min:0',
            'nutrients.sodium' => 'nullable|numeric|min:0',
            'nutrients.sugar' => 'nullable|numeric|min:0',
        ]);
        $provided = array_keys(array_filter($data['nutrients'], fn ($value) => $value !== null));
        $nutrients = array_merge(array_fill_keys(['calories', 'protein', 'carbs', 'fat', 'fiber', 'sodium', 'sugar'], 0), $data['nutrients']);
        $nutrients['available_nutrients'] = $provided;
        $food = NutritionFood::updateOrCreate(
            ['normalized_name' => Str::lower(trim($data['description'])), 'source' => 'local'],
            ['description' => $data['description'], 'nutrients' => $nutrients, 'fetched_at' => now()]
        );

        return response()->json($food, $food->wasRecentlyCreated ? 201 : 200);
    }
}
