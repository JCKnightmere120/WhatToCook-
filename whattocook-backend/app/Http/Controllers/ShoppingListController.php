<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\ShoppingList;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShoppingListController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            ShoppingList::where('user_id', $request->user()->id)->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->data($request);

        $item = ShoppingList::create($data + ['user_id' => $request->user()->id]);

        return response()->json($item, 201);
    }

    public function update(Request $request, ShoppingList $shoppingList)
    {
        $this->owns($request, $shoppingList);

        $shoppingList->update($this->data($request, true));

        return response()->json($shoppingList);
    }

    public function destroy(Request $request, ShoppingList $shoppingList)
    {
        $this->owns($request, $shoppingList);

        $shoppingList->delete();

        return response()->noContent();
    }

    /**
     * Generate shopping list entries from a recipe's missing ingredients
     * (ingredients not already available in the user's own or family pantry).
     */
    public function generateFromRecipe(Request $request, Recipe $recipe)
    {
        $familyIds = FamilyMember::where('user_id', $request->user()->id)->pluck('family_id');

        $pantryNames = PantryItem::where(function ($query) use ($request, $familyIds) {
                $query->where('user_id', $request->user()->id)
                    ->orWhereIn('family_id', $familyIds);
            })
            ->pluck('name')
            ->map(fn ($name) => Str::lower(trim($name)))
            ->unique()
            ->values()
            ->all();

        $existingListNames = ShoppingList::where('user_id', $request->user()->id)
            ->pluck('ingredient_name')
            ->map(fn ($name) => Str::lower(trim($name)))
            ->all();

        $added = [];
        $skipped = [];

        foreach ($recipe->ingredients as $ingredient) {
            $ingredientName = Str::lower(trim($ingredient->name));

            $inPantry = collect($pantryNames)->contains(
                fn ($pantryName) => $pantryName !== '' && (
                    Str::contains($ingredientName, $pantryName) || Str::contains($pantryName, $ingredientName)
                )
            );

            if ($inPantry) {
                continue;
            }

            if (in_array($ingredientName, $existingListNames, true)) {
                $skipped[] = $ingredient->name;
                continue;
            }

            ShoppingList::create([
                'user_id' => $request->user()->id,
                'ingredient_name' => $ingredient->name,
                'quantity' => $ingredient->quantity,
                'unit' => $ingredient->unit,
                'is_purchased' => false,
            ]);

            $added[] = $ingredient->name;
        }

        return response()->json([
            'recipe' => $recipe->name,
            'added_to_shopping_list' => $added,
            'already_in_list' => $skipped,
            'message' => count($added) > 0
                ? count($added).' ingredient(s) added to your shopping list.'
                : 'Nothing new to add — all missing ingredients are already in your shopping list.',
        ]);
    }

    private function data(Request $request, bool $partial = false): array
    {
        $rule = $partial ? 'sometimes|' : 'required|';

        $data = $request->validate([
            'ingredient_name' => $rule.'string|max:255',
            'quantity' => 'sometimes|nullable|string|max:255',
            'unit' => 'sometimes|nullable|string|max:255',
            'is_purchased' => 'sometimes|boolean',
            'family_id' => 'sometimes|nullable|exists:families,id',
        ]);

        if (!empty($data['family_id'])) {
            abort_unless(
                FamilyMember::where(['family_id' => $data['family_id'], 'user_id' => $request->user()->id])->exists(),
                403
            );
        }

        return $data;
    }

    private function owns(Request $request, ShoppingList $item): void
    {
        abort_unless($item->user_id === $request->user()->id, 403);
    }
}