<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            'assets'               => ['status', 'kondisi', 'nama_barang'],
            'transactions'         => ['status_peminjaman'],
            'activity_logs'        => ['event'],
            'maintenance_schedules'=> ['created_by'],
            'categories'           => ['nama'],
            'locations'            => ['nama'],
        ];

        foreach ($indexes as $table => $columns) {
            foreach ($columns as $column) {
                try {
                    Schema::table($table, fn(Blueprint $t) => $t->index($column));
                } catch (\Exception $e) {
                    // Index already exists — safe to skip
                }
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'assets'               => ['status', 'kondisi', 'nama_barang'],
            'transactions'         => ['status_peminjaman'],
            'activity_logs'        => ['event'],
            'maintenance_schedules'=> ['created_by'],
            'categories'           => ['nama'],
            'locations'            => ['nama'],
        ];

        foreach ($indexes as $table => $columns) {
            foreach ($columns as $column) {
                $indexName = $table . '_' . $column . '_index';
                try {
                    Schema::table($table, fn(Blueprint $t) => $t->dropIndex($indexName));
                } catch (\Exception $e) {
                    // Index may not exist — safe to skip
                }
            }
        }
    }
};
