<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pembelian</h1>
        <p>Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $summary['total_transactions'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Item Dibeli</div>
            <div class="value">{{ $summary['total_items_purchased'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Pengeluaran</div>
            <div class="value">Rp {{ number_format($summary['total_spend'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Supplier</th>
                <th class="text-center">Item Unik</th>
                <th class="text-center">Total Qty</th>
                <th class="text-right">Total Nilai</th>
                <th>Pencatat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batches as $batch)
                <tr>
                    <td>{{ $batch['date'] }}</td>
                    <td>{{ $batch['purchase_invoice'] ?? '-' }}</td>
                    <td>{{ $batch['supplier_name'] }}</td>
                    <td class="text-center">{{ $batch['item_count'] }}</td>
                    <td class="text-center">{{ $batch['total_qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($batch['total_cost'], 0, ',', '.') }}</td>
                    <td>{{ $batch['adjusted_by'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Halaman 1</p>
    </div>
</body>
</html>
