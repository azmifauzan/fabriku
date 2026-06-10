<table>
    <thead>
        <tr>
            <th colspan="5">Laporan Layanan</th>
        </tr>
        <tr>
            <th colspan="5">
                Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
            </th>
        </tr>
        <tr>
            <th>Kode</th>
            <th>Layanan</th>
            <th>Kategori</th>
            <th>Jumlah Terjual</th>
            <th>Total Pendapatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($services as $service)
            <tr>
                <td>{{ $service['code'] }}</td>
                <td>{{ $service['name'] }}</td>
                <td>{{ $service['category'] ?? '-' }}</td>
                <td>{{ $service['total_sold'] }}</td>
                <td>{{ number_format($service['total_revenue'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td><strong>{{ $summary['total_services_sold'] }}</strong></td>
            <td><strong>{{ number_format($summary['total_revenue'], 0, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

@if(count($staffRecap) > 0)
<table>
    <thead>
        <tr>
            <th colspan="3">Rekap per Staff</th>
        </tr>
        <tr>
            <th>Staff</th>
            <th>Jumlah Layanan</th>
            <th>Total Pendapatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($staffRecap as $staff)
            <tr>
                <td>{{ $staff['staff_name'] }}</td>
                <td>{{ $staff['total_services'] }}</td>
                <td>{{ number_format($staff['total_revenue'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
