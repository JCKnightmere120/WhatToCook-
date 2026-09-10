<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRecipeController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Middleware\EnsureIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->middleware('guest')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('authenticate');
});

// Legacy entry points: keep saved bookmarks working while canonicalising the
// browser-based administration experience under /admin.
Route::redirect('/login', '/admin/login')->name('login');
Route::redirect('/register', '/admin/login')->name('register');
Route::post('/logout', [AdminAuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->middleware('auth')->name('admin.logout');

Route::middleware(['auth', EnsureIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/pantry', [AdminController::class, 'pantry'])->name('pantry');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::resource('recipes', AdminRecipeController::class)->except('show');
});
