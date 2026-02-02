<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Inventory</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #666;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
        }
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
        }
        .summary-item .value {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: left;
        }
        th {
            background: #4f46e5;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            font-size: 9px;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background: #dcfce7;
            color: #16a34a;
        }
        .badge-warning {
            background: #fef3c7;
            color: #ca8a04;
        }
        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Inventory</h1>
        <p>
            @if(!empty($filters['status']))
                Status: {{ ucfirst(str_replace('_', ' ', $filters['status'])) }}
            @else
                Semua Status
            @endif
            @if(!empty($filters['category']))
                | Kategori: {{ ucfirst($filters['category']) }}
            @endif
        </p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Items</div>
            <div class="value">{{ $summary['total_items'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Quantity</div>
            <div class="value">{{ $summary['total_quantity'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Nilai</div>
            <div class="value">Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Low Stock</div>
            <div class="value">{{ $summary['low_stock_items'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Out of Stock</div>
            <div class="value">{{ $summary['out_of_stock_items'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Item</th>
                <th>Lokasi</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Reserved</th>
                <th class="text-right">Available</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total Value</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['sku'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['location'] ?? '-' }}</td>
                    <td class="text-right">{{ $item['quantity'] }}</td>
                    <td class="text-right">{{ $item['reserved_quantity'] }}</td>
                    <td class="text-right">{{ $item['available_quantity'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['total_value'], 0, ',', '.') }}</td>
                    <td class="text-center">
                        @php
                            $badgeClass = match($item['status']) {
                                'available' => 'badge-success',
                                'low_stock' => 'badge-warning',
                                'out_of_stock' => 'badge-danger',
                                default => 'badge-success'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $item['status'])) }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Halaman 1</p>
    </div>
</body>
</html>
