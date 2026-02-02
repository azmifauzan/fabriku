<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Material</title>
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
        .low-stock {
            background-color: #fee2e2 !important;
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
        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Material</h1>
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
            <div class="label">Total Material</div>
            <div class="value">{{ $summary['total_items'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Nilai Stock</div>
            <div class="value">Rp {{ number_format($summary['total_stock_value'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Diterima</div>
            <div class="value">{{ number_format($summary['total_received'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Digunakan</div>
            <div class="value">{{ number_format($summary['total_used'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Low Stock</div>
            <div class="value">{{ $summary['low_stock_count'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Material</th>
                <th>Tipe</th>
                <th class="text-right">Stok Saat Ini</th>
                <th class="text-right">Total Diterima</th>
                <th class="text-right">Total Digunakan</th>
                <th class="text-right">Total Biaya</th>
                <th class="text-right">Harga Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materials as $material)
                <tr @if($material['is_low_stock']) class="low-stock" @endif>
                    <td>{{ $material['code'] }}</td>
                    <td>
                        {{ $material['name'] }}
                        @if($material['is_low_stock'])
                            <span class="badge badge-danger">Low</span>
                        @endif
                    </td>
                    <td>{{ $material['type'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($material['current_stock'], 2) }} {{ $material['unit'] }}</td>
                    <td class="text-right">{{ number_format($material['total_received'], 2) }}</td>
                    <td class="text-right">{{ number_format($material['total_used'], 2) }}</td>
                    <td class="text-right">Rp {{ number_format($material['total_cost'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($material['average_price'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Halaman 1</p>
    </div>
</body>
</html>
