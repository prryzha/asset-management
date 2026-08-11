<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'nama_peminjam' => fake()->name(),
            'keperluan' => fake()->sentence(),
            'tanggal_pinjam' => fake()->date(),
            'tanggal_kembali' => null,
            'status_peminjaman' => 'Dipinjam',
        ];
    }

    public function dikembalikan(): static
    {
        return $this->state(fn(array $attrs) => [
            'tanggal_kembali' => fake()->date(),
            'status_peminjaman' => 'Dikembalikan',
        ]);
    }
}
