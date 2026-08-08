<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $counts = [
            'users' => User::count(),
            'recipes' => Recipe::count(),
            'pantry_items' => PantryItem::count(),
        ];

        // Get a short list of the most recent recipes for the dashboard
        $recentRecipes = Recipe::orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('admin.dashboard', ['counts' => $counts, 'recentRecipes' => $recentRecipes]);
    }

    public function users()
    {
        $users = User::orderBy('name')->paginate(20);

        return view('admin.users.index', ['users' => $users]);
    }

    public function pantry()
    {
        $items = PantryItem::with('user')->orderBy('name')->paginate(20);

        return view('admin.pantry.index', ['items' => $items]);
    }

    public function categories()
    {
        // Show recipe categories (meal_type) with counts
        $categories = Recipe::select('meal_type')
            ->whereNotNull('meal_type')
            ->groupBy('meal_type')
            ->orderByRaw('COUNT(*) DESC')
            ->get()
            ->map(fn($r) => $r->meal_type);

        return view('admin.categories.index', ['categories' => $categories]);
    }
}
