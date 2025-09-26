<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If tables already created by earlier migration, skip to avoid duplicates
        if (Schema::hasTable('evaluasi_sesi')) {
            return;
        }
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

    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_sesi');
    }
};
