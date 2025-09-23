<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_sesi', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('jadwal_mulai')->nullable();
            $table->dateTime('jadwal_selesai')->nullable();
            $table->string('lokasi', 150)->nullable();
            $table->string('status', 50)->default('Belum dijadwalkan');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['periode_id','kelompok_id']);
        });

        Schema::create('evaluasi_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('evaluasi_sesi')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->enum('status', ['Hadir','Izin','Terlambat','Alpa'])->default('Hadir');
            $table->dateTime('waktu_absen')->nullable();
            $table->string('keterangan',255)->nullable();
            $table->timestamps();

            $table->unique(['sesi_id','mahasiswa_id']);
        });

        Schema::create('evaluasi_indikator', function (Blueprint $table) {
            $table->id();
            $table->string('kode',50)->unique();
            $table->string('nama',150);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluasi_sesi_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('evaluasi_sesi')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('evaluasi_indikator')->cascadeOnDelete();
            $table->integer('bobot')->default(0);
            $table->integer('skor')->default(0);
            $table->integer('urutan')->default(0);
            $table->text('komentar')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluasi_nilai_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('evaluasi_sesi')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('evaluasi_indikator')->cascadeOnDelete();
            $table->integer('skor')->default(0);
            $table->text('komentar')->nullable();
            $table->timestamps();
            $table->unique(['sesi_id','mahasiswa_id','indikator_id']);
        });

        Schema::create('evaluasi_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_settings');
        Schema::dropIfExists('evaluasi_nilai_detail');
        Schema::dropIfExists('evaluasi_sesi_indikator');
        Schema::dropIfExists('evaluasi_indikator');
        Schema::dropIfExists('evaluasi_absensi');
        Schema::dropIfExists('evaluasi_sesi');
    }
};
