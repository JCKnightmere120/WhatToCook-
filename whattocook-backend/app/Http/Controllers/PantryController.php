<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PantryController extends Controller
{
    // Get all pantry items of logged in user
    public function index(Request $request)
    {
        $items = $this->visibleItems($request)->get();

        return response()->json($items);
    }

    // Add pantry item
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'quantity' => 'nullable|string',
        'unit' => 'nullable|string',
        'purchase_date' => 'nullable|date',
        'expiry_date' => 'nullable|date|after_or_equal:purchase_date',
        'family_id' => 'nullable|exists:families,id',
    ]);

    if (!empty($request->family_id)) {
        abort_unless(FamilyMember::where(['family_id' => $request->family_id, 'user_id' => $request->user()->id])->exists(), 403);
    }

    $item = PantryItem::create([
        'user_id' => $request->user()->id,
        'name' => $request->name,
        'quantity' => $request->quantity,
        'unit' => $request->unit,
        'purchase_date' => $request->purchase_date,
        'expiry_date' => $request->expiry_date ?? now()->addDay()->toDateString(),
        'family_id' => $request->family_id,
    ]);

    return response()->json([
        'item' => $item,
        'message' => 'Pantry item added successfully!'
    ], 201);
}

    // Update pantry item
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|nullable|string',
            'unit' => 'sometimes|nullable|string',
            'purchase_date' => 'sometimes|nullable|date',
            'expiry_date' => 'sometimes|nullable|date',
            'family_id' => 'sometimes|nullable|exists:families,id',
        ]);

        $item = $this->visibleItems($request)->findOrFail($id);

        $purchaseDate = $validated['purchase_date'] ?? $item->purchase_date?->toDateString();
        $expiryDate = array_key_exists('expiry_date', $validated)
            ? $validated['expiry_date']
            : $item->expiry_date?->toDateString();

        if ($expiryDate !== null && $purchaseDate !== null && $expiryDate < $purchaseDate) {
            throw ValidationException::withMessages([
                'expiry_date' => ['The expiry date must be on or after the purchase date.'],
            ]);
        }

        if (!empty($validated['family_id'])) {
            abort_unless(FamilyMember::where(['family_id' => $validated['family_id'], 'user_id' => $request->user()->id])->exists(), 403);
        }

        $item->update($validated);

        return response()->json([
            'item' => $item,
            'message' => 'Pantry item updated successfully!'
        ]);
    }

    // Delete pantry item
    public function destroy(Request $request, $id)
    {
        $item = $this->visibleItems($request)->findOrFail($id);

        $item->delete();

        return response()->json([
            'message' => 'Pantry item deleted successfully!'
        ]);
    }

    private function visibleItems(Request $request)
    {
        $familyIds = FamilyMember::where('user_id', $request->user()->id)->pluck('family_id');

        return PantryItem::where(function ($query) use ($request, $familyIds) {
            $query->where('user_id', $request->user()->id)
                ->orWhereIn('family_id', $familyIds);
        });
    }
}
