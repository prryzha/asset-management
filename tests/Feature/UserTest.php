<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    public function test_super_admin_can_view_user_index(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('users.index'));

        $response->assertStatus(200);
    }

    public function test_admin_cannot_view_user_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_staff_cannot_view_user_index(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->superAdmin)
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

    public function test_super_admin_can_update_user(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($this->superAdmin)
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

    public function test_super_admin_can_delete_user(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->delete(route('users.destroy', $this->superAdmin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    public function test_register_route_is_disabled(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_isAdmin_returns_true_for_super_admin(): void
    {
        $this->assertTrue($this->superAdmin->isAdmin());
    }

    public function test_isAdmin_returns_true_for_admin(): void
    {
        $this->assertTrue($this->admin->isAdmin());
    }

    public function test_isAdmin_returns_false_for_staff(): void
    {
        $this->assertFalse($this->staff->isAdmin());
    }

    public function test_isSuperAdmin_returns_true_for_super_admin(): void
    {
        $this->assertTrue($this->superAdmin->isSuperAdmin());
    }

    public function test_isSuperAdmin_returns_false_for_admin(): void
    {
        $this->assertFalse($this->admin->isSuperAdmin());
    }
}
