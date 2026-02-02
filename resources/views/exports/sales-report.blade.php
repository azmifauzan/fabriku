<table>
    <thead>
        <tr>
            <th colspan="9">Laporan Penjualan</th>
        </tr>
        <tr>
            <th colspan="9">
                Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
                @if(!empty($filters['status']))
                    | Status: {{ ucfirst($filters['status']) }}
                @endif
            </th>
        </tr>
        <tr>
            <th>Order #</th>
            <th>Tanggal</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Subtotal</th>
            <th>Discount</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order['order_number'] }}</td>
                <td>{{ $order['order_date'] }}</td>
                <td>{{ $order['customer_name'] }}</td>
                <td>{{ $order['total_items'] }}</td>
                <td>{{ number_format($order['subtotal'], 0, ',', '.') }}</td>
                <td>{{ number_format($order['discount'], 0, ',', '.') }}</td>
                <td>{{ number_format($order['total_amount'], 0, ',', '.') }}</td>
                <td>{{ ucfirst($order['payment_status']) }}</td>
                <td>{{ ucfirst($order['status']) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL</strong></td>
            <td><strong>{{ $summary['total_items_sold'] }}</strong></td>
            <td colspan="2"></td>
            <td><strong>{{ number_format($summary['total_revenue'], 0, ',', '.') }}</strong></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
