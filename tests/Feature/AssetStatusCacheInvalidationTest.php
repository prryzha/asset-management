<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Regresi untuk temuan audit pre-demo: TransactionController dan
 * MaintenanceScheduleController mengubah asset.status tapi tidak selalu
 * meng-invalidate cache yang dihitung dari status itu (dashboard, dropdown
 * "Catat Peminjaman", dropdown pilih-aset Perawatan) — fix-nya adalah
 * Asset::forgetStatusCaches() yang sekarang dipanggil di keempat titik
 * mutasi status: store()/returnItem() (Transaction) dan start()/complete()
 * (Maintenance). Test di sini membuktikan cache BENAR-BENAR terhapus/segar,
 * bukan cuma HTTP 200.
 */
class AssetStatusCacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Store cache 'array' hidup selama satu proses PHPUnit (RefreshDatabase
        // cuma reset DB, bukan cache) — tanpa ini, entry cache milik test lain
        // yang berjalan lebih dulu di suite yang sama bisa nyangkut di sini dan
        // membuat assertion "cache benar-benar segar" di bawah jadi tidak
        // deterministik tergantung urutan test.
        Cache::flush();

        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    private function primeStatusCaches(): void
    {
        Cache::put('dashboard_data', 'stale-marker', 300);
        Cache::put('available_assets', collect(['stale-marker']), 300);
        Cache::put('all_assets_v3', collect(['stale-marker']), 300);
        Cache::put('header_notifications', 'stale-marker', 300);
    }

    private function assertStatusCachesWereCleared(): void
    {
        $this->assertFalse(Cache::has('dashboard_data'), 'dashboard_data seharusnya sudah di-invalidate');
        $this->assertFalse(Cache::has('available_assets'), 'available_assets seharusnya sudah di-invalidate');
        $this->assertFalse(Cache::has('all_assets_v3'), 'all_assets_v3 seharusnya sudah di-invalidate');
        $this->assertFalse(Cache::has('header_notifications'), 'header_notifications seharusnya sudah di-invalidate');
    }

    // 1. Peminjaman berhasil -> cache status ikut terhapus

    public function test_borrowing_an_asset_clears_status_dependent_caches(): void
    {
        $asset = Asset::factory()->tersedia()->create();
        $this->primeStatusCaches();

        $this->actingAs($this->staff)->post(route('transactions.store'), [
            'asset_id' => $asset->id,
            'nama_peminjam' => 'Budi Santoso',
            'keperluan' => 'Uji invalidasi cache',
            'tanggal_pinjam' => now()->toDateString(),
        ])->assertRedirect(route('transactions.index'));

        $this->assertStatusCachesWereCleared();
    }

    // 2. Pengembalian berhasil -> cache status ikut terhapus

    public function test_returning_an_asset_clears_status_dependent_caches(): void
    {
        $asset = Asset::factory()->dipinjam()->create();
        $trx = Transaction::factory()->create(['asset_id' => $asset->id, 'status_peminjaman' => 'Dipinjam']);
        $this->primeStatusCaches();

        $this->actingAs($this->staff)->post(route('transactions.return', $trx))
            ->assertRedirect(route('transactions.index'));

        $this->assertStatusCachesWereCleared();
    }

    // 3. Mulai perawatan -> cache status ikut terhapus (sebelumnya TIDAK ADA sama sekali)

    public function test_starting_maintenance_clears_status_dependent_caches(): void
    {
        $asset = Asset::factory()->tersedia()->create();
        $schedule = MaintenanceSchedule::factory()->dijadwalkan()->create(['asset_id' => $asset->id]);
        $this->primeStatusCaches();

        $this->actingAs($this->staff)->patch(route('maintenance.start', $schedule))
            ->assertRedirect(route('maintenance.index'));

        $this->assertStatusCachesWereCleared();
    }

    // 4. Selesaikan perawatan -> cache status ikut terhapus (sebelumnya TIDAK ADA sama sekali)

    public function test_completing_maintenance_clears_status_dependent_caches(): void
    {
        $asset = Asset::factory()->perbaikan()->create();
        $schedule = MaintenanceSchedule::factory()->dikerjakan()->create(['asset_id' => $asset->id]);
        $this->primeStatusCaches();

        $this->actingAs($this->staff)->put(route('maintenance.complete', $schedule), [
            'kondisi' => 'Baik',
        ])->assertRedirect(route('maintenance.index'));

        $this->assertStatusCachesWereCleared();
    }

    // 5-6. End-to-end: dropdown "Catat Peminjaman" benar-benar segar (bukan cuma key terhapus)

    public function test_borrowed_asset_disappears_from_borrow_dropdown_immediately(): void
    {
        // kondisi dipatok "Baik": AssetFactory mengacak kondisi, dan aset
        // ber-kondisi "Rusak Berat" ikut muncul di dropdown notifikasi header
        // (HeaderNotificationComposer) walau statusnya sudah Dipinjam — bikin
        // assertDontSee di bawah gagal acak (flaky) padahal cache-nya sendiri
        // sudah benar.
        $asset = Asset::factory()->tersedia()->create(['kode_barang' => 'CACHE-BRW-01', 'kondisi' => 'Baik']);

        // Buka "Catat Peminjaman" dulu supaya cache available_assets terisi
        // (berisi aset ini, karena masih Tersedia).
        $before = $this->actingAs($this->staff)->get(route('transactions.create'));
        $before->assertSee('CACHE-BRW-01');

        $this->actingAs($this->staff)->post(route('transactions.store'), [
            'asset_id' => $asset->id,
            'nama_peminjam' => 'Siti Aminah',
            'keperluan' => 'Uji dropdown segar',
            'tanggal_pinjam' => now()->toDateString(),
        ])->assertRedirect(route('transactions.index'));

        // Dropdown harus langsung segar: aset yang baru dipinjam TIDAK BOLEH
        // lagi muncul sebagai pilihan, bukan menunggu TTL cache 5 menit habis.
        $after = $this->actingAs($this->staff)->get(route('transactions.create'));
        $after->assertDontSee('CACHE-BRW-01');
    }

    public function test_returned_asset_reappears_in_borrow_dropdown_immediately(): void
    {
        // kondisi dipatok "Baik" — lihat alasan di test borrow di atas.
        $asset = Asset::factory()->dipinjam()->create(['kode_barang' => 'CACHE-RTN-01', 'kondisi' => 'Baik']);
        $trx = Transaction::factory()->create(['asset_id' => $asset->id, 'status_peminjaman' => 'Dipinjam']);

        // Buka "Catat Peminjaman" dulu supaya cache available_assets terisi
        // TANPA aset ini (masih Dipinjam saat itu).
        $before = $this->actingAs($this->staff)->get(route('transactions.create'));
        $before->assertDontSee('CACHE-RTN-01');

        $this->actingAs($this->staff)->post(route('transactions.return', $trx))
            ->assertRedirect(route('transactions.index'));

        $after = $this->actingAs($this->staff)->get(route('transactions.create'));
        $after->assertSee('CACHE-RTN-01');
    }

    // 7. End-to-end lintas controller: mulai perawatan membuat aset hilang dari dropdown peminjaman

    public function test_asset_under_maintenance_disappears_from_borrow_dropdown_immediately(): void
    {
        // kondisi dipatok "Baik" — lihat alasan di test borrow di atas.
        $asset = Asset::factory()->tersedia()->create(['kode_barang' => 'CACHE-MST-01', 'kondisi' => 'Baik']);
        $schedule = MaintenanceSchedule::factory()->dijadwalkan()->create(['asset_id' => $asset->id]);

        $before = $this->actingAs($this->staff)->get(route('transactions.create'));
        $before->assertSee('CACHE-MST-01');

        $this->actingAs($this->staff)->patch(route('maintenance.start', $schedule))
            ->assertRedirect(route('maintenance.index'));

        $after = $this->actingAs($this->staff)->get(route('transactions.create'));
        $after->assertDontSee('CACHE-MST-01');
    }

    // 8. End-to-end: dashboard menghitung ulang, bukan angka basi, setelah perawatan selesai

    public function test_dashboard_counts_are_fresh_immediately_after_maintenance_completes(): void
    {
        $asset = Asset::factory()->perbaikan()->create();
        $schedule = MaintenanceSchedule::factory()->dikerjakan()->create(['asset_id' => $asset->id]);

        // Buka dashboard dulu supaya dashboard_data terisi dengan angka SEBELUM
        // perawatan selesai (aset ini masih terhitung 'perbaikan').
        $before = $this->captureDashboardCounts(function () {
            $this->actingAs($this->staff)->get(route('dashboard'))->assertOk();
        });
        $this->assertSame(1, $before['perbaikan']);
        $this->assertSame(0, $before['tersedia']);

        $this->actingAs($this->staff)->put(route('maintenance.complete', $schedule), [
            'kondisi' => 'Baik',
        ])->assertRedirect(route('maintenance.index'));

        $after = $this->captureDashboardCounts(function () {
            $this->actingAs($this->staff)->get(route('dashboard'))->assertOk();
        });
        $this->assertSame(0, $after['perbaikan']);
        $this->assertSame(1, $after['tersedia']);
    }

    /**
     * @return array{tersedia: int, perbaikan: int}
     */
    private function captureDashboardCounts(callable $request): array
    {
        $captured = [];

        View::composer('dashboard', function ($view) use (&$captured) {
            $data = $view->getData();
            $captured = ['tersedia' => $data['tersedia'], 'perbaikan' => $data['perbaikan']];
        });

        $request();

        return $captured;
    }

    // 9. Pastikan behavior existing tetap berjalan: guard status/business rule tidak berubah

    public function test_existing_borrow_and_maintenance_guards_still_work(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        // Aset yang sudah Dipinjam tetap tidak bisa dipinjam lagi (guard existing,
        // bukan bagian dari fix cache — cuma memastikan fix ini tidak menyentuhnya).
        $response = $this->actingAs($this->staff)->post(route('transactions.store'), [
            'asset_id' => $asset->id,
            'nama_peminjam' => 'Uji Guard',
            'keperluan' => 'Uji guard existing',
            'tanggal_pinjam' => now()->toDateString(),
        ]);
        $response->assertSessionHasErrors('asset_id');
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Dipinjam']);
    }
}
