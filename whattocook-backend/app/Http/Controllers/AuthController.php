<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;

<<<<<<< HEAD
            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Registration successful!'
            ], 201);
=======
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful!',
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
>>>>>>> e959e466 (feat: expand household meal planning and pantry workflow)
        }

        Auth::login($user);
        return redirect()->route('dashboard');
    }

<<<<<<< HEAD
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => ['These credentials do not match our records.'],
=======
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful!',
>>>>>>> e959e466 (feat: expand household meal planning and pantry workflow)
        ]);
    }

    public function logout(Request $request)
    {
<<<<<<< HEAD
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function dashboard(Request $request)
    {
        $selectedIngredients = trim((string) $request->input('ingredients', ''));
        $ingredientList = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $selectedIngredients) ?: [])));

        $recipes = [
            [
                'title' => 'Quick Pasta Bowl',
                'description' => 'A fast dinner using tomatoes, basil, and pasta.',
                'ingredients' => ['pasta', 'tomato', 'basil', 'olive oil'],
            ],
            [
                'title' => 'Veggie Stir Fry',
                'description' => 'A colorful meal with broccoli, carrots, and soy sauce.',
                'ingredients' => ['broccoli', 'carrot', 'soy sauce', 'garlic'],
            ],
            [
                'title' => 'Chicken Wraps',
                'description' => 'Simple wraps made with chicken, lettuce, and yogurt sauce.',
                'ingredients' => ['chicken', 'lettuce', 'yogurt', 'tortilla'],
            ],
        ];

        foreach ($recipes as &$recipe) {
            $recipe['match_count'] = count(array_intersect($ingredientList, $recipe['ingredients']));
        }

        usort($recipes, fn ($a, $b) => $b['match_count'] <=> $a['match_count']);

        return view('dashboard', compact('recipes', 'selectedIngredients', 'ingredientList'));
=======
        // Revoke all of the account's bearer tokens. This makes logout effective
        // even when the current request was authenticated through a session.
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully!',
        ]);
>>>>>>> e959e466 (feat: expand household meal planning and pantry workflow)
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
