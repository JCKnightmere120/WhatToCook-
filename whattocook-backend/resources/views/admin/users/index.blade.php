@extends('layouts.app')

@section('content')
    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2>Users</h2>
                <p>Review application users and admin accounts.</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">{{ $users->links() }}</div>
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

        .pagination-wrapper {
            padding-top: 1rem;
        }
    </style>
@endsection
