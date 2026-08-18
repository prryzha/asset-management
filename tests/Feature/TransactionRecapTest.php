<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\Location;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class TransactionRecapTest extends TestCase
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

    /**
     * Sama seperti pola di AssetLabelMassalTest/AssetLabelTest: assert lewat
     * data mentah ($recap) yang dikirim ke view, bukan cuma HTTP 200 — supaya
     * benar-benar membuktikan hasil agregasi, bukan cuma "halaman tampil".
     */
    private function recapDikirimKeView(callable $request): ?array
    {
        $captured = null;

        View::composer('transactions.recap', function ($view) use (&$captured) {
            $captured = $view->getData()['recap'];
        });

        $request();

        return $captured;
    }

    // 1. Guest -> login

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('transactions.recap'))->assertRedirect(route('login'));
        $this->get(route('transactions.recap-export-pdf'))->assertRedirect(route('login'));
        $this->get(route('transactions.recap-export-csv'))->assertRedirect(route('login'));
    }

    // 2-3. Admin & staff bisa akses

    public function test_staff_can_open_recap(): void
    {
        $this->actingAs($this->staff)
            ->get(route('transactions.recap'))
            ->assertStatus(200)
            ->assertSee('Rekap Peminjaman');
    }

    public function test_admin_can_open_recap(): void
    {
        $this->actingAs($this->admin)
            ->get(route('transactions.recap'))
            ->assertStatus(200)
            ->assertSee('Rekap Peminjaman');
    }

    // 4. Ringkasan utama & breakdown status sesuai dataset, termasuk status lama
    // (Ditolak) yang tidak lagi diproduksi alur aktif tapi bisa saja masih ada
    // sebagai data historis — total_transaksi harus tetap menghitungnya,
    // sedangkan total_peminjaman (Dipinjam+Dikembalikan) tidak.

    public function test_summary_and_status_breakdown_reflect_dataset(): void
    {
        Transaction::factory()->count(2)->create(['status_peminjaman' => 'Dipinjam']);
        Transaction::factory()->count(3)->create(['status_peminjaman' => 'Dikembalikan']);
        Transaction::factory()->create(['status_peminjaman' => 'Ditolak']);

        $recap = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)->get(route('transactions.recap'))->assertOk();
        });

        $this->assertSame(6, $recap['total_transaksi']);
        $this->assertSame(2, $recap['sedang_dipinjam']);
        $this->assertSame(3, $recap['total_pengembalian']);
        $this->assertSame(5, $recap['total_peminjaman']);

        $perStatus = collect($recap['per_status'])->keyBy('label');
        $this->assertSame(3, $perStatus['Dikembalikan']['jumlah']);
        $this->assertSame(2, $perStatus['Dipinjam']['jumlah']);
        $this->assertSame(1, $perStatus['Ditolak']['jumlah']);
        // Terurut dari jumlah terbesar.
        $this->assertSame(['Dikembalikan', 'Dipinjam', 'Ditolak'], collect($recap['per_status'])->pluck('label')->all());
    }

    // 5-6. Filter kategori & breakdown per kategori

    public function test_category_filter_and_per_kategori_breakdown(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Kategori Rekap A']);
        $kategoriB = Category::factory()->create(['nama' => 'Kategori Rekap B']);
        $asetA = Asset::factory()->create(['category_id' => $kategoriA->id]);
        $asetB = Asset::factory()->create(['category_id' => $kategoriB->id]);

        Transaction::factory()->count(3)->create(['asset_id' => $asetA->id]);
        Transaction::factory()->create(['asset_id' => $asetB->id]);

        $unfiltered = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)->get(route('transactions.recap'))->assertOk();
        });
        $perKategori = collect($unfiltered['per_kategori'])->keyBy('label');
        $this->assertSame(3, $perKategori['Kategori Rekap A']['jumlah']);
        $this->assertSame(1, $perKategori['Kategori Rekap B']['jumlah']);
        // Terurut dari jumlah terbesar.
        $this->assertSame('Kategori Rekap A', collect($unfiltered['per_kategori'])->first()['label']);

        $filtered = $this->recapDikirimKeView(function () use ($kategoriA) {
            $this->actingAs($this->admin)
                ->get(route('transactions.recap', ['category_id' => $kategoriA->id]))
                ->assertOk();
        });
        $this->assertSame(3, $filtered['total_transaksi']);
        $this->assertCount(1, $filtered['per_kategori']);
        $this->assertSame('Kategori Rekap A', $filtered['per_kategori'][0]['label']);
    }

    // 7-8. Filter lokasi & breakdown per lokasi

    public function test_location_filter_and_per_lokasi_breakdown(): void
    {
        $lokasiA = Location::factory()->create(['nama' => 'Lokasi Rekap A']);
        $lokasiB = Location::factory()->create(['nama' => 'Lokasi Rekap B']);
        $asetA = Asset::factory()->create(['location_id' => $lokasiA->id]);
        $asetB = Asset::factory()->create(['location_id' => $lokasiB->id]);

        Transaction::factory()->count(2)->create(['asset_id' => $asetA->id]);
        Transaction::factory()->create(['asset_id' => $asetB->id]);

        $filtered = $this->recapDikirimKeView(function () use ($lokasiA) {
            $this->actingAs($this->admin)
                ->get(route('transactions.recap', ['location_id' => $lokasiA->id]))
                ->assertOk();
        });

        $this->assertSame(2, $filtered['total_transaksi']);
        $this->assertCount(1, $filtered['per_lokasi']);
        $this->assertSame('Lokasi Rekap A', $filtered['per_lokasi'][0]['label']);
        $this->assertSame(2, $filtered['per_lokasi'][0]['jumlah']);
    }

    // 9. Filter periode & breakdown per bulan (label bulan Indonesia, urut kronologis)

    public function test_date_filter_and_per_bulan_breakdown(): void
    {
        Transaction::factory()->create(['nama_peminjam' => 'Peminjam Januari', 'tanggal_pinjam' => '2026-01-10']);
        Transaction::factory()->count(2)->create(['nama_peminjam' => 'Peminjam Maret', 'tanggal_pinjam' => '2026-03-15']);

        $filtered = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)
                ->get(route('transactions.recap', ['tanggal_dari' => '2026-02-01', 'tanggal_sampai' => '2026-04-01']))
                ->assertOk();
        });

        $this->assertSame(2, $filtered['total_transaksi']);
        $this->assertSame([['label' => 'Maret 2026', 'jumlah' => 2]], $filtered['per_bulan']);

        $unfiltered = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)->get(route('transactions.recap'))->assertOk();
        });
        // Urut kronologis (Januari sebelum Maret), BUKAN urut jumlah terbesar
        // (Maret jumlahnya lebih besar tapi harus tetap di posisi kedua).
        $this->assertSame(
            ['Januari 2026', 'Maret 2026'],
            collect($unfiltered['per_bulan'])->pluck('label')->all()
        );
    }

    // 10. Filter status

    public function test_status_filter_works(): void
    {
        Transaction::factory()->count(2)->create(['status_peminjaman' => 'Dipinjam']);
        Transaction::factory()->create(['status_peminjaman' => 'Dikembalikan']);

        $filtered = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)
                ->get(route('transactions.recap', ['status' => 'Dipinjam']))
                ->assertOk();
        });

        $this->assertSame(2, $filtered['total_transaksi']);
        $this->assertCount(1, $filtered['per_status']);
        $this->assertSame('Dipinjam', $filtered['per_status'][0]['label']);
    }

    // 11. Peminjam Terbanyak: normalisasi spasi ganda & huruf besar/kecil, bukan fuzzy matching

    public function test_top_peminjam_normalizes_whitespace_and_case_only(): void
    {
        Transaction::factory()->count(2)->create(['nama_peminjam' => 'Budi Santoso']);
        Transaction::factory()->create(['nama_peminjam' => '  budi   santoso  ']);
        Transaction::factory()->create(['nama_peminjam' => 'BUDI SANTOSO']);
        Transaction::factory()->create(['nama_peminjam' => 'Siti Aminah']);

        $recap = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)->get(route('transactions.recap'))->assertOk();
        });

        // 4 varian nama "budi santoso" digabung jadi SATU baris, bukan 4 baris terpisah.
        $this->assertCount(2, $recap['top_peminjam']);

        $budi = collect($recap['top_peminjam'])->first(fn($r) => strtolower($r['nama']) === 'budi santoso');
        $this->assertNotNull($budi);
        $this->assertSame(4, $budi['jumlah']);
        // Spasi ganda pada nama yang ditampilkan tetap dirapikan (bukan fuzzy match nama lain).
        $this->assertStringNotContainsString('  ', $budi['nama']);

        $siti = collect($recap['top_peminjam'])->first(fn($r) => strtolower($r['nama']) === 'siti aminah');
        $this->assertNotNull($siti);
        $this->assertSame(1, $siti['jumlah']);
    }

    // 12. Peminjam Terbanyak dibatasi 10

    public function test_top_peminjam_is_capped_at_ten(): void
    {
        foreach (range(1, 15) as $i) {
            Transaction::factory()->create(['nama_peminjam' => 'Peminjam Unik ' . $i]);
        }

        $recap = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)->get(route('transactions.recap'))->assertOk();
        });

        $this->assertCount(10, $recap['top_peminjam']);
    }

    // 13. Aset Disposed: histori transaksinya tetap dihitung (transaksi = histori,
    // sesuai business rule Laporan Peminjaman — lihat TransactionReportTest).

    public function test_disposed_asset_transaction_history_still_counted(): void
    {
        $kategori = Category::factory()->create(['nama' => 'Kategori Rekap Disposed']);
        $asset = Asset::factory()->disposed()->create(['kode_barang' => 'REKAP-DSP-01', 'category_id' => $kategori->id]);
        Transaction::factory()->create([
            'asset_id' => $asset->id,
            'status_peminjaman' => 'Dikembalikan',
            'nama_peminjam' => 'Peminjam Histori Disposed',
        ]);

        $recap = $this->recapDikirimKeView(function () {
            $this->actingAs($this->admin)->get(route('transactions.recap'))->assertOk();
        });

        $this->assertSame(1, $recap['total_transaksi']);
        $perKategori = collect($recap['per_kategori'])->keyBy('label');
        $this->assertSame(1, $perKategori['Kategori Rekap Disposed']['jumlah']);
    }

    // 14. Ekspor CSV mengikuti filter & memuat hasil agregasi yang sama (bukan dump baris transaksi)

    public function test_csv_export_respects_filters_and_matches_page_aggregates(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Kategori CSV Rekap A']);
        $kategoriB = Category::factory()->create(['nama' => 'Kategori CSV Rekap B']);
        $asetA = Asset::factory()->create(['category_id' => $kategoriA->id]);
        $asetB = Asset::factory()->create(['category_id' => $kategoriB->id]);

        Transaction::factory()->count(2)->create(['asset_id' => $asetA->id]);
        Transaction::factory()->create(['asset_id' => $asetB->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.recap-export-csv', ['category_id' => $kategoriA->id]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('RINGKASAN', $content);
        $this->assertStringContainsString('Total Transaksi', $content);
        $this->assertStringContainsString('PEMINJAMAN PER KATEGORI', $content);
        $this->assertStringContainsString('Kategori CSV Rekap A', $content);
        $this->assertStringNotContainsString('Kategori CSV Rekap B', $content);

        // Bukan dump baris transaksi mentah: tidak ada kolom "Keperluan" khas
        // Laporan Peminjaman di export ini.
        $this->assertStringNotContainsString('Keperluan', $content);
    }

    // 15. Ekspor PDF

    public function test_pdf_export_returns_application_pdf(): void
    {
        Transaction::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('transactions.recap-export-pdf'));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    // 16. Hasil kosong tidak error

    public function test_empty_result_does_not_error(): void
    {
        $page = $this->actingAs($this->admin)
            ->get(route('transactions.recap', ['category_id' => 999999]));
        $page->assertStatus(200);
        $page->assertSee('Tidak Ada Data');

        $pdf = $this->actingAs($this->admin)
            ->get(route('transactions.recap-export-pdf', ['category_id' => 999999]));
        $pdf->assertStatus(200);
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));

        $csv = $this->actingAs($this->admin)
            ->get(route('transactions.recap-export-csv', ['category_id' => 999999]));
        $csv->assertStatus(200);
        $this->assertStringContainsString('Tidak ada data', $csv->streamedContent());
    }

    public function test_empty_database_does_not_error(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transactions.recap'));

        $response->assertStatus(200);
        $response->assertSee('Tidak Ada Data');
    }

    // 17-18. Read-only: tidak mengubah aset/transaksi/AssetLog/ActivityLog

    public function test_opening_and_exporting_recap_does_not_change_data(): void
    {
        $asset = Asset::factory()->tersedia()->create(['kode_barang' => 'REKAP-NOCHG-01']);
        Transaction::factory()->create(['asset_id' => $asset->id, 'status_peminjaman' => 'Dikembalikan']);

        $assetLogCountBefore = AssetLog::count();
        $activityLogCountBefore = ActivityLog::count();
        $transactionCountBefore = Transaction::count();

        $this->actingAs($this->admin)->get(route('transactions.recap'));
        $this->actingAs($this->admin)->get(route('transactions.recap-export-pdf'));
        $this->actingAs($this->admin)->get(route('transactions.recap-export-csv'));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
        ]);
        $this->assertSame($assetLogCountBefore, AssetLog::count());
        $this->assertSame($activityLogCountBefore, ActivityLog::count());
        $this->assertSame($transactionCountBefore, Transaction::count());
    }
}
