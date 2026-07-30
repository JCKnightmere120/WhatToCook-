<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Services\RecipeMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShoppingListController extends Controller
{
    public function index(Request $request)
    {
<<<<<<< HEAD
        return response()->json(
            ShoppingList::where('user_id', $request->user()->id)->get()
        );
=======
        return response()->json($this->visibleTo($request)->get());
>>>>>>> origin
    }

    public function store(Request $request)
    {
        $data = $this->data($request);

<<<<<<< HEAD
        $item = ShoppingList::create($data + ['user_id' => $request->user()->id]);

        return response()->json($item, 201);
=======
        return response()->json(ShoppingList::create($data + ['user_id' => $request->user()->id]), 201);
>>>>>>> origin
    }

    public function update(Request $request, ShoppingList $shoppingList)
    {
        $this->owns($request, $shoppingList);
<<<<<<< HEAD

=======
>>>>>>> origin
        $shoppingList->update($this->data($request, true));

        return response()->json($shoppingList);
    }

    public function destroy(Request $request, ShoppingList $shoppingList)
    {
        $this->owns($request, $shoppingList);
<<<<<<< HEAD

=======
>>>>>>> origin
        $shoppingList->delete();

        return response()->noContent();
    }

<<<<<<< HEAD
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
=======
    public function generate(Request $request, Recipe $recipe, RecipeMatcher $matcher)
    {
        $familyId = $request->validate(['family_id' => 'nullable|integer|exists:families,id'])['family_id'] ?? null;
        $this->canUseFamily($request, $familyId);
        $pantry = PantryItem::where(fn ($query) => $familyId === null
            ? $query->where('user_id', $request->user()->id)->whereNull('family_id')
            : $query->where('family_id', $familyId))
            ->whereIn('freshness_status', ['fresh', 'review'])
            ->get();
        $match = $matcher->match($recipe->load('ingredients'), $pantry);
        $created = collect($match['missing_ingredients'])->map(function ($ingredient) use ($request, $familyId) {
            $quantity = $ingredient['missing_quantity'] ?? $ingredient['quantity'];
            $existing = ShoppingList::where('user_id', $request->user()->id)
                ->where('family_id', $familyId)
                ->where('is_purchased', false)
                ->whereRaw('LOWER(ingredient_name) = ?', [strtolower($ingredient['name'])])
                ->whereRaw('LOWER(COALESCE(unit, \'\')) = ?', [strtolower($ingredient['unit'] ?? '')])
                ->first();

            if ($existing) {
                if (is_numeric($quantity) && is_numeric($existing->quantity)) {
                    $existing->update(['quantity' => (string) round((float) $existing->quantity + (float) $quantity, 3)]);
                }

                return $existing->fresh();
            }

            return ShoppingList::create([
                'user_id' => $request->user()->id,
                'family_id' => $familyId,
                'ingredient_name' => $ingredient['name'],
                'quantity' => $quantity,
                'unit' => $ingredient['unit'],
                'is_purchased' => false,
            ]);
        });

        return response()->json(['items' => $created->values(), 'message' => 'Missing ingredients added to your shopping list.'], 201);
>>>>>>> origin
    }

    private function data(Request $request, bool $partial = false): array
    {
<<<<<<< HEAD
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
=======
        $p = $partial ? 'sometimes|' : 'required|';
        $data = $request->validate(['ingredient_name' => $p.'string|max:255', 'quantity' => 'sometimes|nullable|string|max:255', 'unit' => 'sometimes|nullable|string|max:255', 'is_purchased' => 'sometimes|boolean', 'family_id' => 'sometimes|nullable|exists:families,id']);
        $this->canUseFamily($request, $data['family_id'] ?? null);
>>>>>>> origin

        return $data;
    }

    private function owns(Request $request, ShoppingList $item): void
    {
<<<<<<< HEAD
        abort_unless($item->user_id === $request->user()->id, 403);
    }
}
=======
        if ($item->family_id) {
            $this->canUseFamily($request, $item->family_id);

            return;
        } abort_unless($item->user_id === $request->user()->id, 403);
    }

    private function canUseFamily(Request $request, ?int $familyId): void
    {
        if ($familyId) {
            abort_unless(FamilyMember::where(['family_id' => $familyId, 'user_id' => $request->user()->id, 'status' => 'accepted'])->exists(), 403);
        }
    }

    private function visibleTo(Request $request)
    {
        $familyIds = FamilyMember::where('user_id', $request->user()->id)->where('status', 'accepted')->pluck('family_id');

        return ShoppingList::where(fn ($query) => $query->where('user_id', $request->user()->id)->whereNull('family_id')->orWhereIn('family_id', $familyIds));
    }
}
>>>>>>> origin
