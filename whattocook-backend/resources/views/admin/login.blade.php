<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login · WhatToCook</title>
    <style>
        :root { --ink: #173d2b; --green: #23864d; --cream: #f7f8f2; --line: #dbe4dc; --danger: #b42318; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; display: grid; place-items: center; margin: 0; padding: 24px; background: var(--cream); color: #18291f; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .card { width: min(100%, 420px); padding: 30px; background: #fff; border: 1px solid var(--line); border-radius: 16px; box-shadow: 0 10px 30px rgba(23, 61, 43, .08); }
        h1 { margin: 0; color: var(--ink); font-size: 27px; letter-spacing: -.03em; }
        p { color: #637267; line-height: 1.5; }
        label { display: block; margin: 18px 0 6px; color: #33473a; font-size: 13px; font-weight: 700; }
        input { width: 100%; padding: 10px; border: 1px solid #b8c9bc; border-radius: 8px; color: #18291f; font: inherit; }
        input:focus { outline: 3px solid rgba(35, 134, 77, .16); border-color: var(--green); }
        .remember { display: flex; gap: 8px; align-items: center; margin-top: 14px; font-size: 14px; color: #435547; }
        .remember input { width: auto; }
        button { width: 100%; margin-top: 22px; border: 0; border-radius: 8px; padding: 11px; background: var(--green); color: #fff; cursor: pointer; font: inherit; font-weight: 700; }
        button:hover { background: #17683a; }
        .error { margin-top: 14px; border: 1px solid #f4c6c1; border-radius: 8px; padding: 10px; background: #fff0ef; color: var(--danger); font-size: 13px; }
        .note { margin-top: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Recipe Admin</h1>
        <p>Sign in with an account that has been granted administrator access.</p>

        @if ($errors->any())
            <div class="error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>

            <label class="remember"><input name="remember" type="checkbox" value="1"> Keep me signed in on this device</label>
            <button type="submit">Sign in to admin</button>
        </form>

        <p class="note">This login is separate from the Ionic app session, but uses the same WhatToCook account.</p>
    </main>
</body>
</html>
