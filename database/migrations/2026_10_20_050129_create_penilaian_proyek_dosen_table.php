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
        Schema::create('penilaian_proyek_dosen', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_card_id')->constrained('project_cards')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->nullable()->constrained('mata_kuliah')->nullOnDelete();
            $table->foreignId('rubrik_kategori_id')->constrained('rubrik_kategori')->cascadeOnDelete();
            $table->foreignId('rubrik_indikator_id')->nullable()->constrained('rubrik_indikator')->nullOnDelete();
            $table->decimal('nilai', 5, 2);
            $table->text('catatan_umpan_balik')->nullable();
            $table->timestamps();

            $table->unique(
                ['project_card_id', 'dosen_id', 'mata_kuliah_id', 'rubrik_kategori_id', 'rubrik_indikator_id'],
                'penilaian_dosen_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_proyek_dosen');
    }
};
