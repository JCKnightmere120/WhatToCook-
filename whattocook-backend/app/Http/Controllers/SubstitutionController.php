<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\PantryItem;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubstitutionController extends Controller
{
    /**
     * Suggest substitutes for a recipe's missing ingredients,
     * flagging any substitute the user already has in their pantry.
     */
    public function forRecipe(Request $request, Recipe $recipe)
    {
        $substitutes = config('ingredient_substitutes');
        $pantryNames = $this->pantryNames($request);

        $suggestions = [];

        foreach ($recipe->ingredients as $ingredient) {
            $ingredientName = Str::lower(trim($ingredient->name));

            // Skip ingredients already available in the pantry — no substitute needed.
            if ($this->isAvailable($ingredientName, $pantryNames)) {
                continue;
            }

            $options = $substitutes[$ingredientName] ?? $this->findClosestKey($ingredientName, $substitutes);

            if (!$options) {
                continue;
            }

            $available = [];
            $unavailable = [];

            foreach ($options as $option) {
                if ($this->isAvailable(Str::lower($option), $pantryNames)) {
                    $available[] = $option;
                } else {
                    $unavailable[] = $option;
                }
            }

            $suggestions[] = [
                'missing_ingredient' => $ingredient->name,
                'available_substitutes' => $available,
                'other_common_substitutes' => $unavailable,
            ];
        }

        return response()->json([
            'recipe' => $recipe->name,
            'substitution_suggestions' => $suggestions,
        ]);
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

    private function isAvailable(string $name, array $pantryNames): bool
    {
        foreach ($pantryNames as $pantryName) {
            if ($pantryName === '') {
                continue;
            }

            if (Str::contains($name, $pantryName) || Str::contains($pantryName, $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If the ingredient name isn't an exact key in the substitution map,
     * try a loose match (e.g. "chicken thighs" containing "chicken").
     */
    private function findClosestKey(string $ingredientName, array $substitutes): ?array
    {
        foreach ($substitutes as $key => $options) {
            if (Str::contains($ingredientName, $key) || Str::contains($key, $ingredientName)) {
                return $options;
            }
        }

        return null;
    }
}