<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LostAssetTest extends TestCase
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

    // A. Report Lost

    public function test_admin_can_report_lost_asset(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-lost', $asset), [
                'tanggal_kehilangan' => now()->format('Y-m-d'),
                'keterangan' => 'Terakhir terlihat di meja guru piket.',
            ]);

        $response->assertRedirect(route('assets.show', $asset));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Hilang',
        ]);

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'hilang',
            'user_id' => $this->admin->id,
        ]);

        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
        ]);
    }

    // B. Duplicate Lost

    public function test_lost_asset_cannot_be_reported_lost_again(): void
    {
        $asset = Asset::factory()->hilang()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-lost', $asset), [
                'tanggal_kehilangan' => now()->format('Y-m-d'),
                'keterangan' => 'Percobaan lapor ulang.',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Hilang',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    // C. Invalid State

    public function test_disposed_asset_cannot_be_reported_lost(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-lost', $asset), [
                'tanggal_kehilangan' => now()->format('Y-m-d'),
                'keterangan' => 'Percobaan.',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Disposed',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_dipinjam_asset_cannot_be_reported_lost(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-lost', $asset), [
                'tanggal_kehilangan' => now()->format('Y-m-d'),
                'keterangan' => 'Percobaan.',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Dipinjam',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_perbaikan_asset_cannot_be_reported_lost(): void
    {
        $asset = Asset::factory()->perbaikan()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-lost', $asset), [
                'tanggal_kehilangan' => now()->format('Y-m-d'),
                'keterangan' => 'Percobaan.',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Perbaikan',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    // D. Lost Asset Operations

    public function test_lost_asset_cannot_be_borrowed(): void
    {
        $asset = Asset::factory()->hilang()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('transactions.store'), [
                'asset_id' => $asset->id,
                'nama_peminjam' => 'Budi',
                'keperluan' => 'Praktikum',
                'tanggal_pinjam' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('asset_id');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Hilang',
        ]);
    }

    public function test_lost_asset_cannot_enter_maintenance(): void
    {
        $asset = Asset::factory()->hilang()->create();
        $schedule = MaintenanceSchedule::factory()->dijadwalkan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('maintenance.start', $schedule));

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Hilang',
        ]);

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $schedule->id,
            'status' => 'Dijadwalkan',
        ]);
    }

    // E. Found

    public function test_admin_can_mark_lost_asset_as_found(): void
    {
        $asset = Asset::factory()->hilang()->create();
        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'hilang',
            'deskripsi' => 'Dilaporkan hilang sebelumnya.',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.mark-found', $asset), [
                'tanggal_ditemukan' => now()->format('Y-m-d'),
                'lokasi_ditemukan' => 'Lab Komputer',
                'catatan' => 'Ditemukan di lemari arsip.',
            ]);

        $response->assertRedirect(route('assets.show', $asset));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
        ]);

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'ditemukan',
        ]);

        // Histori kehilangan sebelumnya tidak boleh hilang.
        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'hilang',
        ]);
    }

    // F. Duplicate Found

    public function test_tersedia_asset_cannot_be_marked_found(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.mark-found', $asset), [
                'tanggal_ditemukan' => now()->format('Y-m-d'),
                'lokasi_ditemukan' => 'Lab Komputer',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    // G. Mutasi Separation

    public function test_report_lost_and_mark_found_do_not_create_mutasi_log(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $this->actingAs($this->admin)->post(route('assets.report-lost', $asset), [
            'tanggal_kehilangan' => now()->format('Y-m-d'),
            'keterangan' => 'Percobaan.',
        ]);

        $this->actingAs($this->admin)->post(route('assets.mark-found', $asset), [
            'tanggal_ditemukan' => now()->format('Y-m-d'),
            'lokasi_ditemukan' => 'Lab Komputer',
        ]);

        $this->assertDatabaseHas('asset_logs', ['asset_id' => $asset->id, 'tipe' => 'hilang']);
        $this->assertDatabaseHas('asset_logs', ['asset_id' => $asset->id, 'tipe' => 'ditemukan']);
        $this->assertDatabaseMissing('asset_logs', ['asset_id' => $asset->id, 'tipe' => 'mutasi']);
    }

    // H. Master Edit

    public function test_master_field_can_still_be_edited_on_lost_asset_and_status_unaffected(): void
    {
        $asset = Asset::factory()->hilang()->create();

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
            'status' => 'Hilang',
        ]);
    }

    // I. Authorization

    public function test_guest_cannot_report_lost_asset(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->post(route('assets.report-lost', $asset), [
            'tanggal_kehilangan' => now()->format('Y-m-d'),
            'keterangan' => 'Percobaan.',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
        ]);
    }

    public function test_guest_cannot_mark_asset_found(): void
    {
        $asset = Asset::factory()->hilang()->create();

        $response = $this->post(route('assets.mark-found', $asset), [
            'tanggal_ditemukan' => now()->format('Y-m-d'),
            'lokasi_ditemukan' => 'Lab Komputer',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Hilang',
        ]);
    }

    public function test_staff_can_report_lost_asset_consistent_with_existing_permission_pattern(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->staff)
            ->post(route('assets.report-lost', $asset), [
                'tanggal_kehilangan' => now()->format('Y-m-d'),
                'keterangan' => 'Percobaan.',
            ]);

        $response->assertRedirect(route('assets.show', $asset));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Hilang',
        ]);
    }
}
