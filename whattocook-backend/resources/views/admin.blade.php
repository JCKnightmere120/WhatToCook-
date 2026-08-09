<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>WhatToCook - Admin Login</title>
<style>
    :root {
        --brown: #2a1810;
        --brown-light: #3d2418;
        --cream: #faf0dc;
        --cream-dark: #f3e4c8;
        --orange: #d97a4d;
        --orange-dark: #c2673d;
        --border: #e8d9bd;
        --text: #2a1810;
        --muted: #8a7a68;
    }
    * { box-sizing: border-box; }
    body {
        font-family: system-ui, -apple-system, sans-serif;
        margin: 0;
        min-height: 100vh;
        background: var(--cream);
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-wrap { width: 100%; max-width: 380px; padding: 0 16px; }
    .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; justify-content: center; }
    .brand .logo {
        width: 40px; height: 40px; border-radius: 10px; background: var(--orange);
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; font-weight: 700;
    }
    .brand-text { text-align: left; }
    .brand-text strong { display: block; font-size: 18px; }
    .brand-text small { color: var(--muted); font-size: 12px; }

    section {
        background: #fffdf8;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 28px;
    }
    h1 { margin: 0 0 4px; font-size: 20px; color: var(--brown); }
    .sub { color: var(--muted); margin: 0 0 20px; font-size: 13px; }

    label { font-size: 12px; color: var(--muted); display: block; margin-bottom: 4px; margin-top: 14px; }
    input {
        width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: 6px;
        box-sizing: border-box; font-size: 14px; background: var(--cream-dark); color: var(--text);
    }
    input:focus { outline: 2px solid var(--orange); outline-offset: 1px; }

    button {
        width: 100%; margin-top: 22px; padding: 11px; border: none; border-radius: 6px;
        background: var(--orange); color: #fff; cursor: pointer; font-size: 14px; font-weight: 600;
    }
    button:hover { background: var(--orange-dark); }
    button:disabled { background: #d9c3af; cursor: not-allowed; }

    .status { font-size: 13px; margin-top: 14px; text-align: center; }
    .status.ok { color: #3f5c30; }
    .status.err { color: #a8412a; }
</style>
</head>
<body>

<div class="login-wrap">
    <div class="brand">
        <div class="logo">W</div>
        <div class="brand-text">
            <strong>WhatToCook</strong>
            <small>Admin Panel</small>
        </div>
    </div>

    <section>
        <h1>Welcome back</h1>
        <p class="sub">Please log in to manage recipes.</p>

        <label for="email">Email</label>
        <input id="email" type="email" placeholder="you@whattocook.test">

        <label for="password">Password</label>
        <input id="password" type="password" placeholder="password">

        <button id="loginBtn" onclick="login()">Login</button>
        <p id="authStatus" class="status"></p>
    </section>
</div>

<script>
const API = '/api';

if (localStorage.getItem('wtc_admin_token')) {
    window.location.href = '/admin/recipes';
}

async function login() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const statusEl = document.getElementById('authStatus');
    const btn = document.getElementById('loginBtn');

    btn.disabled = true;
    statusEl.textContent = 'Logging in...';
    statusEl.className = 'status';

    try {
        const res = await fetch(API + '/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email, password }),
        });
        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || 'Login failed.');
        }

        if (!data.user.is_admin) {
            throw new Error('This account is not an admin.');
        }

        localStorage.setItem('wtc_admin_token', data.token);
        statusEl.textContent = 'Logged in! Redirecting...';
        statusEl.className = 'status ok';
        window.location.href = '/admin/recipes';
    } catch (e) {
        statusEl.textContent = e.message;
        statusEl.className = 'status err';
        btn.disabled = false;
    }
}
</script>

</body>
</html>