<table>
    <thead>
        <tr>
            <th colspan="8">Laporan Material</th>
        </tr>
        <tr>
            <th colspan="8">
                @if(!empty($filters['start_date']) || !empty($filters['end_date']))
                    Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
                @else
                    Semua Periode
                @endif
            </th>
        </tr>
        <tr>
            <th>Kode</th>
            <th>Nama Material</th>
            <th>Tipe</th>
            <th>Stok Saat Ini</th>
            <th>Total Diterima</th>
            <th>Total Digunakan</th>
            <th>Total Biaya</th>
            <th>Harga Rata-rata</th>
        </tr>
    </thead>
    <tbody>
        @foreach($materials as $material)
            <tr>
                <td>{{ $material['code'] }}</td>
                <td>{{ $material['name'] }}</td>
                <td>{{ $material['type'] ?? '-' }}</td>
                <td>{{ number_format($material['current_stock'], 2) }} {{ $material['unit'] }}</td>
                <td>{{ number_format($material['total_received'], 2) }}</td>
                <td>{{ number_format($material['total_used'], 2) }}</td>
                <td>{{ number_format($material['total_cost'], 0, ',', '.') }}</td>
                <td>{{ number_format($material['average_price'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL</strong></td>
            <td><strong>{{ $summary['total_items'] }} items</strong></td>
            <td><strong>{{ number_format($summary['total_received'], 2) }}</strong></td>
            <td><strong>{{ number_format($summary['total_used'], 2) }}</strong></td>
            <td><strong>{{ number_format($summary['total_stock_value'], 0, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
