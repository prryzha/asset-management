<?php

namespace Tests\Feature;

use App\Models\InstitutionProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstitutionProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_institution_profile_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('institution-profile.edit'));

        $response->assertOk();
    }

    public function test_staff_cannot_view_institution_profile_page(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->get(route('institution-profile.edit'));

        $response->assertStatus(403);
    }

    public function test_staff_cannot_update_institution_profile(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->put(route('institution-profile.update'), [
            'nama_instansi' => 'Percobaan Tidak Sah',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('institution_profiles', ['nama_instansi' => 'Percobaan Tidak Sah']);
    }

    public function test_admin_can_update_institution_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put(route('institution-profile.update'), [
            'nama_instansi' => 'SMK Negeri 4 Bandung',
            'nama_singkat' => 'SMKN 4 Bandung',
            'alamat' => 'Jl. Contoh No. 1',
            'telepon' => '0221234567',
            'email' => 'info@smkn4bdg.sch.id',
            'website' => 'https://smkn4bdg.sch.id',
            'deskripsi' => 'Mencetak lulusan berkarakter dan kompeten.',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('institution-profile.edit'));

        $this->assertDatabaseHas('institution_profiles', [
            'id' => 1,
            'nama_instansi' => 'SMK Negeri 4 Bandung',
            'nama_singkat' => 'SMKN 4 Bandung',
        ]);
    }

    public function test_logo_can_be_uploaded_and_replaced(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('institution-profile.update'), [
            'nama_instansi' => 'SMK Negeri 4 Bandung',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ]);
        $oldLogo = InstitutionProfile::current()->logo;
        Storage::disk('public')->assertExists($oldLogo);

        $this->actingAs($admin)->put(route('institution-profile.update'), [
            'nama_instansi' => 'SMK Negeri 4 Bandung',
            'logo' => UploadedFile::fake()->image('logo-baru.jpg'),
        ]);
        $newLogo = InstitutionProfile::current()->logo;

        $this->assertNotSame($oldLogo, $newLogo);
        Storage::disk('public')->assertMissing($oldLogo);
        Storage::disk('public')->assertExists($newLogo);
    }

    public function test_logo_upload_rejects_wrong_file_type(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put(route('institution-profile.update'), [
            'nama_instansi' => 'SMK Negeri 4 Bandung',
            'logo' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('logo');
    }

    public function test_nama_instansi_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put(route('institution-profile.update'), [
            'nama_instansi' => '',
        ]);

        $response->assertSessionHasErrors('nama_instansi');
    }

    public function test_header_branding_reflects_updated_profile_after_cache_invalidation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('institution-profile.update'), [
            'nama_instansi' => 'SMK Negeri 4 Bandung',
            'nama_singkat' => 'SMKN 4 Bandung',
        ]);

        // Composer caches by key "institution_profile" — updating must have
        // invalidated it so the header immediately reflects the new data,
        // not a stale cached null from before the profile existed.
        $this->assertFalse(Cache::has('institution_profile'));

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertSee('SMK Negeri 4 Bandung');
        $response->assertSee('SMKN 4 Bandung');
    }

    public function test_header_does_not_show_institution_branding_when_not_configured(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('app-header-institution-logo-fallback', false);
    }
}
