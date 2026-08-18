<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyNewEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regresi untuk alur ganti-email (stateless, signed URL, TANPA
 * migration/kolom baru) dan verifikasi email aktif (Laravel-native
 * MustVerifyEmail, diaktifkan kembali). Lihat ProfileEmailController &
 * app/Notifications/VerifyNewEmail.php.
 */
class ProfileEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ajukan perubahan email lewat form Profil, lalu tangkap URL verifikasi
     * ASLI yang dikirim ke notifikasi (bukan direkonstruksi manual — payload
     * dienkripsi dengan IV acak, jadi tidak bisa direproduksi ulang di test).
     */
    private function requestEmailChange(User $user, string $newEmail): string
    {
        Notification::fake();

        $this->actingAs($user)
            ->post(route('profile.email.update'), ['new_email' => $newEmail])
            ->assertRedirect(route('profile.edit'));

        $capturedUrl = null;
        Notification::assertSentOnDemand(
            VerifyNewEmail::class,
            function ($notification, $channels, $notifiable) use ($newEmail, &$capturedUrl) {
                if (($notifiable->routes['mail'] ?? null) !== $newEmail) {
                    return false;
                }
                $capturedUrl = $notification->verificationUrl;

                return true;
            }
        );

        $this->assertNotNull($capturedUrl, 'Notifikasi VerifyNewEmail ke alamat baru tidak ditemukan.');

        return $capturedUrl;
    }

    // 3. Email lama tetap aktif segera setelah request ganti email diajukan.

    public function test_old_email_stays_active_immediately_after_requesting_a_change(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);

        $this->requestEmailChange($user, 'baru@sekolah.test');

        $this->assertSame('lama@sekolah.test', $user->fresh()->email);
    }

    // 4. Notifikasi verifikasi benar-benar dikirim ke email BARU.

    public function test_verification_notification_is_sent_to_the_new_email_address(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);

        $url = $this->requestEmailChange($user, 'baru@sekolah.test');

        $this->assertStringContainsString(route('profile.email.verify'), $url);
    }

    // 5. Email baru belum aktif sama sekali sebelum verifikasi (tidak ada
    // baris/atribut manapun yang berubah ke email baru).

    public function test_new_email_is_not_active_before_verification(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);

        $this->requestEmailChange($user, 'baru@sekolah.test');

        $this->assertNotSame('baru@sekolah.test', $user->fresh()->email);
        $this->assertNull(User::where('email', 'baru@sekolah.test')->first());
    }

    // 6. Klik link signed yang valid benar-benar mengubah email aktif.

    public function test_valid_signed_link_changes_the_email(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);
        $url = $this->requestEmailChange($user, 'baru@sekolah.test');

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect(route('profile.edit'));
        $this->assertSame('baru@sekolah.test', $user->fresh()->email);
    }

    // 7. Verifikasi otomatis mengisi email_verified_at (bukan cuma ganti kolom email).

    public function test_verification_sets_email_verified_at(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test', 'email_verified_at' => null]);
        $url = $this->requestEmailChange($user, 'baru@sekolah.test');

        $this->actingAs($user)->get($url);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    // 8. Link kedaluwarsa tidak boleh mengubah apa pun.

    public function test_expired_link_does_not_change_email(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);

        $expiredUrl = URL::temporarySignedRoute(
            'profile.email.verify',
            now()->subMinute(),
            ['payload' => Crypt::encryptString($user->id . '|baru@sekolah.test')],
        );

        $response = $this->actingAs($user)->get($expiredUrl);

        $response->assertForbidden();
        $this->assertSame('lama@sekolah.test', $user->fresh()->email);
    }

    // 9. Signature yang dimanipulasi (payload diubah tanpa signature valid) ditolak.

    public function test_tampered_payload_does_not_change_email(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);
        $url = $this->requestEmailChange($user, 'baru@sekolah.test');

        $tamperedPayload = Crypt::encryptString($user->id . '|penyerang@jahat.test');
        $tamperedUrl = preg_replace('/payload=[^&]+/', 'payload=' . urlencode($tamperedPayload), $url);
        $this->assertNotSame($url, $tamperedUrl, 'Precondition: URL harus benar-benar berubah oleh manipulasi.');

        $response = $this->actingAs($user)->get($tamperedUrl);

        $response->assertForbidden();
        $this->assertSame('lama@sekolah.test', $user->fresh()->email);
    }

    // 10. Race condition: email baru sudah "keduluan" dipakai user lain di
    // antara waktu link dikirim & link diklik -> ditolak saat verifikasi,
    // email lama tidak berubah.

    public function test_new_email_taken_by_another_user_is_rejected_at_verification_time(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);
        $url = $this->requestEmailChange($user, 'rebutan@sekolah.test');

        User::factory()->create(['email' => 'rebutan@sekolah.test']);

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect();
        $this->assertSame('lama@sekolah.test', $user->fresh()->email);
    }

    // Keunikan juga sudah dicek di titik REQUEST (bukan cuma verifikasi) —
    // mencegah pengiriman notifikasi yang percuma untuk email yang jelas
    // sudah dipakai user lain saat itu juga.
    public function test_new_email_already_used_by_another_user_is_rejected_at_request_time(): void
    {
        User::factory()->create(['email' => 'sudah-dipakai@sekolah.test']);
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);

        $response = $this->from(route('profile.edit'))
            ->actingAs($user)
            ->post(route('profile.email.update'), ['new_email' => 'sudah-dipakai@sekolah.test']);

        $response->assertSessionHasErrorsIn('changeEmail', 'new_email');
        $this->assertSame('lama@sekolah.test', $user->fresh()->email);
    }

    // Regresi: field "new_email" wajib punya label Bahasa Indonesia
    // ("email baru") di lang/id/validation.php — tanpa mapping ini, Laravel
    // jatuh ke fallback otomatis dan menampilkan "new email sudah
    // digunakan." (mencampur kata Inggris "new" di halaman yang seharusnya
    // konsisten Bahasa Indonesia). Dicek dari DUA sisi: pesan tersimpan di
    // error bag DAN benar-benar tampil di HTML halaman Profil setelah
    // redirect — supaya kalau suatu saat blade-nya berubah pola render
    // error, test ini tetap membuktikan pesan yang dilihat user, bukan cuma
    // isi session.
    public function test_new_email_validation_error_uses_indonesian_label_not_english(): void
    {
        User::factory()->create(['email' => 'sudah-dipakai@sekolah.test']);
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);

        $response = $this->from(route('profile.edit'))
            ->actingAs($user)
            ->post(route('profile.email.update'), ['new_email' => 'sudah-dipakai@sekolah.test']);

        $errors = $response->getSession()->get('errors')->getBag('changeEmail');
        $this->assertSame('email baru sudah digunakan.', $errors->first('new_email'));
        $this->assertStringNotContainsString('new email', $errors->first('new_email'));

        $page = $this->actingAs($user)->get(route('profile.edit'));
        $page->assertSee('email baru sudah digunakan.');
        $page->assertDontSee('new email sudah digunakan.');
    }

    // Mengajukan "ganti" ke email yang sama dengan email aktif ditolak —
    // bukan business rule inti, tapi mencegah notifikasi yang tidak perlu.
    public function test_requesting_change_to_the_same_active_email_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'lama@sekolah.test']);

        Notification::fake();
        $response = $this->from(route('profile.edit'))
            ->actingAs($user)
            ->post(route('profile.email.update'), ['new_email' => 'lama@sekolah.test']);

        $response->assertSessionHasErrorsIn('changeEmail', 'new_email');
        Notification::assertNothingSent();
    }

    // --- Verifikasi email AKTIF (MustVerifyEmail bawaan Laravel) -----------

    // 11. Resend bekerja untuk email aktif yang belum terverifikasi.

    public function test_resend_verification_sends_notification_for_unverified_active_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)->post(route('verification.send'))->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    // 12. Email yang sudah terverifikasi tidak perlu (tidak memicu) resend.

    public function test_resend_verification_does_nothing_for_already_verified_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post(route('verification.send'));

        Notification::assertNothingSentTo($user);
    }

    public function test_clicking_valid_active_email_verification_link_marks_it_verified(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)->post(route('verification.send'));

        $capturedUrl = null;
        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user, &$capturedUrl) {
            $capturedUrl = $notification->toMail($user)->actionUrl;

            return true;
        });

        $response = $this->actingAs($user)->get($capturedUrl);

        $response->assertRedirect(route('profile.edit'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_clicking_the_same_active_email_link_twice_does_not_error(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user)->post(route('verification.send'));

        $capturedUrl = null;
        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user, &$capturedUrl) {
            $capturedUrl = $notification->toMail($user)->actionUrl;

            return true;
        });

        $this->actingAs($user)->get($capturedUrl);
        $verifiedAtAfterFirstClick = $user->fresh()->email_verified_at;

        $response = $this->actingAs($user)->get($capturedUrl);

        $response->assertRedirect(route('profile.edit'));
        $this->assertEquals($verifiedAtAfterFirstClick, $user->fresh()->email_verified_at);
    }

    // 16. Role tidak bisa dimanipulasi lewat form ganti-email juga.

    public function test_role_cannot_be_changed_via_email_change_request(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'email' => 'lama@sekolah.test']);

        Notification::fake();
        $this->actingAs($staff)->post(route('profile.email.update'), [
            'new_email' => 'baru@sekolah.test',
            'role' => 'admin',
        ]);

        $this->assertSame('staff', $staff->fresh()->role);
    }

    // 17. Regresi: forgot/reset password tidak boleh ikut terpengaruh oleh
    // perubahan di area profile/verifikasi ini (pengecekan menyeluruh ada di
    // PasswordResetTest.php — ini smoke test tambahan di area yang sama).

    public function test_forgot_password_flow_is_unaffected_by_profile_changes(): void
    {
        $this->get(route('password.request'))->assertOk();
    }
}
