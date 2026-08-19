<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_produces_clean_master_data(): void
    {
        $this->seed();

        // User demo: 1 admin + 1 staff — tidak boleh ada user acak tambahan
        // (dulu MaintenanceScheduleFactory membuat user baru per jadwal perawatan).
        $this->assertSame(2, User::count());

        // Master data EKSPLISIT, bukan hasil random AssetFactory.
        $this->assertSame(
            ['Elektronik', 'Mebel', 'Laboratorium', 'Buku', 'Olahraga'],
            Category::orderBy('id')->pluck('nama')->all()
        );
        $this->assertSame(
            ['Lab Komputer', 'Ruang Guru', 'Ruang Kelas', 'Perpustakaan', 'Lab IPA', 'Gudang Olahraga'],
            Location::orderBy('id')->pluck('nama')->all()
        );

        $this->assertSame(24, Asset::count());

        // SEMUA aset memakai master data yang di-seed — tidak boleh ada
        // kategori/lokasi nyasar dari factory.
        $kategoriIds = Category::pluck('id');
        $lokasiIds = Location::pluck('id');
        $this->assertTrue(Asset::pluck('category_id')->every(fn ($id) => $kategoriIds->contains($id)));
        $this->assertTrue(Asset::pluck('location_id')->every(fn ($id) => $lokasiIds->contains($id)));

        // Dataset demo SEKARANG sengaja mencakup seluruh status aset yang
        // didukung aplikasi (termasuk Hilang & Disposed) — lihat
        // DemoDataSeeder::addLostAsset()/addDisposedAsset() — supaya halaman
        // Aset Hilang & Arsip Aset tidak kosong saat demo.
        $this->assertSame(1, Asset::where('status', 'Hilang')->count());
        $this->assertSame(1, Asset::where('status', 'Disposed')->count());
        $this->assertSame(23, Asset::where('status', '!=', 'Disposed')->count());
        $this->assertGreaterThan(0, Transaction::count());
        $this->assertGreaterThan(0, MaintenanceSchedule::count());

        // Rekap Peminjaman "per bulan" butuh transaksi yang tersebar di lebih
        // dari satu bulan kalender, bukan cuma satu — dikelompokkan di PHP
        // (bukan fungsi SQL spesifik-driver), konsisten dengan cara
        // TransactionController membangun breakdown "per bulan" Rekap
        // Peminjaman itu sendiri.
        $distinctMonths = Transaction::pluck('tanggal_pinjam')
            ->map(fn ($tanggal) => \Carbon\Carbon::parse($tanggal)->format('Y-m'))
            ->unique();
        $this->assertGreaterThan(1, $distinctMonths->count());

        // Setidaknya satu jadwal perawatan yang belum dimulai (Dijadwalkan) —
        // sebelumnya dataset demo cuma punya Dikerjakan/Selesai.
        $this->assertGreaterThan(0, MaintenanceSchedule::where('status', 'Dijadwalkan')->count());
    }

    public function test_seeder_is_safe_to_run_twice(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(2, User::count());
        $this->assertSame(5, Category::count());
        $this->assertSame(6, Location::count());
        $this->assertSame(24, Asset::count());
    }
}
