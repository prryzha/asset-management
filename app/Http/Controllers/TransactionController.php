<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
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
        $transactions = Transaction::with(['asset', 'approver'])
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
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status_peminjaman', $request->status);
            })
            ->when($request->filled('tanggal_dari'), function ($q) use ($request) {
                $q->whereDate('tanggal_pinjam', '>=', $request->input('tanggal_dari'));
            })
            ->when($request->filled('tanggal_sampai'), function ($q) use ($request) {
                $q->whereDate('tanggal_pinjam', '<=', $request->input('tanggal_sampai'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
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
                'tipe' => 'mutasi',
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
                'tipe' => 'mutasi',
                'deskripsi' => "Dikembalikan oleh {$trx->nama_peminjam}",
                'user_id' => auth()->id(),
            ]);
        });

        Cache::forget('available_assets');

        return redirect()->route('transactions.index')->with('success', 'Pengembalian berhasil dicatat.');
    }

    public function exportPdf(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $transactions = Transaction::with('asset')
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->when(!$ids && $request->filled('status'), fn($q) => $q->where('status_peminjaman', $request->status))
            ->when(!$ids && $request->filled('tanggal_dari'), fn($q) => $q->whereDate('tanggal_pinjam', '>=', $request->input('tanggal_dari')))
            ->when(!$ids && $request->filled('tanggal_sampai'), fn($q) => $q->whereDate('tanggal_pinjam', '<=', $request->input('tanggal_sampai')))
            ->latest()
            ->get();

        $isSelection = !empty($ids);

        $pdf = Pdf::loadView('pdf.transactions', compact('transactions', 'isSelection'));

        return $pdf->download('laporan-peminjaman.pdf');
    }
}
