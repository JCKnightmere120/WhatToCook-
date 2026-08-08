<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminRecipeController;
use Illuminate\Support\Facades\Route;

// Show login form at root by default
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Simple admin recipe management (web)
    Route::middleware(\App\Http\Middleware\EnsureUserIsAdmin::class)->group(function () {
        Route::resource('admin/recipes', AdminRecipeController::class, ['as' => 'admin']);
        Route::get('admin', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('admin/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
        Route::get('admin/pantry', [\App\Http\Controllers\AdminController::class, 'pantry'])->name('admin.pantry');
        Route::get('admin/categories', [\App\Http\Controllers\AdminController::class, 'categories'])->name('admin.categories');
    });
});
