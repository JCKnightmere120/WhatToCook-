<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>WhatToCook - Admin</title>
<style>
    body { font-family: system-ui, sans-serif; max-width: 1000px; margin: 30px auto; padding: 0 16px; background: #f5f5f5; color: #222; }
    h1 { margin-bottom: 4px; }
    .sub { color: #777; margin-bottom: 24px; }
    section { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
    section h2 { margin-top: 0; font-size: 18px; }
    input, select, textarea { padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px; margin: 4px 4px 4px 0; font-family: inherit; }
    textarea { width: 100%; min-height: 60px; }
    button { padding: 6px 14px; border: none; border-radius: 4px; background: #2563eb; color: #fff; cursor: pointer; margin: 4px 4px 4px 0; }
    button:hover { background: #1d4ed8; }
    button.danger { background: #dc2626; }
    button.danger:hover { background: #b91c1c; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: top; }
    th { background: #fafafa; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .grid label { font-size: 12px; color: #555; display: block; margin-bottom: 2px; }
    .grid input, .grid select { width: 100%; box-sizing: border-box; }
    .status { font-size: 13px; margin-left: 8px; }
    .status.ok { color: #16a34a; }
    .status.err { color: #dc2626; }
    pre { background: #111; color: #0f0; padding: 10px; border-radius: 6px; max-height: 250px; overflow: auto; font-size: 12px; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; background: #eee; font-size: 11px; }
</style>
</head>
<body>

<h1>WhatToCook Admin</h1>
<p class="sub">Simple admin panel — recipe management.</p>

<section>
    <h2>Admin Login</h2>
    <div>
        <input id="email" type="email" placeholder="email" value="test@example.com">
        <input id="password" type="password" placeholder="password" value="password">
        <button onclick="login()">Login</button>
        <span id="authStatus" class="status"></span>
    </div>
</section>

<section id="recipeFormSection" style="display:none;">
    <h2>Add / Edit Recipe</h2>
    <div class="grid">
        <div>
            <label>Name</label>
            <input id="f_name" placeholder="e.g. Adobong Manok">
        </div>
        <div>
            <label>Region</label>
            <input id="f_region" placeholder="e.g. Luzon">
        </div>
        <div>
            <label>Meal type</label>
            <select id="f_meal_type">
                <option value="breakfast">breakfast</option>
                <option value="lunch">lunch</option>
                <option value="dinner">dinner</option>
                <option value="snack">snack</option>
            </select>
        </div>
        <div>
            <label>Difficulty</label>
            <select id="f_difficulty">
                <option value="easy">easy</option>
                <option value="medium">medium</option>
                <option value="hard">hard</option>
            </select>
        </div>
        <div>
            <label>Prep time (mins)</label>
            <input id="f_prep_time" type="number" value="10">
        </div>
        <div>
            <label>Cook time (mins)</label>
            <input id="f_cook_time" type="number" value="30">
        </div>
        <div>
            <label>Servings</label>
            <input id="f_servings" type="number" value="4">
        </div>
    </div>
    <label style="font-size:12px;color:#555;display:block;margin-top:8px;">Description</label>
    <textarea id="f_description" placeholder="Short description"></textarea>
    <label style="font-size:12px;color:#555;display:block;">Instructions</label>
    <textarea id="f_instructions" placeholder="Step 1...&#10;Step 2..."></textarea>

    <div style="margin-top:8px;">
        <button onclick="createRecipe()">Save New Recipe</button>
        <span id="recipeStatus" class="status"></span>
    </div>
</section>

<section id="recipeListSection" style="display:none;">
    <h2>Recipes</h2>
    <button onclick="loadRecipes()">Refresh List</button>
    <table id="recipeTable">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Region</th><th>Meal</th><th>Difficulty</th><th>Prep/Cook</th><th></th></tr>
        </thead>
        <tbody></tbody>
    </table>
</section>

<section>
    <h2>Raw response (debug)</h2>
    <pre id="output">Wala pa nga request gipadala.</pre>
</section>

<script>
const API = '/api';
let token = localStorage.getItem('wtc_admin_token') || null;

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

    if (!res.ok) {
        throw new Error(data.message || ('HTTP ' + res.status));
    }
    return data;
}

async function login() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const statusEl = document.getElementById('authStatus');

    try {
        const data = await api('/login', 'POST', { email, password });
        token = data.token;
        localStorage.setItem('wtc_admin_token', token);
        statusEl.textContent = 'Logged in as ' + data.user.name;
        statusEl.className = 'status ok';
        document.getElementById('recipeFormSection').style.display = 'block';
        document.getElementById('recipeListSection').style.display = 'block';
        loadRecipes();
    } catch (e) {
        statusEl.textContent = 'Login failed: ' + e.message;
        statusEl.className = 'status err';
    }
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
        const list = data.data ?? data; // handles paginated or plain array response
        const tbody = document.querySelector('#recipeTable tbody');
        tbody.innerHTML = '';
        list.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${r.id}</td>
                <td>${r.name}</td>
                <td>${r.region ?? ''}</td>
                <td><span class="badge">${r.meal_type ?? ''}</span></td>
                <td><span class="badge">${r.difficulty ?? ''}</span></td>
                <td>${r.prep_time ?? 0}m / ${r.cook_time ?? 0}m</td>
                <td><button class="danger" onclick="deleteRecipe(${r.id})">Delete</button></td>
            `;
            tbody.appendChild(tr);
        });
    } catch (e) {}
}

async function deleteRecipe(id) {
    if (!confirm('Delete this recipe?')) return;
    try {
        await api('/recipes/' + id, 'DELETE');
        loadRecipes();
    } catch (e) {}
}

if (token) {
    document.getElementById('recipeFormSection').style.display = 'block';
    document.getElementById('recipeListSection').style.display = 'block';
    loadRecipes();
}
</script>

</body>
</html>