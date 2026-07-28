<?php

namespace App\Services;

use App\Models\PantryItem;
use App\Models\Recipe;
use Illuminate\Support\Collection;

class RecipeMatcher
{
    private const SUBSTITUTES = [
        'calamansi' => ['lemon', 'lime'],
        'soy sauce' => ['tamari', 'coconut aminos'],
        'fish sauce' => ['soy sauce', 'salt'],
        'coconut milk' => ['evaporated milk', 'coconut cream'],
        'pork' => ['chicken', 'firm tofu'],
        'chicken' => ['pork', 'firm tofu'],
        'egg' => ['silken tofu', 'flax egg'],
    ];

    public function match(Recipe $recipe, Collection $pantry): array
    {
        $ingredients = $recipe->ingredients->map(function ($ingredient) use ($pantry) {
            $key = $this->normalise($ingredient->name);
            $pantryItem = $pantry->first(fn (PantryItem $item) => $this->isIngredientMatch($key, $this->normalise($item->name)));
            $required = $this->number($ingredient->quantity);
            $inStock = $pantryItem ? $this->number($pantryItem->quantity) : null;
            $sufficient = $pantryItem && ($required === null || $inStock === null || $inStock >= $required);

            return [
                'name' => $ingredient->name,
                'quantity' => $ingredient->quantity,
                'unit' => $ingredient->unit,
                'available' => (bool) $pantryItem,
                'sufficient' => $sufficient,
                'pantry_quantity' => $pantryItem?->quantity,
                'substitutes' => self::SUBSTITUTES[$key] ?? [],
            ];
        });

        $matched = $ingredients->where('sufficient', true)->count();
        $total = $ingredients->count();

        return [
            'recipe' => $recipe,
            'match_percentage' => $total ? (int) round(($matched / $total) * 100) : 0,
            'available_ingredients' => $ingredients->where('sufficient', true)->values(),
            'missing_ingredients' => $ingredients->where('sufficient', false)->values(),
        ];
    }

    private function normalise(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }

    private function number(?string $value): ?float
    {
        return $value !== null && preg_match('/\d+(?:\.\d+)?/', $value, $match) ? (float) $match[0] : null;
    }

    private function isIngredientMatch(string $recipeIngredient, string $pantryIngredient): bool
    {
        return $recipeIngredient === $pantryIngredient
            || str_contains($recipeIngredient, $pantryIngredient)
            || str_contains($pantryIngredient, $recipeIngredient);
    }
}
