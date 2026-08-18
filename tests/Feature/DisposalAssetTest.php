<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\MaintenanceSchedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposalAssetTest extends TestCase
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'tanggal_penghapusan' => now()->format('Y-m-d'),
            'alasan' => 'Rusak Berat',
            'keterangan' => 'Sudah tidak dapat diperbaiki.',
        ], $overrides);
    }

    // A. Penghapusan berhasil

    public function test_admin_can_process_disposal_of_available_asset(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertRedirect(route('assets.show', $asset));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Disposed',
        ]);

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'penghapusan',
            'user_id' => $this->admin->id,
        ]);
    }

    // B. Histori

    public function test_disposal_preserves_previous_history_and_creates_no_fake_logs(): void
    {
        $asset = Asset::factory()->tersedia()->create();
        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
            'deskripsi' => 'Perpindahan lokasi sebelumnya.',
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
            'deskripsi' => 'Perpindahan lokasi sebelumnya.',
        ]);

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'penghapusan',
        ]);

        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'hilang',
        ]);

        $this->assertDatabaseCount('asset_logs', 2);
    }

    // C. Authorization

    public function test_staff_cannot_process_disposal(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->staff)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertStatus(403);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_guest_cannot_process_disposal(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
        ]);
    }

    // D. Invalid state

    public function test_already_disposed_asset_cannot_be_disposed_again(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Disposed',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_dipinjam_asset_cannot_be_disposed(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Dipinjam',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_perbaikan_asset_with_active_maintenance_cannot_be_disposed(): void
    {
        $asset = Asset::factory()->perbaikan()->create();
        MaintenanceSchedule::factory()->dikerjakan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Perbaikan',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_perbaikan_asset_without_active_maintenance_can_be_disposed(): void
    {
        $asset = Asset::factory()->perbaikan()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertRedirect(route('assets.show', $asset));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Disposed',
        ]);
    }

    public function test_hilang_asset_can_be_disposed(): void
    {
        $asset = Asset::factory()->hilang()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $response->assertRedirect(route('assets.show', $asset));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Disposed',
        ]);
    }

    // E. Setelah Dihapuskan

    public function test_disposed_asset_cannot_be_borrowed(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('transactions.store'), [
                'asset_id' => $asset->id,
                'nama_peminjam' => 'Budi',
                'keperluan' => 'Praktikum',
                'tanggal_pinjam' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('asset_id');

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Disposed']);
    }

    public function test_disposed_asset_cannot_enter_maintenance(): void
    {
        $asset = Asset::factory()->disposed()->create();
        $schedule = MaintenanceSchedule::factory()->dijadwalkan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('maintenance.start', $schedule));

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Disposed']);
        $this->assertDatabaseHas('maintenance_schedules', ['id' => $schedule->id, 'status' => 'Dijadwalkan']);
    }

    public function test_disposed_asset_cannot_be_reported_lost(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-lost', $asset), [
                'tanggal_kehilangan' => now()->format('Y-m-d'),
                'keterangan' => 'Percobaan.',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Disposed']);
    }

    public function test_disposed_asset_cannot_be_marked_found(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.mark-found', $asset), [
                'tanggal_ditemukan' => now()->format('Y-m-d'),
                'lokasi_ditemukan' => 'Lab Komputer',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Disposed']);
    }

    public function test_processing_disposal_does_not_create_mutasi_log(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $this->actingAs($this->admin)
            ->post(route('assets.process-disposal', $asset), $this->validPayload());

        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
        ]);
    }

    // F. Edit

    public function test_master_field_can_still_be_edited_on_disposed_asset_and_status_unaffected(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => 'Nama Diperbarui',
                'category_id' => $asset->category_id,
                'location_id' => $asset->location_id,
                'kondisi' => $asset->kondisi,
                'status' => 'Tersedia', // raw attempt — harus tetap diabaikan
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'nama_barang' => 'Nama Diperbarui',
            'status' => 'Disposed',
        ]);
    }

    public function test_disposed_asset_location_cannot_be_mutated_via_direct_request(): void
    {
        $asset = Asset::factory()->disposed()->create();
        $newLocation = \App\Models\Location::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => $asset->nama_barang,
                'category_id' => $asset->category_id,
                'location_id' => $newLocation->id,
                'kondisi' => $asset->kondisi,
            ]);

        $response->assertSessionHasErrors('location_id');
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'location_id' => $asset->location_id,
            'status' => 'Disposed',
        ]);
        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
        ]);
    }

    public function test_disposed_asset_cannot_be_soft_deleted_from_archive(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('assets.destroy', $asset));

        $response->assertRedirect(route('assets.archive'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Disposed', 'deleted_at' => null]);
    }

    public function test_disposed_asset_cannot_be_returned_from_legacy_active_transaction(): void
    {
        $asset = Asset::factory()->disposed()->create();
        $transaction = Transaction::factory()->create([
            'asset_id' => $asset->id,
            'status_peminjaman' => 'Dipinjam',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('transactions.return', $transaction));

        $response->assertSessionHasErrors('transaction');
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Disposed']);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status_peminjaman' => 'Dipinjam']);
    }

    // G. Export/filter

    /**
     * DIUBAH pada iterasi "Pemisahan Aset Aktif & Arsip": sebelumnya test ini
     * memastikan aset Disposed MASIH bisa difilter/terlihat di Daftar Aset.
     * Requirement itu sudah digantikan — Daftar Aset sekarang khusus aset aktif,
     * dan aset Disposed pindah ke halaman Arsip Aset. Coverage-nya tidak hilang,
     * cuma dipindah sasaran: dipastikan tetap bisa ditemukan lewat Arsip
     * (lihat ArchiveAssetTest), dan di sini dipastikan sudah tidak bocor
     * ke Daftar Aset walau di-request eksplisit lewat query string.
     */
    public function test_disposed_asset_is_not_visible_in_active_asset_index(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('assets.index', ['status' => 'Disposed', 'f' => 1]));

        $response->assertStatus(200);
        $response->assertDontSee($asset->kode_barang);
    }

    /**
     * DIUBAH pada iterasi "Export Daftar Aset hanya aset aktif": sebelumnya test ini
     * membuat aset Disposed lalu memastikan export CSV Daftar Aset menampilkan label
     * "Dihapuskan" (bukan "Disposed") — artinya aset Disposed ikut terbawa ke export
     * aktif. Business rule sekarang: export Daftar Aset = halaman Daftar Aset = hanya
     * aset aktif, jadi aset Disposed TIDAK boleh muncul sama sekali. Coverage label
     * Indonesia tidak hilang — dipindah ke export Arsip (ArchiveAssetTest
     * test_archive_csv_export_contains_only_disposed_assets: "Dihapuskan" ada,
     * "Disposed" tidak ada).
     */
    public function test_export_csv_excludes_disposed_assets(): void
    {
        $aktif = Asset::factory()->tersedia()->create(['kode_barang' => 'AKT-EXP-001']);
        Asset::factory()->disposed()->create(['kode_barang' => 'DSP-001']);

        $response = $this->actingAs($this->admin)->get(route('assets.export-csv'));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString($aktif->kode_barang, $content);
        $this->assertStringNotContainsString('DSP-001', $content);
    }

    // H. Duplicate

    public function test_processing_disposal_twice_does_not_create_duplicate_log(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $this->actingAs($this->admin)->post(route('assets.process-disposal', $asset), $this->validPayload());
        $this->actingAs($this->admin)->post(route('assets.process-disposal', $asset), $this->validPayload());

        $this->assertDatabaseCount('asset_logs', 1);
    }
}
