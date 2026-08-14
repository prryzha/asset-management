<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\MaintenanceSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $maintenanceSchedules = MaintenanceSchedule::with(['asset', 'creator'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($query) use ($search) {
                    $query->where('jenis_perawatan', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($query) use ($search) {
                            $query->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('tanggal_dari'), function ($query) use ($request) {
                $query->whereDate('tanggal_jadwal', '>=', $request->input('tanggal_dari'));
            })
            ->when($request->filled('tanggal_sampai'), function ($query) use ($request) {
                $query->whereDate('tanggal_jadwal', '<=', $request->input('tanggal_sampai'));
            })
            ->orderBy('tanggal_jadwal')
            ->paginate(10)
            ->withQueryString();

        return view('maintenance.index', compact('maintenanceSchedules'));
    }

    public function create(): View
    {
        $assets = Cache::remember('all_assets_v2', 3600, function () {
            return Asset::orderBy('kode_barang')
                ->get(['id', 'kode_barang', 'nama_barang', 'status', 'kondisi']);
        });

        $damagedAssetIds = $assets->whereIn('kondisi', ['Kurang Baik', 'Rusak Berat'])->pluck('id')->toArray();

        return view('maintenance.create', compact('assets', 'damagedAssetIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id'          => ['required', 'exists:assets,id'],
            'jenis_perawatan'   => ['required', 'string', 'max:255'],
            'tanggal_jadwal'    => ['required', 'date'],
            'catatan'           => ['nullable', 'string'],
        ]);

        $maintenance = MaintenanceSchedule::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        $maintenance->load('asset');

        ActivityLog::record(
            $maintenance,
            'maintenance.created',
            "Menjadwalkan {$maintenance->jenis_perawatan} untuk {$maintenance->asset->kode_barang}"
        );

        AssetLog::create([
            'asset_id' => $maintenance->asset_id,
            'tipe' => 'perawatan',
            'deskripsi' => "Dijadwalkan perawatan: {$maintenance->jenis_perawatan} pada {$maintenance->tanggal_jadwal->format('d/m/Y')}",
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Jadwal perawatan berhasil dibuat.');
    }

    public function edit(MaintenanceSchedule $maintenanceSchedule): View|RedirectResponse
    {
        if ($maintenanceSchedule->status !== 'Dijadwalkan') {
            return redirect()
                ->route('maintenance.index')
                ->with('error', 'Hanya jadwal yang belum dimulai yang dapat diubah.');
        }

        $assets = Cache::remember('all_assets_v2', 3600, function () {
            return Asset::orderBy('kode_barang')
                ->get(['id', 'kode_barang', 'nama_barang', 'status', 'kondisi']);
        });

        $damagedAssetIds = $assets->whereIn('kondisi', ['Kurang Baik', 'Rusak Berat'])->pluck('id')->toArray();

        return view('maintenance.edit', compact('maintenanceSchedule', 'assets', 'damagedAssetIds'));
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        if ($maintenanceSchedule->status !== 'Dijadwalkan') {
            return redirect()
                ->route('maintenance.index')
                ->with('error', 'Hanya jadwal yang belum dimulai yang dapat diubah.');
        }

        $validated = $request->validate([
            'asset_id'          => ['required', 'exists:assets,id'],
            'jenis_perawatan'   => ['required', 'string', 'max:255'],
            'tanggal_jadwal'    => ['required', 'date'],
            'catatan'           => ['nullable', 'string'],
        ]);

        $maintenanceSchedule->update($validated);

        $maintenanceSchedule->load('asset');

        ActivityLog::record(
            $maintenanceSchedule,
            'maintenance.updated',
            "Memperbarui jadwal perawatan {$maintenanceSchedule->asset->kode_barang}",
            $maintenanceSchedule->getChanges()
        );

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Jadwal perawatan berhasil diperbarui.');
    }

    public function start(MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        DB::transaction(function () use ($maintenanceSchedule) {

            $schedule = MaintenanceSchedule::lockForUpdate()->findOrFail($maintenanceSchedule->id);

            if ($schedule->status !== 'Dijadwalkan') {
                throw ValidationException::withMessages([
                    'maintenance' => 'Jadwal ini tidak dapat dimulai.',
                ]);
            }

            $asset = Asset::lockForUpdate()->findOrFail($schedule->asset_id);

            if ($asset->status === 'Dipinjam') {
                throw ValidationException::withMessages([
                    'maintenance' => 'Aset yang sedang dipinjam tidak dapat masuk perbaikan.',
                ]);
            }

            $schedule->update([
                'status' => 'Dikerjakan',
            ]);

            $asset->update([
                'status' => 'Perbaikan',
            ]);

            ActivityLog::record(
                $schedule,
                'maintenance.started',
                "Memulai perawatan {$asset->kode_barang}"
            );

            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'perawatan',
                'deskripsi' => "Perawatan dimulai: {$schedule->jenis_perawatan}",
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Perawatan berhasil dimulai.');
    }

    public function completeForm(MaintenanceSchedule $maintenanceSchedule): View|RedirectResponse
    {
        if ($maintenanceSchedule->status !== 'Dikerjakan') {
            return redirect()
                ->route('maintenance.index')
                ->with('error', 'Hanya perawatan aktif yang dapat diselesaikan.');
        }

        $maintenanceSchedule->load('asset');

        return view('maintenance.complete', compact('maintenanceSchedule'));
    }

    public function complete(Request $request, MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        $validated = $request->validate([
            'kondisi' => ['required', 'in:Baik,Kurang Baik,Rusak Berat'],
            'catatan_selesai' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($maintenanceSchedule, $validated) {

            $schedule = MaintenanceSchedule::lockForUpdate()->findOrFail($maintenanceSchedule->id);

            if ($schedule->status !== 'Dikerjakan') {
                throw ValidationException::withMessages([
                    'maintenance' => 'Maintenance ini sudah tidak aktif.',
                ]);
            }

            $asset = Asset::lockForUpdate()->findOrFail($schedule->asset_id);

            $assetStatus = $validated['kondisi'] === 'Rusak Berat'
                ? 'Perbaikan'
                : 'Tersedia';

            $schedule->update([
                'status' => 'Selesai',
                'tanggal_selesai' => now()->toDateString(),
                'catatan_selesai' => $validated['catatan_selesai'],
            ]);

            $asset->update([
                'kondisi' => $validated['kondisi'],
                'status' => $assetStatus,
            ]);

            ActivityLog::record(
                $schedule,
                'maintenance.completed',
                "Menyelesaikan perawatan {$asset->kode_barang}",
                [
                    'kondisi' => $validated['kondisi'],
                    'status_aset' => $assetStatus,
                ]
            );

            AssetLog::create([
                'asset_id' => $asset->id,
                'tipe' => 'perawatan',
                'deskripsi' => "Perawatan selesai. Kondisi: {$validated['kondisi']}. " . ($validated['catatan_selesai'] ? "Catatan: {$validated['catatan_selesai']}" : ''),
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Perawatan berhasil diselesaikan.');
    }

    public function cancel(MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        if ($maintenanceSchedule->status !== 'Dijadwalkan') {
            return redirect()
                ->route('maintenance.index')
                ->with('error', 'Hanya jadwal yang belum dimulai yang dapat dibatalkan.');
        }

        $maintenanceSchedule->update([
            'status' => 'Dibatalkan',
        ]);

        $maintenanceSchedule->load('asset');

        ActivityLog::record(
            $maintenanceSchedule,
            'maintenance.cancelled',
            "Membatalkan jadwal perawatan {$maintenanceSchedule->asset->kode_barang}"
        );

        AssetLog::create([
            'asset_id' => $maintenanceSchedule->asset_id,
            'tipe' => 'perawatan',
            'deskripsi' => "Perawatan dibatalkan: {$maintenanceSchedule->jenis_perawatan}",
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Jadwal perawatan berhasil dibatalkan.');
    }

    public function exportPdf(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $maintenanceSchedules = MaintenanceSchedule::with('asset')
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->when(!$ids && $request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when(!$ids && $request->filled('tanggal_dari'), fn($q) => $q->whereDate('tanggal_jadwal', '>=', $request->input('tanggal_dari')))
            ->when(!$ids && $request->filled('tanggal_sampai'), fn($q) => $q->whereDate('tanggal_jadwal', '<=', $request->input('tanggal_sampai')))
            ->orderBy('tanggal_jadwal')
            ->get();

        $isSelection = !empty($ids);

        $pdf = Pdf::loadView('pdf.maintenance', compact('maintenanceSchedules', 'isSelection'));

        return $pdf->download('laporan-perawatan.pdf');
    }
}