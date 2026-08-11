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
    private User $kepsek;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kepsek = User::factory()->create(['role' => 'staff']);
    }

    public function test_guest_cannot_access_asset_index(): void
    {
        $response = $this->get(route('assets.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_assets(): void
    {
        Asset::factory(3)->create();

        $response = $this->actingAs($this->kepsek)
            ->get(route('assets.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.create'));

        $response->assertStatus(200);
    }

    public function test_kepsek_cannot_view_create_form(): void
    {
        $response = $this->actingAs($this->kepsek)
            ->get(route('assets.create'));

        $response->assertStatus(403);
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
                'jumlah' => 5,
                'catatan' => 'Test',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'kode_barang' => 'KMR-001',
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
                'jumlah' => 3,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'nama_barang' => 'Updated Name',
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

        $response = $this->actingAs($this->kepsek)
            ->get(route('assets.index', ['search' => 'Proyektor']));

        $response->assertStatus(200);
        $response->assertSee('Proyektor XYZ');
    }
}
