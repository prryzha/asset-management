<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_starting_maintenance_sets_asset_status_to_perbaikan_and_logs_perawatan(): void
    {
        $asset = Asset::factory()->tersedia()->create();
        $schedule = MaintenanceSchedule::factory()->dijadwalkan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('maintenance.start', $schedule));

        $response->assertRedirect(route('maintenance.index'));

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $schedule->id,
            'status' => 'Dikerjakan',
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Perbaikan',
        ]);

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'perawatan',
        ]);
    }

    public function test_completing_maintenance_with_good_condition_sets_asset_status_tersedia(): void
    {
        $asset = Asset::factory()->perbaikan()->create();
        $schedule = MaintenanceSchedule::factory()->dikerjakan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->put(route('maintenance.complete', $schedule), [
                'kondisi' => 'Baik',
            ]);

        $response->assertRedirect(route('maintenance.index'));

        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $schedule->id,
            'status' => 'Selesai',
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Tersedia',
            'kondisi' => 'Baik',
        ]);

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'perawatan',
        ]);
    }

    public function test_completing_maintenance_with_rusak_berat_keeps_asset_in_perbaikan(): void
    {
        $asset = Asset::factory()->perbaikan()->create();
        $schedule = MaintenanceSchedule::factory()->dikerjakan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->put(route('maintenance.complete', $schedule), [
                'kondisi' => 'Rusak Berat',
            ]);

        $response->assertRedirect(route('maintenance.index'));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Perbaikan',
            'kondisi' => 'Rusak Berat',
        ]);
    }

    public function test_lost_asset_cannot_be_scheduled_for_maintenance_via_direct_request(): void
    {
        $asset = Asset::factory()->hilang()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('maintenance.store'), [
                'asset_id' => $asset->id,
                'jenis_perawatan' => 'Servis',
                'tanggal_jadwal' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('asset_id');
        $this->assertDatabaseCount('maintenance_schedules', 0);
        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_disposed_asset_cannot_be_scheduled_for_maintenance_via_direct_request(): void
    {
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('maintenance.store'), [
                'asset_id' => $asset->id,
                'jenis_perawatan' => 'Servis',
                'tanggal_jadwal' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('asset_id');
        $this->assertDatabaseCount('maintenance_schedules', 0);
    }

    public function test_borrowed_asset_cannot_be_scheduled_for_maintenance_via_direct_request(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('maintenance.store'), [
                'asset_id' => $asset->id,
                'jenis_perawatan' => 'Servis',
                'tanggal_jadwal' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('asset_id');
        $this->assertDatabaseCount('maintenance_schedules', 0);
    }

    public function test_second_active_maintenance_cannot_be_started_for_same_asset(): void
    {
        $asset = Asset::factory()->perbaikan()->create();
        MaintenanceSchedule::factory()->dikerjakan()->create(['asset_id' => $asset->id]);
        $scheduled = MaintenanceSchedule::factory()->dijadwalkan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('maintenance.start', $scheduled));

        $response->assertSessionHasErrors('maintenance');
        $this->assertDatabaseHas('maintenance_schedules', [
            'id' => $scheduled->id,
            'status' => 'Dijadwalkan',
        ]);
    }

    public function test_disposed_asset_cannot_complete_legacy_active_maintenance(): void
    {
        $asset = Asset::factory()->disposed()->create();
        $schedule = MaintenanceSchedule::factory()->dikerjakan()->create(['asset_id' => $asset->id]);

        $response = $this->actingAs($this->admin)
            ->put(route('maintenance.complete', $schedule), ['kondisi' => 'Baik']);

        $response->assertSessionHasErrors('maintenance');
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'status' => 'Disposed']);
        $this->assertDatabaseHas('maintenance_schedules', ['id' => $schedule->id, 'status' => 'Dikerjakan']);
    }
}
