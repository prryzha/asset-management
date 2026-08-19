<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    private ?int $staffId = null;
    private ?int $adminId = null;

    /**
     * Bersihkan semua data demo lama lalu isi ulang (kategori, lokasi, aset,
     * peminjaman, perawatan) — SETIAP pembuatan record juga dicatat ke
     * Log Aktivitas persis seperti kalau dilakukan manual lewat aplikasi,
     * jadi halaman Aktivitas ikut keisi riwayat yang masuk akal.
     */
    public function run(): void
    {
        $this->clearDummyData();

        $staff = User::where('role', 'staff')->first() ?? User::first();
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $this->staffId = $staff?->id;
        $this->adminId = $admin?->id;

        $this->seedCategoriesAndLocations();
        $this->seedAssetsAndTransactions();
    }

    private function clearDummyData(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['activity_logs', 'asset_logs', 'transactions', 'maintenance_schedules', 'assets', 'categories', 'locations'] as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();
    }

    private function logActivity(?object $subject, string $event, string $description, array $properties, ?int $userId, int $daysAgo, int $hour = 9): void
    {
        // forceCreate: created_at/updated_at sengaja di-backdate supaya riwayat
        // aktivitas terlihat tersebar di beberapa hari terakhir, bukan semuanya
        // "baru saja" — kolom timestamp tidak ada di $fillable model, jadi create
        // biasa akan mengabaikannya diam-diam.
        ActivityLog::forceCreate([
            'user_id' => $userId,
            'event' => $event,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
            'created_at' => now()->subDays($daysAgo)->setTime($hour, rand(0, 59)),
            'updated_at' => now()->subDays($daysAgo)->setTime($hour, rand(0, 59)),
        ]);
    }

    /** Log per-aset ("Log Aktivitas Aset" di halaman detail) — tabel asset_logs, terpisah dari activity_logs. */
    private function logAsset(int $assetId, string $tipe, string $deskripsi, ?int $userId, int $daysAgo, int $hour = 9): void
    {
        // forceCreate: backdate created_at (tidak ada di $fillable AssetLog).
        AssetLog::forceCreate([
            'asset_id' => $assetId,
            'tipe' => $tipe,
            'deskripsi' => $deskripsi,
            'user_id' => $userId,
            'created_at' => now()->subDays($daysAgo)->setTime($hour, rand(0, 59)),
            'updated_at' => now()->subDays($daysAgo)->setTime($hour, rand(0, 59)),
        ]);
    }

    private function seedCategoriesAndLocations(): void
    {
        $categories = [
            'Elektronik' => 'ELK',
            'Mebel' => 'MBL',
            'Laboratorium' => 'LAB',
            'Buku' => 'BKU',
            'Olahraga' => 'OR',
        ];
        foreach ($categories as $nama => $kode) {
            $category = Category::create(['nama' => $nama, 'kode' => $kode]);
            $this->logActivity($category, 'category.created', "Menambahkan kategori {$nama}", [], $this->staffId, 20);
        }

        $locations = [
            'Lab Komputer' => 'LK',
            'Ruang Guru' => 'RG',
            'Ruang Kelas' => 'RK',
            'Perpustakaan' => 'PUS',
            'Lab IPA' => 'LI',
            'Gudang Olahraga' => 'GOR',
        ];
        foreach ($locations as $nama => $kode) {
            $location = Location::create(['nama' => $nama, 'kode' => $kode]);
            $this->logActivity($location, 'location.created', "Menambahkan lokasi {$nama}", [], $this->staffId, 20);
        }
    }

    private function seedAssetsAndTransactions(): void
    {
        // [kategori, nama, merk, lokasi, status, kondisi, tahun, nilai, PIC, hari-lalu-dibuat]
        $items = [
            // Elektronik
            ['Elektronik', 'Laptop', 'Lenovo ThinkPad E14', 'Lab Komputer', 'Tersedia', 'Baik', 2023, 8500000, 'Budi Santoso', 18],
            ['Elektronik', 'Laptop', 'Asus VivoBook 14', 'Lab Komputer', 'Dipinjam', 'Baik', 2023, 7200000, 'Budi Santoso', 18],
            ['Elektronik', 'Komputer PC', 'Lenovo ThinkCentre', 'Lab Komputer', 'Tersedia', 'Baik', 2022, 6000000, null, 17],
            ['Elektronik', 'Proyektor', 'Epson EB-X500', 'Ruang Kelas', 'Tersedia', 'Baik', 2023, 4500000, null, 17],
            ['Elektronik', 'Proyektor', 'BenQ MS550', 'Ruang Guru', 'Dipinjam', 'Baik', 2022, 4200000, 'Siti Aminah', 16],
            ['Elektronik', 'Printer', 'Canon Pixma G2010', 'Ruang Guru', 'Tersedia', 'Baik', 2023, 2100000, 'Siti Aminah', 16],
            ['Elektronik', 'AC Split', 'Daikin 1PK', 'Ruang Guru', 'Perbaikan', 'Rusak Berat', 2021, 3800000, null, 15],

            // Mebel
            // Kondisi Rusak Berat tapi status TETAP Tersedia (belum dijadwalkan
            // perbaikan) — sengaja, supaya notifikasi header "Aset Rusak Berat"
            // (AssetController::dashboard()/HeaderNotificationComposer, yang
            // mengecualikan status Perbaikan/Disposed) benar-benar punya data,
            // bukan selalu kosong seperti dataset lama.
            ['Mebel', 'Meja Kerja', 'Olympic', 'Ruang Guru', 'Tersedia', 'Rusak Berat', 2022, 1200000, null, 15],
            ['Mebel', 'Kursi Kantor', 'Chairman', 'Ruang Guru', 'Tersedia', 'Baik', 2022, 850000, null, 14],
            ['Mebel', 'Lemari Arsip', 'Lion', 'Ruang Guru', 'Tersedia', 'Kurang Baik', 2020, 1500000, null, 14],
            ['Mebel', 'Whiteboard', 'Sakana 120x240', 'Ruang Kelas', 'Tersedia', 'Baik', 2023, 650000, null, 13],
            ['Mebel', 'Meja Kerja', 'Informa', 'Ruang Kelas', 'Perbaikan', 'Rusak Berat', 2019, 1100000, null, 13],

            // Laboratorium
            ['Laboratorium', 'Mikroskop', 'Olympus CX23', 'Lab IPA', 'Tersedia', 'Baik', 2022, 5500000, 'Dewi Lestari', 12],
            ['Laboratorium', 'Mikroskop', 'Zeiss Primo Star', 'Lab IPA', 'Tersedia', 'Baik', 2022, 6200000, 'Dewi Lestari', 12],
            ['Laboratorium', 'Proyektor', 'ViewSonic PA503S', 'Lab IPA', 'Tersedia', 'Baik', 2023, 3900000, null, 11],

            // Buku
            ['Buku', 'Ensiklopedia Sains', null, 'Perpustakaan', 'Tersedia', 'Baik', 2021, 250000, null, 11],
            ['Buku', 'Novel Laskar Pelangi', null, 'Perpustakaan', 'Dipinjam', 'Baik', 2020, 85000, null, 10],
            ['Buku', 'Kamus Bahasa Inggris', null, 'Perpustakaan', 'Tersedia', 'Baik', 2021, 150000, null, 10],
            ['Buku', 'Atlas Dunia', null, 'Perpustakaan', 'Tersedia', 'Baik', 2020, 200000, null, 9],

            // Olahraga
            ['Olahraga', 'Bola Basket', 'Molten', 'Gudang Olahraga', 'Tersedia', 'Baik', 2023, 350000, 'Agus Wijaya', 9],
            ['Olahraga', 'Matras Senam', 'Body Gym', 'Gudang Olahraga', 'Tersedia', 'Baik', 2022, 500000, 'Agus Wijaya', 8],
            ['Olahraga', 'Net Voli', 'Mikasa', 'Gudang Olahraga', 'Dipinjam', 'Baik', 2022, 400000, 'Agus Wijaya', 8],
        ];

        $peminjam = ['Ahmad Fauzi (XI RPL)', 'Rina Marlina (Guru)', 'Kelas X IPA 1', 'Dimas Prasetyo (XII TKJ)'];
        $peminjamIndex = 0;
        $assetsByCode = [];

        foreach ($items as [$categoryNama, $namaBarang, $merk, $locationNama, $status, $kondisi, $tahun, $nilai, $pic, $daysAgo]) {
            $category = Category::where('nama', $categoryNama)->first();
            $location = Location::where('nama', $locationNama)->first();

            $asset = Asset::forceCreate([
                'kode_barang' => Asset::findNextKodeBarang($category->kode),
                'nama_barang' => $namaBarang,
                'merk' => $merk,
                'category_id' => $category->id,
                'location_id' => $location->id,
                'penanggung_jawab' => $pic,
                'kondisi' => $kondisi,
                'status' => $status,
                'tahun_perolehan' => $tahun,
                'nilai_perolehan' => $nilai,
                'created_at' => now()->subDays($daysAgo)->setTime(8, rand(0, 59)),
            ]);
            $assetsByCode[$asset->kode_barang] = $asset;

            $this->logActivity($asset, 'asset.created', "Menambahkan aset {$asset->kode_barang}", [], $this->staffId, $daysAgo);
            $this->logAsset($asset->id, 'lainnya', "Aset baru: {$asset->nama_barang} ({$asset->kode_barang})", $this->staffId, $daysAgo);

            if ($status === 'Dipinjam') {
                $pinjamDaysAgo = max(1, $daysAgo - rand(2, 6));
                $nama = $peminjam[$peminjamIndex++ % count($peminjam)];

                $transaction = Transaction::forceCreate([
                    'asset_id' => $asset->id,
                    'created_by' => $this->staffId,
                    'nama_peminjam' => $nama,
                    'keperluan' => 'Kegiatan belajar mengajar',
                    'tanggal_pinjam' => now()->subDays($pinjamDaysAgo)->toDateString(),
                    'status_peminjaman' => 'Dipinjam',
                    'created_at' => now()->subDays($pinjamDaysAgo)->setTime(10, rand(0, 59)),
                ]);

                $this->logActivity(
                    $transaction,
                    'transaction.borrowed',
                    "Mencatat peminjaman {$asset->kode_barang} oleh {$nama}",
                    ['asset_id' => $asset->id, 'nama_peminjam' => $nama],
                    $this->staffId,
                    $pinjamDaysAgo
                );
                $this->logAsset($asset->id, 'peminjaman', "Dipinjam oleh {$nama} untuk Kegiatan belajar mengajar", $this->staffId, $pinjamDaysAgo);
            }

            if ($status === 'Perbaikan') {
                $jadwalDaysAgo = max(1, $daysAgo - rand(2, 5));

                $schedule = MaintenanceSchedule::forceCreate([
                    'asset_id' => $asset->id,
                    'created_by' => $this->staffId,
                    'jenis_perawatan' => 'Servis berat',
                    'tanggal_jadwal' => now()->subDays($jadwalDaysAgo)->toDateString(),
                    'catatan' => 'Kondisi ' . strtolower($kondisi) . ', perlu perbaikan.',
                    'status' => 'Dikerjakan',
                    'created_at' => now()->subDays($jadwalDaysAgo)->setTime(9, rand(0, 59)),
                ]);

                $this->logActivity(
                    $schedule,
                    'maintenance.created',
                    "Menjadwalkan {$schedule->jenis_perawatan} untuk {$asset->kode_barang}",
                    [],
                    $this->staffId,
                    $jadwalDaysAgo
                );
                $this->logAsset(
                    $asset->id,
                    'perawatan',
                    "Dijadwalkan perawatan: {$schedule->jenis_perawatan} pada " . $schedule->tanggal_jadwal->format('d/m/Y'),
                    $this->staffId,
                    $jadwalDaysAgo
                );

                $startedDaysAgo = max(0, $jadwalDaysAgo - 1);
                $this->logActivity(
                    $schedule,
                    'maintenance.started',
                    "Memulai perawatan {$asset->kode_barang}",
                    [],
                    $this->staffId,
                    $startedDaysAgo
                );
                $this->logAsset($asset->id, 'perawatan', "Perawatan dimulai: {$schedule->jenis_perawatan}", $this->staffId, $startedDaysAgo);
            }
        }

        // Variasi tambahan biar riwayat Aktivitas nggak cuma "Dipinjam"/"Dikerjakan" —
        // satu peminjaman yang sudah selesai dikembalikan, satu perawatan yang sudah selesai,
        // dan dua contoh perpindahan lokasi (mutasi) supaya halaman Riwayat Mutasi tidak kosong.
        $this->addReturnedTransaction($assetsByCode['ELK-001'] ?? null);
        $this->addCompletedMaintenance($assetsByCode['MBL-004'] ?? null);
        $this->addAssetMutation($assetsByCode['ELK-004'] ?? null, 'Lab Komputer', 5);
        $this->addAssetMutation($assetsByCode['MBL-002'] ?? null, 'Ruang Kelas', 3);

        // Status yang sebelumnya TIDAK ADA sama sekali di data demo — Hilang &
        // Disposed — supaya halaman Aset Hilang dan Arsip Aset tidak kosong.
        $this->addLostAsset();
        $this->addDisposedAsset();

        // Jadwal perawatan yang belum dimulai (Dijadwalkan) — sebelumnya cuma ada
        // Dikerjakan/Selesai. Sekaligus mengisi notifikasi header "Jadwal
        // Perawatan Lewat Tanggal" yang sebelumnya selalu kosong.
        $this->addScheduledMaintenance($assetsByCode['LAB-002'] ?? null);

        // Trail "dilaporkan rusak" untuk aset yang sudah Kurang Baik sejak awal —
        // menambah variasi event Aktivitas (asset.reported-damage) yang
        // sebelumnya tidak pernah muncul di data demo sama sekali.
        $this->addDamageReportTrail(
            $assetsByCode['MBL-003'] ?? null,
            'Baik',
            'Kurang Baik',
            'Sudut lemari penyok dan pintu agak sulit ditutup.',
            10
        );

        // Riwayat peminjaman historis (sudah Dikembalikan) di beberapa bulan
        // sebelumnya — supaya breakdown "per bulan" di Rekap Peminjaman benar-benar
        // menunjukkan lebih dari satu bulan, dan "Peminjam Terbanyak" punya
        // pemenang yang jelas (bukan seri 1-1-1-1-1 seperti dataset lama).
        $this->addHistoricalTransaction($assetsByCode['BKU-003'] ?? null, 'Ahmad Fauzi (XI RPL)', 'Praktikum kelas', 51, 47);
        $this->addHistoricalTransaction($assetsByCode['OR-001'] ?? null, 'Ahmad Fauzi (XI RPL)', 'Pertandingan olahraga sekolah', 23, 21);
        $this->addHistoricalTransaction($assetsByCode['BKU-004'] ?? null, 'Rina Marlina (Guru)', 'Rapat guru', 54, 49);
        $this->addHistoricalTransaction($assetsByCode['LAB-003'] ?? null, 'Kelas X IPA 1', 'Presentasi tugas kelompok', 25, 24);
        $this->addHistoricalTransaction($assetsByCode['OR-002'] ?? null, 'Bayu Setiawan (Guru)', 'Kegiatan pramuka', 19, 18);
    }

    /**
     * Tambahkan riwayat mutasi (perpindahan lokasi) untuk demo. Sekaligus
     * update location_id aset itu sendiri supaya lokasi yang tertampil
     * konsisten dengan riwayat mutasinya — persis seperti kalau dipindah
     * manual lewat form Edit Aset (lihat AssetController::update()).
     */
    private function addAssetMutation(?Asset $asset, string $toLocationNama, int $daysAgo): void
    {
        if (! $asset) {
            return;
        }

        $oldLocationNama = $asset->location?->nama ?? 'unknown';
        $newLocation = Location::where('nama', $toLocationNama)->first();
        if (! $newLocation || $newLocation->nama === $oldLocationNama) {
            return;
        }

        $asset->update(['location_id' => $newLocation->id]);

        $this->logAsset($asset->id, 'mutasi', "Perpindahan lokasi dari {$oldLocationNama} ke {$newLocation->nama}", $this->staffId, $daysAgo);
        $this->logActivity($asset, 'asset.updated', "Mengubah aset {$asset->kode_barang}", [], $this->staffId, $daysAgo);
    }

    private function addReturnedTransaction(?Asset $asset): void
    {
        if (! $asset) {
            return;
        }

        $borrowDaysAgo = 7;
        $returnDaysAgo = 4;

        $transaction = Transaction::forceCreate([
            'asset_id' => $asset->id,
            'created_by' => $this->staffId,
            'nama_peminjam' => 'Wulan Sari (Guru)',
            'keperluan' => 'Presentasi rapat wali murid',
            'tanggal_pinjam' => now()->subDays($borrowDaysAgo)->toDateString(),
            'tanggal_kembali' => now()->subDays($returnDaysAgo)->toDateString(),
            'status_peminjaman' => 'Dikembalikan',
            'created_at' => now()->subDays($borrowDaysAgo)->setTime(10, 0),
        ]);

        $this->logActivity(
            $transaction,
            'transaction.borrowed',
            "Mencatat peminjaman {$asset->kode_barang} oleh Wulan Sari (Guru)",
            ['asset_id' => $asset->id, 'nama_peminjam' => 'Wulan Sari (Guru)'],
            $this->staffId,
            $borrowDaysAgo
        );
        $this->logAsset($asset->id, 'peminjaman', "Dipinjam oleh Wulan Sari (Guru) untuk Presentasi rapat wali murid", $this->staffId, $borrowDaysAgo);

        $this->logActivity(
            $transaction,
            'transaction.returned',
            "Mencatat pengembalian {$asset->kode_barang}",
            ['asset_id' => $asset->id, 'status_aset' => 'Tersedia'],
            $this->staffId,
            $returnDaysAgo
        );
        $this->logAsset($asset->id, 'pengembalian', "Dikembalikan oleh Wulan Sari (Guru)", $this->staffId, $returnDaysAgo);
    }

    private function addCompletedMaintenance(?Asset $asset): void
    {
        if (! $asset) {
            return;
        }

        $jadwalDaysAgo = 6;
        $selesaiDaysAgo = 5;

        $schedule = MaintenanceSchedule::forceCreate([
            'asset_id' => $asset->id,
            'created_by' => $this->staffId,
            'jenis_perawatan' => 'Bersih berkala',
            'tanggal_jadwal' => now()->subDays($jadwalDaysAgo)->toDateString(),
            'tanggal_selesai' => now()->subDays($selesaiDaysAgo)->toDateString(),
            'catatan' => 'Perawatan rutin.',
            'catatan_selesai' => 'Sudah dibersihkan dan dicek, kondisi baik.',
            'status' => 'Selesai',
            'created_at' => now()->subDays($jadwalDaysAgo)->setTime(9, 0),
        ]);

        $this->logActivity(
            $schedule,
            'maintenance.created',
            "Menjadwalkan {$schedule->jenis_perawatan} untuk {$asset->kode_barang}",
            [],
            $this->staffId,
            $jadwalDaysAgo
        );
        $this->logAsset(
            $asset->id,
            'perawatan',
            "Dijadwalkan perawatan: {$schedule->jenis_perawatan} pada " . $schedule->tanggal_jadwal->format('d/m/Y'),
            $this->staffId,
            $jadwalDaysAgo
        );

        $this->logActivity(
            $schedule,
            'maintenance.completed',
            "Menyelesaikan perawatan {$asset->kode_barang}",
            ['kondisi' => 'Baik'],
            $this->staffId,
            $selesaiDaysAgo
        );
        $this->logAsset(
            $asset->id,
            'perawatan',
            "Perawatan selesai. Kondisi: Baik. Catatan: Sudah dibersihkan dan dicek, kondisi baik.",
            $this->staffId,
            $selesaiDaysAgo
        );
    }

    /**
     * Aset berstatus Hilang — mengisi halaman "Aset Hilang" yang sebelumnya
     * kosong. Trail dibuat meniru persis format AssetController::reportLost()
     * (deskripsi AssetLog & event ActivityLog).
     */
    private function addLostAsset(): void
    {
        $category = Category::where('nama', 'Elektronik')->first();
        $location = Location::where('nama', 'Lab Komputer')->first();

        $createdDaysAgo = 30;
        $asset = Asset::forceCreate([
            'kode_barang' => Asset::findNextKodeBarang($category->kode),
            'nama_barang' => 'Laptop',
            'merk' => 'Acer Aspire 3',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'penanggung_jawab' => 'Budi Santoso',
            'kondisi' => 'Baik',
            'status' => 'Hilang',
            'tahun_perolehan' => 2022,
            'nilai_perolehan' => 6800000,
            'created_at' => now()->subDays($createdDaysAgo)->setTime(8, 0),
        ]);

        $this->logActivity($asset, 'asset.created', "Menambahkan aset {$asset->kode_barang}", [], $this->staffId, $createdDaysAgo);
        $this->logAsset($asset->id, 'lainnya', "Aset baru: {$asset->nama_barang} ({$asset->kode_barang})", $this->staffId, $createdDaysAgo);

        $lostDaysAgo = 6;
        $tanggal = now()->subDays($lostDaysAgo)->format('d/m/Y');

        $this->logActivity($asset, 'asset.reported-lost', "Melaporkan aset {$asset->kode_barang} hilang", [], $this->staffId, $lostDaysAgo);
        $this->logAsset(
            $asset->id,
            'hilang',
            "Dilaporkan hilang pada {$tanggal}. Lokasi terakhir: {$location->nama}. Kronologi: Tidak ditemukan saat inventarisasi rutin, kemungkinan tertinggal di ruang lain.",
            $this->staffId,
            $lostDaysAgo
        );
    }

    /**
     * Aset berstatus Disposed — mengisi halaman "Arsip Aset" yang sebelumnya
     * kosong. Diproses oleh ADMIN (bukan staff) karena processDisposal() di
     * aplikasi memang dikunci middleware role:admin — trail-nya ikut
     * mencerminkan itu, bukan asal pakai staffId seperti aksi lain.
     */
    private function addDisposedAsset(): void
    {
        $category = Category::where('nama', 'Elektronik')->first();
        $location = Location::where('nama', 'Ruang Guru')->first();

        $createdDaysAgo = 200;
        $asset = Asset::forceCreate([
            'kode_barang' => Asset::findNextKodeBarang($category->kode),
            'nama_barang' => 'Printer',
            'merk' => 'Epson L120',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'penanggung_jawab' => null,
            'kondisi' => 'Rusak Berat',
            'status' => 'Disposed',
            'tahun_perolehan' => 2017,
            'nilai_perolehan' => 1800000,
            'created_at' => now()->subDays($createdDaysAgo)->setTime(8, 0),
        ]);

        $this->logActivity($asset, 'asset.created', "Menambahkan aset {$asset->kode_barang}", [], $this->staffId, $createdDaysAgo);
        $this->logAsset($asset->id, 'lainnya', "Aset baru: {$asset->nama_barang} ({$asset->kode_barang})", $this->staffId, $createdDaysAgo);

        $disposalDaysAgo = 10;
        $tanggal = now()->subDays($disposalDaysAgo)->format('d/m/Y');
        $adminName = User::find($this->adminId)?->name ?? 'petugas';

        $this->logActivity($asset, 'asset.disposed', "Menghapuskan aset {$asset->kode_barang}", [], $this->adminId, $disposalDaysAgo);
        $this->logAsset(
            $asset->id,
            'penghapusan',
            "Aset dihapuskan pada {$tanggal} karena Rusak Berat. Diproses oleh {$adminName}. Keterangan: Printer sudah tidak bisa dipakai, komponen rusak dan biaya servis tidak ekonomis.",
            $this->adminId,
            $disposalDaysAgo
        );
    }

    /**
     * Jadwal perawatan berstatus Dijadwalkan (belum dimulai) — sebelumnya
     * dataset demo cuma punya Dikerjakan/Selesai. tanggal_jadwal sengaja di
     * masa lalu (lewat tanggal) supaya notifikasi header "Jadwal Perawatan
     * Lewat Tanggal" (HeaderNotificationComposer) juga punya data.
     */
    private function addScheduledMaintenance(?Asset $asset): void
    {
        if (! $asset) {
            return;
        }

        $createdDaysAgo = 5;
        $jadwalDaysAgo = 2;

        $schedule = MaintenanceSchedule::forceCreate([
            'asset_id' => $asset->id,
            'created_by' => $this->staffId,
            'jenis_perawatan' => 'Kalibrasi',
            'tanggal_jadwal' => now()->subDays($jadwalDaysAgo)->toDateString(),
            'catatan' => 'Kalibrasi rutin tahunan.',
            'status' => 'Dijadwalkan',
            'created_at' => now()->subDays($createdDaysAgo)->setTime(9, 0),
        ]);

        $this->logActivity(
            $schedule,
            'maintenance.created',
            "Menjadwalkan {$schedule->jenis_perawatan} untuk {$asset->kode_barang}",
            [],
            $this->staffId,
            $createdDaysAgo
        );
        $this->logAsset(
            $asset->id,
            'perawatan',
            "Dijadwalkan perawatan: {$schedule->jenis_perawatan} pada " . $schedule->tanggal_jadwal->format('d/m/Y'),
            $this->staffId,
            $createdDaysAgo
        );
    }

    /**
     * Trail "dilaporkan rusak" tambahan untuk aset yang kondisinya sudah
     * ditetapkan langsung saat dibuat (bukan lewat AssetController::
     * reportDamage() sungguhan) — murni menambah riwayat naratif di Log
     * Aktivitas Aset & Aktivitas Sistem, TIDAK mengubah kondisi/status aset
     * (sudah sesuai sejak baris di $items). Format meniru persis
     * reportDamage().
     */
    private function addDamageReportTrail(?Asset $asset, string $oldKondisi, string $newKondisi, string $deskripsi, int $daysAgo): void
    {
        if (! $asset) {
            return;
        }

        $this->logActivity($asset, 'asset.reported-damage', "Melaporkan kerusakan {$asset->kode_barang}: {$deskripsi}", [], $this->staffId, $daysAgo);
        $this->logAsset(
            $asset->id,
            'kondisi',
            "{$deskripsi} (Laporan: {$oldKondisi} → {$newKondisi})",
            $this->staffId,
            $daysAgo
        );
    }

    /**
     * Peminjaman historis yang sudah Dikembalikan, dipakai untuk menyebar
     * tanggal_pinjam ke beberapa bulan sebelumnya (bukan cuma bulan
     * berjalan) — supaya breakdown "per bulan" & "Peminjam Terbanyak" di
     * Rekap Peminjaman benar-benar punya variasi untuk didemokan. Aset yang
     * dipakai tetap berstatus Tersedia (konsisten dengan status Dikembalikan
     * — sama seperti addReturnedTransaction()), bukan aset yang sedang dipakai
     * narasi lain.
     */
    private function addHistoricalTransaction(?Asset $asset, string $namaPeminjam, string $keperluan, int $borrowDaysAgo, int $returnDaysAgo): void
    {
        if (! $asset) {
            return;
        }

        $transaction = Transaction::forceCreate([
            'asset_id' => $asset->id,
            'created_by' => $this->staffId,
            'nama_peminjam' => $namaPeminjam,
            'keperluan' => $keperluan,
            'tanggal_pinjam' => now()->subDays($borrowDaysAgo)->toDateString(),
            'tanggal_kembali' => now()->subDays($returnDaysAgo)->toDateString(),
            'status_peminjaman' => 'Dikembalikan',
            'created_at' => now()->subDays($borrowDaysAgo)->setTime(10, rand(0, 59)),
        ]);

        $this->logActivity(
            $transaction,
            'transaction.borrowed',
            "Mencatat peminjaman {$asset->kode_barang} oleh {$namaPeminjam}",
            ['asset_id' => $asset->id, 'nama_peminjam' => $namaPeminjam],
            $this->staffId,
            $borrowDaysAgo
        );
        $this->logAsset($asset->id, 'peminjaman', "Dipinjam oleh {$namaPeminjam} untuk {$keperluan}", $this->staffId, $borrowDaysAgo);

        $this->logActivity(
            $transaction,
            'transaction.returned',
            "Mencatat pengembalian {$asset->kode_barang}",
            ['asset_id' => $asset->id, 'status_aset' => 'Tersedia'],
            $this->staffId,
            $returnDaysAgo
        );
        $this->logAsset($asset->id, 'pengembalian', "Dikembalikan oleh {$namaPeminjam}", $this->staffId, $returnDaysAgo);
    }
}
