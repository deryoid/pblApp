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
        if (Schema::hasTable('evaluasi_sesi_indikator')) {
            return;
        }

        Schema::create('evaluasi_sesi_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('evaluasi_master')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('evaluasi_indikator')->cascadeOnDelete();
            $table->integer('bobot')->default(100);
            $table->integer('skor')->default(0);
            $table->text('komentar')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->unique(['sesi_id', 'indikator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_sesi_indikator');
    }
};
