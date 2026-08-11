<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Asset - {{ $asset->kode_barang }}</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background: #f1f5f9;
            padding: 32px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .label-card {
            width: 360px;
            background: #ffffff;
            border: 2.5px solid #0f172a;
            border-radius: 16px;
            padding: 24px 20px 20px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .cut-tl, .cut-tr, .cut-bl, .cut-br {
            position: absolute;
            width: 18px;
            height: 18px;
            border-color: #94a3b8;
            border-style: dashed;
        }
        .cut-tl { top: -2.5px; left: -2.5px; border-top: 2px dashed #94a3b8; border-left: 2px dashed #94a3b8; border-radius: 16px 0 0 0; }
        .cut-tr { top: -2.5px; right: -2.5px; border-top: 2px dashed #94a3b8; border-right: 2px dashed #94a3b8; border-radius: 0 16px 0 0; }
        .cut-bl { bottom: -2.5px; left: -2.5px; border-bottom: 2px dashed #94a3b8; border-left: 2px dashed #94a3b8; border-radius: 0 0 0 16px; }
        .cut-br { bottom: -2.5px; right: -2.5px; border-bottom: 2px dashed #94a3b8; border-right: 2px dashed #94a3b8; border-radius: 0 0 16px 0; }
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: #64748b;
        }
        .qr-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 14px;
        }
        .qr-wrap img { width: 110px; height: 110px; }
        .info { text-align: center; }
        .kode {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1.5px;
            margin-bottom: 2px;
        }
        .nama {
            font-size: 16px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
        }
        .detail {
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }
        .foot {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 18px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-baik { background: #dcfce7; color: #166534; }
        .badge-kurang { background: #fef9c3; color: #854d0e; }
        .badge-rusak { background: #fee2e2; color: #991b1b; }
        .badge-default { background: #f1f5f9; color: #475569; }
        .hint {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 10px;
        }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button, .no-print a {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            margin: 0 4px;
        }
        .btn-print { background: #0f172a; color: #fff; }
        .btn-pdf { background: #dc2626; color: #fff; }
        .btn-back { background: #e2e8f0; color: #334155; }
        @media print {
            @page { margin: 8mm; }
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .hint { display: none; }
            .label-card { box-shadow: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Cetak Label</button>
        <a href="{{ route('assets.label-pdf', $asset) }}" class="btn-pdf">Download PDF</a>
        <a href="{{ route('assets.show', $asset) }}" class="btn-back">&larr; Kembali</a>
    </div>

    <div class="label-card">
        <div class="cut-tl"></div>
        <div class="cut-tr"></div>
        <div class="cut-bl"></div>
        <div class="cut-br"></div>

        <div class="header">
            <h1>Inventory Asset</h1>
        </div>

        <div class="qr-wrap">
            <img src="{{ route('assets.qr-code', $asset) }}" alt="QR {{ $asset->kode_barang }}">
        </div>

        <div class="info">
            <div class="kode">{{ $asset->kode_barang }}</div>
            <div class="nama">{{ $asset->nama_barang }}</div>
            <div class="detail">
                @if($asset->merk){{ $asset->merk }}<br>@endif
                {{ $asset->category?->nama ?? '-' }} &middot; {{ $asset->location?->nama ?? '-' }}
            </div>
        </div>

        <div class="foot">
            @php
                $badgeMap = [
                    'Baik' => 'badge-baik',
                    'Kurang Baik' => 'badge-kurang',
                    'Rusak Berat' => 'badge-rusak',
                ];
                $badgeClass = $badgeMap[$asset->kondisi] ?? 'badge-default';
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $asset->kondisi }}</span>
        </div>
    </div>

    <div class="hint">Tempelkan label ini pada asset yang sesuai.</div>

</body>
</html>
