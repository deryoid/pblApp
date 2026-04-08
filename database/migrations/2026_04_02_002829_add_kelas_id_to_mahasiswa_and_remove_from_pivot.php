<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: This migration is now a no-op because the changes have been
     * incorporated into the initial migrations:
     * - kelas_id is now in mahasiswa table from the start
     * - kelompok_mahasiswa pivot never had kelas_id in new installations
     *
     * This migration is kept for backward compatibility with databases
     * that may have already applied it.
     */
    public function up(): void
    {
        // Changes already applied in initial migrations
        // This migration does nothing on fresh installs
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op - changes are in initial migrations
    }
};
