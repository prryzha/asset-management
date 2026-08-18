<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Aset Hilang</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
        .subtitle { text-align: center; font-size: 12px; color: #555; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>LAPORAN ASET HILANG</h1>
    <p class="subtitle">
        Daftar aset yang dilaporkan hilang &mdash;
        dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>Kategori</th>
                <th>Lokasi Terakhir</th>
                <th>Tanggal Laporan</th>
                <th>Petugas</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $i => $item)
            @php $lostLog = $item->assetLogs->first(); @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->merk ?? '-' }}</td>
                <td>{{ $item->category?->nama ?? '-' }}</td>
                <td>{{ $item->location?->nama ?? '-' }}</td>
                <td>{{ $lostLog ? $lostLog->created_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $lostLog?->user?->name ?? 'Sistem' }}</td>
                <td>{{ $lostLog?->deskripsi ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; color: #777;">Tidak ada aset yang dilaporkan hilang.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
