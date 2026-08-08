<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WhatToCook Admin</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, Arial, Helvetica, sans-serif;
            background: #f3f5f9;
            color: #1f2937;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #eff3f8;
        }

        .admin-shell {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }

        .admin-sidebar {
            background: #071226;
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1.25rem;
            gap: 1.5rem;
        }

        .admin-brand {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .admin-nav {
            display: grid;
            gap: 0.5rem;
        }

        .admin-nav a {
            display: block;
            color: #f8fafc;
            text-decoration: none;
            border-radius: 12px;
            padding: 0.8rem 1rem;
            transition: background 0.2s ease;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.08);
        }

        .admin-footer {
            margin-top: auto;
            font-size: 0.92rem;
            color: #cbd5e1;
        }

        .admin-wrapper {
            background: #eff3f8;
            padding: 1.5rem;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .admin-header__title {
            margin: 0;
            font-size: clamp(1.75rem, 2vw, 2.25rem);
            line-height: 1.1;
        }

        .admin-header__actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .admin-chip {
            background: rgba(15,23,42,0.08);
            border-radius: 999px;
            padding: 0.65rem 1rem;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .button {
            border: none;
            border-radius: 999px;
            padding: 0.85rem 1.25rem;
            font-size: 0.94rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, background 0.15s ease;
        }

        .button--logout {
            background: #f97316;
            color: #fff;
        }

        .button--logout:hover {
            background: #ea580c;
        }

        .admin-content {
            background: #ffffff;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
        }

        .admin-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .admin-card {
            border-radius: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 1.35rem;
        }

        .admin-card h3 {
            margin: 0 0 0.85rem;
            font-size: 1rem;
            color: #334155;
        }

        .admin-card p {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
        }

        @media (max-width: 860px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
            }

            .admin-nav {
                display: flex;
                flex-wrap: wrap;
            }

            .admin-footer {
                order: 3;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">WhatToCook</div>
            <nav class="admin-nav">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.recipes.index') }}">Recipes</a>
                <a href="{{ route('admin.users') }}">Users</a>
                <a href="{{ route('admin.pantry') }}">Pantry</a>
                <a href="{{ route('admin.categories') }}">Categories</a>
            </nav>
            <div class="admin-footer">
                @auth
                    <div>{{ auth()->user()->email }}</div>
                @endauth
            </div>
        </aside>

        <main class="admin-wrapper">
            <div class="admin-header">
                <div>
                    <h1 class="admin-header__title">Admin panel</h1>
                    <p class="admin-chip">Manage recipes, users, and pantry items from one place.</p>
                </div>
                <div class="admin-header__actions">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="button button--logout">Logout</button>
                    </form>
                </div>
            </div>

            <div class="admin-content">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
