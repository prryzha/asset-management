<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetTest extends TestCase
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

    public function test_guest_cannot_access_asset_index(): void
    {
        $response = $this->get(route('assets.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_assets(): void
    {
        Asset::factory(3)->create();

        $response = $this->actingAs($this->staff)
            ->get(route('assets.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.create'));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_create_form(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('assets.create'));

        $response->assertStatus(200);
    }

    public function test_admin_can_store_asset(): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('assets.store'), [
                'kode_barang' => 'KMR-001',
                'nama_barang' => 'Komputer',
                'merk' => 'Dell',
                'category_id' => $category->id,
                'location_id' => $location->id,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'catatan' => 'Test',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'kode_barang' => 'KMR-001',
        ]);
    }

    public function test_creating_asset_without_status_defaults_to_tersedia(): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('assets.store'), [
                'kode_barang' => 'KMR-101',
                'nama_barang' => 'Komputer',
                'category_id' => $category->id,
                'location_id' => $location->id,
                'kondisi' => 'Baik',
            ]);

        $this->assertDatabaseHas('assets', [
            'kode_barang' => 'KMR-101',
            'status' => 'Tersedia',
        ]);
    }

    public function test_creating_asset_with_raw_status_dipinjam_still_defaults_to_tersedia(): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('assets.store'), [
                'kode_barang' => 'KMR-102',
                'nama_barang' => 'Komputer',
                'category_id' => $category->id,
                'location_id' => $location->id,
                'kondisi' => 'Baik',
                'status' => 'Dipinjam',
            ]);

        $this->assertDatabaseHas('assets', [
            'kode_barang' => 'KMR-102',
            'status' => 'Tersedia',
        ]);
    }

    public function test_creating_asset_with_raw_status_perbaikan_still_defaults_to_tersedia(): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('assets.store'), [
                'kode_barang' => 'KMR-103',
                'nama_barang' => 'Komputer',
                'category_id' => $category->id,
                'location_id' => $location->id,
                'kondisi' => 'Baik',
                'status' => 'Perbaikan',
            ]);

        $this->assertDatabaseHas('assets', [
            'kode_barang' => 'KMR-103',
            'status' => 'Tersedia',
        ]);
    }

    public function test_creating_asset_with_raw_status_hilang_still_defaults_to_tersedia(): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('assets.store'), [
                'kode_barang' => 'KMR-104',
                'nama_barang' => 'Komputer',
                'category_id' => $category->id,
                'location_id' => $location->id,
                'kondisi' => 'Baik',
                'status' => 'Hilang',
            ]);

        $this->assertDatabaseHas('assets', [
            'kode_barang' => 'KMR-104',
            'status' => 'Tersedia',
        ]);
    }

    public function test_creating_asset_with_raw_status_disposed_still_defaults_to_tersedia(): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('assets.store'), [
                'kode_barang' => 'KMR-105',
                'nama_barang' => 'Komputer',
                'category_id' => $category->id,
                'location_id' => $location->id,
                'kondisi' => 'Baik',
                'status' => 'Disposed',
            ]);

        $this->assertDatabaseHas('assets', [
            'kode_barang' => 'KMR-105',
            'status' => 'Tersedia',
        ]);
    }

    public function test_admin_can_update_asset(): void
    {
        $asset = Asset::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => 'Updated Name',
                'merk' => 'Updated',
                'category_id' => $asset->category_id,
                'location_id' => $asset->location_id,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'nama_barang' => 'Updated Name',
        ]);
    }

    public function test_updating_asset_without_location_id_field_does_not_error(): void
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->tersedia()->create(['location_id' => $location->id]);

        // Simulasi raw request yang tidak menyertakan location_id sama sekali
        // (bukan cuma kosong) — form asli selalu kirim field ini, tapi raw
        // request/API tidak wajib. Field ini "nullable" jadi harus aman.
        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => $asset->nama_barang,
                'category_id' => $asset->category_id,
                'kondisi' => $asset->kondisi,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
        ]);
    }

    public function test_report_damage_on_lost_asset_is_rejected(): void
    {
        $asset = Asset::factory()->hilang()->create(['kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-damage', $asset), [
                'kondisi' => 'Rusak Berat',
                'deskripsi' => 'Percobaan pada aset hilang.',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'kondisi' => 'Baik',
            'status' => 'Hilang',
        ]);

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_report_damage_on_disposed_asset_is_rejected(): void
    {
        $asset = Asset::factory()->disposed()->create(['kondisi' => 'Baik']);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.report-damage', $asset), [
                'kondisi' => 'Rusak Berat',
                'deskripsi' => 'Percobaan pada aset yang sudah dihapuskan.',
            ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'kondisi' => 'Baik',
            'status' => 'Disposed',
        ]);
    }

    public function test_edit_asset_cannot_change_status(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        // Simulasi manipulasi request langsung — kirim status di luar UI (Edit
        // Aset sudah tidak lagi menampilkan dropdown status).
        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => $asset->nama_barang,
                'category_id' => $asset->category_id,
                'location_id' => $asset->location_id,
                'kondisi' => $asset->kondisi,
                'status' => 'Tersedia',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Dipinjam',
        ]);
    }

    public function test_changing_location_creates_mutasi_asset_log(): void
    {
        $oldLocation = Location::factory()->create();
        $newLocation = Location::factory()->create();
        $asset = Asset::factory()->tersedia()->create(['location_id' => $oldLocation->id]);

        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => $asset->nama_barang,
                'category_id' => $asset->category_id,
                'location_id' => $newLocation->id,
                'kondisi' => $asset->kondisi,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'location_id' => $newLocation->id,
            'status' => 'Tersedia',
        ]);
    }

    public function test_editing_master_field_without_location_change_does_not_create_mutasi_log(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => 'Nama Baru',
                'category_id' => $asset->category_id,
                'location_id' => $asset->location_id,
                'kondisi' => $asset->kondisi,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('asset_logs', 0);
    }

    public function test_editing_master_field_on_borrowed_asset_is_allowed_and_status_unchanged(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('assets.update', $asset), [
                'kode_barang' => $asset->kode_barang,
                'nama_barang' => 'Nama Diperbarui',
                'category_id' => $asset->category_id,
                'location_id' => $asset->location_id,
                'kondisi' => $asset->kondisi,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'nama_barang' => 'Nama Diperbarui',
            'status' => 'Dipinjam',
        ]);
    }

    public function test_admin_can_delete_asset(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('assets.destroy', $asset));

        $response->assertRedirect(route('assets.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted($asset);
    }

    public function test_admin_cannot_delete_borrowed_asset(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('assets.destroy', $asset));

        $response->assertRedirect(route('assets.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    }

    public function test_search_functionality(): void
    {
        Asset::factory()->create(['nama_barang' => 'Proyektor XYZ']);

        $response = $this->actingAs($this->staff)
            ->get(route('assets.index', ['search' => 'Proyektor']));

        $response->assertStatus(200);
        $response->assertSee('Proyektor XYZ');
    }

    public function test_manual_log_cannot_use_workflow_type(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->staff)
            ->post(route('assets.logs.store', $asset), [
                'tipe' => 'mutasi',
                'deskripsi' => 'Percobaan log mutasi manual.',
            ]);

        $response->assertSessionHasErrors('tipe');
        $this->assertDatabaseCount('asset_logs', 0);
    }
}
