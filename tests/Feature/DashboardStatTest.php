<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_dashboard_kategori_chart_counts_only_active_assets(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Alpha Kategori']);
        $kategoriB = Category::factory()->create(['nama' => 'Beta Kategori']);

        Asset::factory()->tersedia()->create(['category_id' => $kategoriA->id]);
        Asset::factory()->dipinjam()->create(['category_id' => $kategoriA->id]);
        Asset::factory(2)->disposed()->create(['category_id' => $kategoriA->id]);
        Asset::factory()->disposed()->create(['category_id' => $kategoriB->id]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);

        // Catatan: migrasi 2026_07_21_061022 ikut menyisipkan kategori default
        // (Elektronik/Mebel/Buku) saat migrate:fresh, jadi labels berisi lebih dari
        // dua kategori. Yang diuji: nilai hitung pada kategori milik test ini.
        $labels = $response->viewData('kategoriLabels')->all();
        $data = $response->viewData('kategoriData')->all();

        // Kategori A: 2 aset AKTIF (2 aset Disposed TIDAK dihitung). Kategori B: 0 aktif.
        $this->assertSame(2, $data[array_search('Alpha Kategori', $labels, true)]);
        $this->assertSame(0, $data[array_search('Beta Kategori', $labels, true)]);

        // Total seluruh chart = hanya aset aktif (tidak ada satupun Disposed terhitung).
        $this->assertSame(2, array_sum($data));
    }

    public function test_dashboard_lokasi_chart_counts_only_active_assets(): void
    {
        $lokasiA = Location::factory()->create(['nama' => 'Alpha Lokasi']);
        $lokasiB = Location::factory()->create(['nama' => 'Beta Lokasi']);

        Asset::factory()->tersedia()->create(['location_id' => $lokasiA->id]);
        Asset::factory(3)->disposed()->create(['location_id' => $lokasiA->id]);
        Asset::factory()->disposed()->create(['location_id' => $lokasiB->id]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);

        $labels = $response->viewData('lokasiLabels')->all();
        $data = $response->viewData('lokasiData')->all();

        // Lokasi A: 1 aset AKTIF (3 Disposed tidak dihitung). Lokasi B: 0 aktif.
        $this->assertSame(1, $data[array_search('Alpha Lokasi', $labels, true)]);
        $this->assertSame(0, $data[array_search('Beta Lokasi', $labels, true)]);

        // Total seluruh chart = hanya aset aktif.
        $this->assertSame(1, array_sum($data));
    }

    public function test_dashboard_recent_assets_excludes_disposed_assets(): void
    {
        // Aset aktif dibuat lebih dulu, lalu aset Disposed setelahnya — tanpa filter
        // status, urutan latest() (created_at/id desc) akan menaruh aset Disposed
        // di urutan teratas dan ikut masuk ke daftar "Aset Terbaru".
        $aktif1 = Asset::factory()->tersedia()->create(['kode_barang' => 'RCT-AKT-001']);
        $aktif2 = Asset::factory()->dipinjam()->create(['kode_barang' => 'RCT-AKT-002']);
        Asset::factory()->disposed()->create(['kode_barang' => 'RCT-DSP-001']);
        Asset::factory()->disposed()->create(['kode_barang' => 'RCT-DSP-002']);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);

        $codes = $response->viewData('recentAssets')->pluck('kode_barang')->all();

        // Aset aktif tetap muncul...
        $this->assertContains('RCT-AKT-001', $codes);
        $this->assertContains('RCT-AKT-002', $codes);

        // ...dan aset Disposed tidak pernah muncul di "Aset Terbaru".
        $this->assertNotContains('RCT-DSP-001', $codes);
        $this->assertNotContains('RCT-DSP-002', $codes);
    }
}
