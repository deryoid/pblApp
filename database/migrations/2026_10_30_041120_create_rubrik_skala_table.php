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
        Schema::create('rubrik_skala', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubrik_kategori_id')->constrained('rubrik_kategori')->cascadeOnDelete();
            $table->string('nilai_huruf', 10); // A, B+, B, C+, C, D+, D, E
            $table->string('rentang_nilai', 50); // 80 – 100, 75 – 79, etc.
            $table->decimal('nilai_min', 5, 2);
            $table->decimal('nilai_max', 5, 2);
            $table->text('deskripsi');
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->timestamps();

            $table->unique(['rubrik_kategori_id', 'nilai_huruf'], 'rubrik_kat_huruf_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubrik_skala');
    }
};
