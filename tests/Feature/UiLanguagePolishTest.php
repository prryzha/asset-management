<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test untuk FINAL UI LANGUAGE POLISH + ERROR PAGE POLISH:
 *  - halaman error kustom 403/404/500 memakai teks Bahasa Indonesia
 *  - teks UI prioritas (Profil, Masuk, Ubah, Awalan, Sistem) tidak kembali ke Inggris
 *  - status internal "Disposed" tetap tampil sebagai "Dihapuskan" di pesan user
 */
class UiLanguagePolishTest extends TestCase
{
    use RefreshDatabase;

    // --- ERROR PAGES ---------------------------------------------------------

    public function test_custom_403_page_uses_indonesian_text(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->get(route('users.index'));

        $response->assertStatus(403);
        // Judul halaman error custom.
        $response->assertSee('Akses Tidak Diizinkan');
        // Pesan abort custom dari CheckRole tetap tampil.
        $response->assertSee('Akses tidak diizinkan.');
    }

    public function test_custom_404_page_uses_indonesian_text(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/halaman-tidak-ada-xyz');

        $response->assertStatus(404);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('Kembali ke Beranda');
    }

    public function test_custom_500_page_uses_indonesian_text_without_leaking_details(): void
    {
        $html = view('errors.500')->render();

        $this->assertStringContainsString('Terjadi Kesalahan Server', $html);
        $this->assertStringContainsString('Kembali ke Beranda', $html);
        // Detail error tidak boleh bocor ke user.
        $this->assertStringNotContainsString('Exception', $html);
        $this->assertStringNotContainsString('Stack trace', $html);
    }

    // --- TEKS UI PRIORITAS ---------------------------------------------------

    public function test_login_button_uses_indonesian_text(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk');
        $response->assertDontSee('>Login<');
    }

    public function test_profile_page_uses_indonesian_text(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Profil');
        $response->assertSee('Informasi Profil');
        $response->assertDontSee('>Profile<');
    }

    public function test_asset_index_uses_ubah_action(): void
    {
        $user = User::factory()->create();
        Asset::factory()->create();

        $response = $this->actingAs($user)->get(route('assets.index', ['f' => 1]));

        $response->assertOk();
        $response->assertSee('Ubah');
        $response->assertDontSee('>Edit<');
    }

    public function test_asset_create_uses_awalan_placeholder(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('assets.create'));

        $response->assertOk();
        $response->assertSee('Awalan (contoh: MON)');
        $response->assertSee('Terisi otomatis dari awalan');
        $response->assertDontSee('Prefix (contoh: MON)');
    }

    public function test_system_fallback_uses_indonesian_in_mutasi_csv(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        // Log mutasi tanpa user — fallback "Sistem" dipakai (bukan "System").
        AssetLog::create([
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
            'deskripsi' => 'Pindah lokasi uji',
            'user_id' => null,
        ]);

        $response = $this->actingAs($user)->get(route('mutasi.export-csv'));

        $response->assertOk();
        $this->assertStringContainsString('Sistem', $response->streamedContent());
        $this->assertStringNotContainsString('System', $response->streamedContent());
    }

    // --- STATUS INTERNAL DI PESAN USER --------------------------------------

    public function test_maintenance_guard_message_uses_dihapuskan_not_disposed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $asset = Asset::factory()->disposed()->create();

        $response = $this->actingAs($admin)->post(route('maintenance.store'), [
            'asset_id' => $asset->id,
            'jenis_perawatan' => 'Bulanan',
            'tanggal_jadwal' => now()->addDay()->toDateString(),
            'catatan' => null,
        ]);

        $response->assertSessionHasErrors('asset_id');
        $this->assertStringContainsString('Dihapuskan', session('errors')->first('asset_id'));
        $this->assertStringNotContainsString('status Disposed', session('errors')->first('asset_id'));
    }
}
