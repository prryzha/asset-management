<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', fn(Blueprint $t) => $t->softDeletes());
        Schema::table('categories', fn(Blueprint $t) => $t->softDeletes());
        Schema::table('locations', fn(Blueprint $t) => $t->softDeletes());
        Schema::table('transactions', fn(Blueprint $t) => $t->softDeletes());
        Schema::table('maintenance_schedules', fn(Blueprint $t) => $t->softDeletes());
        Schema::table('activity_logs', fn(Blueprint $t) => $t->softDeletes());
        Schema::table('asset_logs', fn(Blueprint $t) => $t->softDeletes());
    }

    public function down(): void
    {
        Schema::table('assets', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('categories', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('locations', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('transactions', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('maintenance_schedules', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('activity_logs', fn(Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('asset_logs', fn(Blueprint $t) => $t->dropSoftDeletes());
    }
};
