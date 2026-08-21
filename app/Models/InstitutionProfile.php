<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionProfile extends Model
{
    protected $fillable = [
        'logo',
        'nama_instansi',
        'nama_singkat',
        'alamat',
        'telepon',
        'email',
        'website',
        'deskripsi',
    ];

    /**
     * Data instansi bersifat global (satu baris untuk seluruh aplikasi,
     * bukan per user) — firstOrNew (bukan firstOrCreate) supaya belum ada
     * baris tersimpan di DB sampai admin benar-benar menekan Simpan lewat
     * InstitutionProfileController::update().
     */
    public static function current(): self
    {
        return static::firstOrNew(['id' => 1]);
    }
}
