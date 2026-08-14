<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Isi data demo (kategori, lokasi, aset + riwayat peminjaman/perawatan) untuk presentasi.
     * Aman dijalankan ulang — kategori/lokasi pakai firstOrCreate, tidak membuat duplikat.
     */
    public function run(): void
    {
        // withTrashed + restore, karena kolom `nama` punya unique constraint mentah di DB
        // yang tidak mengecualikan baris soft-deleted (sama seperti kasus kode_barang dulu).
        $categories = [
            'Elektronik' => 'ELK',
            'Mebel' => 'MBL',
            'Laboratorium' => 'LAB',
            'Buku' => 'BKU',
            'Olahraga' => 'OR',
        ];
        foreach ($categories as $nama => $kode) {
            $existing = Category::withTrashed()->where('nama', $nama)->first();
            if ($existing) {
                $existing->restore();
                if (empty($existing->kode)) {
                    $existing->update(['kode' => $kode]);
                }
            } else {
                Category::create(['nama' => $nama, 'kode' => $kode]);
            }
        }

        $locations = [
            'Lab Komputer' => 'LK',
            'Ruang Guru' => 'RG',
            'Ruang Kelas' => 'RK',
            'Perpustakaan' => 'PUS',
            'Lab IPA' => 'LI',
            'Gudang Olahraga' => 'GOR',
        ];
        foreach ($locations as $nama => $kode) {
            $existing = Location::withTrashed()->where('nama', $nama)->first();
            if ($existing) {
                $existing->restore();
                if (empty($existing->kode)) {
                    $existing->update(['kode' => $kode]);
                }
            } else {
                Location::create(['nama' => $nama, 'kode' => $kode]);
            }
        }

        $admin = User::where('role', 'super_admin')->first() ?? User::first();

        $items = [
            // Elektronik
            ['Elektronik', 'Laptop', 'Lenovo ThinkPad E14', 'Lab Komputer', 'Tersedia', 'Baik', 2023, 8500000, 'Budi Santoso'],
            ['Elektronik', 'Laptop', 'Asus VivoBook 14', 'Lab Komputer', 'Dipinjam', 'Baik', 2023, 7200000, 'Budi Santoso'],
            ['Elektronik', 'Komputer PC', 'Lenovo ThinkCentre', 'Lab Komputer', 'Tersedia', 'Baik', 2022, 6000000, null],
            ['Elektronik', 'Proyektor', 'Epson EB-X500', 'Ruang Kelas', 'Tersedia', 'Baik', 2023, 4500000, null],
            ['Elektronik', 'Proyektor', 'BenQ MS550', 'Ruang Guru', 'Dipinjam', 'Baik', 2022, 4200000, 'Siti Aminah'],
            ['Elektronik', 'Printer', 'Canon Pixma G2010', 'Ruang Guru', 'Tersedia', 'Baik', 2023, 2100000, 'Siti Aminah'],
            ['Elektronik', 'AC Split', 'Daikin 1PK', 'Ruang Guru', 'Perbaikan', 'Rusak Berat', 2021, 3800000, null],

            // Mebel
            ['Mebel', 'Meja Kerja', 'Olympic', 'Ruang Guru', 'Tersedia', 'Baik', 2022, 1200000, null],
            ['Mebel', 'Kursi Kantor', 'Chairman', 'Ruang Guru', 'Tersedia', 'Baik', 2022, 850000, null],
            ['Mebel', 'Lemari Arsip', 'Lion', 'Ruang Guru', 'Tersedia', 'Kurang Baik', 2020, 1500000, null],
            ['Mebel', 'Whiteboard', 'Sakana 120x240', 'Ruang Kelas', 'Tersedia', 'Baik', 2023, 650000, null],
            ['Mebel', 'Meja Kerja', 'Informa', 'Ruang Kelas', 'Perbaikan', 'Rusak Berat', 2019, 1100000, null],

            // Laboratorium
            ['Laboratorium', 'Mikroskop', 'Olympus CX23', 'Lab IPA', 'Tersedia', 'Baik', 2022, 5500000, 'Dewi Lestari'],
            ['Laboratorium', 'Mikroskop', 'Zeiss Primo Star', 'Lab IPA', 'Tersedia', 'Baik', 2022, 6200000, 'Dewi Lestari'],
            ['Laboratorium', 'Proyektor', 'ViewSonic PA503S', 'Lab IPA', 'Tersedia', 'Baik', 2023, 3900000, null],

            // Buku
            ['Buku', 'Ensiklopedia Sains', null, 'Perpustakaan', 'Tersedia', 'Baik', 2021, 250000, null],
            ['Buku', 'Novel Laskar Pelangi', null, 'Perpustakaan', 'Dipinjam', 'Baik', 2020, 85000, null],
            ['Buku', 'Kamus Bahasa Inggris', null, 'Perpustakaan', 'Tersedia', 'Baik', 2021, 150000, null],
            ['Buku', 'Atlas Dunia', null, 'Perpustakaan', 'Tersedia', 'Baik', 2020, 200000, null],

            // Olahraga
            ['Olahraga', 'Bola Basket', 'Molten', 'Gudang Olahraga', 'Tersedia', 'Baik', 2023, 350000, 'Agus Wijaya'],
            ['Olahraga', 'Matras Senam', 'Body Gym', 'Gudang Olahraga', 'Tersedia', 'Baik', 2022, 500000, 'Agus Wijaya'],
            ['Olahraga', 'Net Voli', 'Mikasa', 'Gudang Olahraga', 'Dipinjam', 'Baik', 2022, 400000, 'Agus Wijaya'],
        ];

        $peminjam = ['Ahmad Fauzi (XI RPL)', 'Rina Marlina (Guru)', 'Kelas X IPA 1', 'Dimas Prasetyo (XII TKJ)'];
        $peminjamIndex = 0;

        foreach ($items as [$categoryNama, $namaBarang, $merk, $locationNama, $status, $kondisi, $tahun, $nilai, $pic]) {
            $category = Category::where('nama', $categoryNama)->first();
            $location = Location::where('nama', $locationNama)->first();

            $asset = Asset::create([
                'kode_barang' => Asset::findNextKodeBarang($category->kode),
                'nama_barang' => $namaBarang,
                'merk' => $merk,
                'category_id' => $category->id,
                'location_id' => $location->id,
                'penanggung_jawab' => $pic,
                'kondisi' => $kondisi,
                'status' => $status,
                'tahun_perolehan' => $tahun,
                'nilai_perolehan' => $nilai,
            ]);

            if ($status === 'Dipinjam') {
                Transaction::create([
                    'asset_id' => $asset->id,
                    'nama_peminjam' => $peminjam[$peminjamIndex++ % count($peminjam)],
                    'keperluan' => 'Kegiatan belajar mengajar',
                    'tanggal_pinjam' => now()->subDays(rand(1, 5))->toDateString(),
                    'status_peminjaman' => 'Dipinjam',
                ]);
            }

            if ($status === 'Perbaikan') {
                MaintenanceSchedule::create([
                    'asset_id' => $asset->id,
                    'created_by' => $admin?->id,
                    'jenis_perawatan' => 'Servis berat',
                    'tanggal_jadwal' => now()->subDays(rand(1, 3))->toDateString(),
                    'catatan' => 'Kondisi ' . strtolower($kondisi) . ', perlu perbaikan.',
                    'status' => 'Dikerjakan',
                ]);
            }
        }
    }
}
