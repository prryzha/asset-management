<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('assets', function (Blueprint $table) {
        $table->id();
        $table->string('kode_barang')->unique();
        $table->string('nama_barang');
        $table->string('merk')->nullable(); // Boleh kosong
        $table->string('kategori');
        $table->string('lokasi')->nullable(); // Contoh: Lab Komputer 1, Ruang Guru
        
        // Memisahkan Kondisi Fisik dan Status Ketersediaan
        $table->enum('kondisi', ['Baik', 'Kurang Baik', 'Rusak Berat'])->default('Baik');
        $table->enum('status', ['Tersedia', 'Dipinjam', 'Perbaikan'])->default('Tersedia');
        
        $table->integer('jumlah')->default(1);
        $table->text('catatan')->nullable(); // Untuk mencatat detail kerusakan/perawatan
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
