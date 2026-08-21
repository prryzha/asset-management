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
        Schema::create('institution_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('nama_instansi');
            $table->string('nama_singkat', 50)->nullable();
            $table->string('alamat', 500)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_profiles');
    }
};
