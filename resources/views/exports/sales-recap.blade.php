<table>
    <thead>
        <tr>
            <th colspan="5">Rekapitulasi Penjualan per Customer</th>
        </tr>
        <tr>
            <th colspan="5">
                Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
            </th>
        </tr>
        <tr>
            <th>Customer</th>
            <th>Total Order</th>
            <th>Total Revenue</th>
            <th>Total Terbayar</th>
            <th>Outstanding</th>
        </tr>
    </thead>
    <tbody>
        @foreach($recap as $item)
            <tr>
                <td>{{ $item['customer_name'] }}</td>
                <td>{{ $item['total_orders'] }}</td>
                <td>{{ number_format($item['total_revenue'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['total_paid'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['outstanding'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td><strong>TOTAL</strong></td>
            <td><strong>{{ $summary['total_customers'] }} customers</strong></td>
            <td><strong>{{ number_format($summary['total_revenue'], 0, ',', '.') }}</strong></td>
            <td><strong>{{ number_format($summary['total_paid'], 0, ',', '.') }}</strong></td>
            <td><strong>{{ number_format($summary['total_outstanding'], 0, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
</table>
