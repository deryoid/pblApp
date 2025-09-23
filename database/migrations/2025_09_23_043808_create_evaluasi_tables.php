<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_sesi', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();

            // relasi utama
            $t->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $t->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();

            // evaluator (users)
            $t->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();

            // penjadwalan
            $t->dateTime('jadwal_mulai')->nullable();
            $t->dateTime('jadwal_selesai')->nullable();
            $t->string('lokasi', 150)->nullable();

            // status
            $t->enum('status', [
                'Belum dijadwalkan', 'Terjadwal', 'Berlangsung', 'Selesai', 'Dibatalkan'
            ])->default('Belum dijadwalkan');

            $t->text('catatan')->nullable();

            // audit
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $t->timestamps();

            // bila 1 kelompok hanya boleh 1 sesi per periode, pertahankan unique ini
            $t->unique(['periode_id', 'kelompok_id']);

            // index bantu
            $t->index(['periode_id', 'status', 'jadwal_mulai']);
            $t->index('evaluator_id');
        });

        Schema::create('evaluasi_absensi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sesi_id')->constrained('evaluasi_sesi')->cascadeOnDelete();
            $t->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();

            $t->enum('status', ['Hadir', 'Terlambat', 'Sakit', 'Dispensasi', 'Alpa'])->default('Hadir');
            $t->dateTime('waktu_absen')->nullable();
            $t->string('keterangan', 255)->nullable();

            $t->timestamps();

            $t->unique(['sesi_id', 'mahasiswa_id']);
            $t->index(['sesi_id', 'status']);
        });

        Schema::create('evaluasi_indikator', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();

            $t->string('kode', 20)->unique();   // mis: d_hasil, d_teknis, m_kehadiran, m_presentasi
            $t->string('nama', 100);
            $t->text('deskripsi')->nullable();

            $t->unsignedTinyInteger('skala_default')->default(100);   // range skor default
            $t->unsignedTinyInteger('bobot_default')->default(0);     // bobot default
            $t->boolean('aktif')->default(true);

            $t->timestamps();
        });

        Schema::create('evaluasi_sesi_indikator', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sesi_id')->constrained('evaluasi_sesi')->cascadeOnDelete();
            $t->foreignId('indikator_id')->constrained('evaluasi_indikator')->cascadeOnDelete();

            $t->unsignedTinyInteger('bobot')->default(0);   // bobot yang dipakai di sesi ini
            $t->unsignedInteger('urutan')->default(0);

            // ⬇⬇ penting: nilai set DOSEN per-indikator (per sesi)
            $t->unsignedTinyInteger('skor')->default(0);    // 0..100
            $t->text('komentar')->nullable();

            $t->timestamps();

            $t->unique(['sesi_id', 'indikator_id']);
        });

        Schema::create('evaluasi_nilai_detail', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sesi_id')->constrained('evaluasi_sesi')->cascadeOnDelete();
            $t->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $t->foreignId('indikator_id')->constrained('evaluasi_indikator')->cascadeOnDelete();

            // ⬇⬇ nilai per-mahasiswa (misal set MITRA: m_kehadiran, m_presentasi)
            $t->unsignedTinyInteger('skor')->default(0); // 0..100
            $t->text('komentar')->nullable();

            $t->timestamps();

            $t->unique(['sesi_id', 'mahasiswa_id', 'indikator_id']);
            $t->index(['sesi_id', 'mahasiswa_id']);
        });

        Schema::create('evaluasi_settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('value');
            $t->timestamps();
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
