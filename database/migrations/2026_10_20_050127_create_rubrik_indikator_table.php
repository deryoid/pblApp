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
        Schema::create('rubrik_indikator', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kategori_id')->constrained('rubrik_kategori')->cascadeOnDelete();
            $table->string('kode', 50)->unique();
            $table->string('nama_indikator');
            $table->decimal('bobot', 5, 2)->default(100.00);
            $table->decimal('skala_min', 5, 2)->default(0.00);
            $table->decimal('skala_max', 5, 2)->default(100.00);
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubrik_indikator');
    }
};
