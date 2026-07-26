<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: sans-serif; color: #2a1810; }
    h1 { font-size: 20px; margin-bottom: 4px; }
    p.sub { color: #8a7a68; font-size: 12px; margin-top: 0; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e8d9bd; font-size: 13px; }
    th { background: #f3e4c8; }
    .empty { color: #8a7a68; font-style: italic; }
</style>
</head>
<body>
    <h1>WhatToCook - Shopping List</h1>
    <p class="sub">Generated on {{ $generatedAt }} for {{ $userName }}</p>

    @if($items->isEmpty())
        <p class="empty">Wala nay laing gikinahanglan &mdash; kompleto na ang pantry!</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->ingredient_name }}</td>
                        <td>{{ $item->quantity ?? '-' }}</td>
                        <td>{{ $item->unit ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>