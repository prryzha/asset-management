<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Kepala Sekolah',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Staf Sarpras',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        Category::factory()->createMany([
            ['nama' => 'Elektronik'],
            ['nama' => 'Mebel'],
            ['nama' => 'Buku'],
            ['nama' => 'Olahraga'],
            ['nama' => 'Laboratorium'],
        ]);

        Location::factory()->createMany([
            ['nama' => 'Lab Komputer 1'],
            ['nama' => 'Lab Komputer 2'],
            ['nama' => 'Ruang Guru'],
            ['nama' => 'Ruang Kelas'],
            ['nama' => 'Perpustakaan'],
        ]);

        Asset::factory(20)
            ->tersedia()
            ->create()
            ->each(function (Asset $asset) {
                $asset->update([
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'location_id' => Location::inRandomOrder()->first()->id,
                ]);
            });

        Asset::factory(5)
            ->dipinjam()
            ->create()
            ->each(function (Asset $asset) {
                $asset->update([
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'location_id' => Location::inRandomOrder()->first()->id,
                ]);

                Transaction::factory()->create([
                    'asset_id' => $asset->id,
                ]);
            });

        Asset::factory(3)
            ->perbaikan()
            ->create()
            ->each(function (Asset $asset) {
                $asset->update([
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'location_id' => Location::inRandomOrder()->first()->id,
                ]);

                MaintenanceSchedule::factory()->dikerjakan()->create([
                    'asset_id' => $asset->id,
                ]);
            });
    }
}
