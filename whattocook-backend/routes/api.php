<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PantryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\RecipeEngagementController;
use App\Http\Controllers\MealHistoryController;
use App\Http\Controllers\NutritionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/families', [FamilyController::class, 'index']);
    Route::post('/families', [FamilyController::class, 'store']);
    Route::get('/families/{family}', [FamilyController::class, 'show']);
    Route::post('/families/{family}/members', [FamilyController::class, 'addMember']);
    Route::delete('/families/{family}/members/{user}', [FamilyController::class, 'removeMember']);

    // Pantry routes
    Route::get('/pantry', [PantryController::class, 'index']);
    Route::post('/pantry', [PantryController::class, 'store']);
    Route::put('/pantry/{id}', [PantryController::class, 'update']);
    Route::delete('/pantry/{id}', [PantryController::class, 'destroy']);

    Route::apiResource('recipes', RecipeController::class);
    Route::get('/nutrition/search', [NutritionController::class, 'search']);
    Route::get('/nutrition/foods/{fdcId}', [NutritionController::class, 'show'])->whereNumber('fdcId');
    Route::get('/favorites', [RecipeEngagementController::class, 'favorites']);
    Route::post('/recipes/{recipe}/favorite', [RecipeEngagementController::class, 'favorite']);
    Route::delete('/recipes/{recipe}/favorite', [RecipeEngagementController::class, 'unfavorite']);
    Route::get('/recipes/{recipe}/reviews', [RecipeEngagementController::class, 'reviews']);
    Route::put('/recipes/{recipe}/review', [RecipeEngagementController::class, 'review']);
    Route::delete('/reviews/{review}', [RecipeEngagementController::class, 'deleteReview']);
    Route::apiResource('meal-plans', MealPlanController::class)->except(['show']);
    Route::apiResource('meal-history', MealHistoryController::class)->except(['show']);
    Route::apiResource('shopping-list', ShoppingListController::class)->except(['show']);
});
