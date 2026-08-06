@extends('admin.layout')

@section('title', 'Recipes')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Recipe library</h1>
            <p class="subheading">Create and maintain the recipes shown in the WhatToCook app.</p>
        </div>
        <a class="button" href="{{ route('admin.recipes.create') }}">+ Add recipe</a>
    </div>

    <section class="card">
        <form method="GET" action="{{ route('admin.recipes.index') }}" class="field-grid" style="align-items:end; margin-bottom:20px;">
            <div class="field">
                <label for="q">Search recipes</label>
                <input id="q" name="q" value="{{ $search }}" placeholder="Name, region, or meal type">
            </div>
            <div class="actions" style="margin:0;">
                <button type="submit">Search</button>
                @if ($search !== '')
                    <a class="button secondary" href="{{ route('admin.recipes.index') }}">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Recipe</th>
                        <th>Meal type</th>
                        <th>Region</th>
                        <th>Ingredients</th>
                        <th>Difficulty</th>
                        <th aria-label="Actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recipes as $recipe)
                        <tr>
                            <td><strong>{{ $recipe->name }}</strong></td>
                            <td>{{ $recipe->meal_type ?: '—' }}</td>
                            <td>{{ $recipe->region ?: '—' }}</td>
                            <td>{{ $recipe->ingredients_count }}</td>
                            <td><span class="badge">{{ ucfirst($recipe->difficulty ?: 'easy') }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="button secondary" href="{{ route('admin.recipes.edit', $recipe) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.recipes.destroy', $recipe) }}" onsubmit="return confirm('Delete {{ addslashes($recipe->name) }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="6">No recipes found. Add the first one with the button above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($recipes->hasPages())
            <nav class="pagination" aria-label="Recipe pages">
                <span>
                    @if ($recipes->onFirstPage()) Previous @else <a href="{{ $recipes->previousPageUrl() }}">← Previous</a> @endif
                </span>
                <span>Page {{ $recipes->currentPage() }} of {{ $recipes->lastPage() }}</span>
                <span>
                    @if ($recipes->hasMorePages()) <a href="{{ $recipes->nextPageUrl() }}">Next →</a> @else Next @endif
                </span>
            </nav>
        @endif
    </section>
@endsection
