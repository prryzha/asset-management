<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $items = [
            ['nama' => 'Laptop', 'merk' => ['Lenovo', 'Asus', 'HP', 'Acer', 'Dell']],
            ['nama' => 'Komputer PC', 'merk' => ['Lenovo', 'HP', 'Dell']],
            ['nama' => 'Proyektor', 'merk' => ['Epson', 'BenQ', 'ViewSonic']],
            ['nama' => 'Printer', 'merk' => ['Canon', 'Epson', 'HP']],
            ['nama' => 'Meja Kerja', 'merk' => ['Olympic', 'Informa']],
            ['nama' => 'Kursi Kantor', 'merk' => ['Chairman', 'Ergotec']],
            ['nama' => 'AC Split', 'merk' => ['Daikin', 'Panasonic', 'LG']],
            ['nama' => 'Whiteboard', 'merk' => ['Sakana', 'Keiko']],
            ['nama' => 'Lemari Arsip', 'merk' => ['Lion', 'Brother']],
            ['nama' => 'Mikroskop', 'merk' => ['Olympus', 'Zeiss']],
        ];
        $item = fake()->randomElement($items);

        return [
            'kode_barang' => fake()->unique()->bothify('???-####'),
            'nama_barang' => $item['nama'],
            'merk' => fake()->randomElement($item['merk']),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'kondisi' => fake()->randomElement(['Baik', 'Kurang Baik', 'Rusak Berat']),
            'status' => fake()->randomElement(['Tersedia', 'Dipinjam', 'Perbaikan']),
            'catatan' => fake()->optional()->sentence(),
            'foto' => null,
        ];
    }

    public function tersedia(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'Tersedia',
        ]);
    }

    public function dipinjam(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'Dipinjam',
        ]);
    }

    public function perbaikan(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'Perbaikan',
        ]);
    }

    public function hilang(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'Hilang',
        ]);
    }

    public function disposed(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'Disposed',
        ]);
    }
}
