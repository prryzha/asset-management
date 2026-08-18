<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kepsek;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'staff']);
        $this->kepsek = User::factory()->create(['role' => 'admin']);
    }

    public function test_authenticated_user_can_view_transactions(): void
    {
        $response = $this->actingAs($this->kepsek)
            ->get(route('transactions.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_transaction(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('transactions.store'), [
                'asset_id' => $asset->id,
                'nama_peminjam' => 'Budi',
                'keperluan' => 'Praktikum',
                'tanggal_pinjam' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'asset_id' => $asset->id,
            'nama_peminjam' => 'Budi',
            'status_peminjaman' => 'Dipinjam',
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'Dipinjam',
        ]);
    }

    public function test_admin_can_return_asset(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('transactions.return', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status_peminjaman' => 'Dikembalikan',
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $transaction->asset_id,
            'status' => 'Tersedia',
        ]);
    }

    public function test_borrowing_creates_peminjaman_log_without_mutasi_log(): void
    {
        $asset = Asset::factory()->tersedia()->create();

        $this->actingAs($this->admin)
            ->post(route('transactions.store'), [
                'asset_id' => $asset->id,
                'nama_peminjam' => 'Budi',
                'keperluan' => 'Praktikum',
                'tanggal_pinjam' => now()->format('Y-m-d'),
            ]);

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'peminjaman',
        ]);

        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $asset->id,
            'tipe' => 'mutasi',
        ]);
    }

    public function test_returning_creates_pengembalian_log_without_mutasi_log(): void
    {
        $transaction = Transaction::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('transactions.return', $transaction));

        $this->assertDatabaseHas('asset_logs', [
            'asset_id' => $transaction->asset_id,
            'tipe' => 'pengembalian',
        ]);

        $this->assertDatabaseMissing('asset_logs', [
            'asset_id' => $transaction->asset_id,
            'tipe' => 'mutasi',
        ]);
    }

    public function test_cannot_borrow_unavailable_asset(): void
    {
        $asset = Asset::factory()->dipinjam()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('transactions.store'), [
                'asset_id' => $asset->id,
                'nama_peminjam' => 'Budi',
                'keperluan' => 'Praktikum',
                'tanggal_pinjam' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('asset_id');
    }
}
