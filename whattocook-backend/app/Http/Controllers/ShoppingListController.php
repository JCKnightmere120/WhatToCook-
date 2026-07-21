<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\ShoppingList;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    public function index(Request $request) { return response()->json(ShoppingList::where('user_id', $request->user()->id)->get()); }
    public function store(Request $request) { $data = $this->data($request); return response()->json(ShoppingList::create($data + ['user_id' => $request->user()->id]), 201); }
    public function update(Request $request, ShoppingList $shoppingList) { $this->owns($request, $shoppingList); $shoppingList->update($this->data($request, true)); return response()->json($shoppingList); }
    public function destroy(Request $request, ShoppingList $shoppingList) { $this->owns($request, $shoppingList); $shoppingList->delete(); return response()->noContent(); }
    private function data(Request $request, bool $partial = false): array { $p = $partial ? 'sometimes|' : 'required|'; $data = $request->validate(['ingredient_name' => $p.'string|max:255', 'quantity' => 'sometimes|nullable|string|max:255', 'unit' => 'sometimes|nullable|string|max:255', 'is_purchased' => 'sometimes|boolean', 'family_id' => 'sometimes|nullable|exists:families,id']); if (!empty($data['family_id'])) abort_unless(FamilyMember::where(['family_id' => $data['family_id'], 'user_id' => $request->user()->id])->exists(), 403); return $data; }
    private function owns(Request $request, ShoppingList $item): void { abort_unless($item->user_id === $request->user()->id, 403); }
}
