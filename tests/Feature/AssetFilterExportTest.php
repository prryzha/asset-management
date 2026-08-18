<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mengunci filter Kondisi/Status di Daftar Aset + sinkronisasi export PDF/CSV.
 *
 * CATATAN anti-false-positive: dropdown notifikasi header menampilkan aset
 * ber-kondisi "Rusak Berat" (kode — nama). Karena itu aset yang di-assert
 * "tidak boleh muncul" (assertDontSee kode) selalu dibuat dengan kondisi
 * "Kurang Baik" atau status Perbaikan/Disposed — dua-duanya tidak pernah
 * masuk notifikasi header. Semua request index menyertakan 'f' => 1 karena
 * halaman Daftar Aset menampilkan tabel kosong saat belum difilter.
 */
class AssetFilterExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_index_can_filter_by_kondisi(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-KD-BAIK', 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-KD-KURANG', 'kondisi' => 'Kurang Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['f' => 1, 'kondisi' => 'Baik']));

        $response->assertStatus(200);
        $response->assertSee('FLT-KD-BAIK');
        $response->assertDontSee('FLT-KD-KURANG');
    }

    public function test_index_can_filter_by_status(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-ST-TRS', 'kondisi' => 'Baik']);
        // Status Perbaikan tidak pernah tampil di notifikasi header, jadi aman
        // dipakai sebagai aset yang harus hilang dari hasil filter.
        Asset::factory()->perbaikan()->create(['kode_barang' => 'FLT-ST-PRB', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['f' => 1, 'status' => 'Tersedia']));

        $response->assertStatus(200);
        $response->assertSee('FLT-ST-TRS');
        $response->assertDontSee('FLT-ST-PRB');
    }

    public function test_index_can_combine_kondisi_and_status(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-KOM-1', 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-KOM-2', 'kondisi' => 'Kurang Baik']);
        Asset::factory()->dipinjam()->create(['kode_barang' => 'FLT-KOM-3', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['f' => 1, 'kondisi' => 'Baik', 'status' => 'Tersedia']));

        $response->assertStatus(200);
        $response->assertSee('FLT-KOM-1');
        $response->assertDontSee('FLT-KOM-2');
        $response->assertDontSee('FLT-KOM-3');
    }

    public function test_disposed_never_appears_even_when_status_disposed_requested(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-DSP-AKTIF', 'kondisi' => 'Baik']);
        Asset::factory()->disposed()->create(['kode_barang' => 'FLT-DSP-ARSIP', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['f' => 1, 'status' => 'Disposed']));

        $response->assertStatus(200);
        // Kombinasi where status='Disposed' + status!='Disposed' menghasilkan
        // dataset kosong — aset arsip tidak boleh bocor ke Daftar Aset.
        $response->assertDontSee('FLT-DSP-ARSIP');
        $response->assertDontSee('FLT-DSP-AKTIF');
    }

    public function test_search_combines_with_kondisi_filter(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-SR-A-01', 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-SR-B-01', 'kondisi' => 'Kurang Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['f' => 1, 'search' => 'FLT-SR-A', 'kondisi' => 'Baik']));

        $response->assertStatus(200);
        $response->assertSee('FLT-SR-A-01');
        $response->assertDontSee('FLT-SR-B-01');
    }

    public function test_csv_export_respects_kondisi_filter(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-CSV-K-BAIK', 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-CSV-K-RUSAK', 'kondisi' => 'Rusak Berat']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.export-csv', ['kondisi' => 'Baik']));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('FLT-CSV-K-BAIK', $content);
        $this->assertStringNotContainsString('FLT-CSV-K-RUSAK', $content);
    }

    public function test_csv_export_respects_status_filter(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-CSV-S-TRS', 'kondisi' => 'Baik']);
        Asset::factory()->perbaikan()->create(['kode_barang' => 'FLT-CSV-S-PRB', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.export-csv', ['status' => 'Tersedia']));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('FLT-CSV-S-TRS', $content);
        $this->assertStringNotContainsString('FLT-CSV-S-PRB', $content);
    }

    public function test_csv_export_respects_combined_filters(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Kategori Gabungan A']);
        $kategoriB = Category::factory()->create(['nama' => 'Kategori Gabungan B']);

        // Target: kategori A + Tersedia + Baik.
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-CSV-C-1', 'kondisi' => 'Baik', 'category_id' => $kategoriA->id]);
        // Salah kategori.
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-CSV-C-2', 'kondisi' => 'Baik', 'category_id' => $kategoriB->id]);
        // Salah status (kondisi Baik, kategori A).
        Asset::factory()->perbaikan()->create(['kode_barang' => 'FLT-CSV-C-3', 'kondisi' => 'Baik', 'category_id' => $kategoriA->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.export-csv', [
                'category_id' => $kategoriA->id,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
            ]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('FLT-CSV-C-1', $content);
        $this->assertStringNotContainsString('FLT-CSV-C-2', $content);
        $this->assertStringNotContainsString('FLT-CSV-C-3', $content);
    }

    public function test_pdf_export_respects_filters_and_handles_empty_result(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-PDF-1', 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-PDF-2', 'kondisi' => 'Kurang Baik']);

        $filtered = $this->actingAs($this->admin)
            ->get(route('assets.export-pdf', ['kondisi' => 'Baik', 'status' => 'Tersedia', 'search' => 'FLT-PDF-']));
        $filtered->assertStatus(200);
        $this->assertSame('application/pdf', $filtered->headers->get('content-type'));

        // Filter yang tidak cocok dengan data apa pun — harus tetap 200, bukan error.
        $empty = $this->actingAs($this->admin)
            ->get(route('assets.export-pdf', ['kondisi' => 'Rusak Berat']));
        $empty->assertStatus(200);
        $this->assertSame('application/pdf', $empty->headers->get('content-type'));
    }

    public function test_csv_export_excludes_disposed_even_with_filters(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'FLT-EXP-1', 'kondisi' => 'Baik']);
        Asset::factory()->disposed()->create(['kode_barang' => 'FLT-EXP-ARSIP', 'kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.export-csv', ['kondisi' => 'Baik']));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('FLT-EXP-1', $content);
        $this->assertStringNotContainsString('FLT-EXP-ARSIP', $content);
    }
}
