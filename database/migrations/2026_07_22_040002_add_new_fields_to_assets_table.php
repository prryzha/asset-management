<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('nomor_seri', 100)->nullable()->after('merk');
            $table->string('sumber_dana', 100)->nullable()->after('jumlah');
            $table->year('tahun_perolehan')->nullable()->after('sumber_dana');
            $table->decimal('nilai_perolehan', 15, 2)->default(0)->after('tahun_perolehan');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['nomor_seri', 'sumber_dana', 'tahun_perolehan', 'nilai_perolehan']);
        });
    }
};
