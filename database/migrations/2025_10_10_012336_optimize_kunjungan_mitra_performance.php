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
        Schema::table('kunjungan_mitra', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['periode_id', 'kelompok_id']);
            $table->index(['tanggal_kunjungan']);
            $table->index('perusahaan');
        });

        Schema::table('kelompok_mahasiswa', function (Blueprint $table) {
            $table->index(['mahasiswa_id', 'periode_id']);
            $table->index(['kelompok_id', 'periode_id']);
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungan_mitra', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['periode_id', 'kelompok_id']);
            $table->dropIndex(['tanggal_kunjungan']);
            $table->dropIndex('perusahaan');
        });

        Schema::table('kelompok_mahasiswa', function (Blueprint $table) {
            $table->dropIndex(['mahasiswa_id', 'periode_id']);
            $table->dropIndex(['kelompok_id', 'periode_id']);
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropIndex('user_id');
        });
    }
};
