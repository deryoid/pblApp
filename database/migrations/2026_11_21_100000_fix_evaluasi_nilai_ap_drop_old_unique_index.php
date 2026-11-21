<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // 1. Ensure foreign key index on evaluasi_master_id exists so MySQL error 1553 does not prevent dropping evaluasi_nilai_ap_unique
            $existingMasterIndexes = collect(DB::select("SHOW INDEX FROM evaluasi_nilai_ap WHERE Column_name = 'evaluasi_master_id' AND Key_name != 'evaluasi_nilai_ap_unique'"));
            if ($existingMasterIndexes->isEmpty()) {
                try {
                    DB::statement('ALTER TABLE evaluasi_nilai_ap ADD INDEX evaluasi_nilai_ap_evaluasi_master_id_idx (evaluasi_master_id)');
                } catch (\Throwable $e) {
                    // Ignore if already exists
                }
            }

            // 2. Drop old evaluasi_nilai_ap_unique index if it still exists
            $hasOldUnique = collect(DB::select("SHOW INDEX FROM evaluasi_nilai_ap WHERE Key_name = 'evaluasi_nilai_ap_unique'"))->isNotEmpty();
            if ($hasOldUnique) {
                try {
                    DB::statement('ALTER TABLE evaluasi_nilai_ap DROP INDEX evaluasi_nilai_ap_unique');
                } catch (\Throwable $e) {
                    // Ignore or log
                }
            }

            // 3. Ensure composite unique constraint on (evaluasi_master_id, mahasiswa_id, aktivitas_list_id, evaluator_id) exists
            $hasEvaluatorUnique = collect(DB::select("SHOW INDEX FROM evaluasi_nilai_ap WHERE Key_name = 'evaluasi_nilai_ap_evaluator_unique'"))->isNotEmpty();
            if (! $hasEvaluatorUnique) {
                try {
                    Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
                        $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'aktivitas_list_id', 'evaluator_id'], 'evaluasi_nilai_ap_evaluator_unique');
                    });
                } catch (\Throwable $e) {
                    // Ignore if already created
                }
            }
        } elseif ($driver === 'sqlite') {
            try {
                DB::statement('DROP INDEX IF EXISTS evaluasi_nilai_ap_unique');
            } catch (\Throwable $e) {
                // Ignore
            }

            try {
                Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
                    $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'aktivitas_list_id', 'evaluator_id'], 'evaluasi_nilai_ap_evaluator_unique');
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        } else {
            try {
                Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
                    $table->dropUnique('evaluasi_nilai_ap_unique');
                });
            } catch (\Throwable $e) {
                // Ignore
            }

            try {
                Schema::table('evaluasi_nilai_ap', function (Blueprint $table) {
                    $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'aktivitas_list_id', 'evaluator_id'], 'evaluasi_nilai_ap_evaluator_unique');
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal is omitted as reverting to a single-evaluator unique index would break multi-lecturer cumulative evaluation
    }
};
