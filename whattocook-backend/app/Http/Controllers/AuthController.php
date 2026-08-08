<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

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
            'is_admin' => 1,
        ]);

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Registration successful!',
            ], 201);
        }

        Auth::login($user);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->route('home');
    }

    // API login method
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
        }

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful!',
            ]);
        }

        Auth::login($user);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->route('home');
    }

    // Web login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Web register form
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Web authentication endpoint
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput($request->except('password'));
        }

        $request->session()->regenerate();

        $user = $request->user();
        Log::info('authenticate: user id', ['id' => $user?->id, 'email' => $user?->email, 'is_admin' => $user?->is_admin]);

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        // Revoke all of the account's bearer tokens. This makes logout effective
        // even when the current request was authenticated through a session.
        if ($request->user()) {
            // Only delete tokens if the method exists (Sanctum trait present)
            if (method_exists($request->user(), 'tokens')) {
                $request->user()->tokens()->delete();
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Logged out successfully!',
            ]);
        }

        return redirect()->route('login');
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}

