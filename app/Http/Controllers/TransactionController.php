<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\BorrowingApprovalRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::with(['asset', 'approver'])
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status_peminjaman', $request->status);
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

        $isStaff = !auth()->user()->isAdmin();

        DB::transaction(function () use ($validated, $isStaff) {
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

            $status = $isStaff ? 'Menunggu Persetujuan' : 'Dipinjam';

            $transaction = Transaction::create([
                ...$validated,
                'status_peminjaman' => $status,
            ]);

            if ($status === 'Dipinjam') {
                $asset->update(['status' => 'Dipinjam']);
            }

            ActivityLog::record(
                $transaction,
                $isStaff ? 'transaction.pending' : 'transaction.borrowed',
                $isStaff
                    ? "Mengajukan peminjaman {$asset->kode_barang} oleh {$transaction->nama_peminjam} (menunggu persetujuan)"
                    : "Mencatat peminjaman {$asset->kode_barang} oleh {$transaction->nama_peminjam}",
                ['asset_id' => $asset->id, 'nama_peminjam' => $transaction->nama_peminjam],
            );

            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'mutasi',
                'deskripsi' => $isStaff
                    ? "Diajukan peminjaman oleh {$transaction->nama_peminjam} (menunggu persetujuan)"
                    : "Dipinjam oleh {$transaction->nama_peminjam} untuk {$transaction->keperluan}",
                'user_id' => auth()->id(),
            ]);

            if ($isStaff) {
                $admins = User::whereIn('role', ['super_admin', 'admin'])->get();
                Notification::send($admins, new BorrowingApprovalRequest($transaction));
            }
        });

        Cache::forget('available_assets');

        if (!$isStaff) {
            return redirect()->route('transactions.index')->with('success', 'Peminjaman berhasil dicatat.');
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Permintaan peminjaman telah diajukan dan menunggu persetujuan.');
    }

    public function approve(Transaction $transaction): RedirectResponse
    {
        $this->authorize('approve', $transaction);

        DB::transaction(function () use ($transaction) {
            $trx = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if ($trx->status_peminjaman !== 'Menunggu Persetujuan') {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi ini tidak dalam status menunggu persetujuan.',
                ]);
            }

            $asset = Asset::lockForUpdate()->findOrFail($trx->asset_id);

            if ($asset->status !== 'Tersedia') {
                throw ValidationException::withMessages([
                    'transaction' => 'Aset ini sudah tidak tersedia.',
                ]);
            }

            $trx->update([
                'status_peminjaman' => 'Dipinjam',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $asset->update(['status' => 'Dipinjam']);

            ActivityLog::record(
                $trx,
                'transaction.approved',
                "Menyetujui peminjaman {$asset->kode_barang} oleh {$trx->nama_peminjam}",
                ['approved_by' => auth()->user()->name]
            );

            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'mutasi',
                'deskripsi' => "Peminjaman disetujui oleh " . auth()->user()->name,
                'user_id' => auth()->id(),
            ]);
        });

        Cache::forget('available_assets');

        return redirect()->route('transactions.index')
            ->with('success', 'Peminjaman berhasil disetujui.');
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('approve', $transaction);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($transaction, $validated) {
            $trx = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if ($trx->status_peminjaman !== 'Menunggu Persetujuan') {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi ini tidak dalam status menunggu persetujuan.',
                ]);
            }

            $trx->update([
                'status_peminjaman' => 'Ditolak',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            ActivityLog::record(
                $trx,
                'transaction.rejected',
                "Menolak peminjaman {$trx->asset->kode_barang} oleh {$trx->nama_peminjam}: {$validated['rejection_reason']}",
                ['rejected_by' => auth()->user()->name, 'reason' => $validated['rejection_reason']]
            );
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Peminjaman ditolak.');
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
        $transactions = Transaction::with('asset')
            ->when($request->filled('status'), fn($q, $v) => $q->where('status_peminjaman', $v))
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.transactions', compact('transactions'));

        return $pdf->download('laporan-peminjaman.pdf');
    }
}
