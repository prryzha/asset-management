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
            $table->dropColumn(['masa_manfaat', 'nilai_residu']);
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->integer('masa_manfaat')->nullable()->after('tahun_perolehan');
            $table->decimal('nilai_residu', 15, 2)->nullable()->after('nilai_perolehan');
        });
    }
};
