<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk guard "kategori/lokasi yang masih dipakai aset tidak dapat
 * dihapus" (CategoryController::destroy() / LocationController::destroy()).
 * Ditemukan tanpa coverage sama sekali di audit sebelumnya — bukan bug,
 * murni gap testing pada business rule yang sudah aktif dan benar.
 */
class CategoryLocationDeletionTest extends TestCase
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

    // ===================== KATEGORI =====================

    public function test_guest_cannot_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('login'));
        $this->assertNotSoftDeleted($category);
    }

    public function test_staff_can_delete_unused_category(): void
    {
        $category = Category::factory()->create(['nama' => 'Kategori Tak Terpakai']);

        $response = $this->actingAs($this->staff)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($category);
    }

    public function test_admin_can_delete_unused_category(): void
    {
        $category = Category::factory()->create(['nama' => 'Kategori Tak Terpakai']);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted($category);
    }

    public function test_category_used_by_active_asset_cannot_be_deleted(): void
    {
        $category = Category::factory()->create(['nama' => 'Kategori Dipakai']);
        Asset::factory()->tersedia()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->staff)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error', 'Kategori yang masih dipakai aset tidak dapat dihapus.');
        $this->assertNotSoftDeleted($category);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    // Aset Disposed (Dihapuskan) BUKAN soft-deleted — masih baris aktif di
    // tabel assets, cuma statusnya arsip. Kategori yang direferensikan aset
    // arsip tetap dianggap "dipakai" (demi integritas riwayat/audit arsip),
    // beda dari kasus soft-delete aset di bawah.
    public function test_category_used_by_disposed_asset_cannot_be_deleted(): void
    {
        $category = Category::factory()->create(['nama' => 'Kategori Arsip']);
        Asset::factory()->disposed()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted($category);
    }

    // Aset yang SOFT-DELETED (bukan Disposed, tapi benar-benar dihapus lewat
    // tombol Hapus Aset) sudah tidak dianggap ada di manapun di aplikasi
    // (global scope Eloquent) — kategori yang HANYA direferensikan aset
    // semacam ini seharusnya tetap bisa dihapus.
    public function test_category_only_referenced_by_soft_deleted_asset_can_be_deleted(): void
    {
        $category = Category::factory()->create(['nama' => 'Kategori Aset Terhapus']);
        $asset = Asset::factory()->tersedia()->create(['category_id' => $category->id]);
        $asset->delete();

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted($category);
    }

    public function test_category_becomes_deletable_after_its_only_asset_is_reassigned(): void
    {
        $categoryA = Category::factory()->create(['nama' => 'Kategori A']);
        $categoryB = Category::factory()->create(['nama' => 'Kategori B']);
        $asset = Asset::factory()->tersedia()->create(['category_id' => $categoryA->id]);

        // Selama masih dipakai A, A tidak bisa dihapus.
        $this->actingAs($this->admin)->delete(route('categories.destroy', $categoryA));
        $this->assertNotSoftDeleted($categoryA);

        // Setelah dipindah ke B, A sudah tidak dipakai apa pun -> bisa dihapus.
        $asset->update(['category_id' => $categoryB->id]);
        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $categoryA));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted($categoryA);
    }

    // ===================== LOKASI =====================

    public function test_guest_cannot_delete_location(): void
    {
        $location = Location::factory()->create();

        $response = $this->delete(route('locations.destroy', $location));

        $response->assertRedirect(route('login'));
        $this->assertNotSoftDeleted($location);
    }

    public function test_staff_can_delete_unused_location(): void
    {
        $location = Location::factory()->create(['nama' => 'Lokasi Tak Terpakai']);

        $response = $this->actingAs($this->staff)->delete(route('locations.destroy', $location));

        $response->assertRedirect(route('locations.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($location);
    }

    public function test_admin_can_delete_unused_location(): void
    {
        $location = Location::factory()->create(['nama' => 'Lokasi Tak Terpakai']);

        $response = $this->actingAs($this->admin)->delete(route('locations.destroy', $location));

        $response->assertRedirect(route('locations.index'));
        $this->assertSoftDeleted($location);
    }

    public function test_location_used_by_active_asset_cannot_be_deleted(): void
    {
        $location = Location::factory()->create(['nama' => 'Lokasi Dipakai']);
        Asset::factory()->tersedia()->create(['location_id' => $location->id]);

        $response = $this->actingAs($this->staff)->delete(route('locations.destroy', $location));

        $response->assertRedirect(route('locations.index'));
        $response->assertSessionHas('error', 'Lokasi yang masih dipakai aset tidak dapat dihapus.');
        $this->assertNotSoftDeleted($location);
        $this->assertDatabaseHas('locations', ['id' => $location->id, 'deleted_at' => null]);
    }

    public function test_location_used_by_disposed_asset_cannot_be_deleted(): void
    {
        $location = Location::factory()->create(['nama' => 'Lokasi Arsip']);
        Asset::factory()->disposed()->create(['location_id' => $location->id]);

        $response = $this->actingAs($this->admin)->delete(route('locations.destroy', $location));

        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted($location);
    }

    public function test_location_only_referenced_by_soft_deleted_asset_can_be_deleted(): void
    {
        $location = Location::factory()->create(['nama' => 'Lokasi Aset Terhapus']);
        $asset = Asset::factory()->tersedia()->create(['location_id' => $location->id]);
        $asset->delete();

        $response = $this->actingAs($this->admin)->delete(route('locations.destroy', $location));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted($location);
    }

    public function test_location_becomes_deletable_after_its_only_asset_is_reassigned(): void
    {
        $locationA = Location::factory()->create(['nama' => 'Lokasi A']);
        $locationB = Location::factory()->create(['nama' => 'Lokasi B']);
        $asset = Asset::factory()->tersedia()->create(['location_id' => $locationA->id]);

        $this->actingAs($this->admin)->delete(route('locations.destroy', $locationA));
        $this->assertNotSoftDeleted($locationA);

        $asset->update(['location_id' => $locationB->id]);
        $response = $this->actingAs($this->admin)->delete(route('locations.destroy', $locationA));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted($locationA);
    }
}
