<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatToCook Login</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css','resources/js/app.js'])
    @endif
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(to bottom right, #fffbeb, #ffedd5, #ffe4e6);
            color: #334155;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
        }

        .auth-page {
            display: flex;
            min-height: 100vh;
            max-width: 72rem;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
        }

        .auth-card {
            display: grid;
            width: 100%;
            overflow: hidden;
            border-radius: 1.5rem;
            border: 1px solid rgba(255,255,255,0.7);
            background: rgba(255,255,255,0.8);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            backdrop-filter: blur(8px);
        }

        @media (min-width: 768px) {
            .auth-card {
                grid-template-columns: 1.1fr 0.9fr;
            }
        }

        .auth-panel {
            background: linear-gradient(to bottom right, #f97316, #f43f5e);
            padding: 2.5rem;
            color: #fff;
        }

        .auth-eyebrow {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            margin: 0;
        }

        .auth-heading {
            margin-top: 1rem;
            font-size: 2.25rem;
            font-weight: 600;
        }

        .auth-lead {
            margin-top: 1rem;
            font-size: 0.875rem;
            line-height: 1.75rem;
            color: #ffedd5;
        }

        .auth-callout {
            margin-top: 2rem;
            border-radius: 1rem;
            background: rgba(255,255,255,0.2);
            padding: 1rem;
        }

        .auth-callout-title {
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0;
        }

        .auth-callout-text {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #ffedd5;
        }

        .auth-form-side {
            padding: 2rem;
        }
        @media (min-width: 640px) {
            .auth-form-side {
                padding: 2.5rem;
            }
        }

        .auth-form-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .auth-form-subtitle {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .auth-form {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .field-label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .field-input {
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            outline: none;
            font-size: 1rem;
        }
        .field-input:focus {
            border-color: #fb923c;
        }

        .field-error {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #e11d48;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.875rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
        }

        .remember-checkbox {
            border-radius: 0.25rem;
            border: 1px solid #cbd5e1;
        }

        .link-orange {
            color: #f97316;
            text-decoration: none;
        }
        .link-orange:hover {
            color: #ea580c;
        }

        .submit-btn {
            width: 100%;
            border: none;
            border-radius: 0.75rem;
            background: #f97316;
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .submit-btn:hover {
            background: #ea580c;
        }

        .auth-footer-text {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 0.875rem;
            color: #475569;
        }

        .link-orange-strong {
            font-weight: 600;
            color: #f97316;
            text-decoration: none;
        }
        .link-orange-strong:hover {
            color: #ea580c;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-panel">
                <p class="auth-eyebrow">WhatToCook</p>
                <h1 class="auth-heading">Cook with what you already have.</h1>
                <p class="auth-lead">
                    Log in to explore recipe suggestions based on your pantry and available ingredients.
                </p>
                <div class="auth-callout">
                    <p class="auth-callout-title">New here?</p>
                    <p class="auth-callout-text">Register an account to personalize your meal planner and scan ingredients.</p>
                </div>
            </div>
            <div class="auth-form-side">
                <h2 class="auth-form-title">Welcome back</h2>
                <p class="auth-form-subtitle">Sign in to continue.</p>

                <form method="POST" action="{{ route('login.post') }}" class="auth-form">
                    @csrf
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="field-input">
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label">Password</label>
                        <input type="password" name="password" required class="field-input">
                    </div>
                    <div class="form-row">
                        <label class="remember-label">
                            <input type="checkbox" name="remember" class="remember-checkbox">
                            Remember me
                        </label>
                        <a href="#" class="link-orange">Forgot password?</a>
                    </div>
                    <button type="submit" class="submit-btn">Sign in</button>
                </form>
                <p class="auth-footer-text">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="link-orange-strong">Create one</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>