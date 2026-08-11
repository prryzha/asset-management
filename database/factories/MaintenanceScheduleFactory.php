<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceScheduleFactory extends Factory
{
    protected $model = MaintenanceSchedule::class;

    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'created_by' => User::factory(),
            'jenis_perawatan' => fake()->randomElement(['Bersih berkala', 'Servis ringan', 'Servis berat', 'Kalibrasi', 'Penggantian suku cadang']),
            'tanggal_jadwal' => fake()->date(),
            'tanggal_selesai' => null,
            'catatan' => fake()->optional()->sentence(),
            'catatan_selesai' => null,
            'status' => fake()->randomElement(['Dijadwalkan', 'Dikerjakan', 'Selesai', 'Dibatalkan']),
        ];
    }

    public function dijadwalkan(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'Dijadwalkan',
        ]);
    }

    public function dikerjakan(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'Dikerjakan',
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn(array $attrs) => [
            'tanggal_selesai' => fake()->date(),
            'status' => 'Selesai',
        ]);
    }
}
