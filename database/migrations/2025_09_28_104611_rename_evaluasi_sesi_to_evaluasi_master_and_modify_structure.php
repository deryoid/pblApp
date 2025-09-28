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
        // Drop foreign key constraints from dependent tables
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            $table->dropForeign(['evaluasi_sesi_id']);
        });

        if (Schema::hasTable('evaluasi_absensi')) {
            Schema::table('evaluasi_absensi', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        if (Schema::hasTable('evaluasi_sesi_indikator')) {
            Schema::table('evaluasi_sesi_indikator', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        if (Schema::hasTable('evaluasi_nilai_detail')) {
            Schema::table('evaluasi_nilai_detail', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        if (Schema::hasTable('evaluasi_proyek_nilai')) {
            Schema::table('evaluasi_proyek_nilai', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        // Rename the table
        Schema::rename('evaluasi_sesi', 'evaluasi_master');

        // Remove unwanted columns from evaluasi_master
        // First drop foreign key constraint for evaluator_id if it exists
        try {
            Schema::table('evaluasi_master', function (Blueprint $table) {
                $table->dropForeign('evaluasi_sesi_evaluator_id_foreign');
            });
        } catch (\Exception $e) {
            // Foreign key might not exist, continue
        }

        Schema::table('evaluasi_master', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('evaluasi_master', 'evaluator_id')) {
                $columnsToDrop[] = 'evaluator_id';
            }
            if (Schema::hasColumn('evaluasi_master', 'jadwal_mulai')) {
                $columnsToDrop[] = 'jadwal_mulai';
            }
            if (Schema::hasColumn('evaluasi_master', 'jadwal_selesai')) {
                $columnsToDrop[] = 'jadwal_selesai';
            }
            if (Schema::hasColumn('evaluasi_master', 'lokasi')) {
                $columnsToDrop[] = 'lokasi';
            }
            if (Schema::hasColumn('evaluasi_master', 'status')) {
                $columnsToDrop[] = 'status';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Recreate foreign key constraints with new table name
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            $table->foreign('evaluasi_sesi_id')->references('id')->on('evaluasi_master')->onDelete('cascade');
        });

        if (Schema::hasTable('evaluasi_absensi')) {
            Schema::table('evaluasi_absensi', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_master')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('evaluasi_sesi_indikator')) {
            Schema::table('evaluasi_sesi_indikator', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_master')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('evaluasi_nilai_detail')) {
            Schema::table('evaluasi_nilai_detail', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_master')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('evaluasi_proyek_nilai')) {
            Schema::table('evaluasi_proyek_nilai', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_master')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints again
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            $table->dropForeign(['evaluasi_sesi_id']);
        });

        if (Schema::hasTable('evaluasi_absensi')) {
            Schema::table('evaluasi_absensi', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        if (Schema::hasTable('evaluasi_sesi_indikator')) {
            Schema::table('evaluasi_sesi_indikator', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        if (Schema::hasTable('evaluasi_nilai_detail')) {
            Schema::table('evaluasi_nilai_detail', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        if (Schema::hasTable('evaluasi_proyek_nilai')) {
            Schema::table('evaluasi_proyek_nilai', function (Blueprint $table) {
                $table->dropForeign(['sesi_id']);
            });
        }

        // Rename back to original name
        Schema::rename('evaluasi_master', 'evaluasi_sesi');

        // Add back the removed columns
        Schema::table('evaluasi_sesi', function (Blueprint $table) {
            $table->unsignedBigInteger('evaluator_id')->nullable();
            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('set null');
            $table->dateTime('jadwal_mulai')->nullable();
            $table->dateTime('jadwal_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('status')->default('Belum dijadwalkan');
        });

        // Recreate foreign key constraints with original table name
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            $table->foreign('evaluasi_sesi_id')->references('id')->on('evaluasi_sesi')->onDelete('cascade');
        });

        if (Schema::hasTable('evaluasi_absensi')) {
            Schema::table('evaluasi_absensi', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_sesi')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('evaluasi_sesi_indikator')) {
            Schema::table('evaluasi_sesi_indikator', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_sesi')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('evaluasi_nilai_detail')) {
            Schema::table('evaluasi_nilai_detail', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_sesi')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('evaluasi_proyek_nilai')) {
            Schema::table('evaluasi_proyek_nilai', function (Blueprint $table) {
                $table->foreign('sesi_id')->references('id')->on('evaluasi_sesi')->onDelete('cascade');
            });
        }
    }
};
