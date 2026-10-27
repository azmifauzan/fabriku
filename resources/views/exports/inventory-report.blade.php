<table>
    <thead>
        <tr>
            <th colspan="{{ $summary['total_value'] !== null ? 9 : 7 }}">Laporan Inventory</th>
        </tr>
        <tr>
            <th colspan="{{ $summary['total_value'] !== null ? 9 : 7 }}">
                @if(!empty($filters['status']))
                    Status: {{ ucfirst(str_replace('_', ' ', $filters['status'])) }}
                @else
                    Semua Status
                @endif
                @if(!empty($filters['category']))
                    | Kategori: {{ ucfirst($filters['category']) }}
                @endif
            </th>
        </tr>
        <tr>
            <th>SKU</th>
            <th>Nama Item</th>
            <th>Lokasi</th>
            <th>Qty</th>
            <th>Reserved</th>
            <th>Available</th>
            @if($summary['total_value'] !== null)<th>Unit Price</th>@endif
            @if($summary['total_value'] !== null)<th>Total Value</th>@endif
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item['sku'] }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['location'] ?? '-' }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ $item['reserved_quantity'] }}</td>
                <td>{{ $item['available_quantity'] }}</td>
                @if($summary['total_value'] !== null)<td>{{ number_format($item['unit_price'], 0, ',', '.') }}</td>@endif
                @if($summary['total_value'] !== null)<td>{{ number_format($item['total_value'], 0, ',', '.') }}</td>@endif
                <td>{{ ucfirst(str_replace('_', ' ', $item['status'])) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL</strong></td>
            <td><strong>{{ $summary['total_quantity'] }}</strong></td>
            <td colspan="{{ $summary['total_value'] !== null ? 3 : 2 }}"></td>
            @if($summary['total_value'] !== null)
                <td><strong>{{ number_format($summary['total_value'], 0, ',', '.') }}</strong></td>
            @endif
            <td></td>
        </tr>
    </tfoot>
</table>
