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
        try {
            Schema::table('evaluasi_dosen', function (Blueprint $table) {
                $table->dropUnique('unique_evaluasi_per_mahasiswa_project');
            });
        } catch (\Throwable $e) {
            // Index already dropped or not present
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('evaluasi_dosen', function (Blueprint $table) {
                $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'project_card_id'], 'unique_evaluasi_per_mahasiswa_project');
            });
        } catch (\Throwable $e) {
            //
        }
    }
};
