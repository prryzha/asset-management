<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_admin_can_view_user_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('users.index'));

        $response->assertStatus(200);
    }

    public function test_staff_cannot_view_user_index(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'New User',
                'email' => 'new@sekolah.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'staff',
            ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new@sekolah.test',
            'role' => 'staff',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'role' => 'admin',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_create_user_with_username(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'New User',
                'username' => 'new_user',
                'email' => 'new2@sekolah.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'staff',
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'new2@sekolah.test', 'username' => 'new_user']);
    }

    public function test_admin_can_update_user_username(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'username' => null]);

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'username' => 'budi_santoso',
                'email' => $user->email,
                'role' => 'staff',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('users.index'));
        $this->assertSame('budi_santoso', $user->fresh()->username);
    }

    public function test_duplicate_username_is_rejected_in_user_management(): void
    {
        User::factory()->create(['username' => 'sudah_dipakai']);
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'username' => 'sudah_dipakai',
                'email' => $user->email,
                'role' => 'staff',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSessionHasErrors('username');
        $this->assertNull($user->fresh()->username);
    }

    public function test_user_keeping_their_own_username_on_update_is_allowed(): void
    {
        $user = User::factory()->create(['role' => 'staff', 'username' => 'budi_santoso']);

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => 'Nama Baru',
                'username' => 'budi_santoso',
                'email' => $user->email,
                'role' => 'staff',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('budi_santoso', $user->fresh()->username);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($this->admin)
            ->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_deleting_user_removes_their_foto_profil_file(): void
    {
        Storage::fake('public');
        $fotoPath = UploadedFile::fake()->image('foto.jpg')->store('avatars', 'public');
        $user = User::factory()->create(['role' => 'staff', 'foto_profil' => $fotoPath]);
        Storage::disk('public')->assertExists($fotoPath);

        $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        Storage::disk('public')->assertMissing($fotoPath);
    }

    public function test_deleting_user_without_foto_profil_does_not_error(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'staff', 'foto_profil' => null]);

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->admin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_register_route_is_disabled(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_isAdmin_returns_true_for_admin(): void
    {
        $this->assertTrue($this->admin->isAdmin());
    }

    public function test_isAdmin_returns_false_for_staff(): void
    {
        $this->assertFalse($this->staff->isAdmin());
    }
}
