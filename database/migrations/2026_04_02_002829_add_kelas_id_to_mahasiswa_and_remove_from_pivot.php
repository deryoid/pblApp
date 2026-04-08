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
     * Moves kelas_id from kelompok_mahasiswa pivot to mahasiswa table.
     * Migrates existing data: takes the kelas_id from the most recent pivot
     * record for each mahasiswa.
     */
    public function up(): void
    {
        // 1. Add nullable kelas_id to mahasiswa (skip if already exists)
        if (! Schema::hasColumn('mahasiswa', 'kelas_id')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->foreignId('kelas_id')->nullable()->after('user_id')->constrained('kelas')->nullOnDelete();
            });
        }

        // 2. Migrate existing data: fill mahasiswa.kelas_id from pivot (latest entry per mahasiswa)
        if (Schema::hasColumn('kelompok_mahasiswa', 'kelas_id')) {
            DB::statement('
                UPDATE mahasiswa m
                INNER JOIN kelompok_mahasiswa km
                    ON km.mahasiswa_id = m.id
                    AND km.id = (
                        SELECT MAX(id) FROM kelompok_mahasiswa
                        WHERE mahasiswa_id = m.id
                        AND kelas_id IS NOT NULL
                    )
                SET m.kelas_id = km.kelas_id
                WHERE m.kelas_id IS NULL
            ');

            // 3. Drop kelas_id column from pivot table
            Schema::table('kelompok_mahasiswa', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add kelas_id to pivot
        Schema::table('kelompok_mahasiswa', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->after('periode_id')->constrained('kelas')->nullOnDelete();
        });

        // 2. Copy data back from mahasiswa to pivot
        DB::statement('
            UPDATE kelompok_mahasiswa km
            INNER JOIN mahasiswa m ON m.id = km.mahasiswa_id
            SET km.kelas_id = m.kelas_id
            WHERE m.kelas_id IS NOT NULL
        ');

        // 3. Drop kelas_id from mahasiswa
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn('kelas_id');
        });
    }
};
