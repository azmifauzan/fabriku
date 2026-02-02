<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Produksi</title>
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
        .badge-info {
            background: #dbeafe;
            color: #2563eb;
        }
        .badge-warning {
            background: #fef3c7;
            color: #ca8a04;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Produksi</h1>
        <p>
            @if(!empty($filters['start_date']) || !empty($filters['end_date']))
                Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
            @else
                Semua Periode
            @endif
        </p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Orders</div>
            <div class="value">{{ $summary['total_orders'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Output</div>
            <div class="value">{{ $summary['total_output'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Material Cost</div>
            <div class="value">Rp {{ number_format($summary['total_material_cost'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Labor Cost</div>
            <div class="value">Rp {{ number_format($summary['total_labor_cost'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Cost</div>
            <div class="value">Rp {{ number_format($summary['total_cost'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Pattern</th>
                <th>Contractor</th>
                <th class="text-right">Output</th>
                <th class="text-right">Material</th>
                <th class="text-right">Labor</th>
                <th class="text-right">Total</th>
                <th>Dates</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order['order_number'] }}</td>
                    <td>{{ $order['pattern_name'] }}</td>
                    <td>{{ $order['contractor_name'] }}</td>
                    <td class="text-right">{{ $order['output_quantity'] }}</td>
                    <td class="text-right">Rp {{ number_format($order['material_cost'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order['labor_cost'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order['total_cost'], 0, ',', '.') }}</td>
                    <td>{{ $order['sent_date'] ?? '-' }} / {{ $order['completion_date'] ?? '-' }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = match($order['status']) {
                                'completed' => 'badge-success',
                                'in_progress' => 'badge-info',
                                'pending' => 'badge-warning',
                                default => 'badge-warning'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $order['status'])) }}</span>
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
