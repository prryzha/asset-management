<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * MySQL enum diubah lewat raw ALTER (mengubah enum via Schema::change()
     * butuh doctrine/dbal yang tidak ter-install di project ini). SQLite
     * (dipakai saat testing) tidak punya ALTER MODIFY, dan tidak ada test yang
     * memakai status Hilang/Disposed, jadi cukup di-skip di sana.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE assets MODIFY status ENUM('Tersedia', 'Dipinjam', 'Perbaikan', 'Hilang', 'Disposed') NOT NULL DEFAULT 'Tersedia'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE assets SET status = 'Perbaikan' WHERE status IN ('Hilang', 'Disposed')");
        DB::statement("ALTER TABLE assets MODIFY status ENUM('Tersedia', 'Dipinjam', 'Perbaikan') NOT NULL DEFAULT 'Tersedia'");
    }
};
