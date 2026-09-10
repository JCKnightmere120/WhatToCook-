@extends('layout')

@section('content')
    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2>Categories</h2>
                <p>Browse recipe categories and meal types.</p>
            </div>
        </div>

        <div class="category-list">
            @foreach($categories as $cat)
                <span class="category-pill">{{ $cat }}</span>
            @endforeach
        </div>
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

        .category-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 1.25rem;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--card);
        }

        .category-pill {
            display: inline-flex;
            padding: 0.65rem 1rem;
            border-radius: 999px;
            background: rgba(224, 120, 86, .14);
            color: #ffc0ac;
            font-weight: 600;
        }
    </style>
@endsection
