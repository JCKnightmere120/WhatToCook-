<?php

namespace App\Http\Controllers;

use App\Services\UsdaFoodDataService;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    public function search(Request $request, UsdaFoodDataService $usda)
    {
        $data = $request->validate(['query' => 'required|string|min:2|max:255', 'page_size' => 'nullable|integer|min:1|max:50']);

        return response()->json($usda->search($data['query'], $data['page_size'] ?? 10));
    }

    public function show(int $fdcId, UsdaFoodDataService $usda)
    {
        return response()->json($usda->food($fdcId));
    }
}
