<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · WhatToCook</title>
    <style>
        :root { --ink: #1a1512; --green: #e07856; --green-dark: #f08b66; --cream: #f5f0e8; --card: #252019; --line: #40362c; --muted: #9a9590; --danger: #ffad98; --warning: #e6af69; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--ink); color: var(--cream); font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .topbar { background: #1c1815; border-bottom: 1px solid var(--line); color: var(--cream); min-height: 68px; display: flex; align-items: center; gap: 20px; padding: 10px max(20px, calc((100vw - 1160px) / 2)); }
        .brand { flex: 0 0 auto; color: var(--green); text-decoration: none; font-size: 18px; font-weight: 800; letter-spacing: -.02em; white-space: nowrap; }
        .brand small { color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .12em; margin-left: 8px; text-transform: uppercase; }
        .admin-nav { display: flex; align-items: center; gap: 2px; min-width: 0; }
        .admin-nav__link { border-radius: 8px; color: var(--muted); font-size: 14px; font-weight: 700; padding: 8px 10px; text-decoration: none; transition: background-color .15s ease, color .15s ease; white-space: nowrap; }
        .admin-nav__link:hover, .admin-nav__link:focus-visible { background: rgba(224, 120, 86, .12); color: var(--cream); outline: none; }
        .admin-nav__link[aria-current="page"] { background: rgba(224, 120, 86, .18); color: var(--green-dark); box-shadow: inset 0 -2px 0 var(--green); }
        .topbar-actions { display: flex; gap: 12px; align-items: center; font-size: 14px; margin-left: auto; white-space: nowrap; }
        .admin-name { color: var(--muted); }
        .container { width: min(1160px, calc(100% - 32px)); margin: 40px auto 56px; }
        .page-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
        h1 { color: var(--cream); margin: 0; font-size: clamp(28px, 3vw, 36px); letter-spacing: -.035em; }
        h2 { color: var(--cream); margin: 0 0 16px; font-size: 18px; }
        p { line-height: 1.55; }
        .subheading { margin: 6px 0 0; color: var(--muted); }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 12px 28px rgba(0, 0, 0, .18); padding: 24px; }
        .flash { border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; }
        .flash.success { background: #e8f7ed; color: #145c31; border: 1px solid #b7e4c3; }
        .flash.error { background: #fff0ef; color: #8d2017; border: 1px solid #f4c6c1; }
        .button, button { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 38px; border: 1px solid transparent; border-radius: 8px; padding: 8px 14px; background: var(--green); color: #fff; font: inherit; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .button:hover, button:hover { background: var(--green-dark); }
        .button.secondary, button.secondary { background: transparent; color: var(--cream); border-color: #5a4c3f; }
        .button.secondary:hover, button.secondary:hover { background: #302921; }
        .button.danger, button.danger { background: transparent; color: var(--danger); border-color: rgba(255, 173, 152, .5); }
        .button.danger:hover, button.danger:hover { background: rgba(255, 173, 152, .12); }
        .plain-button { border: 0; padding: 0; min-height: auto; background: transparent; color: var(--cream); font-weight: 600; }
        .plain-button:hover { background: transparent; color: var(--green-dark); }
        .field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .field-grid.four { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .field { min-width: 0; }
        .field.full { grid-column: 1 / -1; }
        label { display: block; color: var(--muted); font-size: 11px; font-weight: 800; letter-spacing: .08em; margin-bottom: 7px; text-transform: uppercase; }
        input, select, textarea { width: 100%; border: 1px solid #514439; border-radius: 9px; padding: 10px 12px; color: var(--cream); background: #1c1815; font: inherit; font-size: 14px; }
        input::placeholder, textarea::placeholder { color: #77716a; }
        textarea { min-height: 105px; resize: vertical; }
        input:focus, select:focus, textarea:focus { outline: 3px solid rgba(224, 120, 86, .18); border-color: var(--green); }
        .help { color: var(--muted); font-size: 12px; margin: 5px 0 0; }
        .error-text { color: var(--danger); font-size: 12px; margin: 5px 0 0; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-top: 24px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td { color: var(--cream); text-align: left; padding: 13px 12px; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
        th { background: rgba(0, 0, 0, .12); color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .08em; }
        tr:last-child td { border-bottom: 0; }
        .row-actions { display: flex; gap: 8px; justify-content: flex-end; }
        .row-actions form { display: inline; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; color: #ffc0ac; background: rgba(224, 120, 86, .14); font-size: 12px; font-weight: 800; }
        .empty { color: var(--muted); text-align: center; padding: 34px; }
        .pagination { display: flex; justify-content: space-between; gap: 12px; margin-top: 18px; color: var(--muted); font-size: 14px; }
        .pagination a { color: var(--green-dark); font-weight: 700; text-decoration: none; }
        @media (max-width: 720px) { .container { width: min(100% - 24px, 1160px); margin-top: 20px; } .topbar { align-items: center; flex-wrap: wrap; gap: 8px 12px; padding: 10px 12px; } .admin-nav { order: 3; flex: 1 0 100%; overflow-x: auto; padding-bottom: 2px; } .admin-nav__link { padding: 7px 9px; } .admin-name { display: none; } .page-heading { display: block; } .page-heading .button { margin-top: 16px; } .card { padding: 16px; } .field-grid, .field-grid.four { grid-template-columns: 1fr; } }
    </style>
    @stack('head')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('admin.recipes.index') }}">WhatToCook <small>Recipe Admin</small></a>
        <nav class="admin-nav" aria-label="Admin navigation">
            <a class="admin-nav__link" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dashboard</a>
            <a class="admin-nav__link" href="{{ route('admin.recipes.index') }}" @if(request()->routeIs('admin.recipes.*')) aria-current="page" @endif>Recipes</a>
            <a class="admin-nav__link" href="{{ route('admin.categories') }}" @if(request()->routeIs('admin.categories')) aria-current="page" @endif>Categories</a>
            <a class="admin-nav__link" href="{{ route('admin.pantry') }}" @if(request()->routeIs('admin.pantry')) aria-current="page" @endif>Pantry</a>
            <a class="admin-nav__link" href="{{ route('admin.users') }}" @if(request()->routeIs('admin.users')) aria-current="page" @endif>Users</a>
        </nav>
        <div class="topbar-actions">
            <span class="admin-name">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="plain-button" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="flash success" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash error" role="alert">
                <strong>Please correct the highlighted fields.</strong>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
