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

        $this->assertSame(22, Asset::count());

        // SEMUA aset memakai master data yang di-seed — tidak boleh ada
        // kategori/lokasi nyasar dari factory.
        $kategoriIds = Category::pluck('id');
        $lokasiIds = Location::pluck('id');
        $this->assertTrue(Asset::pluck('category_id')->every(fn ($id) => $kategoriIds->contains($id)));
        $this->assertTrue(Asset::pluck('location_id')->every(fn ($id) => $lokasiIds->contains($id)));

        // Distribusi demo masuk akal: tidak ada aset Disposed di data awal,
        // peminjaman & perawatan ikut ter-seed.
        $this->assertSame(22, Asset::where('status', '!=', 'Disposed')->count());
        $this->assertGreaterThan(0, Transaction::count());
        $this->assertGreaterThan(0, MaintenanceSchedule::count());
    }

    public function test_seeder_is_safe_to_run_twice(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(2, User::count());
        $this->assertSame(5, Category::count());
        $this->assertSame(6, Location::count());
        $this->assertSame(22, Asset::count());
    }
}
