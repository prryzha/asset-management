<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tests\TestCase;

class AssetLabelTest extends TestCase
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
     * Sama seperti pola di AssetLabelMassalTest: PDF binary DomPDF tidak bisa
     * di-assert langsung (dikompresi), jadi label individual selalu lewat view
     * `pdf.label` — data ($asset) yang dikirim ke view itulah yang dicetak.
     */
    private function assetYangDikirimKeLabel(callable $request): ?Asset
    {
        $captured = null;

        View::composer('pdf.label', function ($view) use (&$captured) {
            $captured = $view->getData()['asset'];
        });

        $request();

        return $captured;
    }

    // 1-2. Guest tidak dapat mengakses

    public function test_guest_cannot_access_qr_code(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->get(route('assets.qr-code', $asset));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_label_pdf(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->get(route('assets.label-pdf', $asset));

        $response->assertRedirect(route('login'));
    }

    // 3-6. Staff & Admin dapat mengakses keduanya (sama-sama grup `auth`, tanpa role gate)

    public function test_staff_can_access_qr_code(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->staff)->get(route('assets.qr-code', $asset));

        $response->assertOk();
    }

    public function test_staff_can_access_label_pdf(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->staff)->get(route('assets.label-pdf', $asset));

        $response->assertOk();
    }

    public function test_admin_can_access_qr_code(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.qr-code', $asset));

        $response->assertOk();
    }

    public function test_admin_can_access_label_pdf(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.label-pdf', $asset));

        $response->assertOk();
    }

    // 7-8. QR: content-type dan payload URL yang benar

    public function test_qr_code_response_has_svg_content_type(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.qr-code', $asset));

        $response->assertOk();
        $this->assertSame('image/svg+xml', $response->headers->get('content-type'));
    }

    /**
     * Generator QR (bacon-qr-code lewat simple-qrcode) deterministik: input yang
     * sama selalu menghasilkan SVG byte-identik. Jadi meregenerasi SVG dengan
     * parameter yang sama persis dengan controller (format/size/margin) untuk
     * URL aset yang benar adalah bukti kuat bahwa payload QR memang URL
     * `assets.show` aset tersebut — bukan cuma HTTP 200.
     *
     * Dibandingkan juga dengan SVG aset LAIN supaya assertion benar-benar
     * mendiskriminasi (bukan cocok karena kebetulan controller selalu memakai
     * aset pertama, dsb).
     */
    public function test_qr_code_contains_correct_asset_detail_url(): void
    {
        $assetA = Asset::factory()->tersedia()->create(['kode_barang' => 'QR-001']);
        $assetB = Asset::factory()->tersedia()->create(['kode_barang' => 'QR-002']);

        $response = $this->actingAs($this->admin)->get(route('assets.qr-code', $assetA));
        $response->assertOk();

        $expectedForA = (string) QrCode::format('svg')->size(240)->margin(1)->generate(route('assets.show', $assetA));
        $expectedForB = (string) QrCode::format('svg')->size(240)->margin(1)->generate(route('assets.show', $assetB));

        $this->assertSame($expectedForA, $response->getContent());
        $this->assertNotSame($expectedForB, $response->getContent());
    }

    // 9-10. Label PDF: content-type dan aset yang benar

    public function test_label_pdf_response_has_pdf_content_type(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.label-pdf', $asset));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_label_pdf_uses_the_requested_asset(): void
    {
        $assetA = Asset::factory()->tersedia()->create(['kode_barang' => 'LBL-IND-001']);
        Asset::factory()->tersedia()->create(['kode_barang' => 'LBL-IND-002']);

        $dikirim = $this->assetYangDikirimKeLabel(function () use ($assetA) {
            $this->actingAs($this->admin)
                ->get(route('assets.label-pdf', $assetA))
                ->assertOk();
        });

        $this->assertNotNull($dikirim);
        $this->assertSame('LBL-IND-001', $dikirim->kode_barang);
    }

    // 11. Aset Disposed — behavior existing (MASIH bisa dicetak), jangan diubah

    public function test_disposed_asset_qr_code_is_still_accessible(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.qr-code', $asset));

        $response->assertOk();
    }

    public function test_disposed_asset_label_pdf_is_still_accessible(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)->get(route('assets.label-pdf', $asset));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    // 12. ID/aset tidak valid -> 404 (route-model binding bawaan Laravel)

    public function test_qr_code_for_nonexistent_asset_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->get(route('assets.qr-code', ['asset' => 999999]));

        $response->assertNotFound();
    }

    public function test_label_pdf_for_nonexistent_asset_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->get(route('assets.label-pdf', ['asset' => 999999]));

        $response->assertNotFound();
    }

    public function test_qr_code_for_soft_deleted_asset_returns_404(): void
    {
        $asset = Asset::factory()->tersedia()->create();
        $asset->delete();

        $response = $this->actingAs($this->admin)->get(route('assets.qr-code', ['asset' => $asset->id]));

        $response->assertNotFound();
    }

    public function test_label_pdf_for_soft_deleted_asset_returns_404(): void
    {
        $asset = Asset::factory()->tersedia()->create();
        $asset->delete();

        $response = $this->actingAs($this->admin)->get(route('assets.label-pdf', ['asset' => $asset->id]));

        $response->assertNotFound();
    }
}
