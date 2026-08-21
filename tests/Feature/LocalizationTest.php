<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test untuk fitur pergantian bahasa ID/EN:
 *  - locale default tetap 'id' tanpa session
 *  - /language/{locale} mengubah session locale dan redirect kembali
 *  - locale tidak valid diabaikan dengan aman
 *  - pergantian locale tidak memengaruhi sesi login/otorisasi
 *  - pesan validasi & UI bersama ikut berganti bahasa
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_indonesian_without_session(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $this->assertSame('id', app()->getLocale());
        $response->assertSee('Masuk');
    }

    public function test_switching_to_english_sets_session_and_redirects_back_preserving_query(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/dashboard?foo=bar')
            ->get('/language/en');

        $response->assertRedirect('/dashboard?foo=bar');
        $this->assertSame('en', session('locale'));
    }

    public function test_switching_back_to_indonesian_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['locale' => 'en'])->get('/language/id');

        $this->assertSame('id', session('locale'));
    }

    public function test_invalid_locale_is_safely_ignored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'id'])
            ->from('/dashboard')
            ->get('/language/xx');

        $response->assertRedirect('/dashboard');
        // Locale tidak valid tidak boleh mengubah session yang sudah ada.
        $this->assertSame('id', session('locale'));
    }

    public function test_user_stays_authenticated_after_switching_language(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/language/en');

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_validation_messages_switch_language_with_locale(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Default (id): pesan validasi berbahasa Indonesia.
        $responseId = $this->actingAs($admin)->post(route('categories.store'), []);
        $responseId->assertSessionHasErrors('nama');
        $this->assertStringContainsString(
            'wajib diisi',
            session('errors')->first('nama')
        );

        // Setelah beralih ke en: pesan validasi berbahasa Inggris.
        $this->actingAs($admin)->get('/language/en');
        $responseEn = $this->actingAs($admin)->withSession(['locale' => 'en'])
            ->post(route('categories.store'), []);
        $responseEn->assertSessionHasErrors('nama');
        $this->assertStringContainsString(
            'field is required',
            session('errors')->first('nama')
        );
    }

    public function test_shared_layout_ui_translates_to_english(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertDontSee('Dasbor');
    }

    public function test_staff_authorization_unaffected_after_switching_locale(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)
            ->withSession(['locale' => 'en'])
            ->get(route('users.index'));

        $response->assertStatus(403);
    }
}
