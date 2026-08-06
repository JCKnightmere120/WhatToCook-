<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeFavorite;
use App\Models\RecipeReview;
use Illuminate\Http\Request;

class RecipeEngagementController extends Controller
{
    public function favorites(Request $request) { return response()->json(Recipe::whereHas('favorites', fn ($q) => $q->where('user_id', $request->user()->id))->with('ingredients')->orderBy('name')->get()); }
    public function favorite(Request $request, Recipe $recipe) { RecipeFavorite::firstOrCreate(['user_id' => $request->user()->id, 'recipe_id' => $recipe->id]); return response()->noContent(); }
    public function unfavorite(Request $request, Recipe $recipe) { RecipeFavorite::where(['user_id' => $request->user()->id, 'recipe_id' => $recipe->id])->delete(); return response()->noContent(); }
    public function reviews(Recipe $recipe) { return response()->json($recipe->reviews()->with('user:id,name')->latest()->get()); }
    public function review(Request $request, Recipe $recipe)
    {
        $data = $request->validate(['rating' => 'required|integer|between:1,5', 'review' => 'nullable|string|max:5000']);
        return response()->json(RecipeReview::updateOrCreate(['user_id' => $request->user()->id, 'recipe_id' => $recipe->id], $data + ['user_id' => $request->user()->id]), 201);
    }
    public function deleteReview(Request $request, RecipeReview $review) { abort_unless($review->user_id === $request->user()->id, 403); $review->delete(); return response()->noContent(); }
}
