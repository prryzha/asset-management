<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AssetLogController extends Controller
{
    public function store(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            // Catatan manual tidak boleh menyamar sebagai event workflow. Mutasi,
            // perawatan, peminjaman, pengembalian, hilang, ditemukan, dan penghapusan
            // semuanya harus dibuat oleh proses bisnis masing-masing.
            'tipe' => ['required', 'in:lainnya'],
            'deskripsi' => ['required', 'string', 'max:500'],
        ]);

        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => $validated['tipe'],
            'deskripsi' => $validated['deskripsi'],
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Log aktivitas berhasil ditambahkan.');
    }

    private function mutasiQuery(Request $request)
    {
        return AssetLog::with(['asset', 'user'])
            ->where('tipe', 'mutasi')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($query) use ($search) {
                    $query->where('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($query) use ($search) {
                            $query->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('tanggal_dari'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('tanggal_dari'));
            })
            ->when($request->filled('tanggal_sampai'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('tanggal_sampai'));
            });
    }

    public function mutasi(Request $request): View
    {
        $mutasiLogs = $this->mutasiQuery($request)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('mutasi.index', compact('mutasiLogs'));
    }

    public function mutasiExportPdf(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $mutasiLogs = $ids
            ? AssetLog::with(['asset', 'user'])->where('tipe', 'mutasi')->whereIn('id', $ids)->latest()->get()
            : $this->mutasiQuery($request)->latest()->get();

        $isSelection = !empty($ids);

        $pdf = Pdf::loadView('pdf.mutasi', compact('mutasiLogs', 'isSelection'));

        return $pdf->download('laporan-mutasi.pdf');
    }

    public function mutasiExportCsv(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $mutasiLogs = $ids
            ? AssetLog::with(['asset', 'user'])->where('tipe', 'mutasi')->whereIn('id', $ids)->latest()->get()
            : $this->mutasiQuery($request)->latest()->get();

        return response()->streamDownload(function () use ($mutasiLogs) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Barang', 'Nama Barang', 'Deskripsi', 'Petugas', 'Tanggal']);
            foreach ($mutasiLogs as $log) {
                fputcsv($out, [
                    $log->asset->kode_barang ?? '-',
                    $log->asset->nama_barang ?? '-',
                    $log->deskripsi,
                    $log->user->name ?? 'Sistem',
                    $log->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, 'data-mutasi-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
