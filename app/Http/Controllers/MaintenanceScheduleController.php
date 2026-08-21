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
        $maintenanceSchedules = $this->maintenanceListQuery($request)
            ->with('creator')
            ->orderBy('tanggal_jadwal')
            ->paginate(10)
            ->withQueryString();

        return view('maintenance.index', compact('maintenanceSchedules'));
    }

    /**
     * Query dasar Manajemen Perawatan — dipakai bareng index() dan export PDF/CSV
     * supaya dataset tabel == dataset export untuk filter yang sama (termasuk
     * search). $respectFilters dimatikan khusus export "data terpilih" (ids),
     * perilaku export lama yang dipertahankan.
     */
    private function maintenanceListQuery(Request $request, bool $respectFilters = true)
    {
        return MaintenanceSchedule::with('asset')
            ->when($respectFilters && $request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($query) use ($search) {
                    $query->where('jenis_perawatan', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($query) use ($search) {
                            $query->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                        });
                });
            })
            ->when($respectFilters && $request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($respectFilters && $request->filled('tanggal_dari'), fn($q) => $q->whereDate('tanggal_jadwal', '>=', $request->input('tanggal_dari')))
            ->when($respectFilters && $request->filled('tanggal_sampai'), fn($q) => $q->whereDate('tanggal_jadwal', '<=', $request->input('tanggal_sampai')));
    }

    public function create(): View
    {
        // Aset yang sudah dihapuskan (Disposed) tidak boleh muncul di pilihan aset —
        // aset arsip bukan bagian dari operasional. Cache key dinaikkan ke v3 supaya
        // cache lama yang masih berisi aset Disposed tidak ikut kepakai.
        $assets = Cache::remember('all_assets_v3', 3600, function () {
            return Asset::whereIn('status', ['Tersedia', 'Perbaikan'])
                ->orderBy('kode_barang')
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
            'teknisi'           => ['nullable', 'string', 'max:255'],
            'tanggal_jadwal'    => ['required', 'date'],
            'catatan'           => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $asset = Asset::lockForUpdate()->findOrFail($validated['asset_id']);
            $this->ensureAssetCanBeScheduled($asset);

            $maintenance = MaintenanceSchedule::create([
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            ActivityLog::record(
                $maintenance,
                'maintenance.created',
                "Menjadwalkan {$maintenance->jenis_perawatan} untuk {$asset->kode_barang}"
            );

            AssetLog::create([
                'asset_id' => $maintenance->asset_id,
                'tipe' => 'perawatan',
                'deskripsi' => "Dijadwalkan perawatan: {$maintenance->jenis_perawatan} pada {$maintenance->tanggal_jadwal->format('d/m/Y')}",
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('maintenance.index')
            ->with('success', __('ui.messages.maintenance_created'));
    }

    public function edit(MaintenanceSchedule $maintenanceSchedule): View|RedirectResponse
    {
        if ($maintenanceSchedule->status !== 'Dijadwalkan') {
            return redirect()
                ->route('maintenance.index')
                ->with('error', __('ui.messages.maintenance_edit_guard'));
        }

        // Aset yang sudah dihapuskan (Disposed) tidak boleh muncul di pilihan aset —
        // aset arsip bukan bagian dari operasional. Cache key dinaikkan ke v3 supaya
        // cache lama yang masih berisi aset Disposed tidak ikut kepakai.
        $assets = Cache::remember('all_assets_v3', 3600, function () {
            return Asset::whereIn('status', ['Tersedia', 'Perbaikan'])
                ->orderBy('kode_barang')
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
                ->with('error', __('ui.messages.maintenance_edit_guard'));
        }

        $validated = $request->validate([
            'asset_id'          => ['required', 'exists:assets,id'],
            'jenis_perawatan'   => ['required', 'string', 'max:255'],
            'teknisi'           => ['nullable', 'string', 'max:255'],
            'tanggal_jadwal'    => ['required', 'date'],
            'catatan'           => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($maintenanceSchedule, $validated) {
            $schedule = MaintenanceSchedule::lockForUpdate()->findOrFail($maintenanceSchedule->id);

            if ($schedule->status !== 'Dijadwalkan') {
                throw ValidationException::withMessages([
                    'maintenance' => __('ui.messages.maintenance_edit_guard'),
                ]);
            }

            $asset = Asset::lockForUpdate()->findOrFail($validated['asset_id']);
            $this->ensureAssetCanBeScheduled($asset, $schedule->id);

            $schedule->update($validated);

            ActivityLog::record(
                $schedule,
                'maintenance.updated',
                "Memperbarui jadwal perawatan {$asset->kode_barang}",
                $schedule->getChanges()
            );
        });

        return redirect()
            ->route('maintenance.index')
            ->with('success', __('ui.messages.maintenance_updated'));
    }

    public function start(MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        DB::transaction(function () use ($maintenanceSchedule) {

            $schedule = MaintenanceSchedule::lockForUpdate()->findOrFail($maintenanceSchedule->id);

            if ($schedule->status !== 'Dijadwalkan') {
                throw ValidationException::withMessages([
                    'maintenance' => __('ui.messages.maintenance_cannot_start'),
                ]);
            }

            $asset = Asset::lockForUpdate()->findOrFail($schedule->asset_id);

            // Hanya aset Tersedia/Perbaikan yang boleh masuk maintenance normal.
            // Dipinjam dimiliki TransactionController; Hilang/Disposed tidak boleh
            // "masuk maintenance normal" sama sekali (lihat AssetController::reportLost()).
            if (! in_array($asset->status, ['Tersedia', 'Perbaikan'])) {
                throw ValidationException::withMessages([
                    'maintenance' => __('ui.messages.maintenance_asset_status_guard', ['status' => $this->statusLabel($asset->status)]),
                ]);
            }

            if ($asset->maintenanceSchedules()
                ->where('status', 'Dikerjakan')
                ->where('id', '!=', $schedule->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'maintenance' => __('ui.messages.maintenance_already_active'),
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

        // start() mengubah asset.status (Tersedia/Perbaikan -> Perbaikan) —
        // invalidate cache yang bergantung pada status aset (dashboard, dropdown
        // "Catat Peminjaman", dsb). Lihat Asset::forgetStatusCaches().
        Asset::forgetStatusCaches();

        return redirect()
            ->route('maintenance.index')
            ->with('success', __('ui.messages.maintenance_started'));
    }

    public function completeForm(MaintenanceSchedule $maintenanceSchedule): View|RedirectResponse
    {
        if ($maintenanceSchedule->status !== 'Dikerjakan') {
            return redirect()
                ->route('maintenance.index')
                ->with('error', __('ui.messages.maintenance_complete_guard'));
        }

        $maintenanceSchedule->load('asset');

        return view('maintenance.complete', compact('maintenanceSchedule'));
    }

    public function complete(Request $request, MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        $validated = $request->validate([
            'kondisi' => ['required', 'in:Baik,Kurang Baik,Rusak Berat'],
            'catatan_selesai' => ['nullable', 'string'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($maintenanceSchedule, $validated) {

            $schedule = MaintenanceSchedule::lockForUpdate()->findOrFail($maintenanceSchedule->id);

            if ($schedule->status !== 'Dikerjakan') {
                throw ValidationException::withMessages([
                    'maintenance' => __('ui.messages.maintenance_not_active'),
                ]);
            }

            $asset = Asset::lockForUpdate()->findOrFail($schedule->asset_id);

            // Jalur normal tidak dapat mencapai kondisi ini karena penghapusan
            // diblokir saat ada perawatan aktif. Guard tetap mencegah data lama
            // yang tidak konsisten mengubah aset Dihapuskan kembali aktif.
            if ($asset->status === 'Disposed') {
                throw ValidationException::withMessages([
                    'maintenance' => __('ui.messages.maintenance_disposed_guard'),
                ]);
            }

            $assetStatus = $validated['kondisi'] === 'Rusak Berat'
                ? 'Perbaikan'
                : 'Tersedia';

            $schedule->update([
                'status' => 'Selesai',
                'tanggal_selesai' => now()->toDateString(),
                'catatan_selesai' => $validated['catatan_selesai'] ?? null,
                'biaya' => $validated['biaya'] ?? null,
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
                'deskripsi' => "Perawatan selesai. Kondisi: {$validated['kondisi']}. " . (($validated['catatan_selesai'] ?? null) ? "Catatan: {$validated['catatan_selesai']}" : ''),
                'user_id' => auth()->id(),
            ]);
        });

        // complete() mengubah asset.status (Perbaikan -> Tersedia/Perbaikan) dan
        // kondisi — invalidate cache yang bergantung pada status aset. Lihat
        // Asset::forgetStatusCaches().
        Asset::forgetStatusCaches();

        return redirect()
            ->route('maintenance.index')
            ->with('success', __('ui.messages.maintenance_completed'));
    }

    public function cancel(MaintenanceSchedule $maintenanceSchedule): RedirectResponse
    {
        if ($maintenanceSchedule->status !== 'Dijadwalkan') {
            return redirect()
                ->route('maintenance.index')
                ->with('error', __('ui.messages.maintenance_cancel_guard'));
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
            ->with('success', __('ui.messages.maintenance_cancelled'));
    }

    /**
     * Label Bahasa Indonesia untuk status internal — sama dengan pola
     * AssetController::statusLabel(). Internal value tetap "Disposed" (enum DB),
     * cuma tampilan ke user yang diterjemahkan jadi "Dihapuskan".
     */
    private function statusLabel(string $status): string
    {
        return __('ui.status.' . $status);
    }

    /**
     * Penjadwalan hanya boleh dibuat untuk aset yang masih bisa ditangani oleh
     * workflow perawatan. Guard ini dipakai oleh store() dan update(), bukan
     * hanya start(), supaya raw request tidak meninggalkan jadwal tidak valid.
     */
    private function ensureAssetCanBeScheduled(Asset $asset, ?int $excludingScheduleId = null): void
    {
        if (! in_array($asset->status, ['Tersedia', 'Perbaikan'])) {
            throw ValidationException::withMessages([
                'asset_id' => __('ui.messages.maintenance_schedule_status_guard', ['status' => $this->statusLabel($asset->status)]),
            ]);
        }

        $activeSchedule = $asset->maintenanceSchedules()
            ->where('status', 'Dikerjakan')
            ->when($excludingScheduleId, fn($query) => $query->where('id', '!=', $excludingScheduleId))
            ->exists();

        if ($activeSchedule) {
            throw ValidationException::withMessages([
                'asset_id' => __('ui.messages.maintenance_already_active'),
            ]);
        }
    }

    public function exportPdf(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $maintenanceSchedules = $this->maintenanceListQuery($request, empty($ids))
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->orderBy('tanggal_jadwal')
            ->get();

        $isSelection = !empty($ids);

        $pdf = Pdf::loadView('pdf.maintenance', compact('maintenanceSchedules', 'isSelection'));

        return $pdf->download('laporan-perawatan.pdf');
    }

    public function exportCsv(Request $request): Response
    {
        $ids = $request->input('ids', []);

        $maintenanceSchedules = $this->maintenanceListQuery($request, empty($ids))
            ->when($ids, fn($q) => $q->whereIn('id', $ids))
            ->orderBy('tanggal_jadwal')
            ->get();

        return response()->streamDownload(function () use ($maintenanceSchedules) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Barang', 'Nama Barang', 'Jenis Perawatan', 'Teknisi/Vendor', 'Tgl Jadwal', 'Tgl Selesai', 'Status', 'Biaya', 'Catatan']);
            foreach ($maintenanceSchedules as $item) {
                fputcsv($out, [
                    $item->asset->kode_barang ?? '-',
                    $item->asset->nama_barang ?? '-',
                    $item->jenis_perawatan,
                    $item->teknisi,
                    $item->tanggal_jadwal->format('Y-m-d'),
                    $item->tanggal_selesai?->format('Y-m-d'),
                    $item->status,
                    $item->biaya,
                    $item->catatan_selesai ?? $item->catatan,
                ]);
            }
            fclose($out);
        }, 'data-perawatan-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
