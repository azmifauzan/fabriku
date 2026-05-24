<table>
    <thead>
        <tr>
            <th colspan="7">Laporan Pembelian</th>
        </tr>
        <tr>
            <th colspan="7">
                Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
            </th>
        </tr>
        <tr>
            <th>Tanggal</th>
            <th>No. Invoice</th>
            <th>Supplier</th>
            <th>Total Item Unik</th>
            <th>Total Qty</th>
            <th>Total Nilai Pembelian</th>
            <th>Pencatat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($batches as $batch)
            <tr>
                <td>{{ $batch['date'] }}</td>
                <td>{{ $batch['purchase_invoice'] ?? '-' }}</td>
                <td>{{ $batch['supplier_name'] }}</td>
                <td>{{ $batch['item_count'] }}</td>
                <td>{{ $batch['total_qty'] }}</td>
                <td>{{ number_format($batch['total_cost'], 0, ',', '.') }}</td>
                <td>{{ $batch['adjusted_by'] }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL</strong></td>
            <td><strong>{{ $batches->sum('item_count') }}</strong></td>
            <td><strong>{{ $summary['total_items_purchased'] }}</strong></td>
            <td><strong>{{ number_format($summary['total_spend'], 0, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
