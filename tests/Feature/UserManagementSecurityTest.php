<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Meniru payload form Edit User asli: field password selalu ikut terkirim
     * walau dikosongkan.
     */
    private function payload(User $user, string $role, array $overrides = []): array
    {
        return array_merge([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'password' => null,
            'password_confirmation' => null,
        ], $overrides);
    }

    // 1. Admin terakhir tidak dapat menurunkan dirinya menjadi Staff

    public function test_last_admin_cannot_demote_self_to_staff(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->put(route('users.update', $admin), $this->payload($admin, 'staff'));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    public function test_last_admin_demotion_is_blocked_even_without_password_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Raw request tanpa field password sama sekali — tidak boleh 500,
        // dan tetap harus ditolak oleh guard admin terakhir.
        $response = $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'staff',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    // 2. Admin terakhir tidak dapat menghapus dirinya sendiri

    public function test_last_admin_cannot_delete_self_via_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    public function test_last_admin_cannot_delete_own_account_via_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    // 3. Masih ada Admin lain → admin boleh menurunkan dirinya sendiri

    public function test_admin_can_demote_self_when_another_admin_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->put(route('users.update', $admin), $this->payload($admin, 'staff'));

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'staff']);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    public function test_admin_can_delete_own_account_via_profile_when_another_admin_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->delete(route('profile.destroy'), ['password' => 'password']);

        $response->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    // 4. Admin tetap dapat mengubah role user lain

    public function test_admin_can_still_change_other_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)
            ->put(route('users.update', $staff), $this->payload($staff, 'admin'));

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'role' => 'admin']);
    }

    public function test_admin_can_demote_another_admin_while_remaining_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->put(route('users.update', $otherAdmin), $this->payload($otherAdmin, 'staff'));

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id, 'role' => 'staff']);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    // 5. Admin tetap dapat membuat user

    public function test_admin_can_still_create_staff_and_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Staff Baru',
            'email' => 'staffbaru@sekolah.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@sekolah.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'staffbaru@sekolah.test', 'role' => 'staff']);
        $this->assertDatabaseHas('users', ['email' => 'adminbaru@sekolah.test', 'role' => 'admin']);
    }

    // 6. Staff tetap tidak dapat mengakses User Management

    public function test_staff_cannot_access_any_user_management_endpoint(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($staff)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($staff)->get(route('users.create'))->assertStatus(403);
        $this->actingAs($staff)->get(route('users.edit', $admin))->assertStatus(403);
        $this->actingAs($staff)->put(route('users.update', $admin), $this->payload($admin, 'staff'))->assertStatus(403);
        $this->actingAs($staff)->delete(route('users.destroy', $admin))->assertStatus(403);
        $this->actingAs($staff)->post(route('users.store'), [
            'name' => 'X', 'email' => 'x@x.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertStatus(403);

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->assertDatabaseMissing('users', ['email' => 'x@x.test']);
    }

    // 7. Tidak ada jalur bypass lewat request langsung

    public function test_staff_cannot_escalate_own_role_via_profile_endpoint(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->patch(route('profile.update'), [
            'name' => 'Naik Pangkat',
            'email' => 'naik@sekolah.test',
            'role' => 'admin', // harus diabaikan — bukan field yang divalidasi
        ]);

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'role' => 'staff']);
    }

    public function test_guest_cannot_touch_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('users.index'))->assertRedirect(route('login'));
        $this->put(route('users.update', $admin), $this->payload($admin, 'staff'))->assertRedirect(route('login'));
        $this->delete(route('users.destroy', $admin))->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    // Regression: bug 500 saat field password tidak dikirim

    public function test_updating_other_user_without_password_field_does_not_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $originalPassword = $staff->password;

        $response = $this->actingAs($admin)
            ->put(route('users.update', $staff), [
                'name' => 'Nama Diperbarui',
                'email' => $staff->email,
                'role' => 'staff',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'name' => 'Nama Diperbarui']);
        // Password tidak boleh ikut berubah/ter-hash-ulang saat tidak dikirim.
        $this->assertSame($originalPassword, $staff->fresh()->password);
    }

    public function test_password_is_updated_only_when_supplied(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $originalPassword = $staff->password;

        $this->actingAs($admin)->put(route('users.update', $staff), $this->payload($staff, 'staff', [
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]));

        $this->assertNotSame($originalPassword, $staff->fresh()->password);
    }
}
