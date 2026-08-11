<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('kategori')->constrained('categories')->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->after('lokasi')->constrained('locations')->restrictOnDelete();
        });

        $now = now();

        foreach (['Elektronik', 'Mebel', 'Buku'] as $nama) {
            DB::table('categories')->updateOrInsert(
                ['nama' => $nama],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }

        foreach (DB::table('assets')->select('kategori')->distinct()->pluck('kategori') as $nama) {
            if ($nama !== null && $nama !== '') {
                DB::table('categories')->updateOrInsert(
                    ['nama' => $nama],
                    ['created_at' => $now, 'updated_at' => $now],
                );
            }
        }

        foreach (DB::table('assets')->select('lokasi')->distinct()->pluck('lokasi') as $nama) {
            if ($nama !== null && $nama !== '') {
                DB::table('locations')->updateOrInsert(
                    ['nama' => $nama],
                    ['created_at' => $now, 'updated_at' => $now],
                );
            }
        }

        foreach (DB::table('assets')->select('id', 'kategori', 'lokasi')->cursor() as $asset) {
            $categoryId = DB::table('categories')->where('nama', $asset->kategori)->value('id');
            $locationId = $asset->lokasi
                ? DB::table('locations')->where('nama', $asset->lokasi)->value('id')
                : null;

            DB::table('assets')->where('id', $asset->id)->update([
                'category_id' => $categoryId,
                'location_id' => $locationId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
