<?php

namespace App\Services;

use App\Models\IngredientCatalog;
use Illuminate\Support\Str;

class IngredientCatalogService
{
    public function resolve(string $input): array
    {
        $input = trim(preg_replace('/\s+/', ' ', Str::lower($input)) ?? '');
        if ($input === '') {
            return ['status' => 'rejected', 'input' => $input, 'message' => 'No ingredient was provided.'];
        }
        $items = IngredientCatalog::where('is_approved', true)->get();
        $exact = $items->first(fn ($item) => $item->canonical_name === $input);
        if ($exact) {
            return ['status' => 'accepted', 'input' => $input, 'ingredient' => $this->payload($exact)];
        }
        $alias = $items->first(fn ($item) => in_array($input, array_map(fn ($alias) => Str::lower(trim($alias)), $item->aliases ?? []), true));
        if ($alias) {
            return ['status' => 'suggested', 'input' => $input, 'suggestion' => $this->payload($alias), 'message' => "Confirm '{$alias->canonical_name}' before adding it."];
        }

        // Spoken and OCR text commonly pluralise a catalog's singular item
        // ("onions" vs. "onion"). Resolve only a safe trailing-s variant,
        // then still return a suggestion for the user to review.
        $singular = $this->singular($input);
        if ($singular !== $input) {
            $pluralMatch = $items->first(fn ($item) => Str::lower($item->canonical_name) === $singular
                || in_array($singular, array_map(fn ($alias) => Str::lower(trim($alias)), $item->aliases ?? []), true));
            if ($pluralMatch) {
                return ['status' => 'suggested', 'input' => $input, 'suggestion' => $this->payload($pluralMatch), 'message' => "Confirm '{$pluralMatch->canonical_name}' before adding it."];
            }
        }

        return ['status' => 'rejected', 'input' => $input, 'message' => "Removed '{$input}' — not recognised as a food ingredient."];
    }

    public function search(string $query, int $limit = 10): array
    {
        $query = Str::lower(trim($query));

        return IngredientCatalog::where('is_approved', true)->when($query !== '', fn ($q) => $q->where(fn ($q) => $q->where('canonical_name', 'like', "%{$query}%")->orWhere('aliases', 'like', "%{$query}%")))->orderBy('canonical_name')->limit($limit)->get()->map(fn ($item) => $this->payload($item))->all();
    }

    public function isCanonicalApproved(string $name): bool
    {
        return IngredientCatalog::where('is_approved', true)
            ->whereRaw('lower(canonical_name) = ?', [Str::lower(trim($name))])
            ->exists();
    }

    public function approvedCanonicalName(string $name): ?string
    {
        $name = trim(preg_replace('/\s+/', ' ', Str::lower($name)) ?? '');
        if ($name === '') {
            return null;
        }
        $item = IngredientCatalog::where('is_approved', true)->get()->first(function (IngredientCatalog $item) use ($name) {
            return Str::lower($item->canonical_name) === $name
                || in_array($name, array_map(fn ($alias) => Str::lower(trim($alias)), $item->aliases ?? []), true);
        });

        return $item?->canonical_name;
    }

    private function payload(IngredientCatalog $item): array
    {
        return ['id' => $item->id, 'canonical_name' => $item->canonical_name, 'aliases' => $item->aliases ?? [], 'category' => $item->category, 'default_units' => $item->default_units ?? []];
    }

    private function singular(string $value): string
    {
        // Avoid damaging words such as "rice" or short words such as "as".
        if (strlen($value) > 3 && str_ends_with($value, 'ies')) {
            return substr($value, 0, -3).'y';
        }
        if (strlen($value) > 3 && str_ends_with($value, 's') && ! str_ends_with($value, 'ss')) {
            return substr($value, 0, -1);
        }

        return $value;
    }
}
