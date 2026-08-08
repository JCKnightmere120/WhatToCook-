@extends('layouts.app')

@section('content')
    <div class="recipe-page">
        <div class="recipe-card">
            <header class="recipe-card__header">
                <div>
                    <h1>Edit Recipe</h1>
                    <p class="recipe-card__subtitle">Update recipe details, difficulty, meal type, timing, and ingredients.</p>
                </div>
            </header>

            @if($errors->any())
                <section class="alert alert--error" role="alert">
                    <strong>There were problems with your submission.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <form action="{{ route('admin.recipes.update', $recipe) }}" method="POST" class="recipe-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Recipe name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $recipe->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="difficulty">Difficulty</label>
                    <select id="difficulty" name="difficulty">
                        <option value="" disabled {{ old('difficulty', $recipe->difficulty) ? '' : 'selected' }}>Select difficulty</option>
                        <option value="easy" {{ old('difficulty', $recipe->difficulty) === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', $recipe->difficulty) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty', $recipe->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="meal_type">Meal type</label>
                    <select id="meal_type" name="meal_type">
                        <option value="" disabled {{ old('meal_type', $recipe->meal_type) ? '' : 'selected' }}>Select meal type</option>
                        <option value="breakfast" {{ old('meal_type', $recipe->meal_type) === 'breakfast' ? 'selected' : '' }}>Breakfast</option>
                        <option value="lunch" {{ old('meal_type', $recipe->meal_type) === 'lunch' ? 'selected' : '' }}>Lunch</option>
                        <option value="dinner" {{ old('meal_type', $recipe->meal_type) === 'dinner' ? 'selected' : '' }}>Dinner</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group form-group--inline">
                        <label for="prep_time">Prep time (minutes)</label>
                        <input id="prep_time" name="prep_time" type="number" min="0" value="{{ old('prep_time', $recipe->prep_time) }}">
                    </div>
                    <div class="form-group form-group--inline">
                        <label for="cook_time">Cook time (minutes)</label>
                        <input id="cook_time" name="cook_time" type="number" min="0" value="{{ old('cook_time', $recipe->cook_time) }}">
                    </div>
                </div>

                <div class="form-group form-group--wide">
                    <label for="instructions">Instructions</label>
                    <textarea id="instructions" name="instructions" rows="7" required>{{ old('instructions', $recipe->instructions) }}</textarea>
                </div>

                <div class="form-group form-group--wide">
                    <div class="form-group__heading">
                        <label>Ingredients</label>
                        <button type="button" class="button button--secondary" id="add-ingredient">Add ingredient</button>
                    </div>
                    <p class="field-help">Add each ingredient as a separate row. Quantity and unit are optional.</p>

                    @php
                        $ingredientRows = old('ingredients', $recipe->ingredients->map(function ($item) {
                            return [
                                'name' => $item['name'] ?? '',
                                'quantity' => $item['quantity'] ?? '',
                                'unit' => $item['unit'] ?? '',
                            ];
                        })->all());

                        if (is_string($ingredientRows)) {
                            $decoded = json_decode($ingredientRows, true);
                            if (is_array($decoded)) {
                                $ingredientRows = $decoded;
                            }
                        }
                    @endphp

                    <div id="ingredients-list" class="ingredient-list">
                        @foreach($ingredientRows as $index => $item)
                            <div class="ingredient-row" data-index="{{ $index }}">
                                <div class="ingredient-row__fields">
                                    <label>
                                        Name
                                        <input type="text" name="ingredients[{{ $index }}][name]" value="{{ old('ingredients.' . $index . '.name', $item['name'] ?? '') }}" required>
                                    </label>
                                    <label>
                                        Quantity
                                        <input type="text" name="ingredients[{{ $index }}][quantity]" value="{{ old('ingredients.' . $index . '.quantity', $item['quantity'] ?? '') }}">
                                    </label>
                                    <label>
                                        Unit
                                        <input type="text" name="ingredients[{{ $index }}][unit]" value="{{ old('ingredients.' . $index . '.unit', $item['unit'] ?? '') }}">
                                    </label>
                                </div>
                                <button type="button" class="button button--ghost ingredient-row__remove">Remove</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button--primary">Save recipe</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .recipe-page {
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .recipe-card {
            background: #fff;
            border: 1px solid #d8d8d8;
            border-radius: 14px;
            box-shadow: 0 14px 36px rgba(14, 30, 37, 0.08);
            padding: 2rem;
        }

        .recipe-card__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .recipe-card__subtitle {
            margin: 0.5rem 0 0;
            color: #556066;
            max-width: 640px;
            line-height: 1.6;
        }

        .alert {
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            background: #fff4f4;
            border: 1px solid #f5c2c7;
        }

        .alert--error strong {
            display: block;
            margin-bottom: 0.5rem;
            color: #b02a37;
        }

        .alert ul {
            margin: 0.5rem 0 0;
            padding-left: 1.25rem;
            color: #5a232a;
        }

        .recipe-form {
            display: grid;
            gap: 1.25rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-group--inline {
            flex: 1;
        }

        .form-row {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .form-group--wide {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            color: #1f2937;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            color: #111827;
            background: #f9fafb;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #7193ff;
            box-shadow: 0 0 0 4px rgba(113, 147, 255, 0.15);
            background: #fff;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .field-help {
            margin: 0;
            color: #4b5563;
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.95rem 1.4rem;
            border-radius: 10px;
            border: 1px solid transparent;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .button--primary {
            color: #fff;
            background: #1d4ed8;
        }

        .button--primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .button--secondary {
            color: #1d4ed8;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .button--secondary:hover {
            background: #dbeafe;
        }

        .button--ghost {
            color: #374151;
            background: transparent;
            border-color: #cbd5e1;
        }

        .button--ghost:hover {
            background: #f8fafc;
        }

        .form-group__heading {
            display: flex;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
        }

        .ingredient-list {
            display: grid;
            gap: 1rem;
            margin-top: 0.75rem;
        }

        .ingredient-row {
            display: grid;
            gap: 0.75rem;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
        }

        .ingredient-row__fields {
            display: grid;
            gap: 0.75rem;
        }

        @media (min-width: 720px) {
            .ingredient-row__fields {
                grid-template-columns: 1.5fr 1fr 1fr;
            }

            .ingredient-row__remove {
                justify-self: end;
                width: auto;
            }
        }

        .ingredient-row__remove {
            align-self: center;
            justify-self: start;
            padding: 0.8rem 1rem;
            border-radius: 10px;
        }

        @media (max-width: 720px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ingredientsList = document.getElementById('ingredients-list');
            const addButton = document.getElementById('add-ingredient');

            function buildIngredientRow(index, values = { name: '', quantity: '', unit: '' }) {
                const row = document.createElement('div');
                row.className = 'ingredient-row';
                row.dataset.index = index;
                row.innerHTML = `
                    <div class="ingredient-row__fields">
                        <label>
                            Name
                            <input type="text" name="ingredients[${index}][name]" value="${escapeHtml(values.name)}" required>
                        </label>
                        <label>
                            Quantity
                            <input type="text" name="ingredients[${index}][quantity]" value="${escapeHtml(values.quantity)}">
                        </label>
                        <label>
                            Unit
                            <input type="text" name="ingredients[${index}][unit]" value="${escapeHtml(values.unit)}">
                        </label>
                    </div>
                    <button type="button" class="button button--ghost ingredient-row__remove">Remove</button>
                `;
                return row;
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            addButton.addEventListener('click', function () {
                const index = ingredientsList.children.length;
                ingredientsList.appendChild(buildIngredientRow(index));
            });

            ingredientsList.addEventListener('click', function (event) {
                if (!event.target.matches('.ingredient-row__remove')) {
                    return;
                }

                const row = event.target.closest('.ingredient-row');
                if (row) {
                    row.remove();
                    Array.from(ingredientsList.querySelectorAll('.ingredient-row')).forEach(function (row, rowIndex) {
                        row.dataset.index = rowIndex;
                        row.querySelectorAll('input').forEach(function (input) {
                            const name = input.name.replace(/ingredients\[\d+\]/, `ingredients[${rowIndex}]`);
                            input.name = name;
                        });
                    });
                }
            });
        });
    </script>
@endsection
