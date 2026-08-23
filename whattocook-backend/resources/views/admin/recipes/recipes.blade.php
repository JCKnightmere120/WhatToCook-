<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>WhatToCook Admin - Recipes</title>
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
    body { font-family: system-ui, -apple-system, sans-serif; margin: 0; background: var(--cream); color: var(--text); }

    /* Navbar */
    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--brown);
        padding: 12px 24px;
    }
    .navbar .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; color: #fdf6ea; }
    .navbar .brand .logo {
        width: 32px; height: 32px; border-radius: 8px; background: var(--orange);
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px;
    }
    .navbar .brand small { display: block; font-weight: 400; font-size: 11px; color: #b0a390; }
    .navlinks { display: flex; gap: 4px; }
    .navlinks a {
        padding: 8px 14px; border-radius: 6px; text-decoration: none; color: #cabca6;
        font-size: 14px; font-weight: 500;
    }
    .navlinks a.active { background: var(--orange); color: #fff; }
    .navbar-right { display: flex; align-items: center; gap: 12px; }
    .navbar-right input {
        padding: 7px 12px; border: 1px solid var(--brown-light); border-radius: 6px; font-size: 13px; width: 220px;
        background: var(--brown-light); color: #fdf6ea;
    }
    .navbar-right input::placeholder { color: #8a7a68; }
    .avatar {
        width: 32px; height: 32px; border-radius: 50%; background: var(--orange);
        color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600;
    }
    .logout-link { font-size: 13px; color: #cabca6; text-decoration: none; cursor: pointer; }
    .logout-link:hover { color: #fff; }

    /* Page content */
    .page { max-width: 1100px; margin: 0 auto; padding: 24px; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .page-header h1 { margin: 0 0 2px; font-size: 22px; }
    .page-header p { margin: 0; color: var(--muted); font-size: 13px; }
    .btn-primary {
        background: var(--orange); color: #fff; border: none; padding: 9px 18px; border-radius: 6px;
        font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .btn-primary:hover { background: var(--orange-dark); }

    .card { background: #fffdf8; border: 1px solid var(--border); border-radius: 10px; padding: 20px; margin-bottom: 20px; }
    .card h2 { margin-top: 0; font-size: 16px; color: var(--brown); }

    /* Form */
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .field label { font-size: 12px; color: var(--muted); display: block; margin-bottom: 4px; }
    .field input, .field select, .field textarea {
        width: 100%; padding: 8px 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; font-family: inherit;
        background: var(--cream-dark); color: var(--text);
    }
    textarea { min-height: 60px; }

    /* Table */
    .toolbar { display: flex; gap: 8px; margin-bottom: 12px; align-items: center; }
    .toolbar input { flex: 1; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; background: var(--cream-dark); }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid var(--border); font-size: 13px; }
    th { color: var(--muted); font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.02em; }
    .pill { display: inline-block; padding: 2px 10px; border-radius: 12px; background: var(--cream-dark); font-size: 11px; font-weight: 600; }
    .pill.easy { background: #dbe8d4; color: #3f5c30; }
    .pill.medium { background: #f0dcb0; color: #7a5a1e; }
    .pill.hard { background: #f0c9bc; color: #8c3f26; }
    .row-actions button {
        border: none; background: none; cursor: pointer; font-size: 12px; padding: 4px 8px; border-radius: 4px;
    }
    .row-actions .delete { color: #b8492e; }
    .row-actions .delete:hover { background: #f0c9bc; }

    .status { font-size: 13px; margin-left: 8px; }
    .status.ok { color: #16a34a; }
    .status.err { color: #dc2626; }
    pre { background: #111; color: #0f0; padding: 12px; border-radius: 8px; max-height: 220px; overflow: auto; font-size: 12px; }
</style>
</head>
<body>

<div class="navbar">
    <div class="brand">
        <div class="logo">W</div>
        <div>
            WhatToCook
            <small>Admin Panel</small>
        </div>
    </div>
    <div class="navlinks">
        <a href="#" class="active">Recipes</a>
    </div>
    <div class="navbar-right">
        <input type="text" placeholder="Search recipes...">
        <span class="avatar" id="avatarInitial">?</span>
        <span class="logout-link" onclick="logout()">Logout</span>
    </div>
</div>

<div class="page">
    <div class="page-header">
        <div>
            <h1>Recipes</h1>
            <p id="whoami">Checking session...</p>
        </div>
        <button class="btn-primary" onclick="document.getElementById('formCard').scrollIntoView({behavior:'smooth'})">+ Add Recipe</button>
    </div>

    <div class="card" id="formCard">
        <h2>Add Recipe</h2>
        <div class="grid">
            <div class="field">
                <label>Name</label>
                <input id="f_name" placeholder="e.g. Adobong Manok">
            </div>
            <div class="field">
                <label>Region</label>
                <input id="f_region" placeholder="e.g. Luzon">
            </div>
            <div class="field">
                <label>Category (meal type)</label>
                <select id="f_meal_type">
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="dinner">Dinner</option>
                    <option value="snack">Snack</option>
                </select>
            </div>
            <div class="field">
                <label>Difficulty</label>
                <select id="f_difficulty">
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="field">
                <label>Prep time (mins)</label>
                <input id="f_prep_time" type="number" value="10">
            </div>
            <div class="field">
                <label>Cook time (mins)</label>
                <input id="f_cook_time" type="number" value="30">
            </div>
            <div class="field">
                <label>Servings</label>
                <input id="f_servings" type="number" value="4">
            </div>
        </div>
        <div class="field" style="margin-top:12px;">
            <label>Description</label>
            <textarea id="f_description" placeholder="Short description"></textarea>
        </div>
        <div class="field" style="margin-top:8px;">
            <label>Instructions</label>
            <textarea id="f_instructions" placeholder="Step 1...&#10;Step 2..."></textarea>
        </div>
        <div style="margin-top:12px;">
            <button class="btn-primary" onclick="createRecipe()">Save Recipe</button>
            <span id="recipeStatus" class="status"></span>
        </div>
    </div>

    <div class="card">
        <div class="toolbar">
            <input id="searchBox" type="text" placeholder="Search recipes or authors..." oninput="renderTable()">
            <button class="btn-primary" onclick="loadRecipes()">Refresh</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Recipe</th>
                    <th>Category</th>
                    <th>Region</th>
                    <th>Difficulty</th>
                    <th>Prep/Cook</th>
                    <th>Servings</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="recipeTableBody"></tbody>
        </table>
    </div>

    <div class="card">
        <h2>Raw response (debug)</h2>
        <pre id="output">Wala pa nga request gipadala.</pre>
    </div>
</div>

<script>
const API = '/api';
let token = localStorage.getItem('wtc_admin_token') || null;
let allRecipes = [];

if (!token) {
    window.location.href = '/admin';
}

function show(data) {
    document.getElementById('output').textContent = JSON.stringify(data, null, 2);
}

async function api(path, method = 'GET', body = null) {
    const headers = { 'Accept': 'application/json' };
    if (body) headers['Content-Type'] = 'application/json';
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(API + path, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null,
    });

    let data;
    try { data = await res.json(); } catch (e) { data = { message: 'No JSON response' }; }

    show(data);

    if (res.status === 401) {
        localStorage.removeItem('wtc_admin_token');
        window.location.href = '/admin';
        return;
    }

    if (!res.ok) {
        throw new Error(data.message || ('HTTP ' + res.status));
    }
    return data;
}

async function whoAmI() {
    try {
        const data = await api('/user');
        document.getElementById('whoami').textContent = data.name + (data.is_admin ? ' · Super Admin' : ' · not an admin, write actions will fail');
        document.getElementById('avatarInitial').textContent = data.name.charAt(0).toUpperCase();
        loadRecipes();
    } catch (e) {}
}

async function logout() {
    try { await api('/logout', 'POST'); } catch (e) {}
    localStorage.removeItem('wtc_admin_token');
    window.location.href = '/admin';
}

async function createRecipe() {
    const statusEl = document.getElementById('recipeStatus');

    const payload = {
        name: document.getElementById('f_name').value,
        region: document.getElementById('f_region').value,
        meal_type: document.getElementById('f_meal_type').value,
        difficulty: document.getElementById('f_difficulty').value,
        prep_time: Number(document.getElementById('f_prep_time').value),
        cook_time: Number(document.getElementById('f_cook_time').value),
        servings: Number(document.getElementById('f_servings').value),
        description: document.getElementById('f_description').value,
        instructions: document.getElementById('f_instructions').value,
    };

    try {
        await api('/recipes', 'POST', payload);
        statusEl.textContent = 'Recipe saved!';
        statusEl.className = 'status ok';
        loadRecipes();
    } catch (e) {
        statusEl.textContent = 'Failed: ' + e.message;
        statusEl.className = 'status err';
    }
}

async function loadRecipes() {
    try {
        const data = await api('/recipes');
        allRecipes = data.data ?? data;
        renderTable();
    } catch (e) {}
}

function renderTable() {
    const q = document.getElementById('searchBox').value.toLowerCase();
    const filtered = allRecipes.filter(r => r.name.toLowerCase().includes(q) || (r.region ?? '').toLowerCase().includes(q));

    const tbody = document.getElementById('recipeTableBody');
    tbody.innerHTML = '';
    filtered.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${r.name}</strong></td>
            <td>${r.meal_type ?? ''}</td>
            <td>${r.region ?? ''}</td>
            <td><span class="pill ${r.difficulty ?? ''}">${r.difficulty ?? ''}</span></td>
            <td>${r.prep_time ?? 0}m / ${r.cook_time ?? 0}m</td>
            <td>${r.servings ?? ''}</td>
            <td class="row-actions"><button class="delete" onclick="deleteRecipe(${r.id})">Delete</button></td>
        `;
        tbody.appendChild(tr);
    });
}

async function deleteRecipe(id) {
    if (!confirm('Delete this recipe?')) return;
    try {
        await api('/recipes/' + id, 'DELETE');
        loadRecipes();
    } catch (e) {}
}

whoAmI();
</script>

</body>
</html>