<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    // --- UI/bahasa: halaman Lupa Password mengikuti gaya halaman login -----

    public function test_guest_can_access_forgot_password_page(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    public function test_forgot_password_page_uses_indonesian_text_and_app_styling(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertSee('Lupa Password');
        $response->assertSee('Kirim Link Atur Ulang Password');
        // Bukan lagi komponen Breeze mentah (x-primary-button dsb) — harus
        // pakai class sistem desain app yang sama dengan login.blade.php.
        $response->assertSee('btn-primary', false);
        $response->assertSee('form-input', false);
        // Tidak ada sisa istilah Inggris yang user-visible.
        $response->assertDontSee('Forgot Password');
        $response->assertDontSee('Reset Password');
    }

    public function test_forgot_password_shows_indonesian_validation_error_for_empty_email(): void
    {
        $response = $this->from(route('password.request'))->post(route('password.email'), ['email' => '']);

        $response->assertSessionHasErrors('email');

        $page = $this->get(route('password.request'));
        // Casing "email" huruf kecil sesuai lang/id/validation.php (custom
        // attribute mapping) — konsisten dengan pesan required di seluruh
        // form lain di app ini, bukan sesuatu yang perlu diseragamkan di sini.
        $page->assertSee('email wajib diisi.');
    }

    public function test_forgot_password_shows_success_status_in_indonesian(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->from(route('password.request'))->post(route('password.email'), ['email' => $user->email]);
        $response->assertSessionHas('status');

        $page = $this->get(route('password.request'));
        $page->assertSee('Link reset password telah dikirim ke email Anda.');
    }

    public function test_forgot_password_shows_indonesian_error_for_unknown_email(): void
    {
        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'tidak-terdaftar@contoh.com',
        ]);

        $response->assertSessionHasErrors('email');

        $page = $this->get(route('password.request'));
        $page->assertSee('Kami tidak dapat menemukan pengguna dengan alamat email tersebut.');
    }

    // --- UI/bahasa: halaman Atur Ulang Password mengikuti gaya halaman login -----

    public function test_guest_can_access_reset_password_page_with_a_token(): void
    {
        $response = $this->get(route('password.reset', 'sembarang-token'));

        $response->assertOk();
    }

    public function test_reset_password_page_uses_indonesian_text_and_app_styling(): void
    {
        $response = $this->get(route('password.reset', 'sembarang-token'));

        $response->assertOk();
        $response->assertSee('Atur Ulang Password');
        $response->assertSee('Password Baru');
        $response->assertSee('Konfirmasi Password Baru');
        $response->assertSee('btn-primary', false);
        $response->assertSee('form-input', false);
        $response->assertDontSee('Forgot Password');
    }

    public function test_reset_password_shows_indonesian_validation_error_for_mismatched_confirmation(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $resetUrl = route('password.reset', $notification->token);

            $response = $this->from($resetUrl)->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-beda-123',
            ]);

            $response->assertSessionHasErrors('password');

            $page = $this->get($resetUrl);
            $page->assertSee('Konfirmasi password tidak cocok.');

            return true;
        });
    }

    public function test_reset_password_shows_indonesian_error_for_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('password.reset', 'token-tidak-valid'))->post(route('password.store'), [
            'token' => 'token-tidak-valid',
            'email' => $user->email,
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertSessionHasErrors('email');

        $page = $this->get(route('password.reset', 'token-tidak-valid'));
        $page->assertSee('Token reset password tidak valid.');
    }

    // --- Business logic (route/controller/token) tidak berubah -------------

    public function test_password_reset_does_not_change_any_existing_user_password_without_a_valid_token(): void
    {
        $user = User::factory()->create();
        $originalPasswordHash = $user->password;

        $this->from(route('password.reset', 'token-asal-asalan'))->post(route('password.store'), [
            'token' => 'token-asal-asalan',
            'email' => $user->email,
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $this->assertSame($originalPasswordHash, $user->fresh()->password);
    }
}
