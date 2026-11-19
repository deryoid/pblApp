<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah semua user dengan role 'evaluator' menjadi 'dosen'
        DB::table('users')->where('role', 'evaluator')->update(['role' => 'dosen']);

        // 2. Modifikasi kolom ENUM pada tabel users menjadi hanya 3 role resmi: 'admin', 'dosen', 'mahasiswa'
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'dosen', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'dosen', 'evaluator', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa'");
        }
    }
};
