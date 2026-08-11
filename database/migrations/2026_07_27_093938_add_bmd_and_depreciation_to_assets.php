<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('kode_bmd')->nullable()->after('kode_barang');
            $table->string('kib')->nullable()->after('kode_bmd');
            $table->integer('masa_manfaat')->nullable()->after('tahun_perolehan');
            $table->decimal('nilai_residu', 15, 2)->nullable()->after('nilai_perolehan');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['kode_bmd', 'kib', 'masa_manfaat', 'nilai_residu']);
        });
    }
};
