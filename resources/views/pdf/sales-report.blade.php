<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
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
        <h1>Laporan Penjualan</h1>
        <p>Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Orders</div>
            <div class="value">{{ $summary['total_orders'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Revenue</div>
            <div class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Discount</div>
            <div class="value">Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Items Sold</div>
            <div class="value">{{ $summary['total_items_sold'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Completed</div>
            <div class="value">{{ $summary['completed_orders'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th class="text-center">Items</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total</th>
                <th class="text-center">Payment</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order['order_number'] }}</td>
                    <td>{{ $order['order_date'] }}</td>
                    <td>{{ $order['customer_name'] }}</td>
                    <td class="text-center">{{ $order['total_items'] }}</td>
                    <td class="text-right">Rp {{ number_format($order['subtotal'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order['discount'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</td>
                    <td class="text-center">
                        @php
                            $paymentClass = match($order['payment_status']) {
                                'paid' => 'badge-success',
                                'partial' => 'badge-warning',
                                default => 'badge-danger'
                            };
                        @endphp
                        <span class="badge {{ $paymentClass }}">{{ ucfirst($order['payment_status']) }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = match($order['status']) {
                                'completed' => 'badge-success',
                                'pending' => 'badge-warning',
                                'cancelled' => 'badge-danger',
                                default => 'badge-success'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ ucfirst($order['status']) }}</span>
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
