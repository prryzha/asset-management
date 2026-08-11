<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // All performance indexes already created via direct SQL.
        // This migration exists only for tracking/reference.
    }

    public function down(): void
    {
        // Intentionally empty to prevent accidental index drops
    }
};
