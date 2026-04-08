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
        if (Schema::hasTable('evaluasi_nilai_detail')) {
            return;
        }

        Schema::create('evaluasi_nilai_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('evaluasi_master')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('indikator_id')->nullable()->constrained('evaluasi_indikator')->nullOnDelete();
            $table->integer('skor')->default(0);
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->unique(['sesi_id', 'mahasiswa_id', 'indikator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_nilai_detail');
    }
};
