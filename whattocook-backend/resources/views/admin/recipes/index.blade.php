@extends('layouts.app')

@section('content')
    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2>Recipes</h2>
                <p>View and manage stored recipes.</p>
            </div>
            <a href="{{ route('admin.recipes.create') }}" class="button button--primary">Create new recipe</a>
        </div>

        @if(session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Difficulty</th>
                        <th>Meal Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recipes as $recipe)
                        <tr>
                            <td>{{ $recipe->name }}</td>
                            <td>{{ ucfirst($recipe->difficulty ?? '—') }}</td>
                            <td>{{ ucfirst($recipe->meal_type ?? '—') }}</td>
                            <td class="data-table__actions">
                                <a class="button button--secondary" href="{{ route('admin.recipes.edit', $recipe) }}">Edit</a>
                                <form action="{{ route('admin.recipes.destroy', $recipe) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button button--ghost" onclick="return confirm('Delete recipe?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">{{ $recipes->links() }}</div>
    </section>

    <style>
        .admin-panel {
            display: grid;
            gap: 1rem;
        }

        .admin-panel__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .alert--success {
            padding: 1rem 1.25rem;
            border-radius: 14px;
            border: 1px solid #d1fae5;
            background: #ecfdf5;
            color: #166534;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 620px;
        }

        .data-table th,
        .data-table td {
            padding: 1rem 1.15rem;
            text-align: left;
        }

        .data-table th {
            background: #f8fafc;
            font-size: 0.95rem;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #eef2ff;
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table__actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .inline-form {
            display: inline;
            margin: 0;
        }

        .pagination-wrapper {
            padding-top: 1rem;
        }
    </style>
@endsection
