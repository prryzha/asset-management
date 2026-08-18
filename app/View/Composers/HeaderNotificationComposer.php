<?php

namespace App\View\Composers;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HeaderNotificationComposer
{
    public function compose(View $view): void
    {
        if (! auth()->check()) {
            $view->with('headerNotifications', null);
            return;
        }

        $view->with('headerNotifications', $this->notifications());
    }

    /**
     * Hal-hal yang perlu ditindaklanjuti: jadwal perawatan lewat tanggal,
     * dan aset rusak berat yang belum dijadwalkan perbaikan. Berlaku buat
     * semua role yang login — tidak ada lagi tingkat akses yang dibatasi.
     */
    private function notifications(): array
    {
        return Cache::remember('header_notifications', 60, function () {
            $maintenance = MaintenanceSchedule::with('asset')
                ->where('status', 'Dijadwalkan')
                ->whereDate('tanggal_jadwal', '<=', today())
                ->latest('tanggal_jadwal')
                ->take(5)
                ->get();
            $maintenanceCount = MaintenanceSchedule::where('status', 'Dijadwalkan')
                ->whereDate('tanggal_jadwal', '<=', today())
                ->count();

            // Aset yang sudah dihapuskan (Disposed) dikecualikan — notifikasi ini
            // adalah daftar tindak lanjut operasional, sedangkan aset arsip sudah
            // tidak boleh masuk workflow operasional lagi. Tanpa ini, aset yang
            // sudah di-write-off tetap menagih "segera dijadwalkan perbaikan".
            $damaged = Asset::where('kondisi', 'Rusak Berat')
                ->whereNotIn('status', ['Perbaikan', 'Disposed'])
                ->latest()
                ->take(5)
                ->get();
            $damagedCount = Asset::where('kondisi', 'Rusak Berat')
                ->whereNotIn('status', ['Perbaikan', 'Disposed'])
                ->count();

            $sections = [];

            if ($maintenanceCount > 0) {
                $sections[] = [
                    'title' => 'Jadwal Perawatan Lewat Tanggal',
                    'count' => $maintenanceCount,
                    'items' => $maintenance->map(fn($m) => [
                        'label' => ($m->asset?->kode_barang ?? '(Dihapus)') . ' — ' . $m->asset?->nama_barang,
                        'sublabel' => 'Jadwal: ' . \Carbon\Carbon::parse($m->tanggal_jadwal)->format('d M Y'),
                        'url' => route('maintenance.edit', $m),
                    ])->all(),
                    'moreUrl' => null,
                ];
            }

            if ($damagedCount > 0) {
                $sections[] = [
                    'title' => 'Aset Rusak Berat',
                    'count' => $damagedCount,
                    'items' => $damaged->map(fn($a) => [
                        'label' => $a->kode_barang . ' — ' . $a->nama_barang,
                        'sublabel' => null,
                        'url' => route('assets.show', $a),
                    ])->all(),
                    'moreUrl' => null,
                ];
            }

            return [
                'sections' => $sections,
                'total' => $maintenanceCount + $damagedCount,
            ];
        });
    }
}
