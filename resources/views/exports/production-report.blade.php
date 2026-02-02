<table>
    <thead>
        <tr>
            <th colspan="9">Laporan Produksi</th>
        </tr>
        <tr>
            <th colspan="9">
                @if(!empty($filters['start_date']) || !empty($filters['end_date']))
                    Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
                @else
                    Semua Periode
                @endif
                @if(!empty($filters['status']))
                    | Status: {{ ucfirst(str_replace('_', ' ', $filters['status'])) }}
                @endif
            </th>
        </tr>
        <tr>
            <th>Order #</th>
            <th>Pattern</th>
            <th>Contractor</th>
            <th>Output Qty</th>
            <th>Material Cost</th>
            <th>Labor Cost</th>
            <th>Total Cost</th>
            <th>Dates</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order['order_number'] }}</td>
                <td>{{ $order['pattern_name'] }}</td>
                <td>{{ $order['contractor_name'] }}</td>
                <td>{{ $order['output_quantity'] }}</td>
                <td>{{ number_format($order['material_cost'], 0, ',', '.') }}</td>
                <td>{{ number_format($order['labor_cost'], 0, ',', '.') }}</td>
                <td>{{ number_format($order['total_cost'], 0, ',', '.') }}</td>
                <td>{{ $order['sent_date'] ?? '-' }} / {{ $order['completion_date'] ?? '-' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $order['status'])) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL</strong></td>
            <td><strong>{{ $summary['total_output'] }}</strong></td>
            <td><strong>{{ number_format($summary['total_material_cost'], 0, ',', '.') }}</strong></td>
            <td><strong>{{ number_format($summary['total_labor_cost'], 0, ',', '.') }}</strong></td>
            <td><strong>{{ number_format($summary['total_cost'], 0, ',', '.') }}</strong></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
