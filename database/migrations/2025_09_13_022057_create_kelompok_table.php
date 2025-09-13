<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('kelompok', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete(); // singular
        $table->string('nama_kelompok'); // jika kosong saat input, akan diisi otomatis "Kelompok {n}"
        $table->unique(['periode_id','nama_kelompok']); // unik per-periode
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok');
    }
};
