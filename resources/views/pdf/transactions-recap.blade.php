<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Peminjaman Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
        .subtitle { text-align: center; font-size: 12px; color: #555; margin-bottom: 20px; }
        h2 { font-size: 13px; margin: 22px 0 8px; border-bottom: 1px solid #333; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        .ringkasan-table td { width: 50%; }
        .empty { text-align: center; color: #777; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>REKAP PEMINJAMAN ASET</h1>
    <p class="subtitle">
        Ringkasan agregat peminjaman &mdash;
        dicetak pada: {{ now()->format('d/m/Y H:i') }}
        @if(request('tanggal_dari') || request('tanggal_sampai'))
            &mdash; Periode:
            {{ request('tanggal_dari') ? \Carbon\Carbon::parse(request('tanggal_dari'))->format('d/m/Y') : 'awal' }}
            s.d.
            {{ request('tanggal_sampai') ? \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d/m/Y') : 'sekarang' }}
        @endif
        @if(request('category_id'))
            &mdash; Kategori: {{ optional(\App\Models\Category::find(request('category_id')))->nama ?? '-' }}
        @endif
        @if(request('location_id'))
            &mdash; Lokasi: {{ optional(\App\Models\Location::find(request('location_id')))->nama ?? '-' }}
        @endif
        @if(request('status'))
            &mdash; Status: {{ request('status') }}
        @endif
    </p>

    <h2>Ringkasan</h2>
    <table class="ringkasan-table">
        <tr><td>Total Peminjaman</td><td>{{ $recap['total_peminjaman'] }}</td></tr>
        <tr><td>Sedang Dipinjam</td><td>{{ $recap['sedang_dipinjam'] }}</td></tr>
        <tr><td>Total Pengembalian</td><td>{{ $recap['total_pengembalian'] }}</td></tr>
        <tr><td>Total Transaksi</td><td>{{ $recap['total_transaksi'] }}</td></tr>
    </table>

    <h2>Peminjaman per Kategori</h2>
    <table>
        <thead><tr><th>Kategori</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($recap['per_kategori'] as $row)
            <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['jumlah'] }}</td></tr>
            @empty
            <tr><td colspan="2" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Peminjaman per Lokasi</h2>
    <table>
        <thead><tr><th>Lokasi</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($recap['per_lokasi'] as $row)
            <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['jumlah'] }}</td></tr>
            @empty
            <tr><td colspan="2" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Peminjaman berdasarkan Status</h2>
    <table>
        <thead><tr><th>Status</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($recap['per_status'] as $row)
            <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['jumlah'] }}</td></tr>
            @empty
            <tr><td colspan="2" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Peminjaman per Bulan</h2>
    <table>
        <thead><tr><th>Bulan</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($recap['per_bulan'] as $row)
            <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['jumlah'] }}</td></tr>
            @empty
            <tr><td colspan="2" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Peminjam Terbanyak (maks. 10)</h2>
    <table>
        <thead><tr><th>No</th><th>Nama Peminjam</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($recap['top_peminjam'] as $i => $row)
            <tr><td>{{ $i + 1 }}</td><td>{{ $row['nama'] }}</td><td class="text-right">{{ $row['jumlah'] }}</td></tr>
            @empty
            <tr><td colspan="3" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
