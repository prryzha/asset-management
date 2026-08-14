<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sederhanakan dari 3 role (super_admin/admin/staff) jadi 2 (admin/staff).
     * super_admin -> admin (Kepala Sekolah), admin lama -> staff (Sarpras/TU).
     * Role staff lama (guru self-service) dihapus total, bukan di-rename.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'staff')->delete();

        DB::table('users')->where('role', 'admin')->update(['role' => '__tmp_staff__']);
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('users')->where('role', '__tmp_staff__')->update(['role' => 'staff']);

        DB::table('users')->where('email', 'superadmin@gmail.com')->update(['name' => 'Kepala Sekolah']);
        DB::table('users')->where('email', 'admin@gmail.com')->update(['name' => 'Staf Sarpras']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'admin')->update(['role' => '__tmp_super__']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'admin']);
        DB::table('users')->where('role', '__tmp_super__')->update(['role' => 'super_admin']);

        DB::table('users')->where('email', 'superadmin@gmail.com')->update(['name' => 'Super Admin']);
        DB::table('users')->where('email', 'admin@gmail.com')->update(['name' => 'Admin TU']);
    }
};
