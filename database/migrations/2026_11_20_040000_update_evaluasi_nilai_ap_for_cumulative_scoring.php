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
        // 1. Drop old unique constraint on evaluasi_nilai_ap if it exists
        if (DB::getDriverName() === 'sqlite') {
            try {
                DB::statement('DROP INDEX IF EXISTS evaluasi_nilai_ap_unique');
            } catch (\Throwable $e) {
                // Ignore
            }
        } else {
            try {
                DB::statement('ALTER TABLE evaluasi_nilai_ap DROP INDEX evaluasi_nilai_ap_unique');
            } catch (\Throwable $e) {
                // Already dropped or does not exist
            }
        }

        // 2. Add mata_kuliah_id and catatan_presentasi if not exists
        Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluasi_nilai_ap', 'mata_kuliah_id')) {
                $table->foreignId('mata_kuliah_id')->nullable()->after('evaluator_id')->constrained('mata_kuliah')->nullOnDelete();
            }
            if (! Schema::hasColumn('evaluasi_nilai_ap', 'catatan_presentasi')) {
                $table->text('catatan_presentasi')->nullable()->after('w_ap_presentasi');
            }
        });

        // 3. Add composite unique constraint per evaluator, session, student, and activity
        try {
            Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
                $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'aktivitas_list_id', 'evaluator_id'], 'evaluasi_nilai_ap_evaluator_unique');
            });
        } catch (\Throwable $e) {
            //
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
                $table->dropUnique('evaluasi_nilai_ap_evaluator_unique');
            });
        } catch (\Throwable $e) {
            //
        }

        Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
            if (Schema::hasColumn('evaluasi_nilai_ap', 'mata_kuliah_id')) {
                $table->dropForeign(['mata_kuliah_id']);
                $table->dropColumn('mata_kuliah_id');
            }
            if (Schema::hasColumn('evaluasi_nilai_ap', 'catatan_presentasi')) {
                $table->dropColumn('catatan_presentasi');
            }
        });

        try {
            Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
                $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'aktivitas_list_id'], 'evaluasi_nilai_ap_unique');
            });
        } catch (\Throwable $e) {
            //
        }
    }
};
