@extends('layouts.app')

@section('content')
    <section class="dashboard-overview">
        <header class="dashboard-header">
            <div>
                <h2>Admin Dashboard</h2>
                <p>Overview of users, recipes, and pantry items.</p>
            </div>
        </header>

        <div class="dashboard-grid">
            <article class="dashboard-card">
                <h3>Users</h3>
                <p>{{ $counts['users'] }}</p>
            </article>
            <article class="dashboard-card">
                <h3>Recipes</h3>
                <p>{{ $counts['recipes'] }}</p>
            </article>
            <article class="dashboard-card">
                <h3>Pantry items</h3>
                <p>{{ $counts['pantry_items'] }}</p>
            </article>
        </div>
    
        <section class="recent-recipes-section" style="margin-top:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
                <h3 style="margin:0">Recent Recipes</h3>
                <a href="{{ route('admin.recipes.index') }}" class="button button--secondary">View all recipes</a>
            </div>

            @if(isset($recentRecipes) && $recentRecipes->count())
                <div class="recent-recipes-list">
                    @foreach($recentRecipes as $recipe)
                        <div class="recent-recipe-item" style="display:flex;align-items:center;justify-content:space-between;padding:0.85rem;border:1px solid #eef2ff;border-radius:10px;background:#fff;margin-bottom:0.6rem">
                            <div>
                                <a href="{{ route('admin.recipes.edit', $recipe) }}" style="font-weight:700;color:#0f172a;text-decoration:none">{{ $recipe->name }}</a>
                                <div style="color:#475569;font-size:0.92rem;margin-top:0.25rem">{{ $recipe->meal_type ? ucfirst($recipe->meal_type) : '—' }} • {{ $recipe->difficulty ? ucfirst($recipe->difficulty) : '—' }}</div>
                            </div>
                            <div style="color:#6b7280;font-size:0.92rem">{{ $recipe->created_at->diffForHumans() }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p>No recent recipes yet.</p>
            @endif
        </section>
    </section>

    <style>
        .dashboard-overview {
            display: grid;
            gap: 1.75rem;
        }

        .dashboard-header {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .dashboard-header h2 {
            margin: 0;
            font-size: 1.75rem;
        }

        .dashboard-header p {
            margin: 0;
            color: #475569;
            line-height: 1.6;
        }

        .dashboard-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }

        .dashboard-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 1.5rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .dashboard-card h3 {
            margin: 0 0 0.8rem;
            color: #0f172a;
            font-size: 1rem;
            font-weight: 700;
        }

        .dashboard-card p {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 800;
            color: #1f2937;
        }
    </style>
@endsection
