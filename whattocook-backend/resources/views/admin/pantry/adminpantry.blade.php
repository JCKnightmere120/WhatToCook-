@extends('layout')

@section('content')
    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2>Pantry Items</h2>
                <p>Manage pantry contents and freshness data.</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>User</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Freshness</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->user?->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ $item->freshness_status ?? 'Unknown' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">{{ $items->links() }}</div>
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

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--card);
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
            background: rgba(0, 0, 0, .12);
            font-size: 0.95rem;
            color: var(--muted);
            border-bottom: 1px solid var(--line);
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--line);
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .pagination-wrapper {
            padding-top: 1rem;
        }
    </style>
@endsection
