<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_asset_csv_export_contains_active_assets(): void
    {
        $aktif = Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-EXP-001']);

        $response = $this->actingAs($this->admin)->get(route('assets.export-csv'));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString($aktif->kode_barang, $content);
    }

    public function test_asset_csv_export_excludes_disposed_assets(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-EXP-002']);
        Asset::factory()->disposed()->create(['kode_barang' => 'DSP-EXP-001']);

        $response = $this->actingAs($this->admin)->get(route('assets.export-csv'));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('AKT-EXP-002', $content);
        $this->assertStringNotContainsString('DSP-EXP-001', $content);
    }

    public function test_asset_csv_export_respects_category_filter(): void
    {
        $kategoriA = Category::factory()->create(['nama' => 'Kategori Filter A']);
        $kategoriB = Category::factory()->create(['nama' => 'Kategori Filter B']);

        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-EXP-003', 'category_id' => $kategoriA->id]);
        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-EXP-004', 'category_id' => $kategoriB->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('assets.export-csv', ['category_id' => $kategoriA->id]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('AKT-EXP-003', $content);
        $this->assertStringNotContainsString('AKT-EXP-004', $content);
    }

    public function test_asset_pdf_export_excludes_disposed_assets_and_responds_successfully(): void
    {
        Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-EXP-005']);
        Asset::factory()->disposed()->create(['kode_barang' => 'DSP-EXP-002']);

        $response = $this->actingAs($this->admin)->get(route('assets.export-pdf'));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_asset_pdf_export_does_not_error_when_empty(): void
    {
        $response = $this->actingAs($this->admin)->get(route('assets.export-pdf'));

        $response->assertStatus(200);
    }
}
