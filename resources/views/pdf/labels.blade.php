<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Label Aset</title>
    {{--
        Desain tiap label sengaja dibuat sama dengan pdf/label.blade.php (label
        individual) — kode, nama, QR, merk, kategori · lokasi, dan badge kondisi.
        Bedanya cuma tata letak: di sini 2 label per baris memakai <table> supaya
        satu lembar A4 memuat 8 label dan tidak boros kertas.

        Kenapa <table>, bukan flex/grid: DomPDF dukungan flex/grid-nya lemah,
        sedangkan tabel adalah jalur paling matang dan paginasinya per-baris —
        dikombinasi dengan page-break-inside: avoid, satu label tidak akan
        terpotong di antara dua halaman.
    --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            font-size: 12px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page { padding: 14px; }
        table.grid { width: 100%; border-collapse: separate; border-spacing: 10px; }
        table.grid tr { page-break-inside: avoid; }
        table.grid td { width: 50%; vertical-align: top; }
        .label {
            border: 2px solid #1e293b;
            border-radius: 14px;
            padding: 14px 12px 12px;
            text-align: center;
            height: 250px;
        }
        .head {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .head h1 {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64748b;
        }
        .qr-wrap { margin-bottom: 8px; }
        .qr-wrap img { width: 90px; height: 90px; }
        .kode {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .nama {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .detail {
            font-size: 10px;
            color: #64748b;
            line-height: 1.4;
        }
        .foot {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 2px solid #e2e8f0;
        }
        .badge {
            display: inline-block;
            padding: 2px 14px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-baik { background: #dcfce7; color: #166534; }
        .badge-kurang { background: #fef9c3; color: #854d0e; }
        .badge-rusak { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
@php
    $kondisiBadge = [
        'Baik' => 'badge-baik',
        'Kurang Baik' => 'badge-kurang',
        'Rusak Berat' => 'badge-rusak',
    ];
@endphp
<div class="page">
    <table class="grid">
        @foreach($assets->chunk(2) as $baris)
        <tr>
            @foreach($baris as $asset)
            <td>
                <div class="label">
                    <div class="head">
                        <h1>Inventaris Aset</h1>
                    </div>
                    <div class="qr-wrap">
                        <img src="{{ $qrDataUris[$asset->id] }}" alt="QR {{ $asset->kode_barang }}" width="90" height="90">
                    </div>
                    <div class="kode">{{ $asset->kode_barang }}</div>
                    <div class="nama">{{ $asset->nama_barang }}</div>
                    <div class="detail">
                        @if($asset->merk){{ $asset->merk }}<br>@endif
                        {{ $asset->category?->nama ?? '-' }} &middot; {{ $asset->location?->nama ?? '-' }}
                    </div>
                    <div class="foot">
                        <span class="badge {{ $kondisiBadge[$asset->kondisi] ?? 'badge-baik' }}">
                            {{ $asset->kondisi }}
                        </span>
                    </div>
                </div>
            </td>
            @endforeach

            {{-- Jumlah aset ganjil: sel terakhir dikosongkan supaya lebar kolom
                 tetap 50% dan label terakhir tidak melar selebar halaman. --}}
            @if($baris->count() === 1)
            <td></td>
            @endif
        </tr>
        @endforeach
    </table>
</div>
</body>
</html>
