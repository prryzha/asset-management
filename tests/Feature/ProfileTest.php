<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    // Ganti email sekarang punya alur verifikasi tersendiri (lihat
    // ProfileEmailVerificationTest.php) — profile.update HANYA menangani
    // nama. Ini pengganti test_profile_information_can_be_updated lama yang
    // mengasumsikan email ikut berubah lewat endpoint ini.
    public function test_name_can_be_updated(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $originalEmail = $user->email;
        $originalVerifiedAt = $user->email_verified_at;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        // Endpoint ini tidak pernah menyentuh email/status verifikasi.
        $this->assertSame($originalEmail, $user->email);
        $this->assertEquals($originalVerifiedAt, $user->email_verified_at);
    }

    // profile.update tidak lagi menerima field "email" sama sekali —
    // ProfileUpdateRequest::rules() cuma mendaftarkan "name", jadi walau
    // raw request menyertakan "email", validated() tidak pernah berisi key
    // itu dan fill() tidak pernah menyentuhnya.
    public function test_profile_update_ignores_email_field_even_if_submitted(): void
    {
        $user = User::factory()->create();
        $originalEmail = $user->email;

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'seharusnya-diabaikan@example.com',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame($originalEmail, $user->refresh()->email);
    }

    // Tidak ada endpoint profile.* yang menerima parameter {user} — operasi
    // selalu ke $request->user(). Test ini membuktikan langsung: user lain
    // tidak ikut berubah walau ada user lain di database saat request dikirim.
    public function test_updating_profile_does_not_affect_other_users(): void
    {
        $user = User::factory()->create(['name' => 'Nama Asli']);
        $otherUser = User::factory()->create(['name' => 'User Lain']);

        $this->actingAs($user)->patch('/profile', ['name' => 'Nama Baru']);

        $this->assertSame('Nama Baru', $user->fresh()->name);
        $this->assertSame('User Lain', $otherUser->fresh()->name);
    }

    // Role hanya bisa diubah lewat Manajemen User (role:admin) — bukan lewat
    // profile milik sendiri. ProfileUpdateRequest tidak mendaftarkan "role"
    // sebagai rule, jadi mass-assignment lewat sini mustahil.
    public function test_role_cannot_be_changed_via_profile_update(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->patch('/profile', [
            'name' => $staff->name,
            'role' => 'admin',
        ]);

        $this->assertSame('staff', $staff->fresh()->role);
    }

    public function test_username_can_be_set(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'username' => 'budi_santoso',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->assertSame('budi_santoso', $user->fresh()->username);
    }

    public function test_username_can_be_left_blank(): void
    {
        $user = User::factory()->create(['username' => null]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'username' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->username);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        User::factory()->create(['username' => 'sudah_dipakai']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'username' => 'sudah_dipakai',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertNull($user->fresh()->username);
    }

    public function test_user_can_keep_their_own_existing_username(): void
    {
        $user = User::factory()->create(['username' => 'budi_santoso']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama Baru',
            'username' => 'budi_santoso',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('budi_santoso', $user->fresh()->username);
    }

    public function test_username_rejects_invalid_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'username' => 'Budi Santoso!',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_username_rejects_too_short_value(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'username' => 'ab',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_photo_can_be_uploaded(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('foto.jpg');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'foto_profil' => $photo,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->foto_profil);
        Storage::disk('public')->assertExists($user->foto_profil);
    }

    public function test_replacing_photo_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'foto_profil' => UploadedFile::fake()->image('lama.jpg'),
        ]);
        $oldPath = $user->fresh()->foto_profil;

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'foto_profil' => UploadedFile::fake()->image('baru.jpg'),
        ]);
        $newPath = $user->fresh()->foto_profil;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_photo_upload_rejects_oversized_or_wrong_type_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'foto_profil' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]);
        $response->assertSessionHasErrors('foto_profil');

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'foto_profil' => UploadedFile::fake()->image('terlalu-besar.jpg')->size(3000),
        ]);
        $response->assertSessionHasErrors('foto_profil');

        $this->assertNull($user->fresh()->foto_profil);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_deleting_own_account_removes_foto_profil_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'foto_profil' => UploadedFile::fake()->image('foto.jpg'),
        ]);
        $fotoPath = $user->fresh()->foto_profil;
        Storage::disk('public')->assertExists($fotoPath);

        $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        Storage::disk('public')->assertMissing($fotoPath);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
