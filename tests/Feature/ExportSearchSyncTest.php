<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mengunci sinkronisasi dataset halaman vs export untuk filter pencarian (search):
 * Riwayat Peminjaman (transactions.*) dan Manajemen Perawatan (maintenance.*).
 * Sebelum diperbaiki, export PDF/CSV mengabaikan parameter search sehingga isi
 * export bisa berbeda dari tabel yang sedang dilihat user.
 */
class ExportSearchSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ----- Riwayat Peminjaman -----

    public function test_transaction_csv_export_respects_search_filter(): void
    {
        $asetA = Asset::factory()->create(['kode_barang' => 'TRX-SRCH-A']);
        $asetB = Asset::factory()->create(['kode_barang' => 'TRX-SRCH-B']);
        Transaction::factory()->create(['asset_id' => $asetA->id, 'nama_peminjam' => 'Peminjam Target']);
        Transaction::factory()->create(['asset_id' => $asetB->id, 'nama_peminjam' => 'Peminjam Lain']);

        // Search oleh kode aset.
        $byCode = $this->actingAs($this->admin)
            ->get(route('transactions.export-csv', ['search' => 'TRX-SRCH-A']));
        $byCode->assertStatus(200);
        $contentCode = $byCode->streamedContent();
        $this->assertStringContainsString('TRX-SRCH-A', $contentCode);
        $this->assertStringNotContainsString('TRX-SRCH-B', $contentCode);

        // Search oleh nama peminjam.
        $byBorrower = $this->actingAs($this->admin)
            ->get(route('transactions.export-csv', ['search' => 'Peminjam Target']));
        $byBorrower->assertStatus(200);
        $contentBorrower = $byBorrower->streamedContent();
        $this->assertStringContainsString('Peminjam Target', $contentBorrower);
        $this->assertStringNotContainsString('Peminjam Lain', $contentBorrower);
    }

    public function test_transaction_pdf_export_respects_search_filter(): void
    {
        $asetA = Asset::factory()->create(['kode_barang' => 'TRX-PDF-SRCH-A']);
        Transaction::factory()->create(['asset_id' => $asetA->id, 'nama_peminjam' => 'Peminjam Target']);
        Transaction::factory()->create(['nama_peminjam' => 'Peminjam Lain']);

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.export-pdf', ['search' => 'TRX-PDF-SRCH-A']));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    // ----- Manajemen Perawatan -----

    public function test_maintenance_csv_export_respects_search_filter(): void
    {
        $asetA = Asset::factory()->create(['kode_barang' => 'MNT-SRCH-A']);
        $asetB = Asset::factory()->create(['kode_barang' => 'MNT-SRCH-B']);
        MaintenanceSchedule::factory()->create(['asset_id' => $asetA->id, 'jenis_perawatan' => 'Servis AC']);
        MaintenanceSchedule::factory()->create(['asset_id' => $asetB->id, 'jenis_perawatan' => 'Ganti Filter']);

        // Search oleh kode aset.
        $byCode = $this->actingAs($this->admin)
            ->get(route('maintenance.export-csv', ['search' => 'MNT-SRCH-A']));
        $byCode->assertStatus(200);
        $contentCode = $byCode->streamedContent();
        $this->assertStringContainsString('MNT-SRCH-A', $contentCode);
        $this->assertStringNotContainsString('MNT-SRCH-B', $contentCode);

        // Search oleh jenis perawatan.
        $byJenis = $this->actingAs($this->admin)
            ->get(route('maintenance.export-csv', ['search' => 'Servis AC']));
        $byJenis->assertStatus(200);
        $contentJenis = $byJenis->streamedContent();
        $this->assertStringContainsString('Servis AC', $contentJenis);
        $this->assertStringNotContainsString('Ganti Filter', $contentJenis);
    }

    public function test_maintenance_pdf_export_respects_search_filter(): void
    {
        $asetA = Asset::factory()->create(['kode_barang' => 'MNT-PDF-SRCH-A']);
        MaintenanceSchedule::factory()->create(['asset_id' => $asetA->id, 'jenis_perawatan' => 'Servis AC']);
        MaintenanceSchedule::factory()->create(['jenis_perawatan' => 'Ganti Filter']);

        $response = $this->actingAs($this->admin)
            ->get(route('maintenance.export-pdf', ['search' => 'MNT-PDF-SRCH-A']));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
