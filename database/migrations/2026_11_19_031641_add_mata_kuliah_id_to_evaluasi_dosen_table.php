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
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluasi_dosen', 'mata_kuliah_id')) {
                $table->foreignId('mata_kuliah_id')->nullable()->after('project_card_id')->constrained('mata_kuliah')->nullOnDelete();
            }
        });

        // Drop the old unique constraint that prevented multiple course evaluations per project
        try {
            Schema::table('evaluasi_dosen', function (Blueprint $table) {
                $table->dropUnique('unique_evaluasi_per_mahasiswa_project');
            });
        } catch (\Throwable $e) {
            // Safe fallback if index was already dropped or unsupported
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluasi_dosen', function (Blueprint $table) {
            if (Schema::hasColumn('evaluasi_dosen', 'mata_kuliah_id')) {
                $table->dropForeign(['mata_kuliah_id']);
                $table->dropColumn('mata_kuliah_id');
            }
        });
    }
};
