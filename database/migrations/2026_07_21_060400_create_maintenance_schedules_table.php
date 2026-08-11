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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis_perawatan');
            $table->date('tanggal_jadwal');
            $table->date('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['Dijadwalkan', 'Dikerjakan', 'Selesai', 'Dibatalkan'])->default('Dijadwalkan');
            $table->timestamps();
            $table->index(['status', 'tanggal_jadwal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
