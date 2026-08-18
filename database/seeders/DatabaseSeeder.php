<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrCreate (bukan factory create) supaya seeder aman dijalankan ulang —
        // email user unique, jadi create biasa akan nabrak duplicate key di seed kedua.
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Admin Sarpras', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Staf Sarpras', 'password' => bcrypt('password'), 'role' => 'staff']
        );

        // Data demo (kategori, lokasi, aset, peminjaman, perawatan) dibuat oleh
        // DemoDataSeeder — master data EKSPLISIT dan realistis, bukan hasil random
        // AssetFactory yang menciptakan kategori/lokasi baru untuk setiap aset
        // (dulu: setelah migrate:fresh --seed muncul puluhan kategori/lokasi acak).
        $this->call(DemoDataSeeder::class);
    }
}
