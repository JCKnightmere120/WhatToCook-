<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Collection;

/**
 * The single, conservative vocabulary used for allergy and diet exclusions.
 *
 * Profile values remain free-text, but recognised umbrella restrictions are
 * expanded here before a recipe is offered, generated, or saved.
 */
class FoodSafetyTaxonomy
{
    private const EXPANSIONS = [
        'shellfish' => ['shellfish', 'shrimp', 'prawn', 'crab', 'lobster', 'oyster', 'mussel', 'clam', 'scallop'],
        'vegetarian' => ['chicken', 'pork', 'beef', 'fish', 'seafood', 'shellfish', 'meat'],
        'vegan' => ['chicken', 'pork', 'beef', 'fish', 'seafood', 'shellfish', 'meat', 'egg', 'milk', 'dairy', 'cheese', 'butter', 'yogurt', 'honey'],
        'halal' => ['pork', 'ham', 'bacon', 'lard', 'alcohol', 'wine', 'beer', 'liquor', 'spirits', 'rum', 'brandy', 'vodka'],
    ];

    /** @param iterable<object|null> $profiles */
    public function blockedTerms(iterable $profiles): Collection
    {
        return collect($profiles)
            ->filter()
            ->flatMap(function (object $profile) {
                $values = array_merge($profile->allergies ?? [], $profile->dietary_restrictions ?? []);

                return collect($values)
                    ->map(fn ($value) => $this->normalise((string) $value))
                    ->filter()
                    ->flatMap(fn ($value) => self::EXPANSIONS[$value] ?? [$value]);
            })
            ->map(fn ($term) => $this->normalise($term))
            ->filter()
            ->unique()
            ->values();
    }

    public function recipeIsSafe(Recipe $recipe, iterable $profiles): bool
    {
        return ! $this->recipeConflicts($recipe, $this->blockedTerms($profiles));
    }

    /** @param iterable<string> $blockedTerms */
    public function recipeConflicts(Recipe $recipe, iterable $blockedTerms): bool
    {
        $terms = collect($blockedTerms)->filter()->values();
        if ($terms->isEmpty()) {
            return false;
        }

        return $this->recipeTerms($recipe)->contains(fn ($text) => $terms->contains(fn ($term) => $this->containsTerm($text, $term)));
    }

    public function containsTerm(string $text, string $term): bool
    {
        $text = $this->normalise($text);
        $term = $this->normalise($term);
        if ($text === '' || $term === '') {
            return false;
        }

        // Word boundaries prevent vegan's "egg" rule from excluding eggplant.
        return preg_match('/(?<![[:alnum:]])'.preg_quote($term, '/').'(?![[:alnum:]])/u', $text) === 1;
    }

    private function recipeTerms(Recipe $recipe): Collection
    {
        return $recipe->ingredients->pluck('name')->push($recipe->name)->map(fn ($value) => $this->normalise((string) $value));
    }

    private function normalise(string $value): string
    {
        return trim(mb_strtolower($value));
    }
}
