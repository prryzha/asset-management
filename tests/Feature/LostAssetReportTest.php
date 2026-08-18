<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LostAssetReportTest extends TestCase
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

    // 1. Halaman laporan hanya menampilkan aset berstatus Hilang

    public function test_lost_report_shows_only_hilang_assets(): void
    {
        // kondisi dipatok "Baik" di semua aset — aset aktif ber-kondisi "Rusak Berat"
        // akan ikut muncul di dropdown notifikasi header pada SEMUA halaman dan
        // membuat assertDontSee di bawah flaky (pola yang sama dipakai ArchiveAssetTest).
        $hilang = Asset::factory()->hilang()->create(['kode_barang' => 'HLG-001', 'kondisi' => 'Baik']);
        $tersedia = Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-001', 'kondisi' => 'Baik']);
        $dipinjam = Asset::factory()->dipinjam()->create(['kode_barang' => 'AKT-002', 'kondisi' => 'Baik']);
        $perbaikan = Asset::factory()->perbaikan()->create(['kode_barang' => 'AKT-003', 'kondisi' => 'Baik']);
        $disposed = Asset::factory()->disposed()->create(['kode_barang' => 'ARS-001', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)->get(route('assets.hilang'));

        $response->assertStatus(200);
        $response->assertSee($hilang->kode_barang);
        $response->assertDontSee($tersedia->kode_barang);
        $response->assertDontSee($dipinjam->kode_barang);
        $response->assertDontSee($perbaikan->kode_barang);
        $response->assertDontSee($disposed->kode_barang);
    }

    // 2. Detail laporan (tanggal + petugas + kronologi) diambil dari log tipe 'hilang'

    public function test_lost_report_displays_lost_log_details(): void
    {
        $asset = Asset::factory()->hilang()->create(['kode_barang' => 'HLG-002', 'kondisi' => 'Baik']);
        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'hilang',
            'deskripsi' => 'Dilaporkan hilang pada 10/08/2026. Lokasi terakhir: Lab Komputer 1. Kronologi: Tidak ditemukan setelah jam pulang.',
            'user_id' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('assets.hilang'));

        $response->assertStatus(200);
        $response->assertSee('Tidak ditemukan setelah jam pulang.');
        $response->assertSee($this->staff->name);
    }

    // 3. Aset yang sudah ditemukan (status berubah dari Hilang) tidak muncul lagi

    public function test_lost_report_does_not_show_found_assets(): void
    {
        // Aset ini pernah dilaporkan hilang, tapi statusnya sudah kembali Tersedia
        // (proses ditemukan kembali) — log 'hilang' masih ada tapi tidak boleh muncul.
        $found = Asset::factory()->tersedia()->create(['kode_barang' => 'TMP-001', 'kondisi' => 'Baik']);
        AssetLog::create([
            'asset_id' => $found->id,
            'tipe' => 'hilang',
            'deskripsi' => 'Pernah dilaporkan hilang.',
            'user_id' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('assets.hilang'));

        $response->assertStatus(200);
        $response->assertDontSee('TMP-001');
    }

    // 4. Filter pencarian & kategori

    public function test_lost_report_search_filters_by_kode_or_nama(): void
    {
        Asset::factory()->hilang()->create([
            'kode_barang' => 'HLG-003',
            'nama_barang' => 'Proyektor Kelas A',
            'kondisi' => 'Baik',
        ]);
        Asset::factory()->hilang()->create([
            'kode_barang' => 'HLG-004',
            'nama_barang' => 'Laptop Lab',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.hilang', ['search' => 'Proyektor Kelas A']));

        $response->assertStatus(200);
        $response->assertSee('HLG-003');
        $response->assertDontSee('HLG-004');
    }

    public function test_lost_report_can_be_filtered_by_category(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Elektronik Hilang']);
        $kategoriB = Category::factory()->create(['nama' => 'Mebel Hilang']);

        Asset::factory()->hilang()->create(['kode_barang' => 'HLG-005', 'category_id' => $kategoriA->id, 'kondisi' => 'Baik']);
        Asset::factory()->hilang()->create(['kode_barang' => 'HLG-006', 'category_id' => $kategoriB->id, 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.hilang', ['category_id' => $kategoriA->id]));

        $response->assertStatus(200);
        $response->assertSee('HLG-005');
        $response->assertDontSee('HLG-006');
    }

    // 5. Authorization konsisten dengan halaman baca lainnya (semua role login)

    public function test_guest_cannot_access_lost_report(): void
    {
        $response = $this->get(route('assets.hilang'));

        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_lost_report(): void
    {
        Asset::factory()->hilang()->create(['kode_barang' => 'HLG-007', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->staff)->get(route('assets.hilang'));

        $response->assertStatus(200);
        $response->assertSee('HLG-007');
    }

    // 6. Export hanya berisi aset Hilang + filter ikut terbawa

    public function test_lost_report_csv_export_contains_only_hilang_assets(): void
    {
        Asset::factory()->hilang()->create(['kode_barang' => 'HLG-008', 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-008', 'kondisi' => 'Baik']);
        Asset::factory()->disposed()->create(['kode_barang' => 'ARS-008', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)->get(route('assets.hilang-export-csv'));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('HLG-008', $content);
        $this->assertStringNotContainsString('AKT-008', $content);
        $this->assertStringNotContainsString('ARS-008', $content);
    }

    public function test_lost_report_csv_export_respects_search_filter(): void
    {
        Asset::factory()->hilang()->create([
            'kode_barang' => 'HLG-009',
            'nama_barang' => 'Whiteboard Ruang Guru',
            'kondisi' => 'Baik',
        ]);
        Asset::factory()->hilang()->create([
            'kode_barang' => 'HLG-010',
            'nama_barang' => 'AC Ruang Tamu',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.hilang-export-csv', ['search' => 'Whiteboard Ruang Guru']));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('HLG-009', $content);
        $this->assertStringNotContainsString('HLG-010', $content);
    }

    public function test_lost_report_pdf_export_responds_successfully(): void
    {
        Asset::factory()->hilang()->create(['kode_barang' => 'HLG-011', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)->get(route('assets.hilang-export-pdf'));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_lost_report_pdf_export_does_not_error_when_empty(): void
    {
        Asset::factory()->tersedia()->create(['kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)->get(route('assets.hilang-export-pdf'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_export_lost_report(): void
    {
        $response = $this->get(route('assets.hilang-export-csv'));

        $response->assertRedirect(route('login'));
    }
}
