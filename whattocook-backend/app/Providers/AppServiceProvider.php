<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by((string) ($request->user()?->id ?? $request->ip())));

        if (app()->environment('production') && config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Redirect authenticated admins to admin dashboard instead of the
        // default dashboard route when visiting auth pages.
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            // If the authenticated user is accessing admin routes, send admins
            // to the admin dashboard; otherwise send them to the standard
            // dashboard. This preserves showing the login first for guests.
            if (Auth::check() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) {
                if ($request->is('admin') || $request->is('admin/*')) {
                    return route('admin.dashboard');
                }
                return route('home');
            }

            return route('home');
        });
    }
}
