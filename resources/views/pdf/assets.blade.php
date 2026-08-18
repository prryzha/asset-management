<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Aset</title>
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
    <h1>LAPORAN DATA ASET</h1>
    <p class="subtitle">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
        @if($isSelection ?? false)
            &mdash; {{ $assets->count() }} data terpilih
        @endif
        @if($filterCategory ?? false)
            &mdash; Kategori: {{ $filterCategory }}
        @endif
        @if($filterLocation ?? false)
            &mdash; Lokasi: {{ $filterLocation }}
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Kondisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->merk }}</td>
                <td>{{ $item->category?->nama ?? '-' }}</td>
                <td>{{ $item->location?->nama ?? '-' }}</td>
                <td>{{ $item->kondisi }}</td>
                <td>{{ $item->status === 'Disposed' ? 'Dihapuskan' : $item->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
