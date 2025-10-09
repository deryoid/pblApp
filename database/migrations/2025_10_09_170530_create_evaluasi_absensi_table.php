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
        if (Schema::hasTable('evaluasi_absensi')) {
            return;
        }

        Schema::create('evaluasi_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('evaluasi_master')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->string('status'); // 'Hadir', 'Terlambat', 'Sakit', 'Dispensasi', 'Alpa'
            $table->datetime('waktu_absen')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['sesi_id', 'mahasiswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_absensi');
    }
};
