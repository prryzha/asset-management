<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionReportTest extends TestCase
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

    private function makeTransaction(array $attributes = []): Transaction
    {
        return Transaction::factory()->create($attributes);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('transactions.report'))->assertRedirect(route('login'));
        $this->get(route('transactions.report-export-pdf'))->assertRedirect(route('login'));
        $this->get(route('transactions.report-export-csv'))->assertRedirect(route('login'));
    }

    public function test_staff_can_open_report(): void
    {
        $this->actingAs($this->staff)
            ->get(route('transactions.report'))
            ->assertStatus(200)
            ->assertSee('Laporan Peminjaman');
    }

    public function test_admin_can_open_report(): void
    {
        $this->actingAs($this->admin)
            ->get(route('transactions.report'))
            ->assertStatus(200)
            ->assertSee('Laporan Peminjaman');
    }

    public function test_only_existing_transactions_appear(): void
    {
        $asset = Asset::factory()->create(['kode_barang' => 'RP-EXIST-01']);
        $this->makeTransaction(['asset_id' => $asset->id, 'nama_peminjam' => 'Budi Peminjam']);

        $response = $this->actingAs($this->admin)->get(route('transactions.report'));

        $response->assertStatus(200);
        $response->assertSee('RP-EXIST-01');
        $response->assertSee('Budi Peminjam');
    }

    public function test_date_filter_works(): void
    {
        $asset = Asset::factory()->create(['kode_barang' => 'RP-DATE-01']);
        $this->makeTransaction(['asset_id' => $asset->id, 'nama_peminjam' => 'Peminjam Januari', 'tanggal_pinjam' => '2026-01-10']);
        $this->makeTransaction(['nama_peminjam' => 'Peminjam Maret', 'tanggal_pinjam' => '2026-03-10']);

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.report', ['tanggal_dari' => '2026-02-01', 'tanggal_sampai' => '2026-04-01']));

        $response->assertStatus(200);
        $response->assertSee('Peminjam Maret');
        // Nama peminjam tidak muncul di dropdown notifikasi header, jadi aman dipakai
        // sebagai penanda baris yang harus hilang (kode aset bisa saja tampil di
        // notifikasi "Aset Rusak Berat" karena kondisi aset hasil factory acak).
        $response->assertDontSee('Peminjam Januari');
    }

    public function test_category_filter_works(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Kategori Laporan A']);
        $kategoriB = Category::factory()->create(['nama' => 'Kategori Laporan B']);
        $asetA = Asset::factory()->create(['kode_barang' => 'RP-CAT-A', 'category_id' => $kategoriA->id]);
        $asetB = Asset::factory()->create(['kode_barang' => 'RP-CAT-B', 'category_id' => $kategoriB->id]);
        $this->makeTransaction(['asset_id' => $asetA->id, 'nama_peminjam' => 'Peminjam Kat A']);
        $this->makeTransaction(['asset_id' => $asetB->id, 'nama_peminjam' => 'Peminjam Kat B']);

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.report', ['category_id' => $kategoriA->id]));

        $response->assertStatus(200);
        $response->assertSee('RP-CAT-A');
        $response->assertDontSee('Peminjam Kat B');
    }

    public function test_location_filter_works(): void
    {
        $lokasiA = Location::factory()->create(['nama' => 'Lokasi Laporan A']);
        $lokasiB = Location::factory()->create(['nama' => 'Lokasi Laporan B']);
        $asetA = Asset::factory()->create(['kode_barang' => 'RP-LOC-A', 'location_id' => $lokasiA->id]);
        $asetB = Asset::factory()->create(['kode_barang' => 'RP-LOC-B', 'location_id' => $lokasiB->id]);
        $this->makeTransaction(['asset_id' => $asetA->id, 'nama_peminjam' => 'Peminjam Lok A']);
        $this->makeTransaction(['asset_id' => $asetB->id, 'nama_peminjam' => 'Peminjam Lok B']);

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.report', ['location_id' => $lokasiA->id]));

        $response->assertStatus(200);
        $response->assertSee('RP-LOC-A');
        $response->assertDontSee('Peminjam Lok B');
    }

    public function test_search_filter_works_by_asset_code_and_borrower(): void
    {
        $asset = Asset::factory()->create(['kode_barang' => 'RP-SRCH-01']);
        $this->makeTransaction(['asset_id' => $asset->id, 'nama_peminjam' => 'Siswa Pencarian']);

        $byCode = $this->actingAs($this->admin)
            ->get(route('transactions.report', ['search' => 'RP-SRCH-01']));
        $byCode->assertStatus(200)->assertSee('RP-SRCH-01');

        $byBorrower = $this->actingAs($this->admin)
            ->get(route('transactions.report', ['search' => 'Siswa Pencarian']));
        $byBorrower->assertStatus(200)->assertSee('RP-SRCH-01');

        $empty = $this->actingAs($this->admin)
            ->get(route('transactions.report', ['search' => 'tidak-ada-match']));
        $empty->assertStatus(200)->assertDontSee('Siswa Pencarian');
    }

    public function test_status_filter_works(): void
    {
        $asetA = Asset::factory()->create(['kode_barang' => 'RP-ST-01']);
        $asetB = Asset::factory()->create(['kode_barang' => 'RP-ST-02']);
        $this->makeTransaction(['asset_id' => $asetA->id, 'nama_peminjam' => 'Peminjam Aktif', 'status_peminjaman' => 'Dipinjam']);
        $this->makeTransaction(['asset_id' => $asetB->id, 'nama_peminjam' => 'Peminjam Selesai', 'status_peminjaman' => 'Dikembalikan']);

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.report', ['status' => 'Dipinjam']));

        $response->assertStatus(200);
        $response->assertSee('RP-ST-01');
        $response->assertDontSee('Peminjam Selesai');
    }

    public function test_pagination_works(): void
    {
        foreach (range(1, 11) as $i) {
            $asset = Asset::factory()->create(['kode_barang' => 'RP-PG-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT)]);
            $this->makeTransaction([
                'asset_id' => $asset->id,
                'nama_peminjam' => 'Peminjam No ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'tanggal_pinjam' => '2026-01-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        // Diurutkan berdasarkan tanggal pinjam terbaru (desc): No 11 di halaman 1, No 01 di halaman 2.
        $page1 = $this->actingAs($this->admin)->get(route('transactions.report'));
        $page1->assertStatus(200)->assertSee('RP-PG-11')->assertDontSee('Peminjam No 01');

        $page2 = $this->actingAs($this->admin)->get(route('transactions.report', ['page' => 2]));
        $page2->assertStatus(200)->assertSee('RP-PG-01')->assertDontSee('Peminjam No 11');
    }

    public function test_csv_export_respects_filters(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Kategori CSV A']);
        $kategoriB = Category::factory()->create(['nama' => 'Kategori CSV B']);
        $asetA = Asset::factory()->create(['kode_barang' => 'RP-CSV-A', 'category_id' => $kategoriA->id]);
        $asetB = Asset::factory()->create(['kode_barang' => 'RP-CSV-B', 'category_id' => $kategoriB->id]);
        $this->makeTransaction(['asset_id' => $asetA->id, 'tanggal_pinjam' => '2026-01-05']);
        $this->makeTransaction(['asset_id' => $asetB->id, 'tanggal_pinjam' => '2026-01-15']);

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.report-export-csv', [
                'category_id' => $kategoriA->id,
                'tanggal_dari' => '2026-01-01',
                'tanggal_sampai' => '2026-01-10',
            ]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('RP-CSV-A', $content);
        $this->assertStringNotContainsString('RP-CSV-B', $content);
        // BOM UTF-8 + header Bahasa Indonesia
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Kode Barang', $content);
    }

    public function test_pdf_export_returns_application_pdf(): void
    {
        $asset = Asset::factory()->create(['kode_barang' => 'RP-PDF-01']);
        $this->makeTransaction(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)->get(route('transactions.report-export-pdf'));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_empty_result_does_not_error(): void
    {
        $page = $this->actingAs($this->admin)->get(route('transactions.report', ['search' => 'zzz-tidak-ada']));
        $page->assertStatus(200);

        $pdf = $this->actingAs($this->admin)->get(route('transactions.report-export-pdf', ['search' => 'zzz-tidak-ada']));
        $pdf->assertStatus(200);
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));

        $csv = $this->actingAs($this->admin)->get(route('transactions.report-export-csv', ['search' => 'zzz-tidak-ada']));
        $csv->assertStatus(200);
    }

    public function test_disposed_asset_transaction_history_still_appears(): void
    {
        $asset = Asset::factory()->disposed()->create(['kode_barang' => 'RP-DSP-01']);
        $this->makeTransaction([
            'asset_id' => $asset->id,
            'nama_peminjam' => 'Peminjam Historis',
            'status_peminjaman' => 'Dikembalikan',
            'tanggal_pinjam' => '2026-01-05',
            'tanggal_kembali' => '2026-01-10',
        ]);

        $response = $this->actingAs($this->admin)->get(route('transactions.report'));

        $response->assertStatus(200);
        $response->assertSee('RP-DSP-01');
        $response->assertSee('Peminjam Historis');

        $csv = $this->actingAs($this->admin)->get(route('transactions.report-export-csv'));
        $this->assertStringContainsString('RP-DSP-01', $csv->streamedContent());
    }

    public function test_opening_and_exporting_report_does_not_change_asset_status(): void
    {
        $asset = Asset::factory()->tersedia()->create(['kode_barang' => 'RP-NOCHG-01']);
        $this->makeTransaction(['asset_id' => $asset->id, 'status_peminjaman' => 'Dikembalikan']);

        $this->actingAs($this->admin)->get(route('transactions.report'));
        $this->actingAs($this->admin)->get(route('transactions.report-export-pdf'));
        $this->actingAs($this->admin)->get(route('transactions.report-export-csv'));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
        ]);

        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
        ]);
    }
}
