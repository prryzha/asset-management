<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perawatan Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
        .subtitle { text-align: center; font-size: 12px; color: #555; margin-bottom: 20px; }
        tfoot td { font-weight: bold; background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>LAPORAN PERAWATAN ASET</h1>
    <p class="subtitle">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
        @if($isSelection ?? false)
            &mdash; {{ $maintenanceSchedules->count() }} data terpilih
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jenis Perawatan</th>
                <th>Teknisi/Vendor</th>
                <th>Tgl Jadwal</th>
                <th>Tgl Selesai</th>
                <th>Status</th>
                <th>Biaya</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($maintenanceSchedules as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->asset->kode_barang ?? '-' }}</td>
                <td>{{ $item->asset->nama_barang ?? '-' }}</td>
                <td>{{ $item->jenis_perawatan }}</td>
                <td>{{ $item->teknisi ?? '-' }}</td>
                <td>{{ $item->tanggal_jadwal->format('d/m/Y') }}</td>
                <td>{{ $item->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->biaya ? 'Rp '.number_format($item->biaya,0,',','.') : '-' }}</td>
                <td>{{ $item->catatan_selesai ?? $item->catatan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">Total Biaya Perawatan</td>
                <td colspan="2">Rp {{ number_format($maintenanceSchedules->sum('biaya'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
