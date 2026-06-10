<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Layanan</title>
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
            font-size: 13px;
            font-weight: bold;
            color: #333;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        table.data th {
            background: #f1f1f1;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        h2 { font-size: 13px; margin: 16px 0 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Layanan</h1>
        <p>Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">Jenis Layanan Terjual</div>
            <div class="value">{{ $summary['total_service_types'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Layanan Terjual</div>
            <div class="value">{{ $summary['total_services_sold'] }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Layanan</th>
                <th>Kategori</th>
                <th class="text-right">Jumlah Terjual</th>
                <th class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
                <tr>
                    <td>{{ $service['code'] }}</td>
                    <td>{{ $service['name'] }}</td>
                    <td>{{ $service['category'] ?? '-' }}</td>
                    <td class="text-right">{{ $service['total_sold'] }}</td>
                    <td class="text-right">Rp {{ number_format($service['total_revenue'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center">Tidak ada data layanan pada periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($staffRecap) > 0)
        <h2>Rekap per Staff</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th class="text-right">Jumlah Layanan</th>
                    <th class="text-right">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffRecap as $staff)
                    <tr>
                        <td>{{ $staff['staff_name'] }}</td>
                        <td class="text-right">{{ $staff['total_services'] }}</td>
                        <td class="text-right">Rp {{ number_format($staff['total_revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
