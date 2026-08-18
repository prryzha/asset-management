<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetLabelMassalTest extends TestCase
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
     * Isi PDF tidak bisa di-assert langsung karena DomPDF mengompresi stream-nya.
     * Supaya test benar-benar memeriksa DATA yang masuk (bukan cuma status 200),
     * dipakai event view: render label massal selalu lewat view `pdf.labels`,
     * jadi koleksi $assets yang dikirim ke view itulah dataset final PDF-nya.
     *
     * @return array<int, string> daftar kode_barang yang benar-benar dicetak
     */
    private function kodeYangDicetak(callable $request): array
    {
        $tercetak = [];

        \Illuminate\Support\Facades\View::composer('pdf.labels', function ($view) use (&$tercetak) {
            $tercetak = collect($view->getData()['assets'])->pluck('kode_barang')->all();
        });

        $request();

        return $tercetak;
    }

    // 1-3. Authorization

    public function test_guest_cannot_print_bulk_labels(): void
    {
        Asset::factory()->tersedia()->create();

        $response = $this->get(route('assets.label-massal-pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_print_bulk_labels(): void
    {
        Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.label-massal-pdf'));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_staff_can_print_bulk_labels_same_as_individual_label(): void
    {
        Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->staff)->get(route('assets.label-massal-pdf'));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    // 4-5. PDF dihasilkan untuk satu maupun banyak aset

    public function test_multiple_assets_produce_single_pdf_containing_all_of_them(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'LBL-001']);
        Asset::factory()->dipinjam()->create(['kode_barang' => 'LBL-002']);
        Asset::factory()->perbaikan()->create(['kode_barang' => 'LBL-003']);

        $kode = $this->kodeYangDicetak(function () {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf'))
                ->assertStatus(200);
        });

        $this->assertSame(['LBL-001', 'LBL-002', 'LBL-003'], $kode);
    }

    // 6-11. Filter

    public function test_category_filter_is_applied(): void
    {
        $elektronik = Category::factory()->create(['nama' => 'Elektronik Label']);
        $mebel = Category::factory()->create(['nama' => 'Mebel Label']);

        Asset::factory()->tersedia()->create(['kode_barang' => 'CAT-001', 'category_id' => $elektronik->id]);
        Asset::factory()->tersedia()->create(['kode_barang' => 'CAT-002', 'category_id' => $mebel->id]);

        $kode = $this->kodeYangDicetak(function () use ($elektronik) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['category_id' => $elektronik->id]))
                ->assertStatus(200);
        });

        $this->assertSame(['CAT-001'], $kode);
    }

    public function test_location_filter_is_applied(): void
    {
        $lab = Location::factory()->create(['nama' => 'Lab Label']);
        $gudang = Location::factory()->create(['nama' => 'Gudang Label']);

        Asset::factory()->tersedia()->create(['kode_barang' => 'LOC-001', 'location_id' => $lab->id]);
        Asset::factory()->tersedia()->create(['kode_barang' => 'LOC-002', 'location_id' => $gudang->id]);

        $kode = $this->kodeYangDicetak(function () use ($lab) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['location_id' => $lab->id]))
                ->assertStatus(200);
        });

        $this->assertSame(['LOC-001'], $kode);
    }

    public function test_kondisi_filter_is_applied(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'KND-001', 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'KND-002', 'kondisi' => 'Rusak Berat']);

        $kode = $this->kodeYangDicetak(function () {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['kondisi' => 'Baik']))
                ->assertStatus(200);
        });

        $this->assertSame(['KND-001'], $kode);
    }

    public function test_status_filter_is_applied(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'STS-001']);
        Asset::factory()->dipinjam()->create(['kode_barang' => 'STS-002']);

        $kode = $this->kodeYangDicetak(function () {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['status' => 'Dipinjam']))
                ->assertStatus(200);
        });

        $this->assertSame(['STS-002'], $kode);
    }

    public function test_search_is_applied(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'SRC-001', 'nama_barang' => 'Proyektor Aula']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'SRC-002', 'nama_barang' => 'Kursi Kayu']);

        $kode = $this->kodeYangDicetak(function () {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['search' => 'Proyektor Aula']))
                ->assertStatus(200);
        });

        $this->assertSame(['SRC-001'], $kode);
    }

    public function test_combined_filters_are_applied(): void
    {
        $kategori = Category::factory()->create(['nama' => 'Elektronik Kombinasi']);
        $lokasi = Location::factory()->create(['nama' => 'Lab Kombinasi']);

        // Hanya aset ini yang memenuhi ketiga filter sekaligus.
        Asset::factory()->tersedia()->create([
            'kode_barang' => 'MIX-001', 'category_id' => $kategori->id,
            'location_id' => $lokasi->id, 'kondisi' => 'Baik',
        ]);
        // Kategori beda.
        Asset::factory()->tersedia()->create([
            'kode_barang' => 'MIX-002', 'location_id' => $lokasi->id, 'kondisi' => 'Baik',
        ]);
        // Kondisi beda.
        Asset::factory()->tersedia()->create([
            'kode_barang' => 'MIX-003', 'category_id' => $kategori->id,
            'location_id' => $lokasi->id, 'kondisi' => 'Rusak Berat',
        ]);

        $kode = $this->kodeYangDicetak(function () use ($kategori, $lokasi) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', [
                    'category_id' => $kategori->id,
                    'location_id' => $lokasi->id,
                    'kondisi' => 'Baik',
                ]))
                ->assertStatus(200);
        });

        $this->assertSame(['MIX-001'], $kode);
    }

    // 12-13. Disposed selalu dikecualikan

    public function test_disposed_assets_are_never_printed(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-001']);
        Asset::factory()->disposed()->create(['kode_barang' => 'DSP-001']);

        $kode = $this->kodeYangDicetak(function () {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf'))
                ->assertStatus(200);
        });

        $this->assertSame(['AKT-001'], $kode);
    }

    public function test_raw_status_disposed_does_not_leak_disposed_assets(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-002']);
        Asset::factory()->disposed()->create(['kode_barang' => 'DSP-002']);

        // ?status=Disposed harus menghasilkan nol aset (bukan membocorkan arsip),
        // sehingga jatuh ke penanganan hasil kosong.
        $response = $this->actingAs($this->admin)
            ->get(route('assets.label-massal-pdf', ['status' => 'Disposed']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_disposed_asset_cannot_be_printed_even_when_id_is_supplied_directly(): void
    {
        $aktif = Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-003']);
        $disposed = Asset::factory()->disposed()->create(['kode_barang' => 'DSP-003']);

        $kode = $this->kodeYangDicetak(function () use ($aktif, $disposed) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['ids' => [$aktif->id, $disposed->id]]))
                ->assertStatus(200);
        });

        $this->assertSame(['AKT-003'], $kode);
    }

    // 14. Hasil kosong tidak error

    public function test_empty_result_redirects_with_message_instead_of_broken_pdf(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'ADA-001']);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.label-massal-pdf', ['search' => 'tidak-ada-aset-seperti-ini']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_empty_database_does_not_error(): void
    {
        $response = $this->actingAs($this->admin)->get(route('assets.label-massal-pdf'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // 16. Mode terpilih

    public function test_selected_ids_mode_prints_only_selected_assets(): void
    {
        $a = Asset::factory()->tersedia()->create(['kode_barang' => 'SEL-001']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'SEL-002']);
        $c = Asset::factory()->tersedia()->create(['kode_barang' => 'SEL-003']);

        $kode = $this->kodeYangDicetak(function () use ($a, $c) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['ids' => [$a->id, $c->id]]))
                ->assertStatus(200);
        });

        $this->assertSame(['SEL-001', 'SEL-003'], $kode);
    }

    public function test_selected_ids_mode_ignores_page_filters_like_existing_export(): void
    {
        $kategori = Category::factory()->create(['nama' => 'Kategori Terpilih']);
        $lain = Asset::factory()->tersedia()->create(['kode_barang' => 'IDS-001']);

        // Filter kategori sengaja tidak cocok dengan aset yang dipilih —
        // perilaku export existing: mode "terpilih" melewati filter halaman.
        $kode = $this->kodeYangDicetak(function () use ($lain, $kategori) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', [
                    'ids' => [$lain->id],
                    'category_id' => $kategori->id,
                ]))
                ->assertStatus(200);
        });

        $this->assertSame(['IDS-001'], $kode);
    }

    // 17-18. Read-only: tidak mengubah data & tidak membuat log

    public function test_printing_labels_does_not_modify_assets_or_create_logs(): void
    {
        $asset = Asset::factory()->tersedia()->create(['kode_barang' => 'RO-001']);
        $sebelum = $asset->fresh()->toArray();

        $this->actingAs($this->admin)
            ->get(route('assets.label-massal-pdf'))
            ->assertStatus(200);

        $this->assertSame($sebelum, $asset->fresh()->toArray());
        $this->assertSame(0, AssetLog::count());
        $this->assertSame(0, ActivityLog::count());
    }

    // Konsistensi dataset dengan Daftar Aset / export

    public function test_label_dataset_matches_asset_index_dataset_for_same_filter(): void
    {
        $kategori = Category::factory()->create(['nama' => 'Kategori Sinkron']);
        // kondisi dipatok "Baik": AssetFactory mengacak kondisi, dan aset aktif
        // ber-kondisi "Rusak Berat" ikut muncul di dropdown notifikasi header yang
        // dirender di semua halaman — bikin assertDontSee di bawah gagal acak
        // (flaky) padahal dataset tabelnya sendiri sudah benar.
        Asset::factory()->tersedia()->create(['kode_barang' => 'SYN-001', 'category_id' => $kategori->id, 'kondisi' => 'Baik']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'SYN-002', 'kondisi' => 'Baik']);
        Asset::factory()->disposed()->create(['kode_barang' => 'SYN-003', 'category_id' => $kategori->id, 'kondisi' => 'Baik']);

        $kode = $this->kodeYangDicetak(function () use ($kategori) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-massal-pdf', ['category_id' => $kategori->id]))
                ->assertStatus(200);
        });

        $index = $this->actingAs($this->admin)
            ->get(route('assets.index', ['category_id' => $kategori->id, 'f' => 1]));

        $index->assertSee('SYN-001');
        $index->assertDontSee('SYN-002');
        $index->assertDontSee('SYN-003');
        $this->assertSame(['SYN-001'], $kode);
    }
}
