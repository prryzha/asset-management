<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveAssetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    // 0. Konsistensi bahasa UI — seluruh label yang terlihat user harus Bahasa Indonesia

    public function test_export_buttons_use_indonesian_labels(): void
    {
        Asset::factory()->tersedia()->create();
        Asset::factory()->disposed()->create();

        // Daftar Aset (aktif) — label tombol export harus "Ekspor PDF/CSV", bukan English.
        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['f' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Ekspor PDF');
        $response->assertSee('Ekspor CSV');
        $response->assertDontSee('>Export PDF');
        $response->assertDontSee('>Export CSV');

        // Arsip Aset (Disposed)
        $response = $this->actingAs($this->admin)
            ->get(route('assets.archive'));
        $response->assertStatus(200);
        $response->assertSee('Ekspor PDF');
        $response->assertSee('Ekspor CSV');

        // Laporan Aset Hilang
        $response = $this->actingAs($this->admin)
            ->get(route('assets.hilang'));
        $response->assertStatus(200);
        $response->assertSee('Ekspor PDF');
        $response->assertSee('Ekspor CSV');
    }

    // 1. Daftar Aset tidak menampilkan Disposed

    public function test_active_asset_index_excludes_disposed_assets(): void
    {
        $aktif = Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-001']);
        $arsip = Asset::factory()->disposed()->create(['kode_barang' => 'ARS-001']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['f' => 1]));

        $response->assertStatus(200);
        $response->assertSee($aktif->kode_barang);
        $response->assertDontSee($arsip->kode_barang);
    }

    public function test_active_asset_index_search_does_not_return_disposed(): void
    {
        Asset::factory()->disposed()->create([
            'kode_barang' => 'ARS-002',
            'nama_barang' => 'Proyektor Arsip',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['search' => 'Proyektor Arsip']));

        $response->assertStatus(200);
        $response->assertDontSee('ARS-002');
    }

    // 2 & 3. Arsip hanya menampilkan Disposed, aset aktif tidak muncul

    public function test_archive_shows_only_disposed_assets(): void
    {
        // kondisi "Baik" dipakai supaya aset aktif tidak ikut muncul di dropdown
        // notifikasi header ("Aset Rusak Berat") yang dirender di semua halaman —
        // yang diuji di sini adalah isi tabel arsip, bukan chrome layout.
        $arsip = Asset::factory()->disposed()->create(['kode_barang' => 'ARS-003', 'kondisi' => 'Baik']);
        $tersedia = Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-002', 'kondisi' => 'Baik']);
        $dipinjam = Asset::factory()->dipinjam()->create(['kode_barang' => 'AKT-003', 'kondisi' => 'Baik']);
        $perbaikan = Asset::factory()->perbaikan()->create(['kode_barang' => 'AKT-004', 'kondisi' => 'Baik']);
        $hilang = Asset::factory()->hilang()->create(['kode_barang' => 'AKT-005', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)->get(route('assets.archive'));

        $response->assertStatus(200);
        $response->assertSee($arsip->kode_barang);
        $response->assertDontSee($tersedia->kode_barang);
        $response->assertDontSee($dipinjam->kode_barang);
        $response->assertDontSee($perbaikan->kode_barang);
        $response->assertDontSee($hilang->kode_barang);
    }

    // 4. Status UI menampilkan "Dihapuskan", bukan "Disposed"

    public function test_archive_displays_indonesian_status_label(): void
    {
        Asset::factory()->disposed()->create(['kode_barang' => 'ARS-004']);

        $response = $this->actingAs($this->admin)->get(route('assets.archive'));

        $response->assertStatus(200);
        $response->assertSee('Dihapuskan');
        $response->assertDontSee('Disposed');
    }

    // 5. Aset Disposed tidak menampilkan action operasional

    /**
     * Diassert lewat URL endpoint-nya, bukan label tombol — beberapa label
     * ("Laporkan Kerusakan", "Proses Penghapusan") juga muncul sebagai judul
     * modal di blok <script> yang selalu ikut dirender, jadi assert label saja
     * bisa lolos/gagal karena alasan yang salah. Yang benar-benar menentukan
     * suatu aksi tersedia atau tidak adalah ada/tidaknya form/link ke endpoint.
     */
    public function test_disposed_asset_detail_hides_operational_actions(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertDontSee(route('transactions.create') . '?asset_id=' . $asset->id);
        $response->assertDontSee(route('maintenance.create') . '?asset_id=' . $asset->id);
        $response->assertDontSee(route('assets.report-damage', $asset));
        $response->assertDontSee(route('assets.report-lost', $asset));
        $response->assertDontSee(route('assets.mark-found', $asset));
        $response->assertDontSee(route('assets.process-disposal', $asset));
    }

    public function test_active_asset_detail_still_shows_operational_actions(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertSee(route('transactions.create') . '?asset_id=' . $asset->id);
        $response->assertSee(route('assets.report-lost', $asset));
        $response->assertSee(route('assets.process-disposal', $asset));
    }

    // 6. Detail aset Disposed tetap dapat dibuka

    public function test_disposed_asset_detail_can_still_be_opened(): void
    {
        $asset = Asset::factory()->disposed()->create(['kode_barang' => 'ARS-005']);

        $response = $this->actingAs($this->admin)->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertSee($asset->kode_barang);
        $response->assertSee($asset->nama_barang);
    }

    public function test_disposal_history_log_still_visible_on_detail(): void
    {
        $asset = Asset::factory()->disposed()->create();
        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'penghapusan',
            'deskripsi' => 'Aset dihapuskan karena Rusak Berat.',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertSee('Aset dihapuskan karena Rusak Berat.');
    }

    // 7. Dashboard tidak salah menghitung aset Disposed

    public function test_dashboard_total_asset_excludes_disposed(): void
    {
        Asset::factory(3)->tersedia()->create();
        Asset::factory(2)->disposed()->create();

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        // Total aset aktif = 3 (bukan 5), disposed dihitung terpisah = 2.
        $response->assertViewHas('totalAsset', 3);
        $response->assertViewHas('tersedia', 3);
        $response->assertViewHas('disposed', 2);
    }

    public function test_dashboard_condition_counts_exclude_disposed(): void
    {
        Asset::factory(2)->tersedia()->create(['kondisi' => 'Baik']);
        Asset::factory(3)->disposed()->create(['kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        // Kalau kondisi ikut menghitung disposed, bar "baik/total" bisa > 100%.
        $response->assertViewHas('baik', 2);
        $response->assertViewHas('totalAsset', 2);
    }

    // 8. Export arsip hanya berisi aset Disposed

    public function test_archive_csv_export_contains_only_disposed_assets(): void
    {
        Asset::factory()->disposed()->create(['kode_barang' => 'ARS-006']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-006']);

        $response = $this->actingAs($this->admin)->get(route('assets.archive-export-csv'));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('ARS-006', $content);
        $this->assertStringNotContainsString('AKT-006', $content);
        $this->assertStringContainsString('Dihapuskan', $content);
        $this->assertStringNotContainsString('Disposed', $content);
    }

    public function test_archive_pdf_export_responds_successfully(): void
    {
        Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.archive-export-pdf'));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_archive_pdf_export_does_not_error_when_archive_empty(): void
    {
        Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.archive-export-pdf'));

        $response->assertStatus(200);
    }

    // 9. Search arsip tidak mengambil aset aktif

    public function test_archive_search_does_not_return_active_assets(): void
    {
        // kondisi dipatok "Baik": AssetFactory mengacak kondisi, dan aset aktif
        // ber-kondisi "Rusak Berat" akan ikut terlihat di dropdown notifikasi header
        // pada SEMUA halaman — bikin assertDontSee di bawah gagal secara acak
        // (flaky) padahal tabel arsipnya sendiri sudah benar.
        Asset::factory()->tersedia()->create([
            'kode_barang' => 'AKT-007',
            'nama_barang' => 'Laptop Kembar',
            'kondisi' => 'Baik',
        ]);
        Asset::factory()->disposed()->create([
            'kode_barang' => 'ARS-007',
            'nama_barang' => 'Laptop Kembar',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.archive', ['search' => 'Laptop Kembar']));

        $response->assertStatus(200);
        $response->assertSee('ARS-007');
        $response->assertDontSee('AKT-007');
    }

    public function test_archive_can_be_filtered_by_category(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Elektronik Arsip']);
        $kategoriB = Category::factory()->create(['nama' => 'Mebel Arsip']);

        Asset::factory()->disposed()->create(['kode_barang' => 'ARS-008', 'category_id' => $kategoriA->id, 'kondisi' => 'Baik']);
        Asset::factory()->disposed()->create(['kode_barang' => 'ARS-009', 'category_id' => $kategoriB->id, 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.archive', ['category_id' => $kategoriA->id]));

        $response->assertStatus(200);
        $response->assertSee('ARS-008');
        $response->assertDontSee('ARS-009');
    }

    // 10. Authorization sesuai role existing

    public function test_guest_cannot_access_archive(): void
    {
        $response = $this->get(route('assets.archive'));

        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_archive_consistent_with_existing_read_permission(): void
    {
        Asset::factory()->disposed()->create(['kode_barang' => 'ARS-010']);

        $response = $this->actingAs($this->staff)->get(route('assets.archive'));

        $response->assertStatus(200);
        $response->assertSee('ARS-010');
    }

    public function test_guest_cannot_export_archive(): void
    {
        $response = $this->get(route('assets.archive-export-csv'));

        $response->assertRedirect(route('login'));
    }

    // Arsip tidak boleh dicampur dengan konsep SoftDeletes

    public function test_soft_deleted_asset_does_not_appear_in_archive(): void
    {
        $asset = Asset::factory()->tersedia()->create(['kode_barang' => 'SFT-001']);
        $asset->delete(); // SoftDeletes — bukan penghapusan administratif

        $response = $this->actingAs($this->admin)->get(route('assets.archive'));

        $response->assertStatus(200);
        $response->assertDontSee('SFT-001');
    }
}
