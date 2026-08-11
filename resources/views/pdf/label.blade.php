<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Label - {{ $asset->kode_barang }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            font-size: 12px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page {
            width: 100%;
            padding: 20px;
        }
        .label {
            width: 320px;
            margin: 0 auto;
            border: 2px solid #1e293b;
            border-radius: 14px;
            padding: 20px 18px 16px;
            text-align: center;
        }
        .head {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .head h1 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64748b;
        }
        .qr-wrap { margin-bottom: 12px; }
        .qr-wrap img { width: 100px; height: 100px; }
        .kode {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .nama {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        .detail {
            font-size: 11px;
            color: #64748b;
            line-height: 1.5;
        }
        .foot {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
        }
        .badge {
            display: inline-block;
            padding: 3px 16px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-baik { background: #dcfce7; color: #166534; }
        .badge-kurang { background: #fef9c3; color: #854d0e; }
        .badge-rusak { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="page">
        <div class="label">
            <div class="head">
                <h1>Inventory Asset</h1>
            </div>
            <div class="qr-wrap">
                <img src="{{ $qrDataUri }}" alt="QR {{ $asset->kode_barang }}" width="100" height="100">
            </div>
            <div class="kode">{{ $asset->kode_barang }}</div>
            <div class="nama">{{ $asset->nama_barang }}</div>
            <div class="detail">
                @if($asset->merk){{ $asset->merk }}<br>@endif
                {{ $asset->category?->nama ?? '-' }} &middot; {{ $asset->location?->nama ?? '-' }}
            </div>
            <div class="foot">
                @php
                    $map = [
                        'Baik' => 'badge-baik',
                        'Kurang Baik' => 'badge-kurang',
                        'Rusak Berat' => 'badge-rusak',
                    ];
                @endphp
                <span class="badge {{ $map[$asset->kondisi] ?? 'badge-baik' }}">
                    {{ $asset->kondisi }}
                </span>
            </div>
        </div>
    </div>
</body>
</html>
