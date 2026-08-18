<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\Location;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = $this->transactionListQuery($request)
            ->with('approver')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Query dasar Riwayat Peminjaman — dipakai bareng index() dan export PDF/CSV
     * supaya dataset tabel == dataset export untuk filter yang sama (termasuk
     * search). $respectFilters dimatikan khusus export "data terpilih" (ids),
     * perilaku export lama yang dipertahankan.
     */
    private function transactionListQuery(Request $request, bool $respectFilters = true)
    {
        return Transaction::with('asset')
            ->when($respectFilters && $request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($q) use ($search) {
                    $q->where('nama_peminjam', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($q) use ($search) {
                            $q->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                        });
                });
            })
            ->when($respectFilters && $request->filled('status'), fn($q) => $q->where('status_peminjaman', $request->input('status')))
            ->when($respectFilters && $request->filled('tanggal_dari'), fn($q) => $q->whereDate('tanggal_pinjam', '>=', $request->input('tanggal_dari')))
            ->when($respectFilters && $request->filled('tanggal_sampai'), fn($q) => $q->whereDate('tanggal_pinjam', '<=', $request->input('tanggal_sampai')));
    }

    public function create(): View
    {
        $assets = Cache::remember('available_assets', 300, function () {
            return Asset::where('status', 'Tersedia')
                ->orderBy('kode_barang')
                ->get(['id', 'kode_barang', 'nama_barang']);
        });

        return view('transactions.create', compact('assets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'nama_peminjam' => ['required', 'string', 'max:255'],
            'keperluan' => ['required', 'string', 'max:255'],
            'tanggal_pinjam' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated) {
            $asset = Asset::lockForUpdate()->findOrFail($validated['asset_id']);

            if ($asset->status !== 'Tersedia') {
                throw ValidationException::withMessages([
                    'asset_id' => 'Aset ini sudah tidak tersedia untuk dipinjam.',
                ]);
            }

            if ($asset->maintenanceSchedules()->where('status', 'Dikerjakan')->exists()) {
                throw ValidationException::withMessages([
                    'asset_id' => 'Aset ini sedang dalam perbaikan.',
                ]);
            }

            $transaction = Transaction::create([
                ...$validated,
                'created_by' => auth()->id(),
                'status_peminjaman' => 'Dipinjam',
            ]);

            $asset->update(['status' => 'Dipinjam']);

            ActivityLog::record(
                $transaction,
                'transaction.borrowed',
                "Mencatat peminjaman {$asset->kode_barang} oleh {$transaction->nama_peminjam}",
                ['asset_id' => $asset->id, 'nama_peminjam' => $transaction->nama_peminjam],
            );

            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'peminjaman',
                'deskripsi' => "Dipinjam oleh {$transaction->nama_peminjam} untuk {$transaction->keperluan}",
                'user_id' => auth()->id(),
            ]);
        });

        Cache::forget('available_assets');

        return redirect()->route('transactions.index')->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function returnItem(Transaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $trx = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if (!in_array($trx->status_peminjaman, ['Dipinjam'])) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi ini tidak dapat dikembalikan.',
                ]);
            }

            $asset = Asset::lockForUpdate()->findOrFail($trx->asset_id);

            // Jalur normal tidak mungkin punya transaksi Dipinjam pada aset
            // Dihapuskan (processDisposal() sudah menolaknya). Guard ini tetap
            // diperlukan agar data lama/tidak konsisten tidak dapat membuat return
            // yang membangkitkan aset arsip kembali menjadi aktif.
            if ($asset->status === 'Disposed') {
                throw ValidationException::withMessages([
                    'transaction' => 'Aset yang sudah Dihapuskan tidak dapat diproses pengembaliannya.',
                ]);
            }

            $trx->update([
                'status_peminjaman' => 'Dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            $assetStatus = $asset->maintenanceSchedules()->where('status', 'Dikerjakan')->exists()
                ? 'Perbaikan'
                : 'Tersedia';
            $asset->update(['status' => $assetStatus]);

            ActivityLog::record(
                $trx,
                'transaction.returned',
                "Mencatat pengembalian {$asset->kode_barang}",
                ['asset_id' => $asset->id, 'status_aset' => $assetStatus],
            );

            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'pengembalian',
                'deskripsi' => "Dikembalikan oleh {$trx->nama_peminjam}",
                'user_id' => auth()->id(),
            ]);
        });

        Cache::forget('available_assets');

        return redirect()->route('transactions.index')->with('success', 'Pengembalian berhasil dicatat.');
    }

    /**
     * Laporan Peminjaman — laporan administratif BACA-SAJA yang dibaca langsung
     * dari tabel `transactions` yang SUDAH ADA. Tidak mengubah status aset,
     * transaksi, maintenance, maupun AssetLog; tidak menulis log apapun.
     *
     * Riwayat transaksi sengaja TIDAK difilter berdasarkan status aset saat ini:
     * aset yang sekarang sudah Dihapuskan (Disposed) tetap muncul transaksi
     * lamanya di laporan ini, karena transaksi tersebut memang pernah terjadi
     * (contoh: Tersedia -> Dipinjam -> Dikembalikan -> Disposed).
     */
    public function report(Request $request): View
    {
        $transactions = $this->transactionReportQuery($request)
            ->with(['asset.category', 'asset.location'])
            ->latest('tanggal_pinjam')
            ->paginate(10)
            ->withQueryString();

        // Ringkasan dihitung dari dataset TERFILTER yang sama dengan tabel, bukan
        // dari keseluruhan tabel — jadi angka ringkasan selalu konsisten dengan
        // baris yang sedang ditampilkan. Semua nilai adalah aggregate nyata dari DB.
        $stats = $this->transactionReportQuery($request)
            ->toBase()
            ->selectRaw('
                COUNT(*) as total_transaksi,
                SUM(CASE WHEN status_peminjaman = ? THEN 1 ELSE 0 END) as sedang_dipinjam,
                SUM(CASE WHEN status_peminjaman = ? THEN 1 ELSE 0 END) as total_pengembalian,
                SUM(CASE WHEN status_peminjaman IN (?, ?) THEN 1 ELSE 0 END) as total_peminjaman
            ', ['Dipinjam', 'Dikembalikan', 'Dipinjam', 'Dikembalikan'])
            ->first()
            // Aggregate tanpa GROUP BY selalu mengembalikan satu baris, tapi fallback
            // ini menjaga view tetap aman kalau query berubah di masa depan.
            ?? (object) ['total_transaksi' => 0, 'sedang_dipinjam' => 0, 'total_pengembalian' => 0, 'total_peminjaman' => 0];

        $categories = Cache::remember('filter_categories', 60, function () {
            return Category::orderBy('nama')->get();
        });
        $locations = Cache::remember('filter_locations', 60, function () {
            return Location::orderBy('nama')->get();
        });

        return view('transactions.report', compact('transactions', 'stats', 'categories', 'locations'));
    }

    /**
     * Query dasar Laporan Peminjaman — dipakai bareng halaman & export-nya supaya
     * filter (pencarian, kategori, lokasi, periode, status) tidak mungkin kelewat
     * di salah satu jalur. Satu-satunya sumber kebenaran dataset laporan.
     */
    private function transactionReportQuery(Request $request)
    {
        return Transaction::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($q) use ($search) {
                    $q->where('nama_peminjam', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($q) use ($search) {
                            $q->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status_peminjaman', $request->input('status')))
            ->when($request->filled('category_id'), fn($q) => $q->whereHas('asset', fn($q) => $q->where('category_id', $request->input('category_id'))))
            ->when($request->filled('location_id'), fn($q) => $q->whereHas('asset', fn($q) => $q->where('location_id', $request->input('location_id'))))
            ->when($request->filled('tanggal_dari'), fn($q) => $q->whereDate('tanggal_pinjam', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn($q) => $q->whereDate('tanggal_pinjam', '<=', $request->input('tanggal_sampai')));
    }

    public function reportExportPdf(Request $request): Response
    {
        $transactions = $this->transactionReportQuery($request)
            ->with(['asset.category', 'asset.location'])
            ->latest('tanggal_pinjam')
            ->get();

        $pdf = Pdf::loadView('pdf.transactions-report', compact('transactions'));

        return $pdf->download('laporan-peminjaman-' . now()->format('Ymd-His') . '.pdf');
    }

    public function reportExportCsv(Request $request): Response
    {
        $transactions = $this->transactionReportQuery($request)
            ->with(['asset.category', 'asset.location'])
            ->latest('tanggal_pinjam')
            ->get();

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Barang', 'Nama Barang', 'Kategori', 'Lokasi', 'Peminjam', 'Keperluan', 'Tanggal Pinjam', 'Tanggal Kembali', 'Status']);
            foreach ($transactions as $trx) {
                fputcsv($out, [
                    $trx->asset?->kode_barang ?? '-',
                    $trx->asset?->nama_barang ?? '-',
                    $trx->asset?->category?->nama ?? '-',
                    $trx->asset?->location?->nama ?? '-',
                    $trx->nama_peminjam,
                    $trx->keperluan ?? '-',
                    $trx->tanggal_pinjam,
                    $trx->tanggal_kembali ?? '-',
                    $trx->status_peminjaman,
                ]);
            }
            fclose($out);
        }, 'laporan-peminjaman-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $transactions = $this->transactionListQuery($request, empty($ids))
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->latest()
            ->get();

        $isSelection = !empty($ids);

        $pdf = Pdf::loadView('pdf.transactions', compact('transactions', 'isSelection'));

        return $pdf->download('laporan-peminjaman.pdf');
    }

    public function exportCsv(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $transactions = $this->transactionListQuery($request, empty($ids))
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Barang', 'Nama Barang', 'Peminjam', 'Keperluan', 'Tgl Pinjam', 'Tgl Kembali', 'Status']);
            foreach ($transactions as $trx) {
                fputcsv($out, [
                    $trx->asset->kode_barang ?? '-',
                    $trx->asset->nama_barang ?? '-',
                    $trx->nama_peminjam,
                    $trx->keperluan,
                    $trx->tanggal_pinjam,
                    $trx->tanggal_kembali,
                    $trx->status_peminjaman,
                ]);
            }
            fclose($out);
        }, 'data-peminjaman-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
