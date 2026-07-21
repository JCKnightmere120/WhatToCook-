<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\PantryItem;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecipeMatchController extends Controller
{
    public function matched(Request $request)
    {
        $pantryNames = $this->pantryNames($request);

        $recipes = Recipe::with('ingredients')->get()->map(function (Recipe $recipe) use ($pantryNames) {
            $matched = [];
            $missing = [];

            foreach ($recipe->ingredients as $ingredient) {
                if ($this->isAvailable($ingredient->name, $pantryNames)) {
                    $matched[] = $ingredient->name;
                } else {
                    $missing[] = $ingredient->name;
                }
            }

            $total = $matched ? count($matched) + count($missing) : count($missing);
            $total = max($total, 1);

            return [
                'id' => $recipe->id,
                'name' => $recipe->name,
                'region' => $recipe->region,
                'meal_type' => $recipe->meal_type,
                'difficulty' => $recipe->difficulty,
                'prep_time' => $recipe->prep_time,
                'cook_time' => $recipe->cook_time,
                'servings' => $recipe->servings,
                'image' => $recipe->image,
                'match_percentage' => round((count($matched) / $total) * 100),
                'matched_ingredients' => $matched,
                'missing_ingredients' => $missing,
                'is_fully_matched' => count($missing) === 0,
            ];
        });

        $sorted = $recipes->sortByDesc('match_percentage')->values();

        if ($request->boolean('fully_matched_only')) {
            $sorted = $sorted->where('is_fully_matched', true)->values();
        }

        return response()->json($sorted);
    }

    /**
     * Collect the pantry item names visible to the user (own pantry + any family pantry).
     */
    private function pantryNames(Request $request): array
    {
        $familyIds = FamilyMember::where('user_id', $request->user()->id)->pluck('family_id');

        return PantryItem::where(function ($query) use ($request, $familyIds) {
            $query->where('user_id', $request->user()->id)
                ->orWhereIn('family_id', $familyIds);
        })
            ->pluck('name')
            ->map(fn ($name) => Str::lower(trim($name)))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Check if a recipe ingredient is considered available in the pantry,
     * using a case-insensitive substring match in either direction.
     */
    private function isAvailable(string $ingredientName, array $pantryNames): bool
    {
        $ingredientName = Str::lower(trim($ingredientName));

        foreach ($pantryNames as $pantryName) {
            if ($pantryName === '') {
                continue;
            }

            if (Str::contains($ingredientName, $pantryName) || Str::contains($pantryName, $ingredientName)) {
                return true;
            }
        }

        return false;
    }
}