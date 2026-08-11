<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_new', 20)->default('staff')->after('password');
        });

        DB::table('users')->update([
            'role_new' => DB::raw("CASE WHEN role = 'kepala_sekolah' THEN 'staff' ELSE role END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_new', 'role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_old', 20)->default('kepala_sekolah')->after('password');
        });

        DB::table('users')->update([
            'role_old' => DB::raw("CASE WHEN role = 'staff' THEN 'kepala_sekolah' WHEN role = 'super_admin' THEN 'admin' ELSE role END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_old', 'role');
        });
    }
};
