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
        if (Schema::hasTable('evaluasi_indikator')) {
            return;
        }

        Schema::create('evaluasi_indikator', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // e.g., 'd_hasil', 'm_kehadiran', etc.
            $table->string('nama'); // e.g., 'Kualitas Hasil', 'Kehadiran', etc.
            $table->text('deskripsi')->nullable();
            $table->string('tipe'); // 'dosen' or 'mitra'
            $table->integer('bobot')->default(100); // default bobot
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_indikator');
    }
};
