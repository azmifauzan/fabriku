<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Penjualan per Customer</title>
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
        .summary-item .value.success {
            color: #16a34a;
        }
        .summary-item .value.danger {
            color: #dc2626;
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
        .text-success {
            color: #16a34a;
        }
        .text-danger {
            color: #dc2626;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekapitulasi Penjualan per Customer</h1>
        <p>Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Customer</div>
            <div class="value">{{ $summary['total_customers'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Revenue</div>
            <div class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Terbayar</div>
            <div class="value success">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Outstanding</div>
            <div class="value danger">Rp {{ number_format($summary['total_outstanding'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th class="text-center">Total Order</th>
                <th class="text-right">Total Revenue</th>
                <th class="text-right">Total Terbayar</th>
                <th class="text-right">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recap as $item)
                <tr>
                    <td>{{ $item['customer_name'] }}</td>
                    <td class="text-center">{{ $item['total_orders'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}</td>
                    <td class="text-right text-success">Rp {{ number_format($item['total_paid'], 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp {{ number_format($item['outstanding'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Halaman 1</p>
    </div>
</body>
</html>
