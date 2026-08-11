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
        Schema::table('assets', function (Blueprint $table) {
            // Kode barang milik asset yang sudah di-soft-delete tetap tersimpan
            // di tabel, jadi unique constraint mentah harus dilepas — keunikan
            // untuk asset yang masih aktif sekarang divalidasi di level aplikasi
            // (lihat AssetController), supaya kode yang sudah dihapus bisa dipakai lagi.
            $table->dropUnique('assets_kode_barang_unique');
            $table->index('kode_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['kode_barang']);
            $table->unique('kode_barang');
        });
    }
};
