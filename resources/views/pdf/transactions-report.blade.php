<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman Aset</title>
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
    <h1>LAPORAN PEMINJAMAN ASET</h1>
    <p class="subtitle">
        Laporan administratif peminjaman aset &mdash;
        dicetak pada: {{ now()->format('d/m/Y H:i') }}
        @if(request('tanggal_dari') || request('tanggal_sampai'))
            &mdash; Periode:
            {{ request('tanggal_dari') ? \Carbon\Carbon::parse(request('tanggal_dari'))->format('d/m/Y') : 'awal' }}
            s.d.
            {{ request('tanggal_sampai') ? \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d/m/Y') : 'sekarang' }}
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Peminjam</th>
                <th>Keperluan</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $trx)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $trx->asset?->kode_barang ?? '-' }}</td>
                <td>{{ $trx->asset?->nama_barang ?? '-' }}</td>
                <td>{{ $trx->asset?->category?->nama ?? '-' }}</td>
                <td>{{ $trx->asset?->location?->nama ?? '-' }}</td>
                <td>{{ $trx->nama_peminjam }}</td>
                <td>{{ $trx->keperluan ?? '-' }}</td>
                <td>{{ $trx->tanggal_pinjam }}</td>
                <td>{{ $trx->tanggal_kembali ?? '-' }}</td>
                <td>{{ $trx->status_peminjaman }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; color: #777;">Tidak ada transaksi peminjaman.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
