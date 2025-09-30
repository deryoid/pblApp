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
        Schema::create('evaluasi_nilai_ap', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('kelompok_id')->constrained('kelompok')->cascadeOnDelete();
            $table->foreignId('evaluasi_master_id')->constrained('evaluasi_master')->cascadeOnDelete();
            $table->foreignId('aktivitas_list_id')->nullable()->constrained('aktivitas_lists')->nullOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('w_ap_kehadiran')->nullable();
            $table->decimal('w_ap_presentasi', 5, 2)->nullable();
            $table->date('tanggal_hadir')->nullable();
            $table->string('status')->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evaluasi_master_id', 'mahasiswa_id', 'aktivitas_list_id'], 'evaluasi_nilai_ap_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_nilai_ap');
    }
};
