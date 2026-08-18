<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Mutasi Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
        .subtitle { text-align: center; font-size: 12px; color: #555; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>LAPORAN MUTASI ASET</h1>
    <p class="subtitle">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
        @if($isSelection ?? false)
            &mdash; {{ $mutasiLogs->count() }} data terpilih
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Deskripsi</th>
                <th>Petugas</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mutasiLogs as $i => $log)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $log->asset->kode_barang ?? '-' }}</td>
                <td>{{ $log->asset->nama_barang ?? '-' }}</td>
                <td>{{ $log->deskripsi }}</td>
                <td>{{ $log->user->name ?? 'Sistem' }}</td>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
