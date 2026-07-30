<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Recipe;

class RecipeNutritionService
{
    private const NUTRIENTS = ['calories', 'protein', 'carbs', 'fat', 'fiber', 'sodium', 'sugar'];

    /** Returns recipe totals and per-serving nutrition, all derived on the server. */
    public function calculate(Recipe $recipe): array
    {
        $recipe->loadMissing('ingredients.nutritionFood');
        $totals = array_fill_keys(self::NUTRIENTS, 0.0);
        $unmatched = [];

        foreach ($recipe->ingredients as $ingredient) {
            $grams = $this->gramsFor($ingredient);
            if (! $ingredient->nutritionFood || $grams === null) {
                $unmatched[] = ['ingredient_id' => $ingredient->id, 'name' => $ingredient->name, 'reason' => ! $ingredient->nutritionFood ? 'nutrition_food_not_linked' : 'quantity_cannot_be_converted_to_grams'];

                continue;
            }
            foreach (self::NUTRIENTS as $nutrient) {
                $totals[$nutrient] += ((float) ($ingredient->nutritionFood->nutrients[$nutrient] ?? 0)) * $grams / 100;
            }
        }

        $servings = max(1, (int) ($recipe->servings ?: 1));
        $round = fn (array $values) => collect($values)->map(fn ($value) => round($value, 2))->all();

        return [
            'recipe_id' => $recipe->id,
            'servings' => $servings,
            'totals' => $round($totals),
            'per_serving' => $round(collect($totals)->map(fn ($value) => $value / $servings)->all()),
            'unmatched_ingredients' => $unmatched,
            'is_complete' => $unmatched === [],
        ];
    }

    public function updateRecipeMacros(Recipe $recipe): array
    {
        $nutrition = $this->calculate($recipe);
        $recipe->update(collect($nutrition['per_serving'])->only(['calories', 'protein', 'carbs', 'fat'])->all());

        return $nutrition;
    }

    private function gramsFor(Ingredient $ingredient): ?float
    {
        if ($ingredient->nutrition_grams !== null) {
            return (float) $ingredient->nutrition_grams;
        }
        $quantity = $this->number((string) $ingredient->quantity);
        if ($quantity === null) {
            return null;
        }

        return match (strtolower(trim((string) $ingredient->unit))) {
            'g', 'gram', 'grams' => $quantity,
            'kg', 'kilogram', 'kilograms' => $quantity * 1000,
            'mg', 'milligram', 'milligrams' => $quantity / 1000,
            'oz', 'ounce', 'ounces' => $quantity * 28.3495,
            'lb', 'lbs', 'pound', 'pounds' => $quantity * 453.592,
            default => null,
        };
    }

    private function number(string $value): ?float
    {
        $value = trim($value);
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', $value, $matches) && (int) $matches[2] !== 0) {
            return (int) $matches[1] / (int) $matches[2];
        }
        if (preg_match('/^(\d+)\s+(\d+)\s*\/\s*(\d+)$/', $value, $matches) && (int) $matches[3] !== 0) {
            return (int) $matches[1] + ((int) $matches[2] / (int) $matches[3]);
        }

        return null;
    }
}
